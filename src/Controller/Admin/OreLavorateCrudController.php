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
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Filesystem\Filesystem;

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


    public function confirmView(Request $request):Response
    {
        $item = 0;
        if ($request !== null ){
            $context = $request->attributes->get(EA::CONTEXT_REQUEST_ATTRIBUTE);
            $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
            $filters = $this->get(FilterFactory::class)->create($context->getCrud()->getFiltersConfig(), $fields, $context->getEntity());
            $listaorari = $this->createIndexQueryBuilder($context->getSearch(), $context->getEntity(), $fields, $filters)
                ->getQuery()
                ->getResult();

                foreach ($listaorari as $orarioRecord) {
                    if ($orarioRecord->getIsConfirmed() === false) {
                        $orarioRecord->setIsConfirmed(true);
                        $this->entityManager->persist($orarioRecord);
                        $this->entityManager->flush();
                        $item++ ;
                    } 
                }
            }
        if ($item > 0 ) {
        $this->addFlash('success', sprintf('Sono stati confermati %d orari, se necessario eseguire altri filtri per confermare altri orari.', $item )); 
        } else {
        $this->addFlash('info', sprintf('Non ci sono orari da confermare.')); 
        }
        $crud = $context->getCrud();
        $controller = $crud->getControllerFqcn();
        $action     = $crud->getCurrentAction();
        $url = $this->adminUrlGenerator
        ->setController($controller)
        ->setAction('index')
        ->generateUrl();

        return (new RedirectResponse($url));
        
    }


    public function riepilogoOre(Request $request):Response
    {
        $item = 0;
        if ($request !== null ){
            $context = $request->attributes->get(EA::CONTEXT_REQUEST_ATTRIBUTE);
            $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
            $filters = $this->get(FilterFactory::class)->create($context->getCrud()->getFiltersConfig(), $fields, $context->getEntity());
            $listaorari = $this->createIndexQueryBuilder($context->getSearch(), $context->getEntity(), $fields, $filters)
                ->getQuery()
                ->getResult();

                // determina array personale ( max 20 persone = cartelle su un foglio di excel) Limitato per motivi di leggibilità 
                $personescelte = []; 
                $lastdate = new \DateTime; 
                $first = true; 
                foreach ($listaorari as $orarioRecord) {
                    // data più recente nei risultati
                    if ($first === true) {
                        $lastdate = $orarioRecord->getGiorno(); $first = false; 
                        $aziendaNickName = $orarioRecord->getAzienda()->getNickName();
                    } else {  
                        if ($orarioRecord->getGiorno() > $lastdate ) { $lastdate = $orarioRecord->getGiorno(); }
                    }
                    // array persone
                    $idpers =  $orarioRecord->getPersona()->getId();
                    if(array_key_exists($idpers, $personescelte) === false) { 
                        $personescelte[] = [$idpers => $orarioRecord->getPersona()->getFullName(), ]; $item++ ;
                    }
                    if ($item > 20 ) {
                       break; 
                    } 
                }
            if ($item <= 20 ) {    
                // prepara array (giorni del mese)
                $arrDaysOfMonth = $this->daysOfMonth($lastdate);
                $col = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH'];    
                
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
        
                $sheet->setTitle('Nome persona');
                $sheet->getCell('A1')->setValue('RIEPILOGO ORE MENSILI');
                $sheet->getCell('A3')->setValue('Nome Azienda');
                $sheet->getCell('C3')->setValue('Mese Anno');
                $sheet->getCell('A5')->setValue('Operatore');
                $sheet->getCell('B5')->setValue('Nome persona');
                $sheet->getCell('A7')->setValue('Cantiere');
                $d = 0;  // colonne dei giorni
                    foreach ( $arrDaysOfMonth as $dayOfMonth) {
                        foreach ( $dayOfMonth as $key => $valore) {
                           // $this->addFlash('info', sprintf('Array giorni del mese con key: %s valore: %s', $key, $valore) ); 
                           $cellalpha = $col[$d+1] ;
                           $sheet->getCell($cellalpha."7")->setValue($valore);
                           $d++;
                        } 
                    }
                   
                // ciclo lettura orari personale 


                // crea il file
                $writer = new Xlsx($spreadsheet);
                $filename = $aziendaNickName.'_riepilogo_personale_'.date_create()->format('Y-m-d\TH:i:s').'.xlsx';
                $writer->save('downloads/flowsalary/'.$filename);
            
                $filesystem = new Filesystem();
                //$filename = $this->adminUrlGenerator->get('azienda');
                $pathfile = 'downloads/flowsalary/'.$filename;
                // $filesystem->dumpFile($pathfile,  $value);
                $link = '<a href="'.$pathfile.'" download> Clicca qui per scaricarlo</a>';
                }
        }

        // risultati   
        if ($item > 0 ) {    
            if ($item <= 20 ) {
            // emissione file 
            $success =  sprintf('File excel prodotto. Sono state preparate %d cartelle, una ciascuna per persona. ', $item ) ; 
            $this->addFlash('success', $success.$link ); 
            } else {
            $this->addFlash('warning', sprintf('La selezione supera 20 persone, riepilogo troppo esteso e non rappresentabile.')); 
            }
        } else { $this->addFlash('info', sprintf('Riepilogo non rappresentabile con nessun risultato trovato.')); }

        // rimane sul crud attuale
        $crud = $context->getCrud();
        $controller = $crud->getControllerFqcn();
        $action     = $crud->getCurrentAction();
        $url = $this->adminUrlGenerator
        ->setController($controller)
        ->setAction('index')
        ->generateUrl();

        return (new RedirectResponse($url));
        
    }

    public function daysOfMonth($lastdate): array
    {
        $giornodellasettimana=array('','lun','mar','mer','gio','ven','sab','dom');//0 vuoto
        $arrDays = [];
        $mese = intval($lastdate->format('m')) ; $anno = intval($lastdate->format('Y'));
        $numday = cal_days_in_month(CAL_GREGORIAN, $mese , $anno);
        for ($ii=1; $ii<=$numday; $ii++) {
            // $giorno = new \DateTime;
            $giorno=mktime(0,0,0,$mese,$ii,$anno);
            $num_gg=(int)date("N",$giorno);
            $dayExcel = sprintf('%d %s', $ii, $giornodellasettimana[$num_gg] );
            $day = sprintf("%d-%'.02d-%'.02d", $anno, $mese, $ii );
            $arrDays[] = [$day => $dayExcel ]; 
            // $arrDays[] = $dayExcel;
        }
        return $arrDays ;
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
         ->linkToCrudAction('addOreLavorate')->setCssClass('btn')->displayIf(fn ($entity) => !$entity->getIsTransfer());
         
         $confirmView = Action::new('confirmView', 'Conferma orari in elenco')
         ->setIcon('fa fa-clipboard-check')->setHtmlAttributes(['title' => 'Conferma gli orari dell\'elenco attuale (usare i filtri per la selezione desiderata)'])
         ->linkToCrudAction('confirmView')
         ->setCssClass('btn btn-primary')
         ->createAsGlobalAction();
        
         $riepilogoOre = Action::new('riepilogoOre', 'Riepilogo ore in Excel')
         ->setIcon('fa fa-file-excel')->setHtmlAttributes(['title' => 'Produce un file di excel con il personale dell\'elenco attuale (usare i filtri per la selezione desiderata)'])
         ->linkToCrudAction('riepilogoOre')
         ->setCssClass('btn btn-secondary')
         ->createAsGlobalAction();
       
        return $actions
                ->remove(Crud::PAGE_INDEX, Action::NEW)
             //   ->remove(Crud::PAGE_INDEX, Action::DELETE)
                ->remove(Crud::PAGE_DETAIL, Action::DELETE)
                ->add(Crud::PAGE_INDEX, $add_orelavorate)->add(Crud::PAGE_EDIT, $add_orelavorate)
                ->add(Crud::PAGE_INDEX, $confirmView)  ->add(Crud::PAGE_INDEX, $riepilogoOre)
                // ...
                ->add(Crud::PAGE_INDEX, Action::DETAIL)
                // ->add(Crud::PAGE_DETAIL,)
                //->add(Crud::PAGE_EDIT,  Action::INDEX )
                ->add(Crud::PAGE_NEW,   Action::INDEX )
    
                ->update(Crud::PAGE_INDEX, Action::EDIT,
                 fn (Action $action) => $action->setIcon('fa fa-edit')->displayIf(fn ($entity) => !$entity->getIsConfirmed() 
                 ) )
                 ->update(Crud::PAGE_DETAIL, Action::EDIT,
                 fn (Action $action) => $action->displayIf(fn ($entity) => !$entity->getIsConfirmed() 
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
// ->unsetAll()
        $url = $this->adminUrlGenerator
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
        $giorno = DateField::new('giorno', 'Data'); //->setFormTypeOptions(['disabled' => 'true']);;
        $dayOfWeek = TextField::new('dayOfWeek', 'Giorno')->setFormTypeOptions(['disabled' => 'true']);;
        $isConfirmed= BooleanField::new('isConfirmed', 'Orario confermato');
        $orePianificate = TextField::new('orePianificate', 'Ore previste')->setFormTypeOptions(['disabled' => 'true']);
        $keyReference = TextField::new('keyReference', 'Chiave registrazione')->setFormTypeOptions(['disabled' => 'true']);
        $oreRegistrate = TextField::new('oreRegistrate', 'Ore lavorate')->setHelp('<mark>Inserire 0 ore se orario da annullare.</mark>');
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


