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
       
     
        $gant_data = [
            ['TaskID' => '2014Spring', 'TaskName' => "Spring 2014", 'Resource' => 'spring', 'StartDate' => '20140222' , 'EndDate' => '20140520' , 'Duration' => null, 'PercentComplete' => 100, 'Dependencies' => null ],
            ['TaskID' => '2014Summer', 'TaskName' => "Summer 2014", 'Resource' => 'summer', 'StartDate' => '20140521' , 'EndDate' => '20140820' , 'Duration' => null, 'PercentComplete' => 100, 'Dependencies' => null ],
            ['TaskID' => '2014Autumn', 'TaskName' => 'Autumn 2014', 'Resource' => 'autumn', 'StartDate' => '20140821' , 'EndDate' => '20141120' , 'Duration' => null, 'PercentComplete' => 100, 'Dependencies' => null ],
            ['TaskID' => '2014Winter', 'TaskName' => 'Winter 2014', 'Resource' => 'winter', 'StartDate' => '20141121' , 'EndDate' => '20150221' , 'Duration' => null, 'PercentComplete' => 100, 'Dependencies' => null ],
            ['TaskID' => '2015Spring', 'TaskName' => 'Spring 2015', 'Resource' => 'spring', 'StartDate' => '20150222' , 'EndDate' => '20150521' , 'Duration' => null, 'PercentComplete' => 50, 'Dependencies' => null ],
           ];
        

        $area_data = [
            ['Year' => '2016', 'Sales' => 1000, 'Expenses' => 400],
            ['Year' => '2017', 'Sales' => 1170, 'Expenses' => 460],
            ['Year' => '2018', 'Sales' => 660, 'Expenses' => 1120],
            ['Year' => '2019', 'Sales' => 1030, 'Expenses' => 540]
         ];

        return new Response($twig->render('admin/bundles/EasyAdminBundle/example_chart.html.twig', [
            'page_title' => 'Piano Cantieri',
            'gant_chart' => $gant_data ,
            'arrayphp' => $area_data,
        ]));
    }
     
}