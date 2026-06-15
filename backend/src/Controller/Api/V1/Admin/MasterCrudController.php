<?php
// src/Controller/Admin/MasterCrudController.php

namespace App\Controller\Api\V1\Admin;

use App\Domain\Admin\Service\AdminMasterService;
use App\Domain\Master\Entity\Master;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Domain\User\Entity\User;

class MasterCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminMasterService $adminMasterService,
        private readonly AdminUrlGenerator  $adminUrlGenerator,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Master::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Мастер')
            ->setEntityLabelInPlural('Мастера')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['user.email', 'address']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield AssociationField::new('user',   'Пользователь');
        yield AssociationField::new('region', 'Регион');
        yield TextField::new('address',       'Адрес');
        yield NumberField::new('rating',      'Рейтинг')
            ->setNumDecimals(2)
            ->onlyOnIndex();
        yield NumberField::new('reviewsCount', 'Отзывов')
            ->onlyOnIndex();
        yield BooleanField::new('isVerified', 'Верифицирован')
            ->renderAsSwitch(false);
        yield BooleanField::new('isActive',   'Активен')
            ->renderAsSwitch(false);
        yield DateTimeField::new('createdAt', 'Создан')
            ->onlyOnIndex()
            ->setFormat('dd.MM.yyyy');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('isVerified', 'Верифицирован'))
            ->add(BooleanFilter::new('isActive',   'Активен'));
    }

    public function configureActions(Actions $actions): Actions
    {
        // Кастомные действия — верификация и блокировка
        $verify = Action::new('verify', 'Верифицировать', 'fa fa-check')
            ->linkToCrudAction('verifyMaster')
            ->displayIf(fn(Master $m) => !$m->isVerified());

        $block = Action::new('block', 'Заблокировать', 'fa fa-ban')
            ->linkToCrudAction('blockMaster')
            ->addCssClass('text-danger')
            ->displayIf(fn(Master $m) => $m->isActive());

        $unblock = Action::new('unblock', 'Разблокировать', 'fa fa-check-circle')
            ->linkToCrudAction('unblockMaster')
            ->displayIf(fn(Master $m) => !$m->isActive());

        return $actions
            ->disable(Action::NEW, Action::DELETE)
            ->add(Crud::PAGE_INDEX, $verify)
            ->add(Crud::PAGE_INDEX, $block)
            ->add(Crud::PAGE_INDEX, $unblock)
            ->add(Crud::PAGE_DETAIL, $verify)
            ->add(Crud::PAGE_DETAIL, $block)
            ->add(Crud::PAGE_DETAIL, $unblock);
    }

    public function verifyMaster(
        AdminContext $context,
        #[CurrentUser] User $admin,
    ): RedirectResponse {
        /** @var Master $master */
        $master = $context->getEntity()->getInstance();
        $this->adminMasterService->verify($master->getId(), $admin);
        $this->addFlash('success', 'Мастер верифицирован');
        return $this->redirect($this->getIndexUrl());
    }

    public function blockMaster(
        AdminContext $context,
        #[CurrentUser] User $admin,
    ): RedirectResponse {
        /** @var Master $master */
        $master = $context->getEntity()->getInstance();
        $this->adminMasterService->block($master->getId(), $admin);
        $this->addFlash('warning', 'Мастер заблокирован');
        return $this->redirect($this->getIndexUrl());
    }

    public function unblockMaster(
        AdminContext $context,
        #[CurrentUser] User $admin,
    ): RedirectResponse {
        /** @var Master $master */
        $master = $context->getEntity()->getInstance();
        $this->adminMasterService->unblock($master->getId(), $admin);
        $this->addFlash('success', 'Мастер разблокирован');
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
