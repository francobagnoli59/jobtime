<?php

namespace App\Controller\Admin;

use App\Entity\Clienti;
use App\Entity\Province;
use App\Repository\ProvinceRepository;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;


class ClientiCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Clienti::class;
    }

    public function createEntity(string $entityFqcn)
    {
        $clienti = new Clienti();
        $clienti->setCountry('IT');
        return $clienti;
    }

    public function configureCrud(Crud $crud): Crud
    {
    
        return $crud
            ->setEntityLabelInSingular('Cliente')
            ->setEntityLabelInPlural('Clienti')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Clienti del gruppo')
            ->setPageTitle(Crud::PAGE_NEW, 'Crea nuovo Cliente')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Clienti $name) => sprintf('Scheda di <b>%s</b>', $name->getName()))
            ->setPageTitle(Crud::PAGE_EDIT, fn (Clienti $name) => sprintf('Modifica Cliente <b>%s</b>', $name->getName()))
    
         //   ->setSearchFields([$nameResult, $partitaIva, $city, $provincia, $codeSdi ])
            ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('typeCliente', 'Tipo Cliente')->setChoices(['Pubblica amministrazione' => 'PA', 'Azienda Privata' => 'PG', 'Ditta Individuale' => 'DI', 'Ente NO Profit' => 'EN', 'Persona fisica' => 'PF' ]) )
            ->add(TextFilter::new('name', 'Ragione Sociale'))
            ->add(TextFilter::new('nickName', 'Nick name Cliente'))
            ->add('partitaIva')
            ->add(TextFilter::new('fiscalCode', 'Codice Fiscale'))
            ->add('codeSdi')
            ->add(EntityFilter::new('provincia') 
            ->setFormTypeOption('value_type_options.query_builder', 
                static fn(ProvinceRepository $pr) => $pr->createQueryBuilder('provincia')
                        ->orderBy('provincia.name', 'ASC') )
             );
        
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
                 fn (Action $action) => $action->setIcon('fa fa-edit') )
                ->update(Crud::PAGE_INDEX, Action::DELETE,
                 fn (Action $action) => $action->setIcon('fa fa-trash') )
                ->update(Crud::PAGE_INDEX, Action::DETAIL,
                 fn (Action $action) => $action->setIcon('fa fa-eye') )
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('INFORMAZIONI DI BASE')->setIcon('fas fa-users');
        $typeCliente = ChoiceField::new('typeCliente', 'Tipo Cliente')->setChoices(['Pubblica amministrazione' => 'PA', 'Azienda Privata' => 'PG', 'Ditta Individuale' => 'DI', 'Ente NO Profit' => 'EN', 'Persona fisica' => 'PF' ]);
        $name = TextField::new('name', 'Nome Cliente');
        $nameResult = TextField::new('nameResult', 'Cliente');
        $nickName = TextField::new('nickName', 'Nick Name')
        ->setFormTypeOptions([
            'attr' => ['placeholder' => 'soprannome .. nome sintetico'] ]);
        $partitaIva = TextField::new('partitaIva', 'Partita Iva');
        $fiscalCode = TextField::new('fiscalCode', 'Codice Fiscale');
        $address = TextField::new('address', 'Indirizzo')
        ->setFormTypeOptions([
                'attr' => ['placeholder' => 'via, piazza ... civico'] ]);
        //->setFormTypeOptions([
        //    'attr' => ['value' => 'Via Pastrengo'] ]);  Per valore iniziale, creare due field tra PAGE:NEW e le altre
        $city = TextField::new('city', 'Città');
        $zipCode = TextField::new('zipCode', 'Codice Avviamento Postale');
        $codeSdi = TextField::new('codeSdi', 'Codice Univoco (SDI)');
        $country = CountryField::new('country', 'Nazione');
        $provincia = AssociationField::new('provincia', 'Provincia')
            ->setFormTypeOptions([
            'query_builder' => function (ProvinceRepository $pr) {
                return $pr->createQueryBuilder('p')
                    ->orderBy('p.name', 'ASC');
            },
             ])->setRequired(true)->setCustomOptions(array('widget' => 'native'));
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);
        
        if (Crud::PAGE_INDEX === $pageName) {
            return [$nameResult, $typeCliente, $partitaIva, $city, $provincia, $codeSdi];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $typeCliente, $name, $nickName, $address, $zipCode, $city, $provincia, $country, $partitaIva, $fiscalCode, $codeSdi, $panel_ID, $id, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $typeCliente, $name, $nickName, $address, $zipCode, $city, $provincia, $country, $partitaIva, $fiscalCode, $codeSdi];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $typeCliente, $name, $nickName, $address, $zipCode, $city, $provincia, $country, $partitaIva, $fiscalCode, $codeSdi, $panel_ID, $id, $createdAt];
        }
    }
}


