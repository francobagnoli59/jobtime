<?php

namespace App\Controller\Admin;

use App\Repository\PersonaleRepository;
use App\Repository\MansioniRepository;
use App\Entity\Mansioni;
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
    public function index(Environment $twig, PersonaleRepository $personaleRepository, MansioniRepository $mansioniRepository): Response
    {
        $arrMansioni = [];
     /*    $mansioni = $mansioniRepository->findAll();
        foreach ($mansioni as $mansione) {
            $key = $mansione->getMansione(); 
            $arrMansioni[] = [$key => 0,];
        }  */
        $arrMansioni[] = ["Da Assegnare" => 0]; 

        $personale = $personaleRepository->findAll(); // prevedere chiamata  X AZIENDA

        $pieInv_data = [];  // Torta rapp invalidi / personale
        $pieMan_data = [];  // Torta Mansioni
        $tabDet_data = [];  // tabella contratti a termine
        $tabMed_data = [];  // tabella visite mediche
        $countPers = 0; $countDis = 0;

        foreach ($personale as $persona) {
            if ($persona->getIsEnforce() === true) {
                $countPers++ ;  //   if ($persona->getIsInvalid() === true && $persona->getMansione()->getIsValidDA() === true) { $countDis++; }  
             
                if ($persona->getIsInvalid() === true) { $countDis++; }  
                if ($persona->getMansione() === null ) { $key = "Da Assegnare"; }
                else { 
                    $mansioniPersona = $persona->getMansione();
                    foreach ($mansioniPersona as $mansione) {
                         $key = $mansione->getMansione();
                        break;
                    }
                }
               /*  if (array_key_exists( $key, $arrMansioni) ) { 
                    $value = $arrMansioni[$key]; 
                    $value++; $arrMansioni[$key] = $value;
                } else { 
                    $arrMansioni[] = [$key => 1];
                } */
               

                if ($persona->getScadenzaContratto() !== null) {
                    $nowplus2m = new \DateTime("now"); // da sistemare: inserire solo dipend con scadenza da + 60gg
                    $tabDet_data[] = [$persona->getFullName(), $persona->getScadenzaContratto()->format('d-m-Y') ];
                }

                if ($persona->getScadenzaVisitaMedica() !== null) {
                    $nowplus2m = new \DateTime("now"); // da sistemare: inserire solo dipend con scadenza da +/- 60gg
                    $tabMed_data[] = [$persona->getFullName(), $persona->getScadenzaVisitaMedica()->format('d-m-Y') ];
                }
 
            }
        }

        $pieInv_data[] = ['Tipo' => 'Invalidi', 'Numero' => $countDis,];
        $pieInv_data[] = ['Tipo' => 'Personale', 'Numero' => $countPers,];
          
        $arrayDatiMan = array_keys($arrMansioni);
        foreach ($arrayDatiMan as $key)  {
            $pieMan_data[] = ['Tipo' => $key , 'Numero' => $arrMansioni[$key],];
        }
     
        return new Response($twig->render('admin/personale/mainchart.html.twig', [
            'page_title' => 'Dashboard Personale',
            'pieInv_chart' => $pieInv_data ,
            'pieMan_chart' => $pieMan_data ,
            'tabDet_chart' => $tabDet_data ,
            'tabMed_chart' => $tabMed_data ,
        ]));
    
    }
     
}