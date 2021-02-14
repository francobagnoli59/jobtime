<?php

namespace App\Controller\Admin;

use App\Entity\OreLavorate;
use App\Entity\FestivitaAnnuali;
use App\Entity\MesiAziendali;
use App\Entity\Personale;
use App\Entity\Cantieri;
use App\Entity\Aziende;
use App\Repository\AziendeRepository;
use App\Repository\CantieriRepository;
use App\Repository\PersonaleRepository;
//use App\Repository\CausaliRepository;
use Doctrine\ORM\EntityManagerInterface;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;



class OreLavorateCrudController extends AbstractCrudController
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
        return OreLavorate::class;
    }


    public function createEntity(string $entityFqcn)
    {
        $orelavorate = new OreLavorate();

        $azienda_id = $this->adminUrlGenerator->get('azienda');
        $orelavorate->setAzienda($this->entityManager->getRepository(Aziende::class)->findOneBy(['id'=> $azienda_id]));
        $persona_id = $this->adminUrlGenerator->get('persona');
        $orelavorate->setPersona($this->entityManager->getRepository(Personale::class)->findOneBy(['id'=> $persona_id]));
        $cantiere_id = $this->adminUrlGenerator->get('cantiere');
        $orelavorate->setCantiere($this->entityManager->getRepository(Cantieri::class)->findOneBy(['id'=> $cantiere_id]));
        $date = new \DateTime();
        $date->setTime(0,0,0);
        $date->setDate($this->adminUrlGenerator->get('anno'), $this->adminUrlGenerator->get('mese'), $this->adminUrlGenerator->get('giorno'));
        $orelavorate->setGiorno($date);
        $orelavorate->setOrePianificate('0');
        $orelavorate->setOreRegistrate('0');
        $orelavorate->setIsTransfer(false);
        $orelavorate->setIsConfirmed(false);

        return  $orelavorate;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
          // key ore giornata
        $keyreference =  $entityInstance->getKeyReference();
        $retcode = $this->verifyEntity($entityManager, $entityInstance);
        if ($retcode === 'OK') {
            $entityManager->persist($entityInstance);
            $entityManager->flush();
        } else { $this->addFlash('danger', sprintf('Orario di lavoro con key %s non modificato!!!', $keyreference )); }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $retcode = $this->verifyEntity($entityManager, $entityInstance);
        if ($retcode === 'OK') {
            $entityManager->persist($entityInstance);
            $entityManager->flush();
        } else { $this->addFlash('danger', 'Orario di lavoro non inserito!!!'); }
    }

    private function verifyEntity(EntityManagerInterface $entityManager, $entityInstance): string
    {

        // dati ore giornata
        $azienda_id = $entityInstance->getAzienda();
        $retcode = 'ER';

        // Controllo giorno nel periodo pianificato
        $count = $entityManager->getRepository(MesiAziendali::class)->countOpenMonth($azienda_id);
            switch ($count) {
                case 0:
                    // Mesnsilità chiusa
                    $this->addFlash('danger',  'Mese già consolidato, modifica non ammessa.');

                    break;
                case 1:
                    // ci deve essere solo un mese da consolidare per azienda
                    $resultId = $entityManager->getRepository(MesiAziendali::class)->getIdOpenMonth($azienda_id);
                    $mesiaziendali = $entityManager->getRepository(MesiAziendali::class)->findOneBy(['id'=> $resultId]);
                    $festivitaAnnuale_id = $mesiaziendali->getFestivitaAnnuale();
                    $meseopen = $mesiaziendali->getMese();
                    // legge anno dalle festività dell'anno
                    $festivita = $entityManager->getRepository(FestivitaAnnuali::class)->findOneBy(['id'=> $festivitaAnnuale_id]);
                    $annoopen = $festivita->getAnno();
                    // confronta con la data impostata
                    $anno = $entityInstance->getGiorno()->format('Y');
                    $mese = $entityInstance->getGiorno()->format('m');                   
                        if ($anno === $annoopen && $mese === $meseopen ) { 
                            // controlla che il nominativo appartenga alla azienda impostata
                            if ($entityInstance->getPersona()->getAzienda() === $azienda_id ) {
                                  // controlla che il cantiere appartenga alla azienda impostata
                                    if ($entityInstance->getCantiere()->getAzienda() === $azienda_id ) {
                                        if($entityInstance->getCausale()->getCode() === 'STRA') { 
                                            if ( $entityInstance->getPersona()->getCostoStraordinario() > 0) {
                                                if($entityInstance->getCantiere()->getExtraRate() > 0  ) { 
                                                    // Straordinaro quotato
                                                    $retcode = 'OK';
                                                } else {  
                                                    // Accetta ma informa  - Prezzo ore straordinario non quotato sul cantiere
                                                    $this->addFlash('warning', sprintf('Tariffa ora straordinaria NON contrattualizzata, lo straordinario è a costo sull\'azienda'));
                                                    $retcode = 'OK';
                                                }
                                            } else {
                                                $this->addFlash('danger', 'La persona non ha quotato il costo lavoro straordinario');
                                            }
                                        }
                                        else {  // Tutto OK aggiona ore giornata
                                            $retcode = 'OK'; } 
                                      } else { $this->addFlash('danger', 'L\'azienda non corrisponde al cantiere selezionato'); }
                              }  else { $this->addFlash('danger', 'L\'azienda non corrisponde al nominativo selezionato'); }
                        } else  { $this->addFlash('danger', sprintf('La data deve essere relativa all\'anno %d e al mese %d ancora da consolidare', $annoopen , $meseopen)); }
                    break;
                default :
                    $this->addFlash('danger',  'Anomalia, ci sono più mesi da consolidare!!! modifica non ammessa.');
                    
                    break;
            }

        return $retcode ;
    }


    public function configureCrud(Crud $crud): Crud
    {
    
        return $crud
            ->setEntityLabelInSingular('Orario di lavoro')
            ->setEntityLabelInPlural('Ore Lavorate')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Ore Lavorate')
            ->setPageTitle(Crud::PAGE_NEW, 'Registra ore lavorate')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (OreLavorate $orario) => sprintf('Ore giornata lavorate da <b>%s</b>', $orario->getPersona()->getFullName()))
            ->setPageTitle(Crud::PAGE_EDIT, fn (OreLavorate $orario) => sprintf('Modifica Ore giornata lavorate da <b>%s</b>', $orario->getPersona()->getFullName()))
            ->setSearchFields(['id', 'giorno', 'azienda.nickName', 'cantiere.nameJob', 'persona.surname', 'oreRegistrate'])
            ->setDefaultSort(['persona' => 'ASC', 'giorno' => 'ASC', 'cantiere' => 'ASC'])
            ->showEntityActionsAsDropdown();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('isTransfer', 'Orari trasferiti'))
            ->add(BooleanFilter::new('isConfirmed', 'Orari confermati'))
            ->add('giorno')
            ->add(EntityFilter::new('causale', 'Causale lavoro'))
            ->add(EntityFilter::new('azienda')->setFormTypeOption('value_type_options.query_builder', 
                static fn(AziendeRepository $az) => $az->createQueryBuilder('azienda')
                        ->orderBy('azienda.nickName', 'ASC') ) ) 
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
          $add_orelavorate = Action::new('addOreLavorate', 'Aggiungi Ore lavorate', 'fa fa-calendar-plus')
         ->linkToCrudAction('addOreLavorate')->displayIf(fn ($entity) => !$entity->getIsTransfer());
           
       
        return $actions
                ->remove(Crud::PAGE_INDEX, Action::NEW)
             //   ->remove(Crud::PAGE_INDEX, Action::DELETE)
                ->remove(Crud::PAGE_DETAIL, Action::DELETE)
                ->add(Crud::PAGE_INDEX, $add_orelavorate)->add(Crud::PAGE_EDIT, $add_orelavorate)
                // ...
                ->add(Crud::PAGE_INDEX, Action::DETAIL)
                // ->add(Crud::PAGE_DETAIL,)
                //->add(Crud::PAGE_EDIT,  Action::INDEX )
                ->add(Crud::PAGE_NEW,   Action::INDEX )
    
                ->update(Crud::PAGE_INDEX, Action::EDIT,
                 fn (Action $action) => $action->setIcon('fa fa-edit')->displayIf(fn ($entity) => !$entity->getIsConfirmed() 
                 ) )
                 ->update(Crud::PAGE_INDEX, Action::DELETE,
                 fn (Action $action) => $action->setIcon('fa fa-trash')->displayIf(fn ($entity) => $entity->getOrePianificate() === '0' && !$entity->getIsTransfer() 
                 ) ) 
                ->update(Crud::PAGE_INDEX, Action::DETAIL,
                 fn (Action $action) => $action->setIcon('fa fa-eye') )
            ;  // fn ($entity) => !$entity->getIsTransfer() && 
    }

    public function addOreLavorate(AdminContext $context)
    {
        $oreitem = $context->getEntity()->getInstance();

        $url = $this->adminUrlGenerator->unsetAll()
            ->setController(OreLavorateCrudController::class)
            ->setAction(Action::NEW)
            ->set('azienda', $oreitem->getAzienda()->getId())
            ->set('cantiere', $oreitem->getCantiere()->getId())
            ->set('persona', $oreitem->getPersona()->getId())
            ->set('anno', $oreitem->getGiorno()->format('Y'))
            ->set('mese', $oreitem->getGiorno()->format('m'))
            ->set('giorno', $oreitem->getGiorno()->format('d'));
            return $this->redirect($url);
    }

     public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('ORE LAVORATE')->setIcon('fas fa-clock');
        $giorno = DateField::new('giorno', 'Data')->setFormTypeOptions(['disabled' => 'true']);;
        $dayOfWeek = TextField::new('dayOfWeek', 'Giorno')->setFormTypeOptions(['disabled' => 'true']);;
        $isConfirmed= BooleanField::new('isConfirmed', 'Orario confermato');
        $orePianificate = TextField::new('orePianificate', 'Ore previste')->setFormTypeOptions(['disabled' => 'true']);
        $keyReference = TextField::new('keyReference', 'Chiave registrazione')->setFormTypeOptions(['disabled' => 'true']);
        $oreRegistrate = TextField::new('oreRegistrate', 'Ore lavorate');
        $azienda = AssociationField::new('azienda', 'Azienda')
            ->setFormTypeOptions([
            'query_builder' => function (AziendeRepository $az) {
                return $az->createQueryBuilder('a')
                    ->orderBy('a.nickName', 'ASC');
            },
            ])->setRequired(true)->setCustomOptions(array('widget' => 'native'));
        $cantiere = AssociationField::new('cantiere', 'Cantiere')
            ->setFormTypeOptions([
            'query_builder' => function (CantieriRepository $ca) {
                 return $ca->createQueryBuilder('c')
                     ->orderBy('c.nameJob', 'ASC');
            },
            ])->setRequired(true)->setCustomOptions(array('widget' => 'native'));
        $persona = AssociationField::new('persona', 'Nome persona')
            ->setFormTypeOptions([
            'query_builder' => function (PersonaleRepository $pe) {
                  return $pe->createQueryBuilder('p')
                      ->orderBy('p.surname', 'ASC');
            }, 'disabled' => 'true'
        ] )->setRequired(true)->setCustomOptions(array('widget' => 'native'));
        $causale = AssociationField::new('causale', 'Causale orario')->setRequired(true)->setCustomOptions(array('widget' => 'native'));
          /*   ->setFormTypeOptions([
            'query_builder' => function (CausaliRepository $cp) {
                return $cp->createQueryBuilder('u')
                    ->orderBy('u.code', 'ASC');
            },
            ])->setRequired(true)->setCustomOptions(array('widget' => 'native'));  */

        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);
        
        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $azienda, $persona, $cantiere, $giorno, $dayOfWeek, $causale,  $orePianificate, $oreRegistrate, $isConfirmed];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $azienda, $persona, $cantiere, $giorno, $dayOfWeek, $causale, $orePianificate, $oreRegistrate, $isConfirmed,  $panel_ID, $id, $keyReference, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $azienda, $persona, $cantiere, $giorno, $dayOfWeek, $causale, $orePianificate, $oreRegistrate, $isConfirmed];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1,  $azienda, $persona, $cantiere, $giorno, $dayOfWeek, $causale, $orePianificate, $oreRegistrate,  $isConfirmed, $panel_ID, $id, $keyReference, $createdAt];
        }
    }
}


