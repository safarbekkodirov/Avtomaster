<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Entity\Review;
use App\Entity\NotificationBuilder;
use App\Repository\BookingRepository;
use App\Repository\MasterRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/bookings/{bookingId}/review', methods: ['POST'])]
class ReviewCreateAction extends AbstractController
{
    public function __invoke(
        int $bookingId,
        Request $request,
        BookingRepository $bookingRepository,
        ReviewRepository $reviewRepository,
        EntityManagerInterface $em,
    ): Response {
        $booking = $bookingRepository->find($bookingId);
        if (!$booking) {
            throw new NotFoundHttpException('Booking not found');
        }

        if ($booking->getStatus() !== 'completed') {
            throw new BadRequestHttpException('Reviews can only be left for completed bookings');
        }

        if ($booking->getClient()?->getId() !== $this->getUser()->getId()) {
            throw new BadRequestHttpException('You can only review your own bookings');
        }

        $existingReview = $reviewRepository->findOneBy(['booking' => $booking]);
        if ($existingReview) {
            throw new BadRequestHttpException('Review already exists for this booking');
        }

        $body = json_decode($request->getContent(), true);
        $rating  = $body['rating'] ?? null;
        $comment = $body['comment'] ?? null;

        if (!$rating || $rating < 1 || $rating > 5) {
            throw new BadRequestHttpException('Rating must be between 1 and 5');
        }

        $master = $booking->getMaster();

        $review = new Review();
        $review->setBooking($booking);
        $review->setMaster($master);
        $review->setClient($this->getUser());
        $review->setRating((int) $rating);
        $review->setComment($comment);
        $review->setCreatedAt(new DateTime());

        $em->persist($review);
        $em->flush();

        $avgRating = $reviewRepository->avgRatingByMaster($master);
        $reviewsCount = $reviewRepository->countByMaster($master);
        $master->setRating((string) $avgRating);
        $master->setReviewsCount($reviewsCount);

        $masterUser = $master->getUser();
        if ($masterUser) {
            $notification = (new NotificationBuilder())
                ->reviewReceived($booking, (int) $rating)
                ->build($masterUser);
            $em->persist($notification);
        }

        $em->flush();

        $serialized = $this->getSerializer()->serialize($review, 'jsonld', [
            'groups' => ['review:read'],
        ]);

        return new Response($serialized, Response::HTTP_CREATED, [
            'Content-Type' => 'application/ld+json',
        ]);
    }
}
