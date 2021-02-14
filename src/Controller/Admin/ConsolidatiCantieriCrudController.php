<?php

namespace App\Controller\Admin;

use App\Entity\ConsolidatiCantieri;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ConsolidatiCantieriCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ConsolidatiCantieri::class;
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
