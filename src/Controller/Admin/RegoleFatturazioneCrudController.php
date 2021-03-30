<?php

namespace App\Controller\Admin;

use App\Entity\RegoleFatturazione;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class RegoleFatturazioneCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RegoleFatturazione::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Regola di Fatturazione')
            ->setEntityLabelInPlural('Regole di Fatturazione')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Regole di Fatturazione')
            ->setPageTitle(Crud::PAGE_NEW, 'Crea nuova Regola di Fatturazione')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (RegoleFatturazione $billingCadence) => (string) $billingCadence)
            ->setPageTitle(Crud::PAGE_EDIT, fn (RegoleFatturazione $billingCadence) => sprintf('Modifica regola <b>%s</b>', $billingCadence->getBillingCadence()))
            ->setSearchFields(['id', 'billingCadence', 'daysRange']);
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
        $panel1 = FormField::addPanel('REGOLE DI FATTURAZIONE')->setIcon('fas fa-wave-square');
        $i_billingCadence = TextField::new('billingCadence', 'Nome regola di fatturazione');
        $billingCadence = TextField::new('billingCadence', 'Nome regola di fatturazione')->addCssClass('list-group-item-warning');  //->addCssClass('row col-12 col-lg-6')
        $daysRange = IntegerField::new('daysRange', 'Giorni del ciclo')->addCssClass('list-group-item-warning')->setHelp('Indicare 0 per fatturazione quando richiesta, 30 per mensile, 60 bimestrale, etc. etc.');
        $i_daysRange = IntegerField::new('daysRange', 'Giorni del ciclo');
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true'])->addCssClass('list-group-item-dark');
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true'])->addCssClass('list-group-item-dark');
        $i_id = IntegerField::new('id', 'ID');
        $i_createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$i_id, $i_billingCadence, $i_daysRange, $i_createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $billingCadence, $daysRange, $panel_ID, $id, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $billingCadence, $daysRange];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $billingCadence, $daysRange, $panel_ID, $id, $createdAt];
        }
    }
}
