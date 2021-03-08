<?php

namespace App\Controller\Admin;

use App\Entity\Mansioni;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class MansioniCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Mansioni::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Mansione')
            ->setEntityLabelInPlural('Mansioni')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Mansioni')
            ->setPageTitle(Crud::PAGE_NEW, 'Crea nuova Mansione')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Mansioni $mansione) => (string) $mansione)
            ->setPageTitle(Crud::PAGE_EDIT, fn (Mansioni $mansione) => sprintf('Modifica Mansione <b>%s</b>', $mansione->getMansioneName()))
            ->setSearchFields(['id', 'mansione', 'isValidDA']);
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
        $panel1 = FormField::addPanel('MANSIONI DEL PERSONALE')->setIcon('fas fa-id-card-alt');
        $mansione = TextField::new('mansioneName', 'Mansione'); 
        $isValidDA = BooleanField::new('isValidDA', 'Valida per diversamente abili')->setHelp('<mark>Selezionare se la mansione è valida per il calcolo del rapporto percentuale forza lavoro diversamente abile</mark>');  //->addCssClass('row col-12 col-lg-6')
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $mansione, $isValidDA, $createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $mansione, $isValidDA, $panel_ID, $id, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $mansione, $isValidDA];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $mansione,  $isValidDA, $panel_ID, $id, $createdAt];
        }
    }
}
