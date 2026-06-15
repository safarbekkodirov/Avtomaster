<?php
// src/Domain/Auth/Service/AuthService.php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\DTO\AuthResponseDTO;
use App\Domain\Auth\DTO\LoginRequestDTO;
use App\Domain\Auth\DTO\RegisterRequestDTO;
use App\Domain\Auth\Entity\RefreshToken;
use App\Domain\Auth\Repository\RefreshTokenRepository;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\UserProfile;
use App\Domain\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AuthService
{
    // TTL refresh токена
    private const REFRESH_TOKEN_TTL = '+30 days';
    // Длина refresh токена в байтах перед кодированием
    private const TOKEN_BYTES       = 64;

    public function __construct(
        private readonly EntityManagerInterface      $em,
        private readonly UserRepository              $userRepository,
        private readonly RefreshTokenRepository      $refreshTokenRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface    $jwtManager,
    ) {}

    /**
     * Регистрация нового пользователя
     */
    public function register(RegisterRequestDTO $dto, Request $request): AuthResponseDTO
    {
        if ($this->userRepository->findByEmail($dto->email) !== null) {
            throw new \DomainException('Email уже занят');
        }

        $user = new User(
            email: $dto->email,
            roles: [$dto->role === 'master' ? 'ROLE_MASTER' : 'ROLE_CLIENT'],
        );
        $user->setPasswordHash(
            $this->passwordHasher->hashPassword($user, $dto->password)
        );

        $profile = new UserProfile(
            user:      $user,
            firstName: $dto->firstName,
            lastName:  $dto->lastName,
        );

        $this->em->persist($user);
        $this->em->persist($profile);
        $this->em->flush();

        return $this->issueTokens($user, $request, familyId: $this->newFamilyId());
    }

    /**
     * Логин — выдача access + refresh токенов
     */
    public function login(LoginRequestDTO $dto, Request $request): AuthResponseDTO
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if ($user === null || !$this->passwordHasher->isPasswordValid($user, $dto->password)) {
            // Одинаковое сообщение — не раскрываем, что именно неверно
            throw new \DomainException('Неверный email или пароль');
        }

        if (!$user->isActive()) {
            throw new \DomainException('Аккаунт заблокирован');
        }

        return $this->issueTokens($user, $request, familyId: $this->newFamilyId());
    }

    /**
     * Refresh rotation с reuse detection
     */
    public function refresh(string $rawToken, Request $request): AuthResponseDTO
    {
        $hash         = $this->hashToken($rawToken);
        $refreshToken = $this->refreshTokenRepository->findByTokenHash($hash);

        if ($refreshToken === null) {
            throw new \DomainException('Токен не найден');
        }

        // Reuse detection — токен уже использован → компрометация
        if ($refreshToken->isUsed()) {
            // Инвалидируем всю семью — все устройства этой сессии разлогинены
            $this->refreshTokenRepository->invalidateFamily($refreshToken->getFamilyId());
            $this->em->flush();

            throw new \DomainException('Токен уже использован. Требуется повторный вход.');
        }

        if ($refreshToken->isExpired()) {
            throw new \DomainException('Токен истёк');
        }

        $user = $refreshToken->getUser();

        if (!$user->isActive()) {
            throw new \DomainException('Аккаунт заблокирован');
        }

        // Помечаем текущий токен использованным
        $refreshToken->markAsUsed();
        $this->em->flush();

        // Выдаём новую пару, сохраняя familyId (та же сессия/устройство)
        return $this->issueTokens($user, $request, familyId: $refreshToken->getFamilyId());
    }

    /**
     * Logout — инвалидация конкретной сессии
     */
    public function logout(string $rawToken): void
    {
        $hash         = $this->hashToken($rawToken);
        $refreshToken = $this->refreshTokenRepository->findByTokenHash($hash);

        if ($refreshToken !== null) {
            $this->refreshTokenRepository->invalidateFamily($refreshToken->getFamilyId());
            $this->em->flush();
        }
    }

    /**
     * Logout everywhere — инвалидация всех сессий пользователя
     */
    public function logoutAll(User $user): void
    {
        $this->refreshTokenRepository->invalidateAllForUser($user->getId());
        $this->em->flush();
    }

    /**
     * Создание пары access + refresh токенов
     */
    private function issueTokens(User $user, Request $request, string $familyId): AuthResponseDTO
    {
        $accessToken  = $this->jwtManager->create($user);
        $rawRefresh   = $this->generateRawToken();

        $refreshToken = new RefreshToken(
            user:      $user,
            tokenHash: $this->hashToken($rawRefresh),
            familyId:  $familyId,
            expiresAt: new \DateTimeImmutable(self::REFRESH_TOKEN_TTL),
            ipAddress: $request->getClientIp(),
            userAgent: $request->headers->get('User-Agent'),
        );

        $this->em->persist($refreshToken);
        $this->em->flush();

        // Передаём raw токен через request attribute — контроллер положит его в httpOnly cookie
        $request->attributes->set('_refresh_raw', $rawRefresh);

        return new AuthResponseDTO(
            accessToken: $accessToken,
            expiresIn:   900, // 15 минут — должно совпадать с lexik jwt config
            user:        $this->serializeUser($user),
        );
    }

    private function generateRawToken(): string
    {
        return bin2hex(random_bytes(self::TOKEN_BYTES));
    }

    private function hashToken(string $rawToken): string
    {
        return hash('sha256', $rawToken);
    }

    private function newFamilyId(): string
    {
        return \Symfony\Component\Uid\Uuid::v4()->toRfc4122();
    }

    private function serializeUser(User $user): array
    {
        return [
            'id'         => $user->getId(),
            'email'      => $user->getEmail(),
            'roles'      => $user->getRoles(),
            'firstName'  => $user->getProfile()?->getFirstName(),
            'lastName'   => $user->getProfile()?->getLastName(),
            'avatar'     => $user->getProfile()?->getAvatar(),
        ];
    }
}
