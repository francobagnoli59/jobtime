<?php

namespace App\Controller\Admin;

use App\Entity\OreLavorate;
use App\Entity\FestivitaAnnuali;
use App\Entity\MesiAziendali;
use App\Entity\Personale;
use App\Entity\Cantieri;
use App\Entity\Aziende;
use App\Entity\Causali;
use App\Repository\AziendeRepository;
use App\Repository\CantieriRepository;
use App\Repository\PersonaleRepository;
use App\ServicesRoutine\PhpOfficeStyle;
use App\ServicesRoutine\DateUtility;
use App\Controller\RaccoltaOrePersonaController;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Filesystem\Filesystem;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


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

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $response = $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $azienda = $this->getUser()->getAziendadefault();
        if ($azienda !== null ) {
            $aziendaId = $azienda->getId();
        $response->andWhere('entity.azienda = '. $aziendaId);
        } else {  $response->andWhere('entity.azienda = 0'); } // così non visualizza niente
        return $response;
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

                // determina array personale ( max 40 persone = cartelle su un foglio di excel) Limitato per motivi di leggibilità 
                $personescelte = []; 
                $lastdate = new \DateTime; 
                $first = true; 
                $item = 0;
                foreach ($listaorari as $orarioRecord) {
                    // data più recente nei risultati
                    if ($first === true) {
                        $lastdate = $orarioRecord->getGiorno(); $first = false; 
                        $aziendaNickName = $orarioRecord->getAzienda()->getNickName();
                        $aziendaRagSociale = $orarioRecord->getAzienda()->getCompanyName();
                    } else {  
                        if ($orarioRecord->getGiorno() > $lastdate ) { $lastdate = $orarioRecord->getGiorno(); }
                    }
                    // array persone
                    $idpers =  $orarioRecord->getPersona()->getId();
                    $fullname =  $orarioRecord->getPersona()->getFullName();
                    if(array_key_exists($idpers, $personescelte) === false) { 
                        $personescelte[$idpers] = $fullname ;
                        $item++ ;
                    }
                    if ($item > 40 ) {
                       break; 
                    } 
                }
            if ($item <= 40 && $item > 0 ) { 

                // valori iniziali per preparazione periodo mensile (primo e ultimo giorno)
                $anno = sprintf("%04d",  intval($lastdate->format('Y')));
                $dateutility = new DateUtility ;
                $limitiMese = $dateutility->calculateLimitMonth($anno, sprintf('%d', intval($lastdate->format('m')) ) );
                $dataInizio = $limitiMese[1] ;
                $dataFine = $limitiMese[2] ;

                // legge le festività dell'anno
                $festivita = $this->entityManager->getRepository(FestivitaAnnuali::class)->findOneBy(['anno'=> $anno]);
                $arrayFestivita = $festivita->getDateFestivita();
                // Costruisce date festività  
                $dateFeste = [];
                foreach ($arrayFestivita as $ar) {
                    $gg = substr($ar,0,2);
                    $mm = substr($ar,2,2);
                    $dateholiday = sprintf("%d-%'.02d-%'.02d", $anno, $mm, $gg );
                    $dateFeste[] = $dateholiday;
                }
                              
                // prepara array (giorni del mese)
                $arrDaysOfMonth = $this->daysOfMonth($lastdate, $dateFeste);
                $col = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH'];    
                $meseanno=array('','Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre');//0 vuoto
                
                $giorninelmese = cal_days_in_month(CAL_GREGORIAN, intval($lastdate->format('m')) , intval($lastdate->format('Y')));
                
                // stili configurati
                $styleArray = new PhpOfficeStyle ;
              
                // scorre array persone 
                $spreadsheet = new Spreadsheet();
                $spreadsheet->getProperties()
                    ->setCreator("Produced by Masotech (c)")
                    ->setLastModifiedBy("Masotech")
                    ->setTitle("Riepilogo orari mensili")
                    ->setSubject("Informazioni orari lavoro su dati ".$aziendaNickName)
                    ->setDescription(
                        "Documento da considerarsi strettamente riservato."
                    )
                    ->setKeywords($meseanno[intval($lastdate->format('m'))].' '.$lastdate->format('Y'))
                    ->setManager("JobTime")
                    ->setCompany($aziendaRagSociale);
                $personekeys = array_keys($personescelte) ;
                $indexsheet = 0;
                foreach ( $personekeys as $idPersona) {
                   //
                        $fullname = $personescelte[$idPersona]; 
                        if ($indexsheet > 0) { $sheet = new Worksheet($spreadsheet,  $fullname); $spreadsheet->addSheet($sheet); }
                        else {$sheet = $spreadsheet->getActiveSheet(); $sheet->setTitle($fullname); }
                       
                        // 
                        $sheet->getCell('A1')->setValue('RIEPILOGO ORE MENSILI');
                        $spreadsheet->getSheet($indexsheet)->getStyle('A1')->applyFromArray($styleArray->title1());
                        // foreground rosso 
                        // $spreadsheet->getSheet($indexsheet)->getStyle('A1')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);

                        $sheet->getCell('A3')->setValue($aziendaNickName);
                        $spreadsheet->getSheet($indexsheet)->getStyle('A3')->applyFromArray($styleArray->title2());
                        $sheet->getCell('C1')->setValue($meseanno[intval($lastdate->format('m'))].' '.$lastdate->format('Y'));
                        $spreadsheet->getSheet($indexsheet)->getStyle('C1')->applyFromArray($styleArray->title2());
                       
                        $sheet->getCell('A5')->setValue('Operatore:');
                        $spreadsheet->getSheet($indexsheet)->getStyle('A5')->applyFromArray($styleArray->title3());
                        $spreadsheet->getSheet($indexsheet)->getStyle('A5')->applyFromArray($styleArray->alignHRight());
                      
                        $sheet->getCell('B5')->setValue($fullname);
                        $spreadsheet->getSheet($indexsheet)->getStyle('B5:D5')->applyFromArray($styleArray->title2());
                      
                        $sheet->getCell('A7')->setValue('Cantiere');
                        $spreadsheet->getSheet($indexsheet)->getStyle('A7')->applyFromArray($styleArray->columnTitleGrey());
                        $d = 0; $countDayMonth = 0;  $firstLoop = false; // costruisce colonne dei giorni
                        $dayFestiviMese = [];
                        foreach ( $arrDaysOfMonth as $dayOfMonth) {
                                foreach ( $dayOfMonth as $key => $valore) {
                                    if ($key !== 'festa') {  
                                    $sheet->getCell($col[$d+1-$countDayMonth]."7")->setValue($valore);
                                    $countDayMonth++ ; }
                                    else {
                                        if ($dayOfMonth['festa'] === false ) {
                                        $spreadsheet->getSheet($indexsheet)->getStyle($col[$d+1-$countDayMonth]."7")->applyFromArray($styleArray->columnTitleGrey()); }
                                        else {
                                            $spreadsheet->getSheet($indexsheet)->getStyle($col[$d+1-$countDayMonth]."7")->applyFromArray($styleArray->columnTitleCoral());
                                            array_push($dayFestiviMese, $col[$d+1-$countDayMonth]); }
                                        }
                                    // $this->addFlash('info', sprintf('Array giorni del mese con key: %s valore: %s', $key, $valore) );        
                                    $d++; 
                                     if ($countDayMonth === $giorninelmese && $firstLoop === false ) { $sheet->getCell($col[$d+2-$countDayMonth]."7")->setValue('Totale');
                                     $spreadsheet->getSheet($indexsheet)->getStyle($col[$d+2-$countDayMonth]."7")->applyFromArray($styleArray->columnTotal());
                                     $firstLoop = true; $columnTotal = $col[$d+2-$countDayMonth] ; $lastColumnMonth = $col[$d+1-$countDayMonth] ; }
                                    } 
                            }
                   // 

                $row = 8;  // prima riga utile dopo l'intestazione colonne
                // ciclo lettura orari personale 
                $cantierilavorati = []; $daConfermare = false;
                $causaliLavoro = [];
                    // cerca per persona se ci sono ore lavorate CONFERMATE nel mese 
                    $count = $this->entityManager->getRepository(OreLavorate::class)->countPersonaConfirmed($idPersona, true , $dataInizio, $dataFine);
                    if ($count > 0) {
                        // Seleziona le ore lavorate e confermate del mese
                        $oreLavorateCollection = $this->entityManager->getRepository(OreLavorate::class)->collectionPersonaConfirmed($idPersona, true , $dataInizio, $dataFine);
                        // ciclo sulla collection delle ore lavorate
                        foreach ($oreLavorateCollection as $ol ){
                            $giorno = $ol->getGiorno();
                            $causale = $ol->getCausale()->getCode();
                            $oreReg = $ol->getOreRegistrate(); 
                            if(array_key_exists($causale,  $causaliLavoro) === false) { 
                                $causaliLavoro[$causale] = $oreReg ;  }
                                else { $causaliLavoro[$causale] = $causaliLavoro[$causale]+$oreReg ; }
                            $idCantiere = $ol->getCantiere()->getId();
                            // determina riga cantiere
                            if(array_key_exists($idCantiere,  $cantierilavorati) === false) { 
                                $cantierilavorati[$idCantiere] = $row ;
                                $currentRow = $row ;
                                $row++ ;
                            } else { $currentRow = $cantierilavorati[$idCantiere] ; } 
                            $d = intval($giorno->format('d'));
                            $sheet->getCell($col[$d].sprintf('%s',$currentRow))->setValue($oreReg);
                            $lettcol = $col[$d];
                            if (in_array( $lettcol, $dayFestiviMese)) { 
                                $spreadsheet->getSheet($indexsheet)->getStyle($col[$d].sprintf('%s',$currentRow))->applyFromArray($styleArray->rowCoral()); 
                            }
                            else {
                                $spreadsheet->getSheet($indexsheet)->getStyle($col[$d].sprintf('%s',$currentRow))->applyFromArray($styleArray->rowGrey());
                             }
                        }
                    }

                    // cerca per persona se ci sono ore lavorate da confrmare nel mese 
                    $count = $this->entityManager->getRepository(OreLavorate::class)->countPersonaConfirmed($idPersona, false , $dataInizio, $dataFine);
                    if ($count > 0) {
                        $daConfermare = true;
                        // Seleziona le ore lavorate e NON confermate del mese
                        $oreLavorateCollection = $this->entityManager->getRepository(OreLavorate::class)->collectionPersonaConfirmed($idPersona, false , $dataInizio, $dataFine);
                        // ciclo sulla collection delle ore lavorate
                        foreach ($oreLavorateCollection as $ol ){
                            $giorno = $ol->getGiorno();
                            $causale = $ol->getCausale()->getCode();
                            $orePian = $ol->getOrePianificate();
                            if(array_key_exists($causale,  $causaliLavoro) === false) { 
                                $causaliLavoro[$causale] = $orePian ;  }
                                else { $causaliLavoro[$causale] = $causaliLavoro[$causale]+$orePian ; }
                            $idCantiere = $ol->getCantiere()->getId();
                            // determina riga cantiere
                            if(array_key_exists($idCantiere,  $cantierilavorati) === false) { 
                                $cantierilavorati[$idCantiere] = $row ;
                                $currentRow = $row ;
                                $row++ ;
                            } else { $currentRow = $cantierilavorati[$idCantiere] ; } 
                            $d = intval($giorno->format('d'));
                            $sheet->getCell($col[$d].sprintf('%s',$currentRow))->setValue($orePian);
                            $lettcol = $col[$d];
                            if (in_array( $lettcol, $dayFestiviMese)) { 
                                $spreadsheet->getSheet($indexsheet)->getStyle($col[$d].sprintf('%s',$currentRow))->applyFromArray($styleArray->rowCoral()); 
                            }
                            else {
                                $spreadsheet->getSheet($indexsheet)->getStyle($col[$d].sprintf('%s',$currentRow))->applyFromArray($styleArray->corsivo3());    
                                //$spreadsheet->getSheet($indexsheet)->getStyle($col[$d+1].sprintf('%s',$currentRow))->applyFromArray($styleArray->rowGrey());
                                }
                               
                        }
                    }

                    // scrive cantieri
                    $locale = 'it';
                    $validLocale = \PhpOffice\PhpSpreadsheet\Settings::setLocale($locale);
                    $lastRow = 0;
                    $cantierikeys = array_keys($cantierilavorati) ;
                    foreach ( $cantierikeys as $idCantiere) {
                       //
                            $rowCantiere = $cantierilavorati[$idCantiere];
                            $cantiereRecord = $this->entityManager->getRepository(Cantieri::class)->findOneBy(['id'=> $idCantiere]);
                            $sheet->getCell('A'.sprintf('%s',$rowCantiere))->setValue($cantiereRecord->getnameJob());
                            $spreadsheet->getSheet($indexsheet)->getStyle('A'.sprintf('%s',$rowCantiere))->applyFromArray($styleArray->rowGrey());
                       // Totale riga Cantiere
                            $spreadsheet->getActiveSheet()->setCellValue($columnTotal.sprintf('%s',$rowCantiere),'=SUM(B'.sprintf('%s',$rowCantiere).':'.$lastColumnMonth.sprintf('%s',$rowCantiere).')');
                            $formula = $spreadsheet->getActiveSheet()->getCell($columnTotal.sprintf('%s',$rowCantiere))->getValue();
                            $translatedFormula = \PhpOffice\PhpSpreadsheet\Calculation\Calculation::getInstance()->_translateFormulaToLocale($formula);
                            $spreadsheet->getSheet($indexsheet)->getStyle($columnTotal.sprintf('%s',$rowCantiere))->applyFromArray($styleArray->rowTotal());
                            if ($rowCantiere > $lastRow ) { $lastRow = $rowCantiere ;}
                        }  
                    if (count($cantierikeys) > 0 ) {
                       // Totale ore della persona
                       // ciclo giorni
                       for ($d=1; $d<=$giorninelmese; $d++) {
                        $spreadsheet->getActiveSheet()->setCellValue($col[$d].sprintf('%s',$lastRow+1),'=SUM('.$col[$d].'8:'.$col[$d].sprintf('%s',$lastRow).')');
                        $formula = $spreadsheet->getActiveSheet()->getCell($col[$d].sprintf('%s',$lastRow+1))->getValue();
                        $translatedFormula = \PhpOffice\PhpSpreadsheet\Calculation\Calculation::getInstance()->_translateFormulaToLocale($formula);
                        $spreadsheet->getSheet($indexsheet)->getStyle($col[$d].sprintf('%s',$lastRow+1))->applyFromArray($styleArray->rowTotal()); 
                       } 
                       // totale finale
                       $spreadsheet->getActiveSheet()->setCellValue($columnTotal.sprintf('%s',$lastRow+1),'=SUM('.$columnTotal.'8:'.$columnTotal.sprintf('%s',$lastRow).')');
                       $formula = $spreadsheet->getActiveSheet()->getCell($columnTotal.sprintf('%s',$lastRow+1))->getValue();
                       $translatedFormula = \PhpOffice\PhpSpreadsheet\Calculation\Calculation::getInstance()->_translateFormulaToLocale($formula);
                       $spreadsheet->getSheet($indexsheet)->getStyle($columnTotal.sprintf('%s',$lastRow+1))->applyFromArray($styleArray->rowTotal());
                    }
                     // avvertimento su orari da confermare e riepilogo per causale
                    $rowRiep = $lastRow+4;
                    if ($daConfermare === true) {
                    $sheet->getCell('A'.sprintf('%s',$rowRiep))->setValue('ATTENZIONE CI SONO ORARI DA CONFERMARE');
                    $spreadsheet->getSheet($indexsheet)->getStyle('A'.sprintf('%s',$rowRiep))->applyFromArray($styleArray->title2());
                    $spreadsheet->getSheet($indexsheet)->getStyle('A'.sprintf('%s',$rowRiep))->applyFromArray($styleArray->backGroundYellow());
                    $spreadsheet->getSheet($indexsheet)->getStyle('A'.sprintf('%s',$rowRiep))->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);    
                    $rowRiep =  $rowRiep + 2;
                    }
                    $causalikeys = array_keys($causaliLavoro) ; $totOreCausale = 0;
                    foreach ( $causalikeys as $causaleCode) {
                            $oreCausale = $causaliLavoro[$causaleCode]; $totOreCausale += $oreCausale ;
                            $causaleRecord = $this->entityManager->getRepository(Causali::class)->findOneBy(['code'=> $causaleCode]);
                            $sheet->getCell('A'.sprintf('%s',$rowRiep))->setValue($causaleRecord->getDescription());
                            $spreadsheet->getSheet($indexsheet)->getStyle('A'.sprintf('%s',$rowRiep))->applyFromArray($styleArray->title3());            
                            $spreadsheet->getSheet($indexsheet)->getStyle('A'.sprintf('%s',$rowRiep))->applyFromArray($styleArray->backGroundSilver());            
                            $sheet->getCell('B'.sprintf('%s',$rowRiep))->setValue($oreCausale);
                            $spreadsheet->getSheet($indexsheet)->getStyle('B'.sprintf('%s',$rowRiep))->applyFromArray($styleArray->title3());            
                            $spreadsheet->getSheet($indexsheet)->getStyle('B'.sprintf('%s',$rowRiep))->applyFromArray($styleArray->backGroundSilver());            
                            $spreadsheet->getSheet($indexsheet)->getStyle('B'.sprintf('%s',$rowRiep))->applyFromArray($styleArray->alignHRight());
                            $rowRiep++;
                        }
                        $sheet->getCell('B'.sprintf('%s',$rowRiep))->setValue($totOreCausale);
                        $spreadsheet->getSheet($indexsheet)->getStyle('B'.sprintf('%s',$rowRiep))->applyFromArray($styleArray->rowTotal());            
                        $spreadsheet->getSheet($indexsheet)->getStyle('B'.sprintf('%s',$rowRiep))->applyFromArray($styleArray->alignHRight());            
                    // Colonna A (auto size)
                    $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);    
                    // passa alla persona successiva  
                    $indexsheet++ ;
                }       
               
                // crea il file
                $writer = new Xlsx($spreadsheet);
                $filename = $aziendaNickName.'_riepilogo_personale_'.date_create()->format('Y_m_d\TH_i_s').'.xlsx';
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
            if ($item <= 40 ) {
            // emissione file
            if ($item === 1 ) { $success =  'File excel prodotto.' ;  } 
            else { 
            $success =  sprintf('File excel prodotto. Sono state preparate %d cartelle, una ciascuna per persona. ', $item ) ; 
            }
            $this->addFlash('success', $success.$link ); 
            } else {
            $this->addFlash('warning', 'La selezione supera 40 persone, riepilogo troppo esteso e non rappresentabile.'); 
            }
        } else { $this->addFlash('info', 'Riepilogo non rappresentabile con nessun risultato trovato.'); }

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

    public function daysOfMonth($lastdate, $dateFeste): array
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
            if ($num_gg === 6 || $num_gg === 7 || in_array($day, $dateFeste) === true ) { $festa = true ;} else {$festa = false ; }
            $arrDays[] = [$day => $dayExcel, 'festa' => $festa ]; 
            // $arrDays[] = $dayExcel;
        }
        return $arrDays ;
    }


    public function configureCrud(Crud $crud): Crud
    {
        $azienda = $this->getUser()->getAziendadefault();
        if ($azienda !== null ) {
            $aziendaNickName = $azienda->getNickName();
        } else { $aziendaNickName = '...seleziona azienda!!!'; } 

        $LabelSing = 'Orario di lavoro '.$aziendaNickName ;
        $LabelPlur = 'Ore Lavorate '.$aziendaNickName ;
        $LabelNew = 'Registra ore lavorate '.$aziendaNickName ;
        $Labellist = 'Elenco Ore Lavorate '.$aziendaNickName ;

        return $crud
            ->setEntityLabelInSingular($LabelSing)
            ->setEntityLabelInPlural($LabelPlur)
            ->setPageTitle(Crud::PAGE_INDEX,  $Labellist)
            ->setPageTitle(Crud::PAGE_NEW, $LabelNew)
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
/*             ->add(EntityFilter::new('azienda')->setFormTypeOption('value_type_options.query_builder', 
                static fn(AziendeRepository $az) => $az->createQueryBuilder('azienda')
                        ->orderBy('azienda.nickName', 'ASC') ) )  */
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
         
         $planningRaccoltaOre = Action::new('raccoltaOreLavorate', 'Modulo raccolta ore', 'fa fa-calendar-week')
         ->linkToCrudAction('raccoltaOreLavorate')->setCssClass('btn btn-secondary')->displayIf(fn ($entity) => !$entity->getIsTransfer());

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
                ->add(Crud::PAGE_INDEX, $planningRaccoltaOre)->add(Crud::PAGE_EDIT, $planningRaccoltaOre)
                ->add(Crud::PAGE_INDEX, $confirmView)->add(Crud::PAGE_INDEX, $riepilogoOre)
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

    public function raccoltaOreLavorate(AdminContext $context)
    {
        $orelavorate = $context->getEntity()->getInstance();
        $url = $this->adminUrlGenerator->unsetAll()
            ->setRoute('person_hour_month', [
            'persona' => $orelavorate->getPersona()->getId(),
            'anno' => $orelavorate->getGiorno()->format('Y'),
            'mese'=> $orelavorate->getGiorno()->format('m')
            ])->generateUrl();
           //  $this->addFlash('warning',  $url);
            return new RedirectResponse($url);
    }

     public function configureFields(string $pageName): iterable
    {
        $azienda = $this->getUser()->getAziendadefault();
        if ($azienda !== null ) {
            $statusAzienda = true ; $helpAz = '';}
            else { $statusAzienda = false ;}

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
            ])->setRequired(true)->setCustomOptions(array('widget' => 'native'))->setFormTypeOptions(['disabled' => $statusAzienda]);
        $cantiere = AssociationField::new('cantiere', 'Cantiere')
            ->setFormTypeOptions([
            'query_builder' => function (CantieriRepository $ca) {
                 return $ca->createQueryBuilder('c')
                     ->orderBy('c.nameJob', 'ASC');
            },
            ])->setRequired(true)->setCustomOptions(array('widget' => 'native'));
        $personaView = AssociationField::new('persona', 'Persona')->setCrudController(PersonaleCrudController::class);
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
            return [$id, $azienda, $personaView, $cantiere, $giorno, $dayOfWeek, $causale,  $orePianificate, $oreRegistrate, $isConfirmed];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $azienda, $personaView, $cantiere, $giorno, $dayOfWeek, $causale, $orePianificate, $oreRegistrate, $isConfirmed,  $panel_ID, $id, $keyReference, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $azienda, $persona, $cantiere, $giorno, $dayOfWeek, $causale, $orePianificate, $oreRegistrate, $isConfirmed];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1,  $azienda, $persona, $cantiere, $giorno, $dayOfWeek, $causale, $orePianificate, $oreRegistrate,  $isConfirmed, $panel_ID, $id, $keyReference, $createdAt];
        }
    }
}


