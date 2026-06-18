<?php

declare(strict_types=1);

namespace App\Controller;

use App\Component\Master\MasterFactory;
use App\Component\Master\MasterManager;
use App\Controller\Base\AbstractController;
use App\Entity\Master;
use App\Entity\User;
use App\Repository\MasterRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MasterCreateAction extends AbstractController
{
    public function __invoke(
        Master $data,
        MasterFactory $masterFactory,
        MasterManager $masterManager,
        MasterRepository $masterRepository,
    ): Master {
        $this->validate($data);

        $user = $this->getUser();

        if ($masterRepository->findOneByUserId($user->getId())) {
            throw new BadRequestHttpException('Master profile already exists for this user');
        }

        $master = $masterFactory->create($user, [
            'firstName'  => $data->getFirstName(),
            'lastName'   => $data->getLastName(),
            'phone'      => $data->getPhone(),
            'bio'        => $data->getBio(),
            'regionName' => $data->getRegionName(),
            'address'    => $data->getAddress(),
            'lat'        => $data->getLat(),
            'lng'        => $data->getLng(),
        ]);

        $masterManager->save($master, true);

        return $master;
    }
}
