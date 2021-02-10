<?php

namespace App\Controller\Admin;

use App\Entity\FestivitaAnnuali;
// use App\Form\FestivitaType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
// use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class FestivitaAnnualiCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FestivitaAnnuali::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Festività Annuali')
            ->setEntityLabelInPlural('Festività Annuali')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Festività Annuali')
            ->setPageTitle(Crud::PAGE_NEW, 'Crea nuovo Anno Festività Annuali')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (FestivitaAnnuali $anno) => (string) $anno)
            ->setPageTitle(Crud::PAGE_EDIT, fn (FestivitaAnnuali $anno) => sprintf('Modifica Anno Festività Annuali <b>%s</b>', $anno->getAnno()))
            ->setSearchFields(['id', 'anno', 'dateCollection']);
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
        $panel1 = FormField::addPanel('FESTE ANNUALI')->setIcon('fas fa-plane-departure');
        $anno = TextField::new('anno', 'Anno festività annuali');
       // $festivitaCollection = CollectionField::new("dateFestivita") ->allowDelete(true) ->allowAdd(true) ->setEntryIsComplex(true)
       // ->setFormTypeOptions(["by_reference" => false, "entry_type" => "App\Form\FestivitaType", "required" => true, "entry_options" => ["label" => false]]);
        $festeArray = ArrayField::new('dateFestivita', 'Giorno della Festa')->setHelp('Indicare la data nella forma ggmm es. 0101 per il capodanno 2021, se desiderato aggiungere il nome della festa.');
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $anno, $festeArray,  $createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $anno, $festeArray,  $panel_ID, $id, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $anno, $festeArray];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $anno, $festeArray, $panel_ID, $id, $createdAt];
        }
    }
}
