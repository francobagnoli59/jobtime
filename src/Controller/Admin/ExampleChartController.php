<?php

namespace App\Controller\Admin;

// use App\Entity\Province;
use App\Repository\ProvinceRepository;
// use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ExampleChartController extends AbstractController
{
    
     /**
     * @Route("/output_chart", name="output_chart")
     */
    public function index(Environment $twig, ProvinceRepository $provinceRepository): Response
    {
        return new Response($twig->render('admin/bundles/EasyAdminBundle/example_chart.html.twig', [
            'page_title' => 'Chart Example',
            'output_chart' => 'Speriamo bene',
        ]));
    }
     
}