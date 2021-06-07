<?php

namespace App\Controller\Admin;

use App\Entity\MovimentiAttrezzature;
use App\Entity\Attrezzature;
use App\Entity\Personale;
use App\Entity\Cantieri;
// use App\Entity\Aziende;
// use App\Repository\AziendeRepository;
use App\Repository\CantieriRepository;
use App\Repository\PersonaleRepository;
use App\Repository\AttrezzatureRepository; 

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
 
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;


use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/* use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse; */


class MovimentiAttrezzatureCrudController extends AbstractCrudController
{

    protected EntityManagerInterface $entityManager;
    
    /**
     * @var AdminUrlGenerator
     */
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(EntityManagerInterface $entityManager,  AdminUrlGenerator $adminUrlGenerator)
    {
    $this->entityManager = $entityManager;
    $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return MovimentiAttrezzature::class;
    }

    public function createEntity(string $entityFqcn)
    {
        $movimentiattrezzature = new MovimentiAttrezzature();
        //$azienda_id = $this->adminUrlGenerator->get('azienda');
        // $movimentiattrezzature->setAzienda($this->entityManager->getRepository(Aziende::class)->findOneBy(['id'=> $azienda_id]));
      
        $attrezzatura_id = $this->adminUrlGenerator->get('attrezzatura');
        $movimentiattrezzature->setAttrezzatura($this->entityManager->getRepository(Attrezzature::class)->findOneBy(['id'=> $attrezzatura_id]));
        $persona_id = $this->adminUrlGenerator->get('persona');
        $movimentiattrezzature->setPersona($this->entityManager->getRepository(Personale::class)->findOneBy(['id'=> $persona_id]));
        $cantiere_id = $this->adminUrlGenerator->get('cantiere');
        $movimentiattrezzature->setCantiere($this->entityManager->getRepository(Cantieri::class)->findOneBy(['id'=> $cantiere_id]));
        $date = new \DateTime();
        $date->setTime(0,0,0);
        $movimentiattrezzature->setDataMovimento($date);
       
        return  $movimentiattrezzature;
    }

 /*      public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $response = $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
          /*  $azienda = $this->getUser()->getAziendadefault();
        if ($azienda !== null ) {
            $aziendaId = $azienda->getId();
        $response->andWhere('entity.azienda = '. $aziendaId);
        } else {  $response->andWhere('entity.azienda = 0'); } // così non visualizza niente 

        return $response;
    }  */

    public function configureCrud(Crud $crud): Crud
    {
/*         $azienda = $this->getUser()->getAziendadefault();
        if ($azienda !== null ) {
            $aziendaNickName = $azienda->getNickName();
        } else { $aziendaNickName = '...seleziona azienda!!!'; }  
        // $LabelPlur = 'Ore Lavorate '.$aziendaNickName ; 
 */
      
        $LabelSing = 'Spostamento attrezzatura ' ;
        $LabelPlur = 'Spostamenti attrezzature ';
        $LabelNew = 'Registra spostamento attrezzatura ' ; 
        $Labellist = 'Elenco spostamenti ' ;

        return $crud
            ->setEntityLabelInSingular($LabelSing)
            ->setEntityLabelInPlural($LabelPlur)
            ->setPageTitle(Crud::PAGE_INDEX,  $Labellist)
            ->setPageTitle(Crud::PAGE_NEW, $LabelNew)
            ->setPageTitle(Crud::PAGE_DETAIL, fn (MovimentiAttrezzature $attrezzo) => sprintf('Spostamento attrezzatura <b>%s</b>', $attrezzo->getAttrezzatura()))
            ->setPageTitle(Crud::PAGE_EDIT, fn (MovimentiAttrezzature $attrezzo) => sprintf('Modifica spostamento attrezzatura <b>%s</b>', $attrezzo->getAttrezzatura()))
            ->setSearchFields(['id', 'dataMovimento', 'cantiere.nameJob', 'persona.surname', 'attrezzatura.name'])
            ->setDefaultSort(['attrezzatura' => 'ASC', 'dataMovimento' => 'DESC', 'cantiere' => 'ASC'])
            ->showEntityActionsAsDropdown();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters

            ->add('dataMovimento')
/*             ->add(EntityFilter::new('azienda')->setFormTypeOption('value_type_options.query_builder', 
                static fn(AziendeRepository $az) => $az->createQueryBuilder('azienda')
                        ->orderBy('azienda.nickName', 'ASC') ) )  */
            ->add(EntityFilter::new('attrezzatura')->setFormTypeOption('value_type_options.query_builder', 
                static fn(AttrezzatureRepository $at) => $at->createQueryBuilder('attrezzatura')
                     ->orderBy('attrezzatura.name', 'ASC') ) )
            ->add(EntityFilter::new('cantiere')->setFormTypeOption('value_type_options.query_builder', 
                static fn(CantieriRepository $ca) => $ca->createQueryBuilder('cantiere')
                     ->orderBy('cantiere.nameJob', 'ASC') ) )
            ->add(EntityFilter::new('persona')->setFormTypeOption('value_type_options.query_builder', 
               static fn(PersonaleRepository $pe) => $pe->createQueryBuilder('personale')
                    ->orderBy('personale.surname', 'ASC') ) )
             ;
        
    }
 
    
    public function configureActions(Actions $actions): Actions
    {
          $add_spostamento = Action::new('addSpostamento', 'Aggiungi Spostamento', 'fa fa-truck-loading')
         ->linkToCrudAction('addSpostamento')->displayIf(fn ($entity) => !$entity->getAttrezzatura()->getIsOutOfOrder());
      // ->displayIf(fn ($entity) => !$entity->getIsOutOfOrder()) ->createAsGlobalAction() ->setCssClass('btn');

        return $actions
                ->remove(Crud::PAGE_INDEX, Action::NEW)
             //   ->remove(Crud::PAGE_INDEX, Action::DELETE)
                ->remove(Crud::PAGE_DETAIL, Action::DELETE)
                ->remove(Crud::PAGE_DETAIL, Action::INDEX)
                ->add(Crud::PAGE_INDEX, $add_spostamento)
                // ...->add(Crud::PAGE_EDIT, $add_spostamento)
                ->add(Crud::PAGE_INDEX, Action::DETAIL)
                // ->add(Crud::PAGE_DETAIL,)
                //->add(Crud::PAGE_EDIT,  Action::INDEX )
                ->add(Crud::PAGE_NEW,   Action::INDEX )
    
                ->update(Crud::PAGE_INDEX, Action::EDIT,
                 fn (Action $action) => $action->setIcon('fa fa-edit') )
                ->update(Crud::PAGE_DETAIL, Action::EDIT,
                 fn (Action $action) => $action )
                 ->update(Crud::PAGE_INDEX, Action::DELETE,
                 fn (Action $action) => $action->setIcon('fa fa-trash') ) 
                ->update(Crud::PAGE_INDEX, Action::DETAIL,
                 fn (Action $action) => $action->setIcon('fa fa-eye') )
            ;  // fn ($entity) => !$entity->getIsTransfer() && 
    }

    public function addSpostamento(AdminContext $context)
    {
        $movItem = $context->getEntity()->getInstance();

        $url = $this->adminUrlGenerator->unsetAll()
            ->setController(MovimentiAttrezzatureCrudController::class)
            ->setAction(Action::NEW)
            ->set('persona', $movItem->getPersona()->getId())
            ->set('cantiere', $movItem->getCantiere()->getId())
            ->set('attrezzatura', $movItem->getAttrezzatura()->getId());
            return $this->redirect($url);
    }

     public function configureFields(string $pageName): iterable
    {
       /*  $azienda = $this->getUser()->getAziendadefault();
        if ($azienda !== null ) {
            $statusAzienda = true ; $helpAz = '';}
            else { $statusAzienda = false ;} */

        $panel1 = FormField::addPanel('MOVIMENTA ATTREZZATURA')->setIcon('fas fa-truck-moving');
        $attrezzatura = AssociationField::new('attrezzatura', 'Attrezzatura')
        ->setFormTypeOptions([
        'query_builder' => function (AttrezzatureRepository $at) {
             return $at->createQueryBuilder('a')
                 ->orderBy('a.name', 'ASC');
        }, 'disabled' => 'true'
        ])->setRequired(true)->setCustomOptions(array('widget' => 'native'));
        $giorno = DateField::new('dataMovimento', 'Giorno spostamento'); //->setFormTypeOptions(['disabled' => 'true']);;
        $giornoEdit = DateField::new('dataMovimento', 'Giorno spostamento')->setFormTypeOptions(['disabled' => 'true']);
        /*  $azienda = AssociationField::new('azienda', 'Azienda')
            ->setFormTypeOptions([
            'query_builder' => function (AziendeRepository $az) {
                return $az->createQueryBuilder('a')
                    ->orderBy('a.nickName', 'ASC');
            },
            ])->setRequired(true)->setCustomOptions(array('widget' => 'native'))->setFormTypeOptions(['disabled' => $statusAzienda]); */
        
        $cantiere = AssociationField::new('cantiere', 'Cantiere di destinazione')
            ->setFormTypeOptions([
            'query_builder' => function (CantieriRepository $ca) {
                 return $ca->createQueryBuilder('c')
                     ->orderBy('c.nameJob', 'ASC');
            },
            ])->setRequired(true)->setCustomOptions(array('widget' => 'native'));
        $cantiereEdit = AssociationField::new('cantiere', 'Cantiere di destinazione')
        ->setFormTypeOptions([
        'query_builder' => function (CantieriRepository $ca) {
                return $ca->createQueryBuilder('c')
                    ->orderBy('c.nameJob', 'ASC');
        }, 'disabled' => 'true'
        ])->setRequired(true)->setCustomOptions(array('widget' => 'native'));
        $personaView = AssociationField::new('persona', 'Persona responsabile spostamento')->setCrudController(PersonaleCrudController::class);
        $persona = AssociationField::new('persona', 'Persona responsabile spostamento')
            ->setFormTypeOptions([
            'query_builder' => function (PersonaleRepository $pe) {
                  return $pe->createQueryBuilder('p')
                      ->orderBy('p.surname', 'ASC');
            }, 
        ] )->setRequired(true)->setCustomOptions(array('widget' => 'native'));

        $note =TextEditorField::new('note', 'Note spostamento');

        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);
        
        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $attrezzatura,  $giorno, $cantiere, $personaView ];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $attrezzatura,  $giorno, $cantiere, $personaView, $note, $panel_ID, $id, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $attrezzatura,  $giorno, $cantiere, $persona, $note];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1,  $attrezzatura,  $giornoEdit, $cantiereEdit, $persona, $note, $panel_ID, $id, $createdAt];
        }
    }
}


