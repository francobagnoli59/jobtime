<?php

namespace App\Controller\Admin;

use App\Entity\Causali;
use App\Service\CsvService;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Symfony\Component\HttpFoundation\Request;

class CausaliCrudController extends AbstractCrudController
{
    private CsvService $csvService;

    public function __construct(CsvService $csvService ) 
    {
    $this->csvService = $csvService;
    }


    public static function getEntityFqcn(): string
    {
        return Causali::class;
    }

    public function export(Request $request)
    {
        $context = $request->attributes->get(EA::CONTEXT_REQUEST_ATTRIBUTE);
        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
        $filters = $this->get(FilterFactory::class)->create($context->getCrud()->getFiltersConfig(), $fields, $context->getEntity());
        $listcausali = $this->createIndexQueryBuilder($context->getSearch(), $context->getEntity(), $fields, $filters)
            ->getQuery()
            ->getResult();
      
        $data = [];
        foreach ($listcausali as $causali) {
            $data[] = $causali->getExportData();
        }
        return $this->csvService->export($data, 'export_causali_'.date_create()->format('d-m-y').'.csv');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('description', 'Descrizione'));
    }

    /* public function createIndexQueryBuilder(Causali $searchDto, Causali $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $qb->andWhere('entity.code = ORDI');
        return $qb;
    } */

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
           
            ->setEntityLabelInSingular('Causale paghe')
            ->setEntityLabelInPlural('Causali paghe')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Causali paghe')
            ->setPageTitle(Crud::PAGE_NEW, 'Crea nuova Causale paghe')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Causali $code) => (string) $code)
            ->setPageTitle(Crud::PAGE_EDIT, fn (Causali $code) => sprintf('Modifica causale <b>%s</b>', $code->getCode()))
            ->setSearchFields(['id', 'code', 'description']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $export = Action::new('export', 'Esporta lista')
        ->setIcon('fa fa-download')
        ->linkToCrudAction('export')
        ->setCssClass('btn')
        ->createAsGlobalAction();
        
        return $actions
            // ...
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $export)
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
        $panel1 = FormField::addPanel('CAUSALI PAGHE')->setIcon('fas fa-pencil-ruler');
        $code = TextField::new('code', 'Codice Causale Paghe')->addCssClass('list-group-item-primary');  
        $description = TextField::new('description', 'Descrizione')->addCssClass('list-group-item-primary');
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true'])->addCssClass('list-group-item-dark');
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true'])->addCssClass('list-group-item-dark');
        $in_code = TextField::new('code', 'Codice Causale Paghe');
        $in_description = TextField::new('description', 'Descrizione');
        $in_createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$in_code, $in_description, $in_createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $code, $description, $panel_ID, $id, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $code, $description];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $code, $description, $panel_ID, $id, $createdAt];
        }
    }
}
