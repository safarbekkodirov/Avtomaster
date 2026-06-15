<?php
// src/Controller/Api/V1/Auth/AuthController.php

namespace App\Controller\Api\V1\Auth;

use App\Domain\Auth\DTO\LoginRequestDTO;
use App\Domain\Auth\DTO\RegisterRequestDTO;
use App\Domain\Auth\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Domain\User\Entity\User;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[Route('/api/v1/auth', name: 'api_v1_auth_')]
final class AuthController extends AbstractController
{
    private const REFRESH_COOKIE    = 'refresh_token';
    private const REFRESH_TTL_DAYS  = 30;

    public function __construct(
        private readonly AuthService        $authService,
        private readonly RateLimiterFactory $loginLimiter,
    ) {}

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] RegisterRequestDTO $dto,
        Request $request,
    ): JsonResponse {
        $result   = $this->authService->register($dto, $request);
        $response = $this->json([
            'accessToken' => $result->accessToken,
            'expiresIn'   => $result->expiresIn,
            'user'        => $result->user,
        ], Response::HTTP_CREATED);

        // Refresh token только в httpOnly cookie — недоступен из JS
        $response->headers->setCookie($this->makeRefreshCookie(
            $request->attributes->get('_refresh_raw')
        ));

        return $response;
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(
        #[MapRequestPayload] LoginRequestDTO $dto,
        Request $request,
    ): JsonResponse {
        // Rate limiting — 10 попыток в минуту по IP
        $limiter = $this->loginLimiter->create($request->getClientIp());
        if (!$limiter->consume()->isAccepted()) {
            return $this->json(['message' => 'Слишком много попыток'], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $result   = $this->authService->login($dto, $request);
        $response = $this->json([
            'accessToken' => $result->accessToken,
            'expiresIn'   => $result->expiresIn,
            'user'        => $result->user,
        ]);

        $response->headers->setCookie(
            $this->makeRefreshCookie($request->attributes->get('_refresh_raw'))
        );

        return $response;
    }

    #[Route('/refresh', name: 'refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        $rawToken = $request->cookies->get(self::REFRESH_COOKIE);

        if ($rawToken === null) {
            return $this->json(['message' => 'Refresh token не передан'], Response::HTTP_UNAUTHORIZED);
        }

        $result   = $this->authService->refresh($rawToken, $request);
        $response = $this->json([
            'accessToken' => $result->accessToken,
            'expiresIn'   => $result->expiresIn,
            'user'        => $result->user,
        ]);

        $response->headers->setCookie(
            $this->makeRefreshCookie($request->attributes->get('_refresh_raw'))
        );

        return $response;
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        $rawToken = $request->cookies->get(self::REFRESH_COOKIE);

        if ($rawToken !== null) {
            $this->authService->logout($rawToken);
        }

        $response = $this->json(['message' => 'Выход выполнен']);
        // Удаляем cookie
        $response->headers->clearCookie(self::REFRESH_COOKIE, '/', null, true, true);

        return $response;
    }

    #[Route('/logout-all', name: 'logout_all', methods: ['POST'])]
    public function logoutAll(#[CurrentUser] User $user, Request $request): JsonResponse
    {
        $this->authService->logoutAll($user);

        $response = $this->json(['message' => 'Выход со всех устройств выполнен']);
        $response->headers->clearCookie(self::REFRESH_COOKIE, '/', null, true, true);

        return $response;
    }

    private function makeRefreshCookie(string $rawToken): Cookie
    {
        return Cookie::create(self::REFRESH_COOKIE)
            ->withValue($rawToken)
            ->withExpires(new \DateTimeImmutable('+' . self::REFRESH_TTL_DAYS . ' days'))
            ->withPath('/')
            ->withSecure(true)       // только HTTPS
            ->withHttpOnly(true)     // недоступен из JS
            ->withSameSite('Strict');
    }
}
