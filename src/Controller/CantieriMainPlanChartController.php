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

class CantieriMainPlanChartController extends AbstractController
{
    
     /**
     * @Route("/main_cantieri_planchart", name="main_cantieri_planchart")
     */
    public function index(Environment $twig, CantieriRepository $cantieriRepository, AziendeRepository $aziendeRepository): Response
    {
         // da parametrizzare nella bar navigation o nell'user collegato
        $azienda =  $aziendeRepository->findOneBy(['id'=> 1]);
        $title = 'Planning cantieri '.$azienda->getNickName();
        
        $cantieri = $cantieriRepository->findAll();
        $gant_data = [];
       
        foreach ($cantieri as $cantiere) {

            if ($cantiere->getCategoria()->getCategoria() !== 'Organizzazione' && $cantiere->getAzienda() === $azienda) {
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


            }
        } 

     
        return new Response($twig->render('cantieri/main_planchart.html.twig', [
            'page_title' => $title,
            'gant_chart' => $gant_data ,
           
        ]));
    }
     
}