<?php

namespace App\Controller;

use App\Repository\CantieriRepository;
use App\Repository\AziendeRepository;
// use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class CantieriMainBarChartController extends AbstractController
{
    
     /**
     * @Route("/main_cantieri_barchart", name="main_cantieri_barchart")
     */
    public function index(Environment $twig, CantieriRepository $cantieriRepository, AziendeRepository $aziendeRepository): Response
    {
        $azienda = $this->getUser()->getAziendadefault();
        if ($azienda !== null ) {
            $aziendaNickName = $azienda->getNickName();
            $aziendaId = $azienda->getId();
        } else { $aziendaNickName = '...seleziona azienda!!!'; $aziendaId = 0;} 
       
        $title = 'Analisi cantieri '.$aziendaNickName;
        
        // $cantieri = $cantieriRepository->findAll();
        $cantieri = $cantieriRepository->findBy(['azienda' => $aziendaId]);
        $bar_data = [];

        foreach ($cantieri as $cantiere) {

            if ($cantiere->getCategoria()->getCategoria() !== 'Organizzazione' && $cantiere->getAzienda() === $azienda) {
            
              // chart Bar   Budget Cantiere
            if ($cantiere->getPlanningHours() === 0 ) {
                $ricavo = 0 ; $ore = 0;
            } else {
                $interval = $cantiere->getDateEndJob()->diff($cantiere->getDateStartJob());
                $giorni = $interval->format('%a');
                if ($giorni < 1 ) { $giorni = 1 ;}
                $oremediegiorno = $cantiere->getPlanningHours() / $giorni;  // ore medie giorno (base 365)
                $ore = $oremediegiorno * 30 ;  // ore medie mese (base 360)
                if ($cantiere->getFlatRate() > 0 ) {
                    $ricavo = (($cantiere->getFlatRate()/100)/$cantiere->getPlanningHours()) ; 
                } else {
                    $ricavo = $cantiere->getHourlyRate()/100;
                }
            }
            // chart Bar  Consolidato mesi colcola la media sui mesi consolidati
            $olav = 0; $oimp = 0; $clav = 0; $oreLav = 0; $costo = 0;
            $consolidatiCantieri = [];
            $consolidatiCantieri = $cantiere->getConsolidatiCantieri();
                 foreach ($consolidatiCantieri as $consolidato) {
                    $olav += floatval($consolidato->getOreLavoro());
                    $olav += floatval($consolidato->getOreStraordinario());
                    $oimp += floatval($consolidato->getOreStraordinario());
                    $clav += floatval($consolidato->getCostoOreLavoro()/100);
                }
                if (($olav + $oimp) <= 0 ) { $costo = $clav; } else { $costo = $clav/($olav + $oimp);}
                $oreLav = $olav;
                
            $arrayBar = [
                'Cantiere' => $cantiere->getNameJob(),
                'OreBud' =>  $ore,
                'OreLav' =>  $oreLav,
                'Prezzo' =>  $ricavo,
                'Costo' =>  $costo,
            ];
            $bar_data[] =  $arrayBar ; 

            }
        } 
        // ordinamento decrescente per ore a budget
        $arrOL = [];
        $arrOL = array_column($bar_data, 'OreBud');
        array_multisort($arrOL, SORT_DESC, $bar_data); 
        
        return new Response($twig->render('cantieri/main_barchart.html.twig', [
            'page_title' => $title,
            'bar_chart' => $bar_data ,
        ]));
    }
     
}