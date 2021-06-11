<?php

namespace App\Controller;

use App\Entity\MesiAziendali;
use App\Repository\MesiAziendaliRepository;
use App\Entity\RaccoltaOrePersone;
use App\Repository\RaccoltaOrePersoneRepository;
use App\Entity\ModuliRaccoltaOreCantieri;
use App\Entity\FestivitaAnnuali;
use App\Entity\Aziende;
use App\Entity\Personale;
use App\Entity\Cantieri;
use App\Entity\Causali;
use App\Entity\Orelavorate;
use App\Form\RaccoltaOrePersonaType;
use App\ServicesRoutine\DateUtility;
use App\Controller\Admin\OreLavorateCrudController;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\HttpFoundation\RedirectResponse;


class RaccoltaOrePersonaController extends AbstractController
{
    private $session;
    /**
     * @var AdminContextProvider
     */
    private AdminContextProvider $adminContextProvider;
    /**
     * @var AdminUrlGenerator
     */
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(
        SessionInterface $session,
        AdminContextProvider $adminContextProvider,
        AdminUrlGenerator $adminUrlGenerator
    ) {
        $this->session = $session;
        $this->adminContextProvider = $adminContextProvider;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

//  MesiAziendaliRepository $mesiAziendaliRepository

    /**
     * @Route("/editMonth/mesepersona", methods="GET|POST", name="person_hour_month")
     */
    public function editMonth(Request $request ): Response
    {
        /* $entityManager = $this->getDoctrine()->getManager();

        $mesiaziendali = new MesiAziendali();
        $azienda = $this->getUser()->getAziendadefault();
        if ($azienda !== null ) {
            $mesiaziendali->setAzienda($azienda);
            }
        $mesiaziendali->setIsHoursCompleted(false);
        $mesiaziendali->setIsInvoicesCompleted(false);
        $mesiaziendali->setCostMonthHuman(0)->setCostMonthMaterial(0)->setIncomeMonth(0)->setNumeroPersone(0)->setNumeroCantieri(0)
        ->setOreLavoro(0)->setOrePianificate(0)->setOreStraordinario(0)->setOreImproduttive(0)->setOreIninfluenti(0); 
                     
        $form = $this->createForm(MesiAziendaliType::class, $mesiaziendali); */


        $entityManager = $this->getDoctrine()->getManager();

        // elenco ore lavorate per persona per Id=1   ( da personalizzare con filtro persona / anno / mese)
        $idPersona = 1 ;
        $personale = $entityManager->getRepository(Personale::class)->findOneBy(['id'=> $idPersona]);
        $fullName = $personale->getFullName();
        $listaorari = $entityManager->getRepository(OreLavorate::class)->findBy(['persona'=> $personale]);

        // Determina Anno e mese dal primo orario in lista
        $lastdate = new \DateTime; 
            $first = true; 
            foreach ($listaorari as $orarioRecord) {
                // data più recente nei risultati
                if ($first === true) {
                    $lastdate = $orarioRecord->getGiorno(); 
                    $first = false; 
                } else {  
                    if ($orarioRecord->getGiorno() > $lastdate ) { $lastdate = $orarioRecord->getGiorno(); }
                }
            }  

        // valori iniziali per preparazione periodo mensile (primo e ultimo giorno)
        $anno = sprintf("%04d",  intval($lastdate->format('Y')));
        $mese = $lastdate->format('m');
        $dateutility = new DateUtility ;
        $limitiMese = $dateutility->calculateLimitMonth($anno, sprintf('%d', intval($lastdate->format('m')) ) );
        $dataInizio = $limitiMese[1] ;
        $dataFine = $limitiMese[2] ;

        // legge le festività dell'anno
        $festivita = $entityManager->getRepository(FestivitaAnnuali::class)->findOneBy(['anno'=> $anno]);
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
        $meseanno=array('','Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre');//0 vuoto
        $descPeriodo = $meseanno[intval($lastdate->format('m'))]. ' '.$anno ;
        $giorninelmese = cal_days_in_month(CAL_GREGORIAN, intval($lastdate->format('m')) , intval($lastdate->format('Y')));
        $tipogiorno = [];
        $nomegiorno = [];
        $d = 0; $countDayMonth = 0;
        foreach ( $arrDaysOfMonth as $dayOfMonth) {
            foreach ( $dayOfMonth as $key => $valore) {
                if ($key !== 'festa') {  
                $nomegiorno[$d] = $valore;
                $countDayMonth++ ; }
                else {
                    if ($dayOfMonth['festa'] === false ) {
                    $tipogiorno[$d] = 'L'; }   
                    else { $tipogiorno[$d] = 'F';  }
                // $this->addFlash('info', sprintf('Array giorni del mese con key: %s valore: %s', $key, $valore) );        
                $d++; 
                 if ($countDayMonth === $giorninelmese ) { 
                // ultimo giorno
                    for ($i = $giorninelmese + 1; $i <= 32; $i++): 
                        if ($i === 32) { $nomegiorno[$i-1] = 'Tot Ore'; $tipogiorno[$i-1] = 'P'; } 
                        else  { $nomegiorno[$i-1] = '... ...'; $tipogiorno[$i-1] = 'N'; }
                    endfor;   
                 }
                } 
            }  
        } 

        // determina cantieri
        $cantierilavorati = [];
        // cerca per persona se ci sono ore registrate nel mese 
        $count = $entityManager->getRepository(OreLavorate::class)->countPersonaMonth($idPersona, $dataInizio, $dataFine);
        if ($count > 0) {
            // ciclo lettura orari personale per cercare cantieri
            $oreLavorateCollection = $entityManager->getRepository(OreLavorate::class)->collectionPersonaMonth($idPersona, $dataInizio, $dataFine);
            // ciclo sulla collection delle ore registrate 
            foreach ($oreLavorateCollection as $ol ){
                $causale = $ol->getCausale()->getCode();
                $oreReg = $ol->getOreRegistrate(); 
                if($causale === 'ORDI' && $oreReg > 0) { 
                    $idCantiere = $ol->getCantiere()->getId();
                    $nomeCantiere = $ol->getCantiere()->getnameJob();
                    // determina riga cantiere
                    if(array_key_exists($idCantiere,  $cantierilavorati) === false) { 
                        $cantierilavorati[$idCantiere] = $nomeCantiere ;
                    } 
                }
            }
            // Inserisce / aggiorna raccolta ore persona
            if (count($cantierilavorati) > 0) {
                 // Elimina raccolte ore cantieri precedenti ( stesso mese) 
                 // ma prima verifica se esiste raccolta persona
                 $keyreference =  sprintf("%010d-%s-%s", $idPersona, $anno, $mese);
                 if ( $entityManager->getRepository(RaccoltaOrePersone::class)->findOneByKeyReference($keyreference) !== null ) {
                    $raccoltaOrePersone = $entityManager->getRepository(RaccoltaOrePersone::class)->findOneByKeyReference($keyreference) ;
                    $deleteItems = $entityManager->getRepository(ModuliRaccoltaOreCantieri::class)->deleteOreCantieri($raccoltaOrePersone->getId());
                 }
                 // totalizzatori
                $altreCausali = ['Altre causali',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
                $totaleXGiorno = ['TOTALI',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
                $totalePianificato = 0 ; 
                // ciclo per cantiere crea nuove raccolte ore
                $cantierikeys = array_keys($cantierilavorati) ;
                foreach ( $cantierikeys as $idCantiere) {
                   //
                    $nomeCantiere = $cantierilavorati[$idCantiere];
                    $cantiere = $entityManager->getRepository(Cantieri::class)->findOneBy(['id'=> $idCantiere]);
                    $oreMeseCantieri = new ModuliRaccoltaOreCantieri();
                        $oreMeseCantieri->setCantiere($cantiere);
                        // crea Array giorni del mese con ore registrate 
                        $totOre = 0 ;
                        $oreGiornaliere = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
                        foreach ($oreLavorateCollection as $ol ) {
                            if ($idCantiere === $ol->getCantiere()->getId()) {
                                $causale = $ol->getCausale()->getCode();
                                $oreReg = $ol->getOreRegistrate();
                                $orePian = $ol->getOrePianificate();
                                if( $orePian > 0) { $totalePianificato = $totalePianificato + $orePian ;}
                                $giorno = $ol->getGiorno();
                                $d = intval($giorno->format('d')) -1;
                                if( $oreReg > 0) { 
                                    $totaleXGiorno[$d+1] = $totaleXGiorno[$d+1] + $oreReg ;
                                    if($causale === 'ORDI') { 
                                    $oreGiornaliere[$d] = $oreReg; 
                                    $totOre = $totOre + $oreReg; 
                                    } else { $altreCausali[$d+1] = $altreCausali[$d+1] + $oreReg ;}
                                }
                            }
                        }
                        $oreGiornaliere[31] = $totOre;
                        $oreMeseCantieri->setOreGiornaliere($oreGiornaliere);
                        
                        // verifica se esiste raccolta persona
                        $keyreference =  sprintf("%010d-%s-%s", $idPersona, $anno, $mese);
                        if ( $entityManager->getRepository(RaccoltaOrePersone::class)->findOneByKeyReference($keyreference) === null ) {
                            // registra la raccolta persona e le ore cantieri
                            $raccoltaOrePersone = new RaccoltaOrePersone();
                            $raccoltaOrePersone->setAnno($festivita);
                            $raccoltaOrePersone->setMese($mese);
                            $raccoltaOrePersone->setPersona($personale);
                            $raccoltaOrePersone->addOreMeseCantieri($oreMeseCantieri);
                            $entityManager->persist($raccoltaOrePersone);
                            $entityManager->flush();
                            
                        } else {
                            // esiste racccolta ore persone, aggiunge ore cantiere
                        $raccoltaOrePersone = $entityManager->getRepository(RaccoltaOrePersone::class)->findOneByKeyReference($keyreference) ;
                        // registra le ore cantieri
                        $raccoltaOrePersone->addOreMeseCantieri($oreMeseCantieri);
                        $entityManager->persist($raccoltaOrePersone);
                        $entityManager->flush();
                        }
                    }
                    // sommatoria altre causali e totali 
                    $totAltreCausali = 0; $tot = 0;
                    for ($i=1 ; $i<=31; $i++) { 
                        $tot = $tot + $totaleXGiorno[$i];
                        $totAltreCausali = $totAltreCausali + $altreCausali[$i];
                     }
                    $totaleXGiorno[32] = $tot;
                    $altreCausali[32] = $totAltreCausali;  
                   
                }
        } 
        else { 
            // persona senza ore registrate nel mese
            // VALUTARE SE INSERIRE RIGA CANTIERE VUOTA !!!
        }      
        

/*         $row = 8;  // prima riga utile dopo l'intestazione colonne
        // ciclo lettura orari personale 
        $daConfermare = false;
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

            // cerca per persona se ci sono ore lavorate da confermare nel mese 
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
                    
                        }
                        
                }
            } */
        




        //
       
       // $raccoltaOrePersone = $entityManager->getRepository(RaccoltaOrePersone::class)->findOneBy(['id'=> '1']);
        
        $form = $this->createForm(RaccoltaOrePersonaType::class, $raccoltaOrePersone);
        $form->get('tipogiorno')->setData($tipogiorno);
        $form->get('nomegiorno')->setData($nomegiorno);
        if ($totAltreCausali === 0) {
        $altreCausali = null;
        }  
        $form->get('altreCausali')->setData($altreCausali);
        $form->get('totaleXGiorno')->setData($totaleXGiorno);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        /*     $entityManager->persist($mesiaziendali);
            $entityManager->flush();
            $this->addFlash('success',  'Mensilità richiesta inserita nel consolidato mensile ');
          
            // dati pianificazione appena inserita
            $azienda_id = $mesiaziendali->getAzienda()->getId();
            $azienda = $mesiaziendali->getAzienda();
            $festivitaAnnuale_id = $mesiaziendali->getFestivitaAnnuale();
            $mese = $mesiaziendali->getMese();
           
            // legge le festività dell'anno
            $festivita = $entityManager->getRepository(FestivitaAnnuali::class)->findOneBy(['id'=> $festivitaAnnuale_id]);
            $anno = $festivita->getAnno();

            // costruisce keyreference mese anzindale appena inserito
            $keyReference = sprintf("%010d-%s-%s", $azienda_id, $anno, $mese);

            $arrayFestivita = $festivita->getDateFestivita();
            // Costruisce date festività
            $dateFeste = [];
            foreach ($arrayFestivita as $ar) {
                $gg = substr($ar,0,2);
                $mm = substr($ar,2,2);
                $dateholiday = mktime(0,0,0,$mm,$gg,$anno);
                $dateFeste[] = $dateholiday;
            }
           
            // valori iniziali per preparazione fine mese
            $dateutility = new DateUtility ;
            $limitiMese = $dateutility->calculateLimitMonth($anno, $mese);
            $finemese = $limitiMese[0] ;
                     
            // per ogni dipendente dell'azienda richiesta inserisce le ore pianificate nel mese impostato
            $count = 0;   // contatore items
            $countPersone = 0; // contatore persone
            $arrayCantieri = []; // utilizzato per contare i cantieri
            $personaledataset = $entityManager->getRepository(Personale::class)->findBy(['azienda'=> $azienda]);
            foreach ($personaledataset as $personale) {
                   // personale con flag assunto su true  
                  if ( $personale->getIsEnforce() === true ) {
                    // ciclo mese
                    $countPersone++ ;
                    for ($i = 1; $i <= $finemese ; $i++) {   
                        $giorno = mktime(0,0,0,$mese,$i,$anno);
                        $num_gg=(int)date("N",$giorno);//1 (for Monday) through 7 (for Sunday)

                        // ciclo su planning settimanale essendo assegnato ad unico cantiere
                        if ( $personale->getCantiere() !== null ) {
                        $orelavorate = new Orelavorate();
                        $orelavorate->setAzienda($azienda);
                        $orelavorate->setCantiere($personale->getCantiere());
                        $orelavorate->setPersona($entityManager->getRepository(Personale::class)->findOneBy(['id' => $personale->getId()]));
                        $orelavorate->setIsConfirmed(false);
                        $orelavorate->setIsTransfer(false);
                        
                        // aggiunge id cantiere
                        array_push($arrayCantieri, $personale->getCantiere()->getId());
                       
                        // planning settimana sulla persona    
                        $planweek = $personale->getPlanHourWeek();
                            if ( $planweek[$num_gg - 1] != null && $planweek[$num_gg - 1] != '0') { 
                                $keyCausale = 'ORDI';
                                if ($num_gg <= 6 ) { 
                                    if (in_array($giorno, $dateFeste)) { $keyCausale = '*EF';  $orelavorate->setIsConfirmed(true); }
                                } 
                                $orelavorate->setCausale($entityManager->getRepository(Causali::class)->findOneBy(['code' => $keyCausale]));
                                $orelavorate->setOrePianificate($planweek[$num_gg - 1]);
                                $orelavorate->setOreRegistrate($planweek[$num_gg - 1]);
                                $date = new \DateTime();
                                $date->setTime(0,0,0);
                                $date->setDate($anno, $mese, $i);
                                $orelavorate->setGiorno($date);
                                // Verifica data licenziamento 
                                    if ($personale->getDateDismissal() === null || $date <= $personale->getDateDismissal() ) {
                                        // scrive la pianificazione
                                        $entityManager->persist($orelavorate);
                                        $entityManager->flush();
                                        $count++ ;
                                    }             
                            }
                        }  // unico cantiere
                        else {
                        // ciclo su Piani orari per cantieri  
                            if ($personale->getCantiere() === null) {
                                $oreCantieri = $personale->getPianoOreCantieri();
                                foreach ($oreCantieri as $oc) {
                                    if ($oc->getPersona()->getId() === $personale->getId() && $oc->getOrePreviste() > 0 && $oc->getdayOfWeek() === $num_gg) {
                                            
                                       $orelavorate = new Orelavorate();
                                       $orelavorate->setAzienda($azienda);
                                       $orelavorate->setCantiere($oc->getCantiere());
                                       $orelavorate->setPersona($oc->getPersona());
                                       $orelavorate->setIsConfirmed(false);
                                       $orelavorate->setIsTransfer(false);
       
                                       // aggiunge id cantiere
                                       array_push($arrayCantieri, $oc->getCantiere()->getId());

                                       $keyCausale = 'ORDI';
                                       if ($num_gg <= 6 ) { 
                                           if (in_array($giorno, $dateFeste)) { $keyCausale = '*EF';  $orelavorate->setIsConfirmed(true); }
                                       } 
                                       $orelavorate->setCausale($entityManager->getRepository(Causali::class)->findOneBy(['code' => $keyCausale]));
                                       $orelavorate->setOrePianificate($oc->getOrePreviste());
                                       $orelavorate->setOreRegistrate($oc->getOrePreviste());
                                       $date = new \DateTime();
                                       $date->setTime(0,0,0);
                                       $date->setDate($anno, $mese, $i);
                                       $orelavorate->setGiorno($date);
                                       // Verifica data licenziamento 
                                           if ($personale->getDateDismissal() === null || $date <= $personale->getDateDismissal() ) {
                                               // scrive la pianificazione
                                               $entityManager->persist($orelavorate);
                                               $entityManager->flush();
                                               $count++ ;
                                           }             
       
                                       } // stessa persona, ore Previste e stesso giorno della settimana
                                    } // ciclo su collection PianoOreCantieri

                            }  // Piani orari per cantieri
                        }

                    }   // ciclo mese
                  }   // enforce = true
       
            } // ciclo sul personale dell'azienda

            // Aggiorna numero persone / cantieri
            $mesiaziendali = $mesiAziendaliRepository->findOneByKeyReference($keyReference);
            $mesiaziendali->setNumeroPersone($countPersone)->setNumeroCantieri(count(array_unique($arrayCantieri)));
            $entityManager->persist($mesiaziendali);
            $entityManager->flush();

            $this->addFlash('success', sprintf('Sono stati inseriti %d item di pianificazione ore mensili relative all\'azienda richiesta.' , $count ));
            */         
           // $url = $this->adminUrlGenerator->unsetAll();   senza altri metodi ritorna all'index del dashboard
           
        

           $entityManager->persist($raccoltaOrePersone);
           $entityManager->flush();

           $url = $this->adminUrlGenerator->unsetAll()
           ->setController(OreLavorateCrudController::class)
           ->setAction(Action::INDEX)
           ->set('filters[persona][comparison]', '=')
           ->set('filters[persona][value]', $idPersona)
           ->set('filters[isTransfer][value]', 0); // 0 = false, 1= true
       //    return $this->redirect($url);   
          
        return new RedirectResponse($url);

        } // premuto submit
        

        return $this->render('admin/raccoltaorepersona/edit.html.twig', [
            'fullName' => $fullName,
            'descPeriodo' => $descPeriodo,
            'pianificato' => $totalePianificato,
            'form' => $form->createView(),
        ]);
    }

    public function daysOfMonth($lastdate, $dateFeste): array
    {
        $giornodellasettimana=array('','Lu','Ma','Me','Gi','Ve','Sa','Do');//0 vuoto
        $arrDays = [];
        $mese = intval($lastdate->format('m')) ; $anno = intval($lastdate->format('Y'));
        $numday = cal_days_in_month(CAL_GREGORIAN, $mese , $anno);
        for ($ii=1; $ii<=$numday; $ii++) {
            // $giorno = new \DateTime;
            $giorno=mktime(0,0,0,$mese,$ii,$anno);
            $num_gg=(int)date("N",$giorno);
            $dayColumn = sprintf('%d %s', $ii, $giornodellasettimana[$num_gg] );
            $day = sprintf("%d-%'.02d-%'.02d", $anno, $mese, $ii );
            if ($num_gg === 6 || $num_gg === 7 || in_array($day, $dateFeste) === true ) { $festa = true ;} else {$festa = false ; }
            $arrDays[] = [$day => $dayColumn, 'festa' => $festa ]; 
            // $arrDays[] = $dayColumn;
        }
        return $arrDays ;
    }

   
}