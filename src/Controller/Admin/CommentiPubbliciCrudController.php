<?php

namespace App\Controller\Admin;

use App\Entity\CommentiPubblici;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class CommentiPubbliciCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CommentiPubblici::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('CommentiPubblici')
            ->setEntityLabelInPlural('CommentiPubblici')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco commenti e segnalazioni')
            ->setPageTitle(Crud::PAGE_EDIT, 'Commento o segnalazione')
            ->setSearchFields(['id', 'author', 'email', 'textComment',  'state']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('cantieri', 'Cantiere'))
            ->add('state', 'Stato messaggio');
    }

    public function configureFields(string $pageName): iterable
    {
        $author = TextField::new('author', 'Autore');
        $email = EmailField::new('email');
        $textComment = TextareaField::new('textComment', 'Commento');
        $photoFilename = ImageField::new('photoFilename')->setBasePath('/uploads/photos')->setLabel('Foto');
        $state = TextField::new('state', 'Stato messaggio');
        $createdAt = DateTimeField::new('createdAt', 'Aggiornato il');
        $cantieri = AssociationField::new('cantieri', 'Cantiere');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$author, $email, $createdAt, $cantieri, $textComment, $photoFilename, $state];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $author, $email, $textComment,  $state, $createdAt, $cantieri];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$author, $email, $textComment, $state, $createdAt, $cantieri];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$cantieri, $createdAt, $author, $state, $email, $textComment];
        }
    }
}
