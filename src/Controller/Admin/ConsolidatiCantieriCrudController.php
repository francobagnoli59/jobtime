<?php

namespace App\Controller\Admin;

use App\Entity\ConsolidatiCantieri;
use App\Repository\CantieriRepository;
use App\Repository\MesiaziendaliRepository;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ConsolidatiCantieriCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ConsolidatiCantieri::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
    
        return $crud
            ->setEntityLabelInSingular('Consolidato cantiere')
            ->setEntityLabelInPlural('Consolidato cantieri')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Ore Consolidate dei Cantieri')
             ->setPageTitle(Crud::PAGE_DETAIL, 'Visualizza Ore Consolidate per Cantiere')
            ->setPageTitle(Crud::PAGE_EDIT,'Modifica Ore Consolidate per Cantiere')
            ->setSearchFields(['id', 'cantiere.nameJob', 'meseAziendale.keyReference'])
            ->setDefaultSort(['cantiere.nameJob' => 'ASC',  'meseAziendale.keyReference' => 'ASC'])
            ->showEntityActionsAsDropdown();
    }
    

    public function configureActions(Actions $actions): Actions
    {
               
        return $actions
                ->remove(Crud::PAGE_INDEX, Action::NEW)
                //->remove(Crud::PAGE_INDEX, Action::DELETE)
                ->remove(Crud::PAGE_DETAIL, Action::DELETE)
                //->remove(Crud::PAGE_DETAIL, Action::INDEX)
                ->add(Crud::PAGE_INDEX, Action::DETAIL)
                ->update(Crud::PAGE_INDEX, Action::EDIT,
                 fn (Action $action) => $action->setIcon('fa fa-edit') )
                ->update(Crud::PAGE_INDEX, Action::DETAIL,
                 fn (Action $action) => $action->setIcon('fa fa-eye') )
               /*  ->update(Crud::PAGE_INDEX, Action::DELETE,
                 fn (Action $action) => $action->setIcon('fa fa-trash') ) */
            ;
    }
    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('CONSOLIDATO DEL MESE per CANTIERE ')->setIcon('fas fa-calendar-check');
        $cantiere = AssociationField::new('cantiere', 'Cantiere')
            ->setFormTypeOptions([
            'query_builder' => function (CantieriRepository $ca) {
                return $ca->createQueryBuilder('a')
                   ->orderBy('a.nameJob', 'ASC');     }, 'disabled' => 'true'
            ])
            ->setCustomOptions(array('widget' => 'native'))->setRequired(true);
        $meseaziendale = AssociationField::new('meseAziendale', 'Azienda/Periodo')
            ->setFormTypeOptions([
            'query_builder' => function (MesiAziendaliRepository $ma) {
                return $ma->createQueryBuilder('f')
                   ->orderBy('f.keyReference', 'ASC');     }, 'disabled' => 'true'
            ])
            ->setCustomOptions(array('widget' => 'native'))->setRequired(true); 
        $oreLavoro = TextField::new('oreLavoro', 'Ore Lavorate');
        $orePianificate = TextField::new('orePianificate', 'Ore Pianificate');
        $oreStraordinario = TextField::new('oreStraordinario', 'Ore Straordinario');
        $oreImproduttive = TextField::new('oreImproduttive', 'Ore Improduttive');
        $oreIninfluenti = TextField::new('oreIninfluenti', 'Ore Ininfluenti'); 
        $costoOreLavoro = MoneyField::new('costoOreLavoro', 'Costo risorse umane')->setNumDecimals(2)->setCurrency('EUR')->setHelp('Calcolato sull\'ammontare delle ore mensili effettive'); 
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $keyReference = TextField::new('keyReference', 'Chiave registrazione')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);
        if (Crud::PAGE_INDEX === $pageName) {
            return [ $cantiere, $meseaziendale, $oreLavoro, $orePianificate, $oreStraordinario, $oreImproduttive, $oreIninfluenti, $costoOreLavoro];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $cantiere, $meseaziendale, $oreLavoro, $orePianificate, $oreStraordinario, $oreImproduttive, $oreIninfluenti, $costoOreLavoro, $panel_ID, $id, $keyReference, $createdAt];
         } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $cantiere, $meseaziendale, $oreLavoro, $orePianificate, $oreStraordinario, $oreImproduttive, $oreIninfluenti, $costoOreLavoro];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $cantiere, $meseaziendale, $oreLavoro, $orePianificate, $oreStraordinario, $oreImproduttive, $oreIninfluenti, $costoOreLavoro, $panel_ID, $id, $keyReference, $createdAt];

        }
    }
    
}
