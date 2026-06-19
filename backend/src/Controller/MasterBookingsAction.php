<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Repository\BookingRepository;
use App\Repository\MasterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MasterBookingsAction extends AbstractController
{
    public function __invoke(
        Request $request,
        MasterRepository $masterRepository,
        BookingRepository $bookingRepository,
        EntityManagerInterface $em,
        NormalizerInterface $normalizer,
    ): JsonResponse {
        $user = $this->getUser();
        $master = $masterRepository->findOneByUserId($user->getId());

        if (!$master) {
            return new JsonResponse(['error' => 'Master profile not found'], 404);
        }

        $status = $request->query->get('status');

        $qb = $em->createQueryBuilder()
            ->select('b')
            ->from('App\Entity\Booking', 'b')
            ->leftJoin('b.client', 'c')
            ->addSelect('c')
            ->leftJoin('b.service', 's')
            ->addSelect('s')
            ->where('b.master = :master')
            ->setParameter('master', $master)
            ->orderBy('b.createdAt', 'DESC');

        if ($status) {
            $qb->andWhere('b.status = :status')
                ->setParameter('status', $status);
        }

        $bookings = $qb->getQuery()->getResult();

        $normalized = $normalizer->normalize(
            $bookings,
            null,
            ['groups' => ['booking:read', 'booking:list']]
        );

        return new JsonResponse(['data' => $normalized]);
    }
}
