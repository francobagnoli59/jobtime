<?php

namespace App\Controller\Admin;

use App\Entity\Attrezzature;
// use App\Repository\AziendeRepository;
use App\Repository\MovimentiAttrezzatureRepository;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
// use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\Constraints\Image;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
  

class AttrezzatureCrudController extends AbstractCrudController
{

 
    /**
     * @var AdminUrlGenerator
     */
    private AdminUrlGenerator $adminUrlGenerator;

    // private CsvService $csvService;

    public function __construct(EntityManagerInterface $entityManager,  AdminUrlGenerator $adminUrlGenerator )  // , CsvService $csvService
    {
    $this->entityManager = $entityManager;
    $this->adminUrlGenerator = $adminUrlGenerator;
    // $this->csvService = $csvService;
    }

    public static function getEntityFqcn(): string
    {
        return Attrezzature::class;
    }

    public function configureCrud(Crud $crud): Crud
    {

/*         $azienda = $this->getUser()->getAziendadefault();
        if ($azienda !== null ) {
            $aziendaNickName = $azienda->getNickName();
        } else { $aziendaNickName = '...seleziona azienda!!!'; }  */

        $LabelSing = 'Attrezzatura ' ;
        $LabelPlur = 'Attrezzature ' ;
        $LabelNew = 'Crea nuova Attrezzatura ' ;
        $LabelList = 'Elenco Attrezzature ';

        return $crud
            ->showEntityActionsAsDropdown()
            ->setEntityLabelInSingular($LabelSing)
            ->setEntityLabelInPlural($LabelPlur)
            ->setPageTitle(Crud::PAGE_INDEX, $LabelList)
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Attrezzature $name) => (string) $name)
            ->setPageTitle(Crud::PAGE_EDIT, fn (Attrezzature $name) => sprintf('Modifica <b>%s</b>', $name->getName()))
            ->setPageTitle(Crud::PAGE_NEW, $LabelNew)
            ->setSearchFields([ 'name' ])
            ->setDefaultSort(['name' => 'ASC'])
            // ->setSearchFields([ 'name', 'surname', 'cantiere.nameJob', 'mansione.mansioneName' ])
            ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', 'Nome attrezzatura'))
            ->add(BooleanFilter::new('isOutOfOrder', 'Fuori servizio'))
            ->add(DateTimeFilter::new('dataAcquisto', 'Data di acquisto'))
            ->add(DateTimeFilter::new('scadenzaManutenzione', 'Scadenza manutenzione'));
           /*  ->add(EntityFilter::new('movimentiAttrezzature')->setFormTypeOption('value_type_options.query_builder', 
                static fn(MovimentiAttrezzatureRepository $ma) => $ma->createQueryBuilder('movimentiAttrezzature')
                 ->orderBy('movimentiAttrezzature.cantiere', 'ASC') ) ); */
           //        ->add(BooleanFilter::new('isEnforce', 'Assunto'))
          
    }
 
    public function configureActions(Actions $actions): Actions
    {
       
        $view_movimenti = Action::new('ViewSpostamenti', 'Spostamenti attrezzatura', 'fa fa-truck-moving')
        ->linkToCrudAction('ViewSpostamenti')->setCssClass('btn btn-secondary')->displayIf(fn ($entity) => !$entity->getIsOutOfOrder());
       
        return $actions
            // ...
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, $view_movimenti)
           // ->add(Crud::PAGE_DETAIL,)
            ->add(Crud::PAGE_EDIT,  Action::INDEX )
            ->add(Crud::PAGE_NEW,   Action::INDEX )

            ->update(Crud::PAGE_INDEX, Action::EDIT,
             fn (Action $action) => $action->setIcon('fa fa-edit')->setHtmlAttributes(['title' => 'Modifica']))
            ->update(Crud::PAGE_INDEX, Action::DELETE,
             fn (Action $action) => $action->setIcon('fa fa-trash')->setHtmlAttributes(['title' => 'Elimina']))
            ->update(Crud::PAGE_INDEX, Action::DETAIL,
             fn (Action $action) => $action->setIcon('fa fa-eye')->setHtmlAttributes(['title' => 'Vedi scheda']))
        ;
    }

    
  /*   public function createEntity(string $entityFqcn)
    {
        $azienda = $this->getUser()->getAziendadefault();
        if ($azienda !== null ) {
            $attrezzature = new Attrezzature();
            $attrezzature->setAzienda($attrezzature);
        return $attrezzature;
        }
    } */

    public function ViewSpostamenti(AdminContext $context)
    {
        $attrezzatura = $context->getEntity()->getInstance();

        $movAttrezzature = []; 
        $movAttrezzature = $attrezzatura->getMovimentiAttrezzature();
        if (count($movAttrezzature) === 0) { 
            $url = $this->adminUrlGenerator->unsetAll()
            ->setController(MovimentiAttrezzatureCrudController::class)
            ->setAction(Action::NEW)
            ->set('attrezzatura', $attrezzatura->getId());
            return $this->redirect($url);  
        } else { 
            $url = $this->adminUrlGenerator->unsetAll()
            ->setController(MovimentiAttrezzatureCrudController::class)
            ->setAction(Action::INDEX)
            ->set('filters[attrezzatura][comparison]', '=')
            ->set('filters[attrezzatura][value]', $attrezzatura->getId());
            return $this->redirect($url);   
        }
    }

   
   /*  public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
          // key Piano ore cantieri
        $fullName =  $entityInstance->getFullName();
        $message = $this->verifyEntity($entityManager, $entityInstance);
        if ($message === '') {
            $entityManager->persist($entityInstance);
            $entityManager->flush();
        } else { $this->addFlash('danger', sprintf('%s Dati %s da correggere!!!', $message, $fullName )); }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $message = $this->verifyEntity($entityManager, $entityInstance);
        if ($message === '') {
            $entityManager->persist($entityInstance);
            $entityManager->flush();
        } else { $this->addFlash('danger', sprintf('%s Dati scheda persona non confermati!!!', $message)); }
    }

    private function verifyEntity(EntityManagerInterface $entityManager, $entityInstance): string
    {

         // controlla che il nominativo appartenga alla stessa azienda del cantiere
        $message = '';
        if ($entityInstance->getCantiere() !== null) {
            $recordcantiere = $entityManager->getRepository(Cantieri::class)->findOneBy(['id'=> $entityInstance->getCantiere()->getId()]);
            if ( $recordcantiere !== null) {
            $azienda_persona = $entityInstance->getAzienda()->getId();
            $azienda_cantiere = $recordcantiere->getAzienda()->getId();
            if ($azienda_persona === $azienda_cantiere ) {
                $message = '';
            } else { $message = 'L\'azienda del cantiere non corrisponde all\'azienda della persona.</br>'; }
            }
        }
        return $message ;
    }

 */

    public function configureFields(string $pageName): iterable
    {
/*         $azienda = $this->getUser()->getAziendadefault();
        if ($azienda !== null ) {
            $statusAzienda = true ; $helpAz = '';}
            else { $statusAzienda = false ; $helpAz = 'Scegliere l\'azienda del gruppo nella quale è assunta la persona'; }
  */
            // $countdeleted = $entityManager->getRepository(OreLavorate::class)->deleteOreLavorate($azienda, false , $dataInizio, $dataFine);

            $collapse = false ;    

            $panel1 = FormField::addPanel('INFORMAZIONI BENE')->setIcon('fas fa-blender');
            $name = TextField::new('name', 'Nome attrezzatura')->addCssClass('list-group-item-primary');
            $nameview = TextField::new('name', 'Attrezzatura')->onlyOnIndex();
            $dataAcquisto = DateField::new('dataAcquisto', 'Data di acquisto')->addCssClass('list-group-item-primary');
            $dataAcqView = DateField::new('dataAcquisto', 'Data di acquisto')->onlyOnIndex();
            // 
            $movAttrezzature = TextField::new('LastLocation', 'Allocato in')->onlyOnIndex();
            /* $movAttrezzature = AssociationField::new('movimentiAttrezzature', 'Allocato in')
            ->setFormTypeOptions([
            'query_builder' => function (MovimentiAttrezzatureRepository $ma) {
                return $ma->createQueryBuilder('m')
                    ->orderBy('m.createdAt', 'DESC');
            },
             ])->setRequired(true)->setCustomOptions(array('widget' => 'native')); */
            $riferimentiAcquisto = TextField::new('riferimentiAcquisto', 'Riferimenti documento acquisto')->addCssClass('list-group-item-primary')
            ->setHelp('<mark><b>Indicare il numero di fattura / il protocollo / il fornitore, o qualsiasi informazione utile a rintracciare il documento di acquisto</b></mark>');
            $riferimentoCespite = TextField::new('riferimentoCespite', 'Riferimento cespite')->addCssClass('list-group-item-primary')
            ->setHelp('Indicare il numero di cespite attribuito dal sistema contabile');
            $costo = MoneyField::new('costo', 'Costo attrezzatura')->setNumDecimals(2)->setCurrency('EUR')->setHelp('Indicare il costo di acquisto compreso degli eventuali oneri accessori')->addCssClass('list-group-item-primary');
            
            
           
           /*             $azienda = AssociationField::new('azienda', 'Azienda')->setHelp($helpAz)->addCssClass('list-group-item-warning')
            ->setFormTypeOptions([
                'query_builder' => function (AziendeRepository $az) {
                    return $az->createQueryBuilder('az')
                        ->orderBy('az.nickName', 'ASC');
                },
                 ])->setRequired(true)->setCustomOptions(array('widget' => 'native'))->setFormTypeOptions(['disabled' => $statusAzienda]); 
            $cant = AssociationField::new('cantiere', 'Cantiere')->onlyOnIndex();
            $cantiere = AssociationField::new('cantiere', 'Cantiere')->addCssClass('list-group-item-warning')
            ->setFormTypeOptions([
                'query_builder' => function (CantieriRepository $ca) {
                    return $ca->createQueryBuilder('c')
                        ->orderBy('c.nameJob', 'ASC');
                },
                 ])
            ->setHelp('<mark><b>Indicare solo nel caso la persona lavori prevalentemente per un unico Cantiere. Per più cantieri una volta inserita la persona utilizzare la funzione [Piano Ore Cantieri]</b></mark>');
            */

            $panelPortrait = FormField::addPanel('DATI ATTREZZATURA')->setIcon('fas fa-newspaper')->renderCollapsed($collapse);
            $photoFile = ImageField::new('photoAttrezzo', 'Upload Foto attrezzatura')->addCssClass('list-group-item-warning')
            ->setBasePath('uploads/images')
            ->setUploadDir('public/uploads/images')
            ->setUploadedFileNamePattern('[contenthash].[extension]');

            $photoFileView = ImageField::new('photoAttrezzo', 'Foto attrezzatura')
            ->setBasePath('uploads/images');
         
            $imagePortrait = TextField::new('imageVFAttrezzo', 'Immagine')->setFormType(VichImageType::class)->addCssClass('list-group-item-warning')
            ->setFormTypeOptions(['constraints' => [ new Image(['maxSize' => '2048k']) ] , 'allow_delete' => false] );

            $isOutOfOrder = BooleanField::new('isOutOfOrder', 'Fuori servizio')->addCssClass('list-group-item-warning');
            $isOOOview = BooleanField::new('isOutOfOrder', 'Fuori servizio')->onlyOnIndex();
            $scadenzaManutenzione = DateField::new('scadenzaManutenzione', 'Data scadenza manutenzione')->addCssClass('list-group-item-warning')->setHelp('Indicare una data se prevista una manutenzione dell\'attrezzatura.');
            $scadManutView = DateField::new('scadenzaManutenzione', 'Scadenza manutenzione')->onlyOnIndex();
            $funzione = TextEditorField::new('funzione', 'Descrizione estesa delle funzionalità dell\'attrezzatura.')->addCssClass('list-group-item-warning') ;   
            
            $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
            $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true'])->addCssClass('list-group-item-dark');
            $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true'])->addCssClass('list-group-item-dark');

            if (Crud::PAGE_INDEX === $pageName) {
                return [$nameview, $movAttrezzature, $photoFileView, $dataAcqView , $isOOOview , $scadManutView];
            } elseif (Crud::PAGE_DETAIL === $pageName) {
                return [$panel1, $name, $dataAcquisto, $riferimentiAcquisto, $costo, $riferimentoCespite, $panelPortrait, $photoFile, $imagePortrait, $isOutOfOrder, $scadenzaManutenzione,  $funzione,  $panel_ID, $id, $createdAt ];
            } elseif (Crud::PAGE_NEW === $pageName) {
                return [$panel1, $name, $dataAcquisto, $riferimentiAcquisto, $costo, $riferimentoCespite, $panelPortrait, $photoFile, $imagePortrait, $isOutOfOrder, $scadenzaManutenzione,  $funzione ];
            } elseif (Crud::PAGE_EDIT === $pageName) {
                return [$panel1, $name, $dataAcquisto, $riferimentiAcquisto, $costo, $riferimentoCespite, $panelPortrait, $photoFile, $imagePortrait, $isOutOfOrder,  $scadenzaManutenzione,  $funzione,  $panel_ID, $id, $createdAt];
            }
    }
    
}
