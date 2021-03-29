<?php

namespace App\Controller;

use App\Repository\PersonaleRepository;
use App\Repository\MansioniRepository;
use App\Repository\AziendeRepository;
use App\ServicesRoutine\DateUtility;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class PersonaleMainChartController extends AbstractController
{
     /**
     * @Route("/main_personale_chart", name="main_personale_chart")
     */
    public function index(Environment $twig, PersonaleRepository $personaleRepository, MansioniRepository $mansioniRepository, AziendeRepository $aziendeRepository): Response
    {
       $azienda = $this->getUser()->getAziendadefault();
       if ($azienda !== null ) {
           $aziendaNickName = $azienda->getNickName();
           $aziendaId = $azienda->getId();
       } else { $aziendaNickName = '...seleziona azienda!!!'; $aziendaId = 0;} 
       // $azienda =  $aziendeRepository->findOneBy(['id'=> 1]);
       $rangeMonth = 0;
       if ($azienda !== null) { $rangeMonth = $azienda->getRangeAnalisi();}
       $title = 'Dashboard Personale '. $aziendaNickName;

       // legge tabella mansioni
       $arrMansioni = [];  $arrManValid = [];
       $mansioni = $mansioniRepository->findAll();
        foreach ($mansioni as $mansione) {
            $name = $mansione->getMansioneName(); 
            $arrMansioni[$name] = 0  ;
            $arrManValid[$name] = $mansione->getIsValidDA(); 
        } 
        $name = 'Da Assegnare';
        $arrMansioni[$name] = 0;
        $arrManValid[$name] = false ; 
       
        // collection personale
        //$personale = $personaleRepository->findAll(); // prevedere chiamata  X AZIENDA
        $personale = $personaleRepository->findBy(['azienda' => $aziendaId]); 
        $arrTypeAnno = []; // Accumula anni di assunzione
        $pieInv_data = [];  // Torta rapp invalidi / personale
        $chartMesiInv = []; //  chart rapp mensile invalidi/personale  
        $chartMesiContr= []; // chart rapp mensile contratto a tempo determinato/indeterminato
        $pieMan_data = [];  // Torta Mansioni
        $chartType_data = []; // Combo chart incremento tipo personale negli anni
        $chartEta_data = []; //  Chart età personale negli anni 
        $tabDet_data = [];  // tabella contratti a termine
        $tabMed_data = [];  // tabella visite mediche

        
        $countPers = 0; $countDis = 0; $countEscl = 0;
        $countErrMan = 0; $oggi = new \DateTime('now');

        foreach ($personale as $persona) {
            if ($persona->getDateHiring() !== null) {
            $date = $persona->getDateHiring();
            $anno=$date->format('Y');
            }

            if (!in_array($anno, $arrTypeAnno)) {$arrTypeAnno[] = $anno ;}
            if ( $persona->getIsEnforce() === true || ( $persona->getDateDismissal() !== null && $persona->getDateDismissal() >= $oggi ) ) {
                if ($persona->getScadenzaContratto() === null || $persona->getScadenzaContratto() >=  $oggi) { 
                        // può considerarlo 
                    $countPers++ ;  
                    
                    if ($persona->getMansione() !== null ) { 
                        $nomeMan = $persona->getMansione()->getMansioneName(); }
                    else {  $nomeMan = 'Da Assegnare';  }
                    if ($persona->getIsInvalid() === true  &&  $arrManValid[$nomeMan] === true ) { $countDis++; }  
                    if(array_key_exists($nomeMan,$arrMansioni) ) { 
                       // if ( $persona->getIsInvalid() === true &&  $arrManValid[$nomeMan] === false ) { $countDis--; $countEscl++ ; } // Mansione non valida per calcolo base % Invalidi
                        if ( $arrManValid[$nomeMan] === false ) { $countEscl++ ; } // Mansione non valida per calcolo % incidenza Invalidi
                        foreach ($arrMansioni as $key => $value) {
                            if ($key === $nomeMan ) {
                                $value ++;
                                $arrMansioni[$nomeMan] = $value;
                            } 
                        }

                    } else { $countErrMan++; }
                }
            }
        }
        if (( $countPers-($countDis + $countEscl) ) > 0 ) {
        $abili = $countPers-($countDis + $countEscl) ; $invalidi = $countDis;   
        $incidDis = $invalidi / $abili * 100; $incidAb = 100 - $incidDis; 
        } else { $abili = 0; $invalidi = 0; $incidDis=0;  $incidAb=0;} 
        $pieInv_data[] = ['Tipo' => 'Abili', 'Numero' => $incidAb ,];
        $pieInv_data[] = ['Tipo' => 'Invalidi', 'Numero' => $incidDis,];
        // $pieInv_data[] = ['Tipo' => 'Errori', 'Numero' => $countErrMan,];

        foreach ($arrMansioni as $key => $value) {
            $pieMan_data[] = ['Tipo' => $key , 'Numero' => $value ];
        }

        // Inizio e fine mese per un anno dal mese in corso -9 mesi e +3  mesi 
        // se rangeAnalisi di Aziende = -9.
        $arrInizMese = []; // primo del Mese -9  dal mese in corso +3 mesi dopo
        $arrFineMese = []; // ultimo del Mese -9  dal mese in corso +3 mesi dopo
        $dateutility = new DateUtility ;
        $dateObj =  new \DateTime(); 
        $annocur = $dateObj->format('Y');  $mesecur = $dateObj->format('m'); 
        $limitiMesi = $dateutility->calculateRangeYear($annocur, $mesecur, $rangeMonth );
        for ($i=0 ; $i<=23; $i++) {
            if ($i < 12 ) {  $arrInizMese[$i] = $limitiMesi[$i]; } 
            else {  $arrFineMese[$i-12] = $limitiMesi[$i];}
          }
        // calcola per mese i diversamente abili e per contratto a tempo Indeter/Determ.
        for ($i=0 ; $i<=11; $i++) {
            $indet = 0; $determ = 0;
            $abili = 0; $invalidi = 0; $neutri = 0;
            foreach ($personale as $persona) {
                // personale a tempo indeterminato
                if ($persona->getTipoContratto() === 'I' ) {
                    if ($persona->getDateHiring() !== null) {
                        if ( $persona->getDateHiring() <=  $arrFineMese[$i] ) {
                            // presente nel mese a condizione che la data di dimissioni sia nulla
                            // o maggiore di inizio mese
                                if ($persona->getDateDismissal() === null || $persona->getDateDismissal() >=  $arrInizMese[$i]) { 
                                    // può considerarlo 
                                    $indet++; 
                                     if ($persona->getMansione() !== null ) { 
                                        $nomeMan = $persona->getMansione()->getMansioneName(); }
                                    else {  $nomeMan = 'Da Assegnare';  }
                                    if(array_key_exists($nomeMan,$arrMansioni) ) { 
                                        if ($persona->getIsInvalid() === true && $arrManValid[$nomeMan] === true) { $invalidi++; }  else { $abili++ ;}
                                        if ( $arrManValid[$nomeMan] === false ) {  $neutri++ ; } // Mansione non valida per calcolo % incidenza Invalidi
                                    }
                                } 
                         }
                    }
                } else {
                // personale contratto a tempo determinato
                if ( ($persona->getDateHiring() <=  $arrFineMese[$i] ) && ($persona->getDateDismissal() === null || $persona->getDateDismissal() >=  $arrInizMese[$i] )) {
                    // presente nel mese a condizione che la data di scadenza contratto  sia nulla
                    // o maggiore di inizio mese
                        if ($persona->getScadenzaContratto() === null || $persona->getScadenzaContratto() >=  $arrInizMese[$i]) { 
                            // può considerarlo 
                            $determ++; 
                            if ($persona->getMansione() !== null ) { 
                                $nomeMan = $persona->getMansione()->getMansioneName(); }
                            else {  $nomeMan = 'Da Assegnare';  }
                                if(array_key_exists($nomeMan,$arrMansioni) ) { 
                                    if ($persona->getIsInvalid() === true && $arrManValid[$nomeMan] === true) { $invalidi++; }  else { $abili++ ;}
                                    if ( $arrManValid[$nomeMan] === false ) {  $neutri++ ; } // Mansione non valida per calcolo % incidenza Invalidi
                                }
                            }
                    }
                }
            }
            // assegna array per chart
            //  $meseaa = jdmonthname ( $arrInizMese[$i]->format('m'), 1 ).' '.$arrInizMese[$i]->format('y');
                $meseaa = $arrInizMese[$i]->format('m').'-'.$arrInizMese[$i]->format('y');
                if ($abili -= $neutri > 0 ) {
                $abili -= $neutri;       
                $incidDis = $invalidi / $abili * 100; $incidAb = 100 - $incidDis; 
                } else { $invalidi = 0;  $abili = 0; $incidDis = 0;  $incidAb = 0; }
                $chartMesiInv[] = ['Periodo' => $meseaa, 'Invalidi' => $incidDis, 'Abili' => $incidAb ];
                $chartMesiContr[] = ['Periodo' => $meseaa, 'Indeterminato' => $indet, 'Determinato' => $determ];
            }
        

        // Calcola anno tipo ed età
        sort($arrTypeAnno);
        $etauomini = 0; $etadonne = 0;
        $proguomini = 0 ; $progdonne = 0;
         foreach ($arrTypeAnno as $anno) {
            $annoObj =  new \DateTime(); 
            $annocur = $annoObj->format('Y');
            $annidiff = intval($annocur) - intval($anno);
            $dyear = new \DateTime();
            $uomini = 0;  $donne = 0; $abili = 0; $invalidi = 0;
           
            foreach ($personale as $persona) {
                if ($persona->getDateHiring() !== null) {
                         $etapers = $dyear->diff($persona->getBirthday())->format('%y');
                         // alla eta di oggi toglie gli anni diff
                         $eta = intval($etapers) - $annidiff;
                if ($anno === $persona->getDateHiring()->format('Y')) {
                    if ($persona->getDateDismissal() === null) { $count = 1;
                    } else { 
                        $annodim = $persona->getDateDismissal()->format('Y');
                        if ($annodim === $anno) { $count = -1 ; }
                    }
                    if ($persona->getGender() === 'M') { 
                         $uomini += $count ;
                         $proguomini += $count;
                         $etauomini += ($count * $eta);
                        }
                    if ($persona->getGender() === 'F') {
                         $donne += $count ;
                         $progdonne += $count;
                         $etadonne += ($count * $eta);
                        }
                    if ( $persona->getIsInvalid() === true) { $invalidi += $count; } else {$abili += $count ; }
                }
                }
            }
            // assegna array per chart
            $chartType_data[] = ['Anno' => $anno, 'Donne' => $donne, 'Uomini' => $uomini, 'Invalidi' => $invalidi, 
            'Abili' => $abili, 'Totale' => ($donne+$uomini), 'Media' => ($donne+$uomini)/2 ];

            if ($progdonne > 0) { $mediadonne = $etadonne/$progdonne ; } else { $mediadonne = 0; }
            if ($proguomini > 0) { $mediauomini = $etauomini/$proguomini ; } else { $mediauomini = 0; }
            $chartEta_data[] = ['Anno' => $anno, 'Donne' => $mediadonne, 'Uomini' => ($mediauomini)];
        }

        // tabella scadenza contratto
        $dataInizio = new \DateTime('now -30 days');
        $dataFine = new \DateTime('now +60 days');
     
        $scadDetermCollection = $personaleRepository->collectionPersScadDeterminato($azienda, $dataInizio, $dataFine);
        // ciclo sulla collection 
        foreach ($scadDetermCollection as $ps ){
            $fullName = $ps->getFullName();
            $scadenza = $ps->getScadenzaContratto();
            if ( $ps->getCantiere() !== null ) {
            $nameJob = $ps->getCantiere()->getNameJob();
             } else { $nameJob = "Vari o non assegnati" ;}
            // determina livello attenzione
            $now = new \DateTime('now');
            $unmese = new \DateTime('now +30 days');
            $duemesi = new \DateTime('now +60 days');
            if ($scadenza <= $now) { $alert = 'Scaduto' ;}
            elseif ($scadenza <= $unmese) { $alert = 'Menodi1mese' ;}
            elseif ($scadenza <= $duemesi) { $alert = 'Menodi2mesi' ;}

            $tabDet_data[] = ['Nome' => $fullName, 'Cantiere' => $nameJob, 'Scadenza' => $scadenza->format('Y-m-d'), 'Alert' => $alert];
        }

        // tabella scadenza visita medica
        $dataInizio = new \DateTime('now -180 days');
        $dataFine = new \DateTime('now +60 days');
     
        $scadVisitaCollection = $personaleRepository->collectionPersScadVisita($azienda, $dataInizio, $dataFine);
        // ciclo sulla collection 
        foreach ($scadVisitaCollection as $ps ){
            $fullName = $ps->getFullName();
            $scadenza = $ps->getScadenzaVisitaMedica();
            if ( $ps->getCantiere() !== null ) {
            $nameJob = $ps->getCantiere()->getNameJob();
             } else { $nameJob = "Vari o non assegnati" ;}
            // determina livello attenzione
            $now = new \DateTime('now');
            $unmese = new \DateTime('now +30 days');
            $duemesi = new \DateTime('now +60 days');
            if ($scadenza <= $now) { $alert = 'Scaduto' ;}
            elseif ($scadenza <= $unmese) { $alert = 'Menodi1mese' ;}
            elseif ($scadenza <= $duemesi) { $alert = 'Menodi2mesi' ;}
                      
            // se scadenza contratto precedente a scadenza visita non la segnala 
            $scadContratto = $ps->getScadenzaContratto();
                 if (($scadContratto !== null && $scadenza < $scadContratto) || $scadContratto === null ) { 
                 $tabMed_data[] = ['Nome' => $fullName, 'Cantiere' => $nameJob, 'Scadenza' => $scadenza->format('Y-m-d'), 'Alert' => $alert];
                 }
        
         }
        // 'page_title' => 'Dashboard Personale',
     // $test = json_encode($chartType_data);
        //  $test =  json_encode($arrMansioni).' ******K: '.json_encode(array_keys($arrMansioni)).
        // ' ******V: '.json_encode(array_values($arrMansioni));

        return new Response($twig->render('personale/mainchart.html.twig', [
            'page_title'  => $title,
            'pieInv_chart' => $pieInv_data ,
            'pieMan_chart' => $pieMan_data ,
            'tabDet_chart' => $tabDet_data ,
            'tabMed_chart' => $tabMed_data ,
            'comboType_chart' => $chartType_data,
            'lineEta_chart' => $chartEta_data,
            'stack_chart' => $chartMesiInv,
            'columns_chart' => $chartMesiContr
        ]));
    
    }
     
}