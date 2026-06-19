<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Entity\Master;
use App\Repository\MasterRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MasterMyProfileAction extends AbstractController
{
    public function __invoke(
        MasterRepository $masterRepository,
    ): Master {
        $user = $this->getUser();
        $master = $masterRepository->findOneByUserId($user->getId());

        if (!$master) {
            throw new NotFoundHttpException('Master profile not found');
        }

        return $master;
    }
}
