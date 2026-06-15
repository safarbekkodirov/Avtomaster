<?php
// src/Controller/Admin/UserCrudController.php

namespace App\Controller\Api\V1\Admin;

use App\Domain\User\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Пользователь')
            ->setEntityLabelInPlural('Пользователи')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['email', 'phone']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield EmailField::new('email', 'Email');
        yield ArrayField::new('roles', 'Роли');
        yield BooleanField::new('isVerified', 'Верифицирован')
            ->renderAsSwitch(false);
        yield BooleanField::new('isActive', 'Активен')
            ->renderAsSwitch($pageName !== Crud::PAGE_INDEX);
        yield DateTimeField::new('createdAt', 'Зарегистрирован')
            ->onlyOnIndex()
            ->setFormat('dd.MM.yyyy HH:mm');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('isActive',   'Активен'))
            ->add(BooleanFilter::new('isVerified', 'Верифицирован'))
            ->add(TextFilter::new('email',         'Email'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)    // пользователи регистрируются сами
            ->disable(Action::DELETE) // мягкое удаление через isActive
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}
