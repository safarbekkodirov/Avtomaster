<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Entity\MasterService;
use App\Repository\MasterRepository;
use App\Repository\ServiceCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MasterServiceCreateAction extends AbstractController
{
    public function __invoke(
        int $id,
        Request $request,
        MasterRepository $masterRepository,
        ServiceCategoryRepository $categoryRepository,
        EntityManagerInterface $em,
    ): MasterService {
        $user = $this->getUser();
        $master = $masterRepository->findOneByUserId($user->getId());

        if (!$master || $master->getId() !== $id) {
            throw new NotFoundHttpException('Master profile not found');
        }

        $data = json_decode($request->getContent(), true);

        $name = $data['name'] ?? '';
        $price = $data['price'] ?? null;
        $durationMinutes = $data['durationMinutes'] ?? null;

        if (!$name || $price === null || !$durationMinutes) {
            throw new BadRequestHttpException('name, price, durationMinutes are required');
        }

        $service = new MasterService();
        $service->setMaster($master);
        $service->setName($name);
        $service->setPrice((string) $price);
        $service->setDurationMinutes((int) $durationMinutes);

        if (!empty($data['categoryId'])) {
            $category = $categoryRepository->find($data['categoryId']);
            if ($category) {
                $service->setCategory($category);
            }
        }

        $em->persist($service);
        $em->flush();

        return $service;
    }
}
