<?php
// src/Controller/Admin/AuditLogCrudController.php

namespace App\Controller\Api\V1\Admin;

use App\Domain\Admin\Entity\AdminAuditLog;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class AuditLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AdminAuditLog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Запись аудита')
            ->setEntityLabelInPlural('Аудит лог')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['entityClass', 'action', 'admin.email']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield AssociationField::new('admin',  'Администратор');
        yield TextField::new('entityClass',   'Сущность');
        yield TextField::new('entityId',      'ID сущности');
        yield TextField::new('action',        'Действие');
        yield TextField::new('ipAddress',     'IP')->onlyOnDetail();
        yield ArrayField::new('changes',      'Изменения')->onlyOnDetail();
        yield DateTimeField::new('createdAt', 'Дата')
            ->setFormat('dd.MM.yyyy HH:mm:ss');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('action',      'Действие'))
            ->add(TextFilter::new('entityClass', 'Сущность'))
            ->add(DateTimeFilter::new('createdAt', 'Дата'));
    }

    public function configureActions(Actions $actions): Actions
    {
        // Аудит — только чтение, никаких изменений
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}
