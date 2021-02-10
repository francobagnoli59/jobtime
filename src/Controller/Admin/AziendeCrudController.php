<?php

namespace App\Controller\Admin;

use App\Entity\Aziende;
use App\Entity\Province;
use App\Repository\ProvinceRepository;
use App\EventSubscriber\Dashboard\DashboardExceptionSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AziendeCrudController extends AbstractCrudController
{
    
    public static function getEntityFqcn(): string
    {
        return Aziende::class;
    }

   
    protected EntityManagerInterface $entityManager;
   
    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
          }
   

    public function createEntity(string $entityFqcn)
    {
        
        $azienda = new Aziende();
        $azienda->setProvincia($this->entityManager->getRepository(Province::class)->findOneBy(['code'=>'PI']));
        return $azienda;
    }
    public function configureCrud(Crud $crud): Crud
    {
    
        return $crud
            ->setEntityLabelInSingular('Azienda')
            ->setEntityLabelInPlural('Aziende')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Aziende del gruppo')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifica Azienda')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Visualizza Azienda')
            ->setPageTitle(Crud::PAGE_NEW, 'Crea nuova Azienda')
            ->setDefaultSort(['nickName' => 'ASC'])
            ;
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
        $panel1 = FormField::addPanel('INFORMAZIONI DI BASE')->setIcon('fas fa-boxes');
        $companyName = TextField::new('companyName', 'Nome Azienda');
        $nickName = TextField::new('nickName', 'Nick Name');
        $partitaIva = TextField::new('partitaIva', 'Partita Iva')->setRequired(true);
        $fiscalCode = TextField::new('fiscalCode', 'Codice Fiscale');
        $address = TextField::new('address', 'Indirizzo');
        $city = TextField::new('city', 'Città');
        $zipCode = TextField::new('zipCode', 'Codice Avviamento Postale');
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
            return [$nickName, $partitaIva, $address, $city, $provincia, $createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $companyName, $nickName, $address, $zipCode, $city, $provincia, $partitaIva, $fiscalCode, $panel_ID, $id, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $companyName, $nickName, $address, $zipCode, $city, $provincia, $partitaIva, $fiscalCode];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $companyName, $nickName, $address, $zipCode, $city, $provincia, $partitaIva, $fiscalCode, $panel_ID, $id, $createdAt];
        }
    }
}


