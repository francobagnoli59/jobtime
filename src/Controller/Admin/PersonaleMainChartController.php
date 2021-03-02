<?php

namespace App\Controller\Admin;

use App\Repository\PersonaleRepository;
use App\Repository\MansioniRepository;
use App\Repository\AziendeRepository;
 
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
       // da parametrizzare nella bar navigation
       $azienda =  $aziendeRepository->findOneBy(['id'=> 1]);

       // legge tabella mansioni
       $arrMansioni = [];  $arrManValid = [];
       $mansioni = $mansioniRepository->findAll();
        foreach ($mansioni as $mansione) {
            $name = $mansione->getMansioneName(); 
            $arrMansioni[$name] = 0  ;
            $arrManValid[$name] = $mansione->getIsValidDA(); 
        } 
        $arrMansioni['Da Assegnare'] = 0;
        $arrManValid[$name] = false ; 
       
        // collection personale
        $personale = $personaleRepository->findAll(); // prevedere chiamata  X AZIENDA

        $pieInv_data = [];  // Torta rapp invalidi / personale
        $pieMan_data = [];  // Torta Mansioni
        $tabDet_data = [];  // tabella contratti a termine
        $tabMed_data = [];  // tabella visite mediche
        $countPers = 0; $countDis = 0;
        $countErrMan = 0;
        foreach ($personale as $persona) {
            if ($persona->getIsEnforce() === true) {
                $countPers++ ;  
                
                if ($persona->getIsInvalid() === true) { $countDis++; }  
                if ($persona->getMansione() !== null ) { 
                    $nomeMan = $persona->getMansione()->getMansioneName(); }
                else {  $nomeMan = 'Da Assegnare';  }
                if(array_key_exists($nomeMan,$arrMansioni) ) { 
                     if ( $persona->getIsInvalid() === true &&  $arrManValid[$nomeMan] === false ) { $countDis-- ; } // Mansione non valida per calcolo % Invalidi
                     foreach ($arrMansioni as $key => $value) {
                        if ($key === $nomeMan ) {
                            $value ++;
                            $arrMansioni[$nomeMan] = $value;
                        } 
                     }

                } else { $countErrMan++; }
            }
        }

        $pieInv_data[] = ['Tipo' => 'Abili', 'Numero' => $countPers-$countDis,];
        $pieInv_data[] = ['Tipo' => 'Invalidi', 'Numero' => $countDis,];
       // $pieInv_data[] = ['Tipo' => 'Errori', 'Numero' => $countErrMan,];

        foreach ($arrMansioni as $key => $value) {
            $pieMan_data[] = ['Tipo' => $key , 'Numero' => $value ];
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
            $tabDet_data[] = ['Nome' => $fullName, 'Cantiere' => $nameJob, 'Scadenza' => $scadenza->format('d-m-y')];
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
            // se scadenza contratto precedente a scadenza visita non la segnala 
            $scadContratto = $ps->getScadenzaContratto();
                 if (($scadContratto !== null && $scadenza < $scadContratto) || $scadContratto === null ) { 
                 $tabMed_data[] = ['Nome' => $fullName, 'Cantiere' => $nameJob, 'Scadenza' => $scadenza->format('d-m-y')];
                 }
        }

        // 'page_title' => 'Dashboard Personale',
        //  $test = json_encode($tabDet_data);
        //  $test =  json_encode($arrMansioni).' ******K: '.json_encode(array_keys($arrMansioni)).
        // ' ******V: '.json_encode(array_values($arrMansioni));

        return new Response($twig->render('admin/personale/mainchart.html.twig', [
            'page_title'  => 'Dashboard Personale',
            'pieInv_chart' => $pieInv_data ,
            'pieMan_chart' => $pieMan_data ,
            'tabDet_chart' => $tabDet_data ,
            'tabMed_chart' => $tabMed_data ,
        ]));
    
    }
     
}