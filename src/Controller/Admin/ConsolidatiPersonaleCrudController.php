<?php

namespace App\Controller\Admin;

use App\Entity\ConsolidatiPersonale;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ConsolidatiPersonaleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ConsolidatiPersonale::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
