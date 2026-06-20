<?php

declare(strict_types=1);

namespace App\Controller;

use App\Component\Core\MarkEntityAsDeleted;
use App\Entity\Master;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MasterDeleteAction extends AbstractController
{
    public function __construct(
        private MarkEntityAsDeleted $markEntityAsDeleted,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Master $data): Response
    {
        $this->markEntityAsDeleted->mark($data, false);

        $user = $data->getUser();
        if ($user instanceof User) {
            $this->markEntityAsDeleted->mark($user, false);
        }

        $this->entityManager->flush();

        return $this->json('{}', Response::HTTP_NO_CONTENT);
    }
}
