<?php

namespace App\Controller\Admin;


use App\Repository\CantieriRepository;
// use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ExampleChartController extends AbstractController
{
    
     /**
     * @Route("/output_chart", name="output_chart")
     */
    public function index(Environment $twig, CantieriRepository $cantieriRepository): Response
    {
        $date = new \DateTime();
        $date->setTime(0,0,0);
       
        $gant_data = [];

      //  $gant_data[] = ['2014Spring', 'Spring 2014', 'spring', date('2014–02–22T10:50:00') , date('2014–05–20T11:55:00') , null, 100, null ];
      //  $gant_data[] = ['2014Summer', 'Summer 2014', 'summer', date('2014–04–30T10:50:00') , date('2014–08–20T11:55:00') , null, 100, null];
        $gant_data[] = ['2014Autumn', 'Autumn 2014', 'autumn', date(DATE_ATOM, mktime(0, 0, 0,  8, 21, 2014)) , date(DATE_ATOM, mktime(0, 0, 0,  11, 20, 2014)), null, 100, null];
       $gant_data[] = ['2014Winter', 'Winter 2014', 'winter', date(DATE_ATOM, mktime(0, 0, 0,  11, 21, 2014)) , date(DATE_ATOM, mktime(0, 0, 0, 2, 21, 2015)) , null, 100, null];
       $gant_data[] = ['2015Spring', 'Spring 2015', 'spring', date(DATE_ATOM, mktime(0, 0, 0, 2, 22, 2015)), date(DATE_ATOM, mktime(0, 0, 0,  5, 20, 2015)) , null, 50, null];

      $gant_data = json_encode($gant_data);

           /*  ['2015Summer', 'Summer 2015', 'summer', 
             new \DateTime(2015, 5, 21), new \DateTime(2015, 8, 20), null, 0, null],
            ['2015Autumn', 'Autumn 2015', 'autumn',
             new \DateTime(2015, 8, 21), new \DateTime(2015, 11, 20), null, 0, null],
            ['2015Winter', 'Winter 2015', 'winter',
             new \DateTime(2015, 11, 21), new \DateTime(2016, 2, 21), null, 0, null],
            ['Football', 'Football Season', 'sports',
             new \DateTime(2014, 8, 4), new \DateTime(2015, 1, 1), null, 100, null],
            ['Baseball', 'Baseball Season', 'sports',
             new \DateTime(2015, 2, 31), new \DateTime(2015, 9, 20), null, 14, null],
            ['Basketball', 'Basketball Season', 'sports',
             new \DateTime(2014, 9, 28), new \DateTime(2015, 5, 20), null, 86, null],
            ['Hockey', 'Hockey Season', 'sports',
             new \DateTime(2014, 9, 8), new \DateTime(2015, 5, 21), null, 89, null] */
        

        return new Response($twig->render('admin/bundles/EasyAdminBundle/example_chart.html.twig', [
            'page_title' => 'Piano Cantieri',
            'output_chart' => $gant_data ,
        ]));
    }
     
}