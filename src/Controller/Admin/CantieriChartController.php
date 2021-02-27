<?php

namespace App\Controller\Admin;

use App\Repository\CantieriRepository;
// use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class CantieriChartController extends AbstractController
{
    
     /**
     * @Route("/cantieri_chart", name="cantieri_chart")
     */
    public function index(Environment $twig, CantieriRepository $cantieriRepository): Response
    {
        $cantieri = $cantieriRepository->findAll();
        $gant_data = [];
        $bar_data = [];

        foreach ($cantieri as $cantiere) {

            $interval = $cantiere->getDateStartJob()->diff($cantiere->getDateEndJob());
            $giorni = $interval->format('%a');
            // calcola la differenza ad oggi
            $interval = $cantiere->getDateStartJob()->diff(new \DateTime("now"));
            $adoggi = $interval->format('%a');
            if ($adoggi > $giorni) {
                $pcompl = 100;
            } else { $pcompl = intdiv( $adoggi*100, $giorni); }
        
            $arrayGantt = [
                'TaskID' => $cantiere->getNameJob(),
                'TaskName' => $cantiere->getNameJob(),
                'Resource' => $cantiere->getProvincia()->getName(),
                'StartDate' => $cantiere->getDateStartJob()->format('Ymd'),
                'EndDate' => $cantiere->getDateEndJob()->format('Ymd'), // la passa ma non la assegna
                'Duration' => $giorni, 
                'PercentComplete' =>  $pcompl, 
                'Dependencies' => null 
            ];
            $gant_data[] =  $arrayGantt ;

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

     
        return new Response($twig->render('admin/cantieri/chart.html.twig', [
            'page_title' => 'Planning Cantieri',
            'gant_chart' => $gant_data ,
            'bar_chart' => $bar_data ,
        ]));
    }
     
}