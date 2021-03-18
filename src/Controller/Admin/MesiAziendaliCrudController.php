<?php

namespace App\Controller\Admin;

use App\Entity\MesiAziendali;
use App\Entity\OreLavorate;
use App\Entity\FestivitaAnnuali;
use App\Entity\Personale;
use App\Entity\Cantieri;
use App\Entity\ConsolidatiPersonale;
use App\Entity\ConsolidatiCantieri;
use App\Repository\AziendeRepository;
use App\Repository\FestivitaAnnualiRepository;
use App\ServicesRoutine\DateUtility;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;


class MesiAziendaliCrudController extends AbstractCrudController
{
    /**
     * @var AdminUrlGenerator
     */
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(EntityManagerInterface $entityManager,  AdminUrlGenerator $adminUrlGenerator ) 
    {
    $this->entityManager = $entityManager;
    $this->adminUrlGenerator = $adminUrlGenerator;
    }


    public static function getEntityFqcn(): string
    {
        return MesiAziendali::class;
    }

    public function buildFlowSalary(AdminContext $context)
    {
        $meseaziendale = $context->getEntity()->getInstance();

        $url = $this->adminUrlGenerator->unsetAll()
            ->setController(FlowSalaryExportController::class)
            ->setRoute('exportflowsalary')
            ->set('aziendamese', $meseaziendale->getAzienda().substr($meseaziendale->getKeyReference(), 10 ) )
            ->set('keyreference', $meseaziendale->getKeyReference());
            return $this->redirect($url);   
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
         // dati pianificazione selezionata
         $keyreference =  $entityInstance->getKeyReference();
         $azienda = $entityInstance->getAzienda();
         $festivitaAnnuale_id = $entityInstance->getFestivitaAnnuale();
         $mese = $entityInstance->getMese();
         // legge anno dalle festività dell'anno
         $festivita = $entityManager->getRepository(FestivitaAnnuali::class)->findOneBy(['id'=> $festivitaAnnuale_id]);
         $anno = $festivita->getAnno();
         // valori iniziali per preparazione periodo mensile (primo e ultimo giorno)
         $dateutility = new DateUtility ;
         $limitiMese = $dateutility->calculateLimitMonth($anno, $mese);
         // $finemese = $limitiMese[0] ;
         $dataInizio = $limitiMese[1] ;
         $dataFine = $limitiMese[2] ;
         
         // contatori e array di totalizzazione
         $countTotalPersone = $entityInstance->getNumeroPersone();
         $countConfirmedPersone = 0;
         $countAlreadyConfPersone = 0;
         $countNotConfPersone = 0;
         $arrayCantieri = [];   // array che contiene index Id cantiere
         $totArrayCantieri = []; // array che contiene totalizzatori a parità di index
         // entra nel ciclo solo se ci sono orari confermati nel mese selezionato
        if ( $entityManager->getRepository(OreLavorate::class)->countConfirmed($azienda, true, $dataInizio, $dataFine) > 0 )
        {
            // ciclo sul personale dell'azienda
            $personaledataset = $entityManager->getRepository(Personale::class)->findBy(['azienda'=> $azienda]);
            foreach ($personaledataset as $persona) {
                  // personale assunto
                  $valido = false ;  
                  if ( $persona->getIsEnforce() === true ) {
                    // esclude personale che è già stato consolidato nel mese 
                    $keyRef_Pers_MeseAz = sprintf("%010d-%010d", $persona->getId(), $entityInstance->getId()); 
                    if ( $entityManager->getRepository(ConsolidatiPersonale::class)->findOneByKeyReference($keyRef_Pers_MeseAz) === null ) {
                       
                        // Ore lavorate nel mese tutte confermate
                        $count = $entityManager->getRepository(OreLavorate::class)->countPersonaConfirmed($persona, true , $dataInizio, $dataFine);
                        if ($count > 0) {
                            $count = $entityManager->getRepository(OreLavorate::class)->countPersonaConfirmed($persona, false , $dataInizio, $dataFine);
                            if ($count === 0) {
                                $countConfirmedPersone++ ;
                                $oreLavoro = 0; $orePianificate = 0; $oreStraordinario = 0; 
                                $oreImproduttive = 0; $oreIninfluenti = 0; $costoLavoro = 0;
                                $costoOrario = floatval($persona->getFullCostHour());  
                                $costoStraordinario = floatval($persona->getCostoStraordinario()) ;  
                                // Seleziona le ore lavorate e confermate del mese
                                $oreLavorateCollection = $entityManager->getRepository(OreLavorate::class)->collectionPersonaConfirmed($persona, true , $dataInizio, $dataFine);
                                // ciclo sulla collection delle ore lavorate
                                foreach ($oreLavorateCollection as $ol ){
                                    $causale = $ol->getCausale()->getCode();
                                    $oreReg = $ol->getOreRegistrate();
                                    $orePian = $ol->getOrePianificate();
                                    $cantiere = $ol->getCantiere()->getId();
                                    // Somma per ogni cantiere coinvolto
                                    // determina index cantiere
                                    if (in_array($cantiere, $arrayCantieri) === true ) {
                                        $keyarr = array_search($cantiere, $arrayCantieri);
                                        $keynew = false ;
                                    } else {
                                       // prima volta, nuovo cantiere mai incontrato
                                       $arrayCantieri[] = $cantiere ;
                                       $keynew = true ;
                                    }
                                    $oreCantLav = 0;  $oreCantStra = 0;  $oreCantInin = 0 ; $oreCantImpr = 0 ;  $costoCantLav = 0;
                                     // indipendente dalla causale somma ore pianificate
                                    $oreCantPian = $orePian;                                
                                    $orePianificate += $orePian ;
                                    switch ($causale) {
                                        case 'ORDI' :
                                            $oreLavoro += $oreReg ;
                                            $costoLavoro += floatval($oreReg * $costoOrario);
                                            $oreCantLav = $oreReg ;
                                            $costoCantLav = floatval($oreReg * $costoOrario) ;
                                            break;
                                        case 'STRA' :
                                            $oreStraordinario += $oreReg ;
                                            $costoLavoro += floatval($oreReg * $costoStraordinario);
                                            $oreCantStra = $oreReg ;
                                            $costoCantLav = floatval($oreReg * $costoStraordinario) ;
                                            break;
                                        case '*SC' :
                                        case '*NG' :
                                            $oreIninfluenti += $oreReg ;
                                            $oreCantInin = $oreReg ;
                                            break;
                                        default:
                                        $oreImproduttive += $oreReg ;
                                        $costoLavoro += floatval($oreReg * $costoOrario);
                                        $oreCantImpr = $oreReg ;
                                        $costoCantLav = floatval($oreReg * $costoOrario) ;
                                        break;
                                    }
                                    // inserisce array cantieri con stesso indice di $arrayCantieri
                                    if ($keynew === true ) {
                                        $totArrayCantieri[] = array('pianificate' => $oreCantPian, 'lavorate' => $oreCantLav,
                                         'straordinarie' => $oreCantStra, 'ininfluenti' => $oreCantInin, 
                                         'improduttive' => $oreCantImpr, 'costolavoro' => $costoCantLav) ;
                                    } else {
                                        // legge valori prececenti
                                        $arrCantItem = $totArrayCantieri[$keyarr];
                                        $totCantPian = $arrCantItem['pianificate'] ; $totCantLav = $arrCantItem['lavorate'] ; 
                                        $totCantStra = $arrCantItem['straordinarie'] ; $totCantInin = $arrCantItem['ininfluenti'] ; 
                                        $totCantImpr = $arrCantItem['improduttive'] ;  $totCantCostoLav = $arrCantItem['costolavoro'] ;
                                        // somma valori correnti ai precedenti e aggiorna array
                                        $totArrayCantieri[$keyarr] = array('pianificate' => $oreCantPian + $totCantPian, 'lavorate' => $oreCantLav + $totCantLav,
                                        'straordinarie' => $oreCantStra + $totCantStra, 'ininfluenti' => $oreCantInin + $totCantInin, 
                                        'improduttive' => $oreCantImpr + $totCantImpr, 'costolavoro' => floatval($costoCantLav + $totCantCostoLav)) ;
                                     }
                                    $valido = true ; 
                                } // ciclo ore lavorate
                            } else { $countNotConfPersone++ ;}
                        } // Ore Lavorate nel mese tutte confermate
                    } // personale consolidato: lo conta
                    else { $countAlreadyConfPersone++ ; }
                  } // personale assunto

                  if ($valido === true ) {
                    //Consolida persona
                     // $this->addFlash('info', sprintf('COSTO LAV: %05.2f  Ore Pian: %03.2f - Ore Ord:  %05.2f - Ore Str:  %05.2f - Ore Imp:  %05.2f  - Ore Ini:  %05.2f ', $costoLavoro, $orePianificate, $oreLavoro, $oreStraordinario, $oreImproduttive, $oreIninfluenti ) );
                     $consolidatopersona = new ConsolidatiPersonale();
                     $consolidatopersona->setPersona($persona);
                     $consolidatopersona->setMeseAziendale($entityInstance);
                     $consolidatopersona->setCostoLavoro(round($costoLavoro,2));
                     $consolidatopersona->setOreLavoro($oreLavoro)->setOrePianificate($orePianificate)->setOreStraordinario($oreStraordinario);
                     $consolidatopersona->setOreImproduttive($oreImproduttive)->setOreIninfluenti($oreIninfluenti);
                     $entityManager->persist($consolidatopersona);
                     $entityManager->flush();
                    }
            }  // ciclo sul personale dell'azienda

            // 
            // Registra consolidato per Cantiere.
            if ($countConfirmedPersone > 0) {
                $globOreCantPian = 0; $globOreCantLav = 0;  $globOreCantStra = 0;  $globOreCantInin = 0 ; $globOreCantImpr = 0 ;  $globCostoCantLav = 0;
                $kArrCant = array_keys($arrayCantieri);
                foreach ($kArrCant as $Kcant) {
                  $idcant = $arrayCantieri[$Kcant] ;
                  $tcant = $totArrayCantieri[$Kcant] ;
                  $totCantPian = $tcant['pianificate'] ; $totCantLav = $tcant['lavorate'] ; 
                  $totCantStra = $tcant['straordinarie'] ; $totCantInin = $tcant['ininfluenti'] ; 
                  $totCantImpr = $tcant['improduttive'] ;  $totCantCostoLav = $tcant['costolavoro'] ;
                  // totali per mesi aziendali
                  $globOreCantPian += $totCantPian;
                  $globOreCantLav += $totCantLav;
                  $globOreCantStra += $totCantStra;
                  $globOreCantInin += $totCantInin;
                  $globOreCantImpr += $totCantImpr;
                  $globCostoCantLav += floatval($totCantCostoLav);
                  // Consolidamento Cantieri
                    // esclude personale che è già stato consolidato nel mese 
                    $keyRef_Cant_MeseAz = sprintf("%010d-%010d", $idcant, $entityInstance->getId()); 
                    if ( $entityManager->getRepository(ConsolidatiCantieri::class)->findOneByKeyReference($keyRef_Cant_MeseAz) === null ) {
                        // nuovo inserimento
                        $cantiereRecord = $entityManager->getRepository(Cantieri::class)->findOneBy(['id'=> $idcant]);
                        $consolidatocantiere = new ConsolidatiCantieri();
                        $consolidatocantiere->setCantiere($cantiereRecord);
                        $consolidatocantiere->setMeseAziendale($entityInstance);
                        $consolidatocantiere->setCostoOreLavoro(round($totCantCostoLav,2));
                        $consolidatocantiere->setOreLavoro($totCantLav)->setOrePianificate($totCantPian)->setOreStraordinario($totCantStra);
                        $consolidatocantiere->setOreImproduttive($totCantImpr)->setOreIninfluenti($totCantInin);
                        $entityManager->persist($consolidatocantiere);
                        $entityManager->flush();
                    } else {
                        // cantiere già esistente viene aggiornato
                        $cantiereRecord = $entityManager->getRepository(ConsolidatiCantieri::class)->findOneByKeyReference($keyRef_Cant_MeseAz) ;
                        $cantiereRecord->setOreLavoro($cantiereRecord->getOreLavoro() + $totCantLav);
                        $cantiereRecord->setOrePianificate($cantiereRecord->getOrePianificate() + $totCantPian);
                        $cantiereRecord->setOreStraordinario($cantiereRecord->getOreStraordinario() + $totCantStra);
                        $cantiereRecord->setOreImproduttive($cantiereRecord->getOreImproduttive() + $totCantImpr);
                        $cantiereRecord->setOreIninfluenti($cantiereRecord->getOreIninfluenti() + $totCantInin);
                        $cantiereRecord->setCostoOreLavoro(round(($cantiereRecord->getCostoOreLavoro() + $totCantCostoLav),2));
                        $entityManager->persist($cantiereRecord);
                        $entityManager->flush();
                    }

                  //$this->addFlash('info', sprintf('CANTIERE: %d : Costo: %05.2f  Ore Pian: %03.2f - Ore Ord:  %05.2f - Ore Str:  %05.2f - Ore Imp:  %05.2f  - Ore Ini:  %05.2f  ', 
                  //$idcant, $totCantCostoLav, $totCantPian, $totCantLav, $totCantStra, $totCantImpr, $totCantInin) );
                }
                  // aggiorna mesi aziendali
                  $entityInstance->setOreLavoro($entityInstance->getOreLavoro() + $globOreCantLav);
                  $entityInstance->setOrePianificate($entityInstance->getOrePianificate() + $globOreCantPian);
                  $entityInstance->setOreStraordinario($entityInstance->getOreStraordinario() + $globOreCantStra);
                  $entityInstance->setOreImproduttive($entityInstance->getOreImproduttive() + $globOreCantImpr);
                  $entityInstance->setOreIninfluenti($entityInstance->getOreIninfluenti() + $globOreCantInin);
                  $entityInstance->setCostMonthHuman(round(($entityInstance->getCostMonthHuman() + $globCostoCantLav),2));
                  if ($countTotalPersone  === ($countConfirmedPersone + $countAlreadyConfPersone) ) {
                    $entityInstance->setIsHoursCompleted(true);
                  }
                // Alla fine riepiloga i risultati della elaborazione (Personale elaborato e non elaborato e Numero cantieri trattai)
                $this->addFlash('info', sprintf('Nel mese selezionato sono state elaborate %d Persone e aggiornati %d Cantieri.', $countConfirmedPersone, count($arrayCantieri)));
                if ($countNotConfPersone > 0) {
                  $this->addFlash('info', sprintf('Restano ancora da elaborare %d Persone', $countNotConfPersone));
                } else { $this->addFlash('info', 'Tutto il personale e tutti i cantieri associati sono stati elaborati. '); }
               
                //
                //Elaborazione del mese non eseguita
            } else { 
                if ($countNotConfPersone > 0) {
                    $this->addFlash('warning', sprintf('Nel mese selezionato sono state trovate %d persone con orari da CONFERMARE. Elaborazione del mese non eseguita', $countNotConfPersone ) );
                } else {
                    if ($countTotalPersone  === ($countConfirmedPersone + $countAlreadyConfPersone) ) {
                        $entityInstance->setIsHoursCompleted(true);
                      }
                }
            }
        }
        else {
              $this->addFlash('warning', 'Nel mese selezionato NON ci sono ore lavorate CONFERMATE. Elaborazione non eseguibile' );
        }

        $entityManager->persist($entityInstance);
        $entityManager->flush();
        return  ;

    }


    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {

        // dati pianificazione selezionata
        $keyreference =  $entityInstance->getKeyReference();
        $azienda = $entityInstance->getAzienda();
        $festivitaAnnuale_id = $entityInstance->getFestivitaAnnuale();
        $mese = $entityInstance->getMese();
        // legge anno dalle festività dell'anno
        $festivita = $entityManager->getRepository(FestivitaAnnuali::class)->findOneBy(['id'=> $festivitaAnnuale_id]);
        $anno = $festivita->getAnno();
        // valori iniziali per preparazione periodo mensile (primo e ultimo giorno)
        $dateutility = new DateUtility ;
        $limitiMese = $dateutility->calculateLimitMonth($anno, $mese);
        $dataInizio = $limitiMese[1] ;
        $dataFine = $limitiMese[2] ;
        
        $count = $entityManager->getRepository(OreLavorate::class)->countConfirmed($azienda, true, $dataInizio, $dataFine);
        if ($count === 0) {
            $count = $entityManager->getRepository(OreLavorate::class)->countConfirmed($azienda, false , $dataInizio, $dataFine);
            if ($count > 0) {
            $countdeleted = $entityManager->getRepository(OreLavorate::class)->deleteOreLavorate($azienda, false , $dataInizio, $dataFine);
            $this->addFlash('success',  sprintf('Sono stati eliminati %d item di Ore lavorate', $countdeleted));    
            } else {
                 $this->addFlash('info',  'Nel mese selezionato NON ci sono ore lavorate registrate.');
            }
            // elimina Consolidato mese
            $entityManager->remove($entityInstance);
            $entityManager->flush();
            $this->addFlash('success',  sprintf('Consolidato del mese con riferimento registrazione %s eliminato con successo', $keyreference));   
        } else {
              $this->addFlash('warning', sprintf('Nel mese selezionato ci sono %d item di ore lavorate CONFERMATE. Eliminazione non eseguibile', $count ));
        }
        return  ;
    }
    
    
    public function configureCrud(Crud $crud): Crud
    {
        
        return $crud
            ->setEntityLabelInSingular('Consolidato mensile')
            ->setEntityLabelInPlural('Consolidati mensili')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Mesi consolidati')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (MesiAziendali $name) => sprintf('Consolidato mensile <b>%s</b>', $name ))
            ->setPageTitle(Crud::PAGE_EDIT, fn (MesiAziendali $name) => sprintf('Consolidato mensile <b>%s</b>', $name->getKeyReference()))
            ->setSearchFields(['festivitaAnnuale', 'mese', 'azienda' ])
            ->setDefaultSort(['festivitaAnnuale' => 'ASC', 'mese' => 'ASC', 'azienda' => 'ASC'])
            ->showEntityActionsAsDropdown();
    }

    public function configureActions(Actions $actions): Actions
    {
        
       
       $build_flowsalary = Action::new('buildFlowSalary', 'Produci file TXT paghe', 'fa fa-stream')
       ->linkToCrudAction('buildFlowSalary')->displayIf(fn ($entity) => $entity->getIsHoursCompleted());

        return $actions
            // ...
            //->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            //->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)

            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $build_flowsalary)
            //->add(Crud::PAGE_EDIT,  Action::INDEX )
            //->add(Crud::PAGE_NEW,   Action::INDEX )
           
            
            ->update(Crud::PAGE_INDEX, Action::EDIT,
             fn (Action $action) => $action->setIcon('fa fa-cogs')->setLabel('Elabora ore confermate')->setHtmlAttributes(['title' => 'Elabora ore confermate'])->displayIf(fn ($entity) => !$entity->getIsHoursCompleted()))
            ->update(Crud::PAGE_INDEX, Action::DELETE,
             fn (Action $action) => $action->setIcon('fa fa-trash')->setHtmlAttributes(['title' => 'Elimina'])->displayIf(fn ($entity) => !$entity->getIsHoursCompleted()))
            ->update(Crud::PAGE_INDEX, Action::DETAIL,
             fn (Action $action) => $action->setIcon('fa fa-eye')->setHtmlAttributes(['title' => 'Vedi']))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('CONSOLIDATO AZIENDA NEL MESE')->setIcon('fas fa-calendar');
        $panelElabora = FormField::addPanel('ELABORA CONSOLIDATO AZIENDA NEL MESE, premere CONFERMA per avviare il processo')->setIcon('fas fa-cogs');
        $azienda = AssociationField::new('azienda', 'Azienda del gruppo')->setHelp('NickName dell\'azienda del gruppo')
            ->setFormTypeOptions([
            'query_builder' => function (AziendeRepository $az) {
                return $az->createQueryBuilder('a')
                   ->orderBy('a.nickName', 'ASC');     }, 'disabled' => 'true'
            ])
            ->setCustomOptions(array('widget' => 'native'))->setRequired(true);
        $festivitaAnnuale = AssociationField::new('festivitaAnnuale', 'Anno')
            ->setFormTypeOptions([
            'query_builder' => function (FestivitaAnnualiRepository $fa) {
                return $fa->createQueryBuilder('f')
                   ->orderBy('f.anno', 'ASC');     }, 'disabled' => 'true'
            ])
            ->setCustomOptions(array('widget' => 'native'))->setRequired(true); 
        $isHoursCompleted = BooleanField::new('isHoursCompleted', 'Orari del mese completati')->setHelp('Se non attivato significa che mancano ancora orari dipendenti da elaborare')->setFormTypeOptions(['disabled' => 'true']);
        $isInvoicesCompleted = BooleanField::new('isInvoicesCompleted', 'Fatture del mese completate')->setHelp('Se non attivato significa che mancano ancora fatture mensili da elaborare')->setFormTypeOptions(['disabled' => 'true']);
        $mese = ChoiceField::new('mese', 'Mese')->setChoices(['Gennaio' => '01', 'Febbraio' => '02', 'Marzo' => '03', 'Aprile' => '04', 'Maggio' => '05', 'Giugno' => '06',
        'Luglio' => '07', 'Agosto' => '08', 'Settembre' => '09', 'Ottobre' => '10', 'Novembre' => '11', 'Dicembre' => '12',])->setFormTypeOptions(['disabled' => 'true']);
        $oreLavoro = TextField::new('oreLavoro', 'Ore Lavorate');
        $orePianificate = TextField::new('orePianificate', 'Ore Pianificate');
        $oreStraordinario = TextField::new('oreStraordinario', 'Ore Straordinario');
        $oreImproduttive = TextField::new('oreImproduttive', 'Ore Improduttive');
        $oreIninfluenti = TextField::new('oreIninfluenti', 'Ore Ininfluenti'); 
        $costMonthHuman = MoneyField::new('costMonthHuman', 'Costo risorse umane')->setNumDecimals(2)->setCurrency('EUR')->setHelp('Calcolato sull\'ammontare delle ore mensili effettive'); 
        $costMonthMaterial = MoneyField::new('costMonthMaterial', 'Costo risorse materiali')->setNumDecimals(2)->setCurrency('EUR')->setHelp('Calcolato sull\'ammontare medio pianificato per cantiere')->setFormTypeOptions(['disabled' => 'true']);
        $incomeMonth = MoneyField::new('incomeMonth', 'Ricavi mensili')->setNumDecimals(2)->setCurrency('EUR')->setHelp('Calcolato sull\'ammontare delle entrate mensili fatturate')->setFormTypeOptions(['disabled' => 'true']);
        $numeroPersone = IntegerField::new('numeroPersone', 'Persone prev.')->setTextAlign('center')->setFormTypeOptions(['disabled' => 'true']);
        $consolidatiPersonale = AssociationField::new('consolidatiPersonale', 'Persone cons.')->setTextAlign('center');
        $numeroCantieri = IntegerField::new('numeroCantieri', 'Cantieri prev.')->setTextAlign('center')->setFormTypeOptions(['disabled' => 'true']);
        $consolidatiCantieri = AssociationField::new('consolidatiCantieri', 'Cantieri cons.')->setTextAlign('center');
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $keyReference = TextField::new('keyReference', 'Chiave registrazione')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);
        if (Crud::PAGE_INDEX === $pageName) {
            return [ $festivitaAnnuale, $azienda, $mese, $numeroPersone, $consolidatiPersonale, $numeroCantieri, $consolidatiCantieri, $costMonthHuman, $costMonthMaterial, $incomeMonth ];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $festivitaAnnuale, $azienda, $mese, $isHoursCompleted, $costMonthHuman, $isInvoicesCompleted, $costMonthMaterial, $incomeMonth, $panel_ID, $id, $keyReference, $createdAt];
         } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $festivitaAnnuale, $azienda, $mese, $isHoursCompleted, $costMonthHuman, $isInvoicesCompleted, $costMonthMaterial, $incomeMonth];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panelElabora, $festivitaAnnuale, $azienda, $mese, $numeroPersone, $numeroCantieri, $isHoursCompleted, $oreLavoro, $orePianificate, $oreStraordinario, $oreImproduttive, $oreIninfluenti, $costMonthHuman, $isInvoicesCompleted, $costMonthMaterial, $incomeMonth, $panel_ID, $id, $keyReference, $createdAt];

        }
    }
}
