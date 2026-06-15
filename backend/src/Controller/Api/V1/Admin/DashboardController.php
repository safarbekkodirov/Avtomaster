<?php
// src/Controller/Admin/DashboardController.php

namespace App\Controller\Api\V1\Admin;

use App\Domain\Booking\Entity\Booking;
use App\Domain\Master\Entity\Master;
use App\Domain\Master\Entity\MasterService;
use App\Domain\Payment\Entity\Payment;
use App\Domain\Region\Entity\Region;
use App\Domain\Review\Entity\Review;
use App\Domain\User\Entity\User;
use App\Domain\Admin\Entity\AdminAuditLog;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin', name: 'admin_')]
class DashboardController extends AbstractDashboardController
{
    #[Route('', name: 'dashboard')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Avtomaster Admin')
            ->setFaviconPath('favicon.ico')
            ->setTranslationDomain('admin')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Главная', 'fa fa-home');

        yield MenuItem::section('Пользователи');
        yield MenuItem::linkToCrud('Пользователи', 'fa fa-users',        User::class);
        yield MenuItem::linkToCrud('Мастера',      'fa fa-wrench',       Master::class);

        yield MenuItem::section('Бизнес');
        yield MenuItem::linkToCrud('Бронирования', 'fa fa-calendar',     Booking::class);
        yield MenuItem::linkToCrud('Платежи',      'fa fa-credit-card',  Payment::class);
        yield MenuItem::linkToCrud('Отзывы',       'fa fa-star',         Review::class);

        yield MenuItem::section('Справочники');
        yield MenuItem::linkToCrud('Регионы',      'fa fa-map',          Region::class);
        yield MenuItem::linkToCrud('Услуги',       'fa fa-list',         MasterService::class);

        yield MenuItem::section('Система');
        yield MenuItem::linkToCrud('Аудит',        'fa fa-history',      AdminAuditLog::class);
        yield MenuItem::linkToLogout('Выйти',      'fa fa-sign-out');
    }
}
