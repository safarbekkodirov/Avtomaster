<?php
// src/Security/Voter/ReviewVoter.php

namespace App\Security\Voter;

use App\Domain\Review\Entity\Review;
use App\Domain\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ReviewVoter extends Voter
{
    public const CREATE   = 'review.create';
    public const MODERATE = 'review.moderate';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return match ($attribute) {
            self::CREATE   => $subject instanceof \App\Domain\Booking\Entity\Booking,
            self::MODERATE => $subject instanceof Review,
            default        => false,
        };
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::CREATE   => $user->hasRole('ROLE_CLIENT'),
            self::MODERATE => $user->hasRole('ROLE_ADMIN'),
            default        => false,
        };
    }
}
