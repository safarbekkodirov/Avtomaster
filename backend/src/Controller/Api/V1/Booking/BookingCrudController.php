<?php
// src/Controller/Admin/BookingCrudController.php

namespace App\Controller\Api\V1\Booking;

use App\Domain\Admin\Service\AdminBookingService;
use App\Domain\Booking\Entity\Booking;
use App\Domain\User\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class BookingCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminBookingService $adminBookingService,
        private readonly AdminUrlGenerator   $adminUrlGenerator,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Booking::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Бронирование')
            ->setEntityLabelInPlural('Бронирования')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['client.email', 'master.user.email']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield AssociationField::new('client',  'Клиент');
        yield AssociationField::new('master',  'Мастер');
        yield AssociationField::new('service', 'Услуга');
        yield ChoiceField::new('status', 'Статус')
            ->setChoices([
                'Ожидает'     => Booking::STATUS_PENDING,
                'Подтверждён' => Booking::STATUS_CONFIRMED,
                'Завершён'    => Booking::STATUS_COMPLETED,
                'Отменён'     => Booking::STATUS_CANCELLED,
                'Возврат'     => Booking::STATUS_REFUNDED,
            ])
            ->renderAsBadges([
                Booking::STATUS_PENDING   => 'warning',
                Booking::STATUS_CONFIRMED => 'primary',
                Booking::STATUS_COMPLETED => 'success',
                Booking::STATUS_CANCELLED => 'danger',
                Booking::STATUS_REFUNDED  => 'secondary',
            ]);
        yield MoneyField::new('total', 'Сумма')
            ->setCurrency('RUB')
            ->setStoredAsCents(false);
        yield TextField::new('cancelledReason', 'Причина отмены')
            ->onlyOnDetail();
        yield DateTimeField::new('createdAt', 'Создано')
            ->setFormat('dd.MM.yyyy HH:mm');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('status', 'Статус')->setChoices([
                'Ожидает'     => Booking::STATUS_PENDING,
                'Подтверждён' => Booking::STATUS_CONFIRMED,
                'Завершён'    => Booking::STATUS_COMPLETED,
                'Отменён'     => Booking::STATUS_CANCELLED,
            ]))
            ->add(DateTimeFilter::new('createdAt', 'Дата создания'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $forceCancel = Action::new('forceCancel', 'Отменить', 'fa fa-times')
            ->linkToCrudAction('forceCancelBooking')
            ->addCssClass('text-danger')
            ->displayIf(fn(Booking $b) => in_array(
                $b->getStatus(),
                [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED],
                true
            ));

        $forceCancelWithRefund = Action::new('forceCancelRefund', 'Отменить + возврат', 'fa fa-undo')
            ->linkToCrudAction('forceCancelWithRefund')
            ->addCssClass('text-warning')
            ->displayIf(fn(Booking $b) => $b->getStatus() === Booking::STATUS_CONFIRMED);

        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX,  $forceCancel)
            ->add(Crud::PAGE_INDEX,  $forceCancelWithRefund)
            ->add(Crud::PAGE_DETAIL, $forceCancel)
            ->add(Crud::PAGE_DETAIL, $forceCancelWithRefund);
    }

    public function forceCancelBooking(
        AdminContext $context,
        #[CurrentUser] User $admin,
    ): RedirectResponse {
        /** @var Booking $booking */
        $booking = $context->getEntity()->getInstance();
        $this->adminBookingService->forceCancel(
            bookingId:  $booking->getId(),
            admin:      $admin,
            reason:     'Отменено администратором',
            withRefund: false,
        );
        $this->addFlash('warning', 'Бронирование отменено');
        return $this->redirect($this->getIndexUrl());
    }

    public function forceCancelWithRefund(
        AdminContext $context,
        #[CurrentUser] User $admin,
    ): RedirectResponse {
        /** @var Booking $booking */
        $booking = $context->getEntity()->getInstance();
        $this->adminBookingService->forceCancel(
            bookingId:  $booking->getId(),
            admin:      $admin,
            reason:     'Отменено администратором с возвратом средств',
            withRefund: true,
        );
        $this->addFlash('success', 'Бронирование отменено, возврат инициирован');
        return $this->redirect($this->getIndexUrl());
    }

    private function getIndexUrl(): string
    {
        return $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();
    }
}
