<?php

declare(strict_types=1);

namespace App\Controller;

use App\Component\User\Exceptions\AuthException;
use App\Component\User\TokensCreator;
use App\Controller\Base\AbstractController;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserAuthAction extends AbstractController
{
    public function __invoke(
        User $data,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordEncoder,
        TokensCreator $tokensCreator,
    ): Response {
        $user = $userRepository->findOneByEmail($data->getEmail());

        if ($user === null) {
            $this->throwInvalidCredentials();
        }

        if (!$passwordEncoder->isPasswordValid($user, $data->getPassword())) {
            $this->throwInvalidCredentials();
        }

        $tokens = $tokensCreator->create($user);

        $response = [
            'accessToken' => $tokens->getAccessToken(),
            'expiresIn'   => 86400,
            'user' => [
                'id'        => $user->getId(),
                'email'     => $user->getEmail(),
                'roles'     => $user->getRoles(),
                'firstName' => $user->getFirstName(),
                'lastName'  => $user->getLastName(),
                'avatar'    => $user->getAvatar(),
            ],
        ];

        return new Response(
            json_encode($response),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    private function throwInvalidCredentials(): void
    {
        throw new AuthException('Invalid credentials');
    }
}
