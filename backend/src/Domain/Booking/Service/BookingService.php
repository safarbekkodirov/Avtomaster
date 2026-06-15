<?php
// src/Domain/Booking/Service/BookingService.php

namespace App\Domain\Booking\Service;

use App\Domain\Booking\DTO\BookingResponseDTO;
use App\Domain\Booking\DTO\CancelBookingRequestDTO;
use App\Domain\Booking\DTO\CreateBookingRequestDTO;
use App\Domain\Booking\Entity\Booking;
use App\Domain\Booking\Event\BookingCreatedEvent;
use App\Domain\Booking\Event\BookingStatusChangedEvent;
use App\Domain\Booking\Repository\BookingRepository;
use App\Domain\Master\Repository\MasterRepository;
use App\Domain\Master\Repository\MasterSlotRepository;
use App\Domain\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class BookingService
{
    public function __construct(
        private readonly EntityManagerInterface   $em,
        private readonly BookingRepository        $bookingRepository,
        private readonly MasterRepository         $masterRepository,
        private readonly MasterSlotRepository     $slotRepository,
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    public function create(CreateBookingRequestDTO $dto, User $client): BookingResponseDTO
    {
        return $this->em->wrapInTransaction(function () use ($dto, $client): BookingResponseDTO {
            $slot = $this->slotRepository->findWithLock($dto->slotId);

            if ($slot === null) {
                throw new \DomainException('Слот не найден');
            }

            if (!$slot->isAvailable()) {
                throw new \DomainException('Выбранное время уже занято');
            }

            $master = $this->masterRepository->find($dto->masterId);

            if ($master === null || !$master->isActive()) {
                throw new \DomainException('Мастер не найден');
            }

            if ($slot->getMaster()->getId() !== $master->getId()) {
                throw new \DomainException('Слот не принадлежит мастеру');
            }

            $service = $master->getServices()->filter(
                fn($s) => $s->getId() === $dto->serviceId && $s->isActive()
            )->first();

            if ($service === false) {
                throw new \DomainException('Услуга не найдена');
            }

            $slot->book();

            $booking = new Booking(
                client:  $client,
                master:  $master,
                slot:    $slot,
                service: $service,
                total:   (string) $service->getPrice(),
                notes:   $dto->notes,
            );

            $this->em->persist($booking);
            $this->em->flush();

            $this->dispatcher->dispatch(new BookingCreatedEvent($booking));

            return BookingResponseDTO::fromEntity($booking);
        });
    }

    public function confirm(int $bookingId, User $actor): BookingResponseDTO
    {
        return $this->em->wrapInTransaction(function () use ($bookingId, $actor): BookingResponseDTO {
            $booking = $this->bookingRepository->find($bookingId);

            if ($booking === null) {
                throw new \DomainException('Бронирование не найдено');
            }

            if (!$booking->canBeConfirmedBy($actor)) {
                throw new \DomainException('Нет прав для подтверждения');
            }

            $prevStatus = $booking->getStatus();
            $booking->confirm();
            $this->em->flush();

            $this->dispatcher->dispatch(
                new BookingStatusChangedEvent($booking, $prevStatus)
            );

            return BookingResponseDTO::fromEntity($booking);
        });
    }

    public function complete(int $bookingId, User $actor): BookingResponseDTO
    {
        return $this->em->wrapInTransaction(function () use ($bookingId, $actor): BookingResponseDTO {
            $booking = $this->bookingRepository->find($bookingId);

            if ($booking === null) {
                throw new \DomainException('Бронирование не найдено');
            }

            if (!$booking->canBeCompletedBy($actor)) {
                throw new \DomainException('Нет прав для завершения');
            }

            $prevStatus = $booking->getStatus();
            $booking->complete();
            $this->em->flush();

            $this->dispatcher->dispatch(
                new BookingStatusChangedEvent($booking, $prevStatus)
            );

            return BookingResponseDTO::fromEntity($booking);
        });
    }

    public function cancel(
        int                    $bookingId,
        CancelBookingRequestDTO $dto,
        User                   $actor
    ): BookingResponseDTO {
        return $this->em->wrapInTransaction(function () use ($bookingId, $dto, $actor): BookingResponseDTO {
            $booking = $this->bookingRepository->findWithLock($bookingId);

            if ($booking === null) {
                throw new \DomainException('Бронирование не найдено');
            }

            if (!$booking->canBeCancelledBy($actor)) {
                throw new \DomainException('Нет прав для отмены или отмена недоступна');
            }

            $prevStatus = $booking->getStatus();
            $booking->cancel($dto->reason ?? '');

            if ($prevStatus === Booking::STATUS_PENDING) {
                $booking->getSlot()->release();
            }

            $this->em->flush();

            $this->dispatcher->dispatch(
                new BookingStatusChangedEvent($booking, $prevStatus)
            );

            return BookingResponseDTO::fromEntity($booking);
        });
    }

    /**
     * @return array{items: BookingResponseDTO[], total: int, page: int, perPage: int}
     */
    public function getClientBookings(User $client, int $page, int $perPage): array
    {
        $result = $this->bookingRepository->findByClient($client->getId(), $page, $perPage);

        return [
            'items'   => array_map(BookingResponseDTO::fromEntity(...), $result['items']),
            'total'   => $result['total'],
            'page'    => $page,
            'perPage' => $perPage,
        ];
    }

    /**
     * @return array{items: BookingResponseDTO[], total: int, page: int, perPage: int}
     */
    public function getMasterBookings(
        User    $master,
        int     $page,
        int     $perPage,
        ?string $status = null
    ): array {
        $masterEntity = $this->masterRepository->findByUser($master->getId());

        if ($masterEntity === null) {
            throw new \DomainException('Профиль мастера не найден');
        }

        $result = $this->bookingRepository->findByMaster(
            $masterEntity->getId(),
            $page,
            $perPage,
            $status
        );

        return [
            'items'   => array_map(BookingResponseDTO::fromEntity(...), $result['items']),
            'total'   => $result['total'],
            'page'    => $page,
            'perPage' => $perPage,
        ];
    }
}
