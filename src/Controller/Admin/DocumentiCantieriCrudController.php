<?php

namespace App\Controller\Admin;

use App\Entity\DocumentiCantieri;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class DocumentiCantieriCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DocumentiCantieri::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Documento Cantiere')
            ->setEntityLabelInPlural('Documenti Cantiere')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Documenti Cantiere')
            ->setPageTitle(Crud::PAGE_NEW, 'Crea nuovo Documento Cantiere')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (DocumentiCantieri $titolo) => (string) $titolo)
            ->setPageTitle(Crud::PAGE_EDIT, fn (DocumentiCantieri $titolo) => sprintf('Modifica documento <b>%s</b>', $titolo->getTitolo()))
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
             
        return $actions
            // ...
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
       
           // ->add(Crud::PAGE_DETAIL,)
            ->add(Crud::PAGE_EDIT,  Action::INDEX )
            ->add(Crud::PAGE_NEW,   Action::INDEX )

            ->update(Crud::PAGE_INDEX, Action::EDIT,
             fn (Action $action) => $action->setIcon('fa fa-edit')->setLabel(false)->setHtmlAttributes(['title' => 'Modifica']))
            ->update(Crud::PAGE_INDEX, Action::DELETE,
             fn (Action $action) => $action->setIcon('fa fa-trash')->setLabel(false)->setHtmlAttributes(['title' => 'Elimina']))
            ->update(Crud::PAGE_INDEX, Action::DETAIL,
             fn (Action $action) => $action->setIcon('fa fa-eye')->setLabel(false)->setHtmlAttributes(['title' => 'Vedi']))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('DOCUMENTO CANTIERE');
        $titolo = TextField::new('titolo', 'Titolo documento');  
        $documentoPath = TextField::new('documentoPath', 'Path documento');  
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $titolo, $documentoPath, $createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $titolo, $documentoPath, $panel_ID, $id, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $titolo, $documentoPath];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $titolo, $documentoPath, $panel_ID, $id, $createdAt];
        }
    }
}
