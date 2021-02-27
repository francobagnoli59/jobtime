<?php

namespace App\Controller\Admin;

use App\Entity\CategorieServizi;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class CategorieServiziCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CategorieServizi::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Categoria Servizi')
            ->setEntityLabelInPlural('Categorie Servizi')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Categorie Servizi')
            ->setPageTitle(Crud::PAGE_NEW, 'Crea nuova Categoria Servizi')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (CategorieServizi $categoria) => (string) $categoria)
            ->setPageTitle(Crud::PAGE_EDIT, fn (CategorieServizi $categoria) => sprintf('Modifica categoria <b>%s</b>', $categoria->getCategoria()))
            ->setSearchFields(['id', 'categoria']);
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
        $panel1 = FormField::addPanel('CATEGORIE SERVIZI')->setIcon('fas fa-dolly');
        $categoria = TextField::new('categoria', 'Nome Categoria Servizi');  //->addCssClass('row col-12 col-lg-6')
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $categoria,  $createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $categoria, $panel_ID, $id, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $categoria];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $categoria, $panel_ID, $id, $createdAt];
        }
    }
}
