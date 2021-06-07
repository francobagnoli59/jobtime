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
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;


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
            ->setEntityLabelInPlural('Documenti Cantieri')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Documenti Cantieri')
            ->setPageTitle(Crud::PAGE_NEW, 'Crea nuovo Documento Cantiere')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (DocumentiCantieri $titolo) => (string) $titolo)
            ->setPageTitle(Crud::PAGE_EDIT, fn (DocumentiCantieri $titolo) => sprintf('Modifica documento <b>%s</b>', $titolo->getTitolo()))
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

           //  ->add(Crud::PAGE_EDIT,  Action::INDEX )
           // ->add(Crud::PAGE_NEW,   Action::INDEX )

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
        $panel1 = FormField::addPanel('DOCUMENTO CANTIERE');
        $tipo = ChoiceField::new('tipologia', 'Tipo')->setChoices([ ' ' => 'NUL'  ,  'Contratto Pubblica Amministrazione'  => 'CPA', 'Determina Pubblica Amministrazione'  => 'DPA' ,
        'Contratto con società non P.A.' => 'CPR'  , 'Commessa/Ordine con società non P.A.' => 'OPR' ]);
        $titolo = TextField::new('titolo', 'Titolo documento');  
        $documentoName = TextField::new('documentoName' , 'Path documento' )->setTemplatePath('admin/cantieri/doc_view.html.twig');
        $cantiere = AssociationField::new('cantiere', 'Cantiere');
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $tipo, $titolo, $cantiere, $documentoName, $createdAt];
        } /*  elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1,  $cantiere,  $panel_ID, $id, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1,  $cantiere];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1,  $cantiere, $panel_ID, $id, $createdAt];
        } */
    }
}
