<?php

namespace App\Controller\Admin;

use App\Entity\Aziende;
use App\Entity\Province;
use App\Repository\ProvinceRepository;
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
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class AziendeCrudController extends AbstractCrudController
{

    /**
     * @var AdminUrlGenerator
     */
    private AdminUrlGenerator $adminUrlGenerator;

    protected EntityManagerInterface $entityManager;
   
    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator)
     {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
          }
   
    
    public static function getEntityFqcn(): string
    {
        return Aziende::class;
    }

 
  /*   public function selectAzienda(AdminContext $context)
    {
        $azienda = $context->getEntity()->getInstance();
// ->unsetAll()    ->setRoute('admin', ['azienda_selection_nickName', $azienda->getNickName()]);       
        $url = $this->adminUrlGenerator
            ->setRoute('admin')
            ->set('azienda_selection_nickName', $azienda->getNickName())
            ->set('azienda_selection_id', $azienda->getId());
           // ->setController(CantieriCrudController::class) 
           // ->setAction(Action::INDEX)    
           
            return $this->redirect($url);
    }
 */

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
        /*
        $selectAzienda = Action::new('selectAzienda', 'Scegli azienda da gestire', 'fa fa-industry')
        ->linkToCrudAction('selectAzienda')->setCssClass('btn btn-primary');
       */
       
        return $actions
                // ...
                ->add(Crud::PAGE_INDEX, Action::DETAIL)
                // ->add(Crud::PAGE_INDEX, $selectAzienda)
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
        $panel1 = FormField::addPanel('INFORMAZIONI DI BASE')->setIcon('fas fa-industry');
        $companyName = TextField::new('companyName', 'Nome Azienda')->addCssClass('list-group-item-primary');
        $nickName = TextField::new('nickName', 'Nick Name')->addCssClass('list-group-item-primary');
        $i_nickName = TextField::new('nickName', 'Nick Name');
        $partitaIva = TextField::new('partitaIva', 'Partita Iva')->setRequired(true)->addCssClass('list-group-item-warning');
        $i_partitaIva = TextField::new('partitaIva', 'Partita Iva');
        $fiscalCode = TextField::new('fiscalCode', 'Codice Fiscale')->addCssClass('list-group-item-warning');
        $address = TextField::new('address', 'Indirizzo')->addCssClass('list-group-item-success');
        $i_address = TextField::new('address', 'Indirizzo');
        $city = TextField::new('city', 'Città')->addCssClass('list-group-item-success');
        $i_city = TextField::new('city', 'Città');
        $zipCode = TextField::new('zipCode', 'Codice Avviamento Postale')->addCssClass('list-group-item-success');
        $i_provincia = AssociationField::new('provincia', 'Provincia');
        $provincia = AssociationField::new('provincia', 'Provincia')
            ->setFormTypeOptions([
            'query_builder' => function (ProvinceRepository $pr) {
                return $pr->createQueryBuilder('p')
                    ->orderBy('p.name', 'ASC');
            },
             ])->setRequired(true)->setCustomOptions(array('widget' => 'native'))->addCssClass('list-group-item-success');
        $codeTransferPaghe = TextField::new('codeTransferPaghe', 'Codice per RPS')->addCssClass('list-group-item-warning')->setRequired(true)->setHelp('Inserire il codice idendificativo azienda per l\'applicativo paghe Ranocchi System (Studio Filippeschi)');
        $rangeAnalisi = IntegerField::new('rangeAnalisi', 'Mesi analisi dashboard')->addCssClass('list-group-item-warning')->setHelp('Inserire il numero di mesi che si desidera controllare per l\'andamento dell\'incidenza diversamente abili e tipi di contratto (determinato/indeterminato). Indicare un numero negativo per andare indietro nel tempo, es -12 equivale ad un anno precedente.');
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true'])->addCssClass('list-group-item-dark');
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true'])->addCssClass('list-group-item-dark');
        $i_createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$i_nickName, $i_partitaIva, $i_address, $i_city, $i_provincia, $i_createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $companyName, $nickName, $address, $zipCode, $city, $provincia, $partitaIva, $fiscalCode, $codeTransferPaghe, $rangeAnalisi, $panel_ID, $id, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $companyName, $nickName, $address, $zipCode, $city, $provincia, $partitaIva, $fiscalCode, $codeTransferPaghe, $rangeAnalisi];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $companyName, $nickName, $address, $zipCode, $city, $provincia, $partitaIva, $fiscalCode, $codeTransferPaghe, $rangeAnalisi, $panel_ID, $id, $createdAt];
        }
    }
}


