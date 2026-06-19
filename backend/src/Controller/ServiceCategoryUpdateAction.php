<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Entity\ServiceCategory;
use App\Repository\ServiceCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ServiceCategoryUpdateAction extends AbstractController
{
    public function __invoke(
        ServiceCategory $data,
        Request $request,
        ServiceCategoryRepository $categoryRepository,
        EntityManagerInterface $em,
    ): ServiceCategory {
        $body = json_decode($request->getContent(), true);

        if (isset($body['name'])) {
            $data->setName($body['name']);
        }
        if (isset($body['slug'])) {
            $existing = $categoryRepository->findOneBySlug($body['slug']);
            if ($existing && $existing->getId() !== $data->getId()) {
                throw new BadRequestHttpException('Slug already taken');
            }
            $data->setSlug($body['slug']);
        }
        if (array_key_exists('description', $body)) {
            $data->setDescription($body['description']);
        }
        if (array_key_exists('icon', $body)) {
            $data->setIcon($body['icon']);
        }

        $em->flush();

        return $data;
    }
}
