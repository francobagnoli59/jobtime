<?php

namespace App\Controller\Admin;

use App\Entity\Province;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class ProvinceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Province::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Provincia')
            ->setEntityLabelInPlural('Province')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Province')
            ->setPageTitle(Crud::PAGE_NEW, 'Crea nuova Provincia')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Province $name) => (string) $name)
            ->setPageTitle(Crud::PAGE_EDIT, fn (Province $name) => sprintf('Modifica provincia <b>%s</b>', $name->getName()))
            ->setSearchFields(['id', 'code', 'name'])
            ->setDefaultSort(['code' => 'ASC'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
         
            // 
            $actionDetail = Action::new(Action::DETAIL)
            ->linkToCrudAction(Action::DETAIL)
            ->displayIf(fn ($entity) => $entity->getIsNotTba()
            );
        
            $actionEdit = Action::new(Action::EDIT)
            ->linkToCrudAction(Action::EDIT)
            ->displayIf(fn ($entity) => $entity->getIsNotTba()
            );

        return $actions
            // ...
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->add(Crud::PAGE_INDEX, $actionDetail)
            ->add(Crud::PAGE_INDEX, $actionEdit)
           
            ->add(Crud::PAGE_EDIT,  Action::INDEX )
            ->add(Crud::PAGE_NEW,   Action::INDEX )
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            
            ->update(Crud::PAGE_INDEX, $actionEdit,
             fn (Action $action) => $action->setIcon('fa fa-edit')->setLabel(false)->setHtmlAttributes(['title' => 'Modifica']))
            // ->update(Crud::PAGE_INDEX, Action::DELETE,
            // fn (Action $action) => $action->setIcon('fa fa-trash')->setLabel(false)->setHtmlAttributes(['title' => 'Elimina']))
            ->update(Crud::PAGE_INDEX, $actionDetail,
             fn (Action $action) => $action->setIcon('fa fa-eye')->setLabel(false)->setHtmlAttributes(['title' => 'Vedi']))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('SIGLA E NOME PROVINCIA')->setIcon('fas fa-map-marker-alt');
        $code = TextField::new('code', 'Sigla targa');
        $name = TextField::new('name', 'Nome provincia');
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);
        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $code, $name, $createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $code, $name, $panel_ID, $id, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $code, $name];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $code, $name, $panel_ID, $id, $createdAt];
        }
    }
}
