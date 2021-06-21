<?php

namespace App\Controller;


use App\Entity\RaccoltaOrePersone;
use App\Entity\ModuliRaccoltaOreCantieri;
use App\Entity\FestivitaAnnuali;
// use App\Entity\Aziende;
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

    /**
     * @Route("/person_hour_month/persona/{persona}/anno/{anno}/mese/{mese}",  name="person_hour_month")
     */
    public function editMonth(Request $request): Response
    {
                
        $entityManager = $this->getDoctrine()->getManager();

        // elenco ore lavorate per persona per Id=1   ( da personalizzare con filtro persona / anno / mese)
        
        $idPersona =  $request->get('persona');
        $anno =  $request->get('anno');
        $mese =  $request->get('mese');
       
        //  $this->addFlash('info',  $idPersona.' '.$annoParm.' '.$meseParm);
 
        $personale = $entityManager->getRepository(Personale::class)->findOneBy(['id'=> $idPersona]);
        $fullName = $personale->getFullName();
         
        // valori iniziali per preparazione periodo mensile (primo e ultimo giorno)
        $annoInt = intval($anno);
        $meseInt = intval($mese);
        $dateutility = new DateUtility ;
        // $limitiMese = $dateutility->calculateLimitMonth($anno, sprintf('%d', intval($lastdate->format('m')) ) );
        $limitiMese = $dateutility->calculateLimitMonth($annoInt, $meseInt );
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
            $dateholiday = sprintf("%d-%'.02d-%'.02d", $annoInt, $mm, $gg );
            $dateFeste[] = $dateholiday;
        }
                    
        // prepara array (giorni del mese)
        $arrDaysOfMonth = $this->daysOfMonth($annoInt, $meseInt, $dateFeste);
        $meseanno=array('','Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre');//0 vuoto
        $descPeriodo = $meseanno[$meseInt]. ' '.$anno ;
        // $giorninelmese = cal_days_in_month(CAL_GREGORIAN, intval($lastdate->format('m')) , intval($lastdate->format('Y')));
        $giorninelmese = cal_days_in_month(CAL_GREGORIAN, $meseInt , $annoInt);
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

        // entity null
        $raccoltaOrePersone = null;

        // contiene id cantieri
        $cantierilavorati = [];
        // contiene true se un orario risulta trasferito, blocca l'input su tutti i cantieri
        // non dovrebbe essere possibile che si creino condizioni miste nello stesso mese (miste = ore lavorate trasferite e non trasferite)
        $cantieriTrasferiti = false;

        // per default rigo cantiere senza orari
        $firstCantiere = null;

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
                $othercau = ['Test causali',1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,9];
                $altreCausali = ['Altre causali',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
                $altreCausaliOreConfermate = [false,false,false,false,false,false,false,false,false,false,
                false,false,false,false,false,false,false,false,false,false,false,false,false,
                false,false,false,false,false,false,false,false,false,false];
                $totaleXGiorno = ['TOTALI',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
                $totalePianificato = 0 ; 
                // ciclo per cantiere crea nuove raccolte ore
                $cantierikeys = array_keys($cantierilavorati) ;
                foreach ( $cantierikeys as $idCantiere) {
                   //
                   if ($firstCantiere === null) { $firstCantiere = $idCantiere ;}
                    $nomeCantiere = $cantierilavorati[$idCantiere];
                    $cantiere = $entityManager->getRepository(Cantieri::class)->findOneBy(['id'=> $idCantiere]);
                    $oreMeseCantieri = new ModuliRaccoltaOreCantieri();
                        $oreMeseCantieri->setCantiere($cantiere);
                        $oreMeseCantieri->setPrevIdPlanned($idCantiere);
                        // crea Array giorni del mese con ore registrate 
                        $totOre = 0 ;
                        $oreGiornaliere = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
                        $isOreConfermate = [false,false,false,false,false,false,false,false,false,false,
                        false,false,false,false,false,false,false,false,false,false,false,false,false,
                        false,false,false,false,false,false,false,false,false];
                        foreach ($oreLavorateCollection as $ol ) {
                            if ($idCantiere === $ol->getCantiere()->getId()) {
                                $causale = $ol->getCausale()->getCode();
                                $oreReg = $ol->getOreRegistrate();
                                $orePian = $ol->getOrePianificate();
                                if ($ol->getIsTransfer() === true) {
                                    $cantieriTrasferiti = true ;
                                }
                                if( $orePian > 0) { $totalePianificato = $totalePianificato + $orePian ;}
                                $giorno = $ol->getGiorno();
                                $d = intval($giorno->format('d')) -1;
                                if( $oreReg > 0) { 
                                    $totaleXGiorno[$d+1] = $totaleXGiorno[$d+1] + $oreReg ;
                                    if($causale === 'ORDI') { 
                                    $isOreConfermate[$d] = $ol->getIsConfirmed(); 
                                    $oreGiornaliere[$d] = $oreReg;
                                    $totOre = $totOre + $oreReg; 
                                    } else { $altreCausali[$d+1] = $altreCausali[$d+1] + $oreReg ;
                                        if ($ol->getIsConfirmed() === true) {
                                          $altreCausaliOreConfermate[$d+1] = $ol->getIsConfirmed(); }
                                    }
                                }
                            }
                        }
                        $oreGiornaliere[31] = $totOre;
                        $oreMeseCantieri->setOreGiornaliere($oreGiornaliere);
                        $oreMeseCantieri->setIsOreConfermate($isOreConfermate);

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
                 // aggiunge cantiere con orari tutti azzerati 
                    $oreGiornaliere = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
                    $isOreConfermate = [false,false,false,false,false,false,false,false,false,false,
                    false,false,false,false,false,false,false,false,false,false,false,false,false,
                    false,false,false,false,false,false,false,false,false];
                    $cantiere = $entityManager->getRepository(Cantieri::class)->findOneBy(['id'=> $firstCantiere]);
                    $oreMeseCantieri = new ModuliRaccoltaOreCantieri();
                    $oreMeseCantieri->setCantiere($cantiere);
                    $oreMeseCantieri->setPrevIdPlanned(0);
                    $oreMeseCantieri->setOreGiornaliere($oreGiornaliere);
                    $oreMeseCantieri->setIsOreConfermate($isOreConfermate);

                    $keyreference =  sprintf("%010d-%s-%s", $idPersona, $anno, $mese);
                    $raccoltaOrePersone = $entityManager->getRepository(RaccoltaOrePersone::class)->findOneByKeyReference($keyreference) ;
                        // registra le ore cantiere libero
                    $raccoltaOrePersone->addOreMeseCantieri($oreMeseCantieri);
                    $entityManager->persist($raccoltaOrePersone);
                    $entityManager->flush();

            }
        } 
               
            if ($raccoltaOrePersone === null) { 
             // non ci sono orari
              $this->addFlash('warning',  'Raccolta ore non eseguibile, non ci sono ore ordinarie registrate '); 
        
            // Ritorna alle ore lavorate del personale
            $url = $this->adminUrlGenerator->unsetAll()
            ->setController(OreLavorateCrudController::class)
            ->setAction(Action::INDEX)
            ->set('filters[persona][comparison]', '=')
            ->set('filters[persona][value]', $idPersona)
            ->set('filters[isTransfer][value]', 0); // 0 = false, 1= true
            return new RedirectResponse($url);
            }

        
        $form = $this->createForm(RaccoltaOrePersonaType::class, $raccoltaOrePersone, ['disabled_cantiere' =>  $cantieriTrasferiti ]);
        $form->get('tipogiorno')->setData($tipogiorno);
        $form->get('nomegiorno')->setData($nomegiorno);

        $form->get('altreCausali')->setData($altreCausali);
        $form->get('altreCausaliOreConfermate')->setData($altreCausaliOreConfermate);
        $form->get('totaleXGiorno')->setData($totaleXGiorno);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {
        //    $this->addFlash('success',  'Raccolta ore confermata ');

        if ( $cantieriTrasferiti === false) {
            $entityManager->persist($raccoltaOrePersone);
            $entityManager->flush();

            // Rilegge il modulo raccolta ore e aggiorna le ore lavorate
            $raccoltaOrePersone = $entityManager->getRepository(RaccoltaOrePersone::class)->findOneByKeyReference($keyreference) ;
            $oreMesiCantieriCollection =  $raccoltaOrePersone->getOreMeseCantieri();
            // scorre la collection aggiornando ogni giorno per le ore ordinarie
            foreach ($oreMesiCantieriCollection as $mroc ) {
                $oreGiornaliere = $mroc->getOreGiornaliere();
                if ($mroc->getPrevIdPlanned() > 0 ) {
                // raccolta ore su cantiere pianificato
                    if ($mroc->getPrevIdPlanned() ===  $mroc->getCantiere()->getId() ) {
                        // stesso cantiere (non modificato)
                        for ($i=1 ; $i<=$giorninelmese; $i++) { 
                            $keyRefggLav = sprintf("%010d-%010d-%s-%s-%02d-ORDI", $mroc->getCantiere()->getId(), $raccoltaOrePersone->getPersona()->getId(), $anno, $mese, $i );
                            $orelavorate = $entityManager->getRepository(OreLavorate::class)->findOneByKeyReference($keyRefggLav) ;
                            if ($orelavorate === null) {
                                // giorno non registrato in ore lavorate, lo deve inserire se ore > 0
                                if ($oreGiornaliere[$i-1] > 0) {
                                    $orelavorate = new Orelavorate();
                                    $orelavorate->setAzienda($personale->getAzienda());
                                    $orelavorate->setCantiere($mroc->getCantiere());
                                    $orelavorate->setPersona($raccoltaOrePersone->getPersona());
                                    $orelavorate->setIsConfirmed(false);
                                    $orelavorate->setIsTransfer(false);

                                    $keyCausale = 'ORDI';
                                    $orelavorate->setCausale($entityManager->getRepository(Causali::class)->findOneBy(['code' => $keyCausale]));
                                    $orelavorate->setOrePianificate(0);
                                    $orelavorate->setOreRegistrate($oreGiornaliere[$i-1]);
                                    $date = new \DateTime();
                                    $date->setTime(0,0,0);
                                    $date->setDate(intval($anno), intval($mese), $i);
                                    $orelavorate->setGiorno($date);
                                    // Verifica data licenziamento 
                                        if ($personale->getDateDismissal() === null || $date <= $personale->getDateDismissal() ) {
                                            // inserisce ore lavorate
                                            $entityManager->persist($orelavorate);
                                            $entityManager->flush();
                                        }             
                                    } 
                            } else {
                                // giorno presente in ore lavorate, aggiorna se differenza sulle ore
                                // (ammette anche zero) essendo cantiere pianificato, altrimenti salta
                                if ($orelavorate->getOreRegistrate() !== $oreGiornaliere[$i-1] ) {
                                    $orelavorate->setOreRegistrate($oreGiornaliere[$i-1]) ;
                                    $entityManager->persist($orelavorate);
                                    $entityManager->flush();
                                }
                            }
                        } //end for stesso cantiere
                    } else {
                         // modificato cantiere pianificato su ore già assegnate, determina id record dal precedente,
                         // e aggiorna tutti gli orari modificando il cantiere.
                         for ($i=1 ; $i<=$giorninelmese; $i++) { 
                            $keyRefggLav = sprintf("%010d-%010d-%s-%s-%02d-ORDI", $mroc->getPrevIdPlanned(), $raccoltaOrePersone->getPersona()->getId(), $anno, $mese, $i );
                            $orelavorate = $entityManager->getRepository(OreLavorate::class)->findOneByKeyReference($keyRefggLav) ;
                                if ($orelavorate !== null) {
                                $orelavorate->setOreRegistrate($oreGiornaliere[$i-1]) ;
                                $orelavorate->setCantiere(
                                    $entityManager->getRepository(Cantieri::class)->findOneBy(['id'=>  $mroc->getCantiere()->getId()])  
                                );
                                $entityManager->persist($orelavorate);
                                $entityManager->flush();
                                }   
                        }
                    }
                } else {
                    // cantiere non pianificato, verifica/inserisce aggiorna orari se > 0
                    for ($i=1 ; $i<=$giorninelmese; $i++) { 
                        if ( $oreGiornaliere[$i-1] > 0 ) {
                            $keyRefggLav = sprintf("%010d-%010d-%s-%s-%02d-ORDI", $mroc->getCantiere()->getId(), $raccoltaOrePersone->getPersona()->getId(), $anno, $mese, $i );
                            $orelavorate = $entityManager->getRepository(OreLavorate::class)->findOneByKeyReference($keyRefggLav) ;
                            if ($orelavorate === null) {
                                // giorno e cantiere non esistente, inserisce
                                $orelavorate = new Orelavorate();
                                $orelavorate->setAzienda($personale->getAzienda());
                                $orelavorate->setCantiere($mroc->getCantiere());
                                $orelavorate->setPersona($raccoltaOrePersone->getPersona());
                                $orelavorate->setIsConfirmed(false);
                                $orelavorate->setIsTransfer(false);

                                $keyCausale = 'ORDI';
                                $orelavorate->setCausale($entityManager->getRepository(Causali::class)->findOneBy(['code' => $keyCausale]));
                                $orelavorate->setOrePianificate(0);
                                $orelavorate->setOreRegistrate($oreGiornaliere[$i-1]);
                                $date = new \DateTime();
                                $date->setTime(0,0,0);
                                $date->setDate(intval($anno), intval($mese), $i);
                                $orelavorate->setGiorno($date);
                                // Verifica data licenziamento 
                                    if ($personale->getDateDismissal() === null || $date <= $personale->getDateDismissal() ) {
                                        // inserisce ore lavorate
                                        $entityManager->persist($orelavorate);
                                        $entityManager->flush();
                                    }             
                                } 
                            //
                            else {
                                // giorno esistente, aggiorna orario
                                $orelavorate->setOreRegistrate($oreGiornaliere[$i-1]) ;
                                $entityManager->persist($orelavorate);
                                $entityManager->flush();   
                                }
                        } //orario inserito (> 0)
                    } // end for cantiere non pianificato
                }
            } //end for each cantieri collection
        } // mese non ancora trasferito
        else {  $this->addFlash('warning',  'Raccolta ore non modificabile, la mensilità è già stata consolidata e trasferita. ');  }
        // Ritorna alle ore lavorate del personale
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

   
    public function daysOfMonth($annoP, $meseP, $dateFeste): array
    {
        $giornodellasettimana=array('','Lu','Ma','Me','Gi','Ve','Sa','Do');//0 vuoto
        $arrDays = [];
        // $mese = intval($lastdate->format('m')) ; $anno = intval($lastdate->format('Y'));
        $numday = cal_days_in_month(CAL_GREGORIAN, $meseP , $annoP);
        for ($ii=1; $ii<=$numday; $ii++) {
            // $giorno = new \DateTime;
            $giorno=mktime(0,0,0,$meseP,$ii,$annoP);
            $num_gg=(int)date("N",$giorno);
            $dayColumn = sprintf('%d %s', $ii, $giornodellasettimana[$num_gg] );
            $day = sprintf("%d-%'.02d-%'.02d", $annoP, $meseP, $ii );
            if ($num_gg === 6 || $num_gg === 7 || in_array($day, $dateFeste) === true ) { $festa = true ;} else {$festa = false ; }
            $arrDays[] = [$day => $dayColumn, 'festa' => $festa ]; 
            // $arrDays[] = $dayColumn;
        }
        return $arrDays ;
    }

   
}