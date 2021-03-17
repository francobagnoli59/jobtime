<?php

namespace App\Controller;

// N O N  U S A T A //
// test modal form

use App\Repository\CantieriRepository;
use App\Repository\AziendeRepository;
// use App\Form\CantiereChartType;
use Doctrine\ORM\EntityManagerInterface;
// use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
// use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class CantiereChartController extends AbstractController
{
  
    /**
     * @var AdminUrlGenerator
     */
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct( AdminUrlGenerator $adminUrlGenerator ) 
    {
       $this->adminUrlGenerator = $adminUrlGenerator;
    }

    //  public function index(Environment $twig, CantieriRepository $cantieriRepository, AziendeRepository $aziendeRepository): Response
    //  public function index(Request $request, CantieriRepository $cantieriRepository, AziendeRepository $aziendeRepository): Response

    /**
     * @Route("/cantiere_chart", name="cantiere_chart")
     */
    public function index(Environment $twig, CantieriRepository $cantieriRepository, AziendeRepository $aziendeRepository): Response
    {
        // dati pianificazione selezionata
        $azienda_id =  $this->adminUrlGenerator->get('azienda_id');
        $azienda =  $aziendeRepository->findOneBy(['id'=> $azienda_id]);

        $cantiere_id = $this->adminUrlGenerator->get('cantiere_id');
        $cantiere = $cantieriRepository->findOneBy(['id' => $cantiere_id]);
        $title = 'Analisi cantiere '.$cantiere->getnameJob().'('.$azienda->getNickName().')';

    //    $form = $this->createForm(CantiereChartType::class, $cantiere );
     //   $form->handleRequest($request);
      //   if ($form->isSubmitted() && $form->isValid()) {
        $bar_data = [];

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
// }
   /* 
       return $this->render('cantieri/modal_chart.html.twig', [
        'form' => $form->createView(),
            'page_title' => $title,
            'bar_chart' => $bar_data ,
        ]); */
        
        return new Response($twig->render('cantieri/index_modal_chart.html.twig', [
            'page_title' => $title,
            'bar_chart' => $bar_data ,
        ])); 
    }
     
}