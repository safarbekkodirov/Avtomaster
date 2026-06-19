<?php

declare(strict_types=1);

namespace App\Controller;

use App\Component\Master\MasterFactory;
use App\Component\Master\MasterManager;
use App\Component\User\UserFactory;
use App\Component\User\UserManager;
use App\Component\User\TokensCreator;
use App\Controller\Base\AbstractController;
use App\Entity\User;
use App\Repository\MasterRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserCreateAction extends AbstractController
{
    public function __invoke(
        Request $request,
        UserFactory $userFactory,
        UserManager $userManager,
        UserRepository $userRepository,
        TokensCreator $tokensCreator,
        MasterFactory $masterFactory,
        MasterManager $masterManager,
        MasterRepository $masterRepository,
    ): Response {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $firstName = $data['firstName'] ?? $data['first_name'] ?? null;
        $lastName = $data['lastName'] ?? $data['last_name'] ?? null;
        $roles = $data['roles'] ?? ['ROLE_USER'];

        if (!$email || !$password) {
            throw new BadRequestHttpException('Email and password are required');
        }

        if ($userRepository->findOneByEmail($email)) {
            throw new BadRequestHttpException('Email already taken');
        }

        $user = $userFactory->create($email, $password);

        if ($firstName) {
            $user->setFirstName($firstName);
        }
        if ($lastName) {
            $user->setLastName($lastName);
        }

        foreach ($roles as $role) {
            if (is_string($role)) {
                $user->addRole($role);
            }
        }

        $userManager->save($user, true);

        if (in_array('ROLE_MASTER', $roles, true)) {
            $master = $masterFactory->create($user, [
                'firstName'  => $firstName ?? '',
                'lastName'   => $lastName ?? '',
                'phone'      => $data['phone'] ?? null,
                'regionName' => $data['regionName'] ?? '',
                'address'    => $data['address'] ?? null,
            ]);
            $masterManager->save($master, true);
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
            Response::HTTP_CREATED,
            ['Content-Type' => 'application/json']
        );
    }
}
