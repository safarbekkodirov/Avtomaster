<?php
// src/Security/Voter/BookingVoter.php

namespace App\Security\Voter;

use App\Domain\Booking\Entity\Booking;
use App\Domain\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class BookingVoter extends Voter
{
    public const VIEW     = 'booking.view';
    public const CONFIRM  = 'booking.confirm';
    public const COMPLETE = 'booking.complete';
    public const CANCEL   = 'booking.cancel';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Booking
            && in_array($attribute, [self::VIEW, self::CONFIRM, self::COMPLETE, self::CANCEL], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Booking $booking */
        $booking = $subject;

        return match ($attribute) {
            self::VIEW     => $this->canView($booking, $user),
            self::CONFIRM  => $booking->canBeConfirmedBy($user),
            self::COMPLETE => $booking->canBeCompletedBy($user),
            self::CANCEL   => $booking->canBeCancelledBy($user),
            default        => false,
        };
    }

    private function canView(Booking $booking, User $user): bool
    {
        return $user->hasRole('ROLE_ADMIN')
            || $booking->isOwnedByClient($user)
            || $booking->getMaster()->getUser()->getId() === $user->getId();
    }
}
