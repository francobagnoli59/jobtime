<?php

namespace App\Controller\Admin;

use App\Entity\Aziende;
use App\Entity\Province;
use App\Entity\User;
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

class SceltaAziendaCrudController extends AbstractCrudController
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

 
    public function selectAzienda(AdminContext $context)
    {
        $azienda = $context->getEntity()->getInstance();
        $UserRecord = $this->entityManager->getRepository(User::class)->findOneByEmail($this->getUser()->getUsername()) ;
        $UserRecord->setAziendadefault($azienda);
        $this->entityManager->persist($UserRecord);
        $this->entityManager->flush();

        $url = $this->adminUrlGenerator
            ->setRoute('admin')
            ->set('azienda_selection_nickName', $azienda->getNickName())
            ->set('azienda_selection_id', $azienda->getId());
            return $this->redirect($url);
    }

    public function resetAzienda(AdminContext $context)
    {
        $azienda = $context->getEntity()->getInstance();
        $UserRecord = $this->entityManager->getRepository(User::class)->findOneByEmail($this->getUser()->getUsername()) ;
        $UserRecord->setAziendadefault(null);
        $this->entityManager->persist($UserRecord);
        $this->entityManager->flush();

        $url = $this->adminUrlGenerator
            ->setRoute('admin')
            ->set('azienda_selection_nickName', ' ')
            ->set('azienda_selection_id', 0);
            return $this->redirect($url);
    }



    public function configureCrud(Crud $crud): Crud
    {
    
        return $crud
            ->setEntityLabelInSingular('Azienda')
            ->setEntityLabelInPlural('Aziende')
            ->setPageTitle(Crud::PAGE_INDEX, 'Scegli un\'azienda del gruppo')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Visualizza Azienda')
            ->setDefaultSort(['nickName' => 'ASC'])
            ;
    }
    
    public function configureActions(Actions $actions): Actions
    {
        $selectAzienda = Action::new('selectAzienda', 'Scegli azienda da gestire', 'fa fa-industry')
        ->linkToCrudAction('selectAzienda')->setCssClass('btn btn-primary');
      
        $resetAzienda = Action::new('resetAzienda', 'Reset scelta azienda ', 'fa fa-backspace')
        ->linkToCrudAction('resetAzienda')->setCssClass('btn btn-light')->createAsGlobalAction();
       
        return $actions
                // ...
                ->add(Crud::PAGE_INDEX, Action::DETAIL)
                ->add(Crud::PAGE_INDEX, $selectAzienda)
                ->add(Crud::PAGE_INDEX, $resetAzienda)
                ->remove(Crud::PAGE_INDEX, Action::EDIT)
                ->remove(Crud::PAGE_INDEX, Action::NEW)
                ->remove(Crud::PAGE_INDEX, Action::DELETE)
                ->remove(Crud::PAGE_DETAIL, Action::EDIT)
                ->remove(Crud::PAGE_DETAIL, Action::DELETE)
                ->update(Crud::PAGE_INDEX, Action::DETAIL,
                 fn (Action $action) => $action->setIcon('fa fa-eye') )
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('INFORMAZIONI DI BASE')->setIcon('fas fa-industry');
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
        $codeTransferPaghe = TextField::new('codeTransferPaghe', 'Codice per RPS')->setRequired(true)->setHelp('Inserire il codice idendificativo azienda per l\'applicativo paghe Ranocchi System (Studio Filippeschi)');
        $rangeAnalisi = IntegerField::new('rangeAnalisi', 'Mesi analisi dashboard')->setHelp('Inserire il numero di mesi che si desidera controllare per l\'andamento dell\'incidenza diversamente abili e tipi di contratto (determinato/indeterminato). Indicare un numero negativo per andare indietro nel tempo, es -12 equivale ad un anno precedente.');
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);
        
        if (Crud::PAGE_INDEX === $pageName) {
            return [$nickName, $partitaIva, $address, $city, $provincia, $createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $companyName, $nickName, $address, $zipCode, $city, $provincia, $partitaIva, $fiscalCode, $codeTransferPaghe, $rangeAnalisi, $panel_ID, $id, $createdAt];
        } 
    }
}


