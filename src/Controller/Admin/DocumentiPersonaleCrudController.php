<?php

namespace App\Controller\Admin;

use App\Entity\DocumentiPersonale;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;



class DocumentiPersonaleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DocumentiPersonale::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Documento Personale')
            ->setEntityLabelInPlural('Documenti Personale')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Documenti Personale')
            ->setPageTitle(Crud::PAGE_NEW, 'Crea nuovo Documento Personale')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (DocumentiPersonale $titolo) => (string) $titolo)
            ->setPageTitle(Crud::PAGE_EDIT, fn (DocumentiPersonale $titolo) => sprintf('Modifica documento <b>%s</b>', $titolo->getTitolo()))
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
             
        return $actions
            // ...
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
           
            //->add(Crud::PAGE_INDEX, Action::DETAIL)
       
           // ->add(Crud::PAGE_DETAIL,)
            ->add(Crud::PAGE_EDIT,  Action::INDEX )
            ->add(Crud::PAGE_NEW,   Action::INDEX )

           /*  ->update(Crud::PAGE_INDEX, Action::EDIT,
             fn (Action $action) => $action->setIcon('fa fa-edit')->setLabel(false)->setHtmlAttributes(['title' => 'Modifica']))
            ->update(Crud::PAGE_INDEX, Action::DELETE,
             fn (Action $action) => $action->setIcon('fa fa-trash')->setLabel(false)->setHtmlAttributes(['title' => 'Elimina']))
            ->update(Crud::PAGE_INDEX, Action::DETAIL,
             fn (Action $action) => $action->setIcon('fa fa-eye')->setLabel(false)->setHtmlAttributes(['title' => 'Vedi'])) */
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('DOCUMENTO PERSONALE');
        $titolo = TextField::new('titolo', 'Titolo documento');  
        $documentoPath = TextField::new('documentoPath' , 'Path documento' )->setTemplatePath('admin/personale/doc_view.html.twig');
        $persona = AssociationField::new('persona', 'Persona');
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $titolo, $persona, $documentoPath, $createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1,  $persona,  $panel_ID, $id, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1,  $persona, ];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1,  $persona,  $documentoPath, $panel_ID, $id, $createdAt];
        }
    }
}
