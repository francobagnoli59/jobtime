<?php

namespace App\Controller\Admin;

use App\Entity\AreeGeografiche;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class AreeGeograficheCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AreeGeografiche::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Area/zona geografica')
            ->setEntityLabelInPlural('Aree/zone geografiche')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Aree/zone geografiche')
            ->setPageTitle(Crud::PAGE_NEW, 'Crea nuova Area/zona geografica')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (AreeGeografiche $area) => (string) $area)
            ->setPageTitle(Crud::PAGE_EDIT, fn (AreeGeografiche $area) => sprintf('Modifica Area <b>%s</b>', $area->getArea()))
            ->setSearchFields(['id', 'area']);
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
        $panel1 = FormField::addPanel('AREE/ZONE GEOGRAFICHE')->setIcon('fas fa-map-marked-alt');
        $i_area = TextField::new('area', 'Nome Area/zona geografica');
        $area = TextField::new('area', 'Nome Area/zona geografica')->addCssClass('list-group-item-success');  //->addCssClass('row col-12 col-lg-6')
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $i_id = IntegerField::new('id', 'ID');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true'])->addCssClass('list-group-item-dark');
        $i_createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento');
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true'])->addCssClass('list-group-item-dark');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$i_id, $i_area,  $i_createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $area, $panel_ID, $id, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $area];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $area, $panel_ID, $id, $createdAt];
        }
    }
}
