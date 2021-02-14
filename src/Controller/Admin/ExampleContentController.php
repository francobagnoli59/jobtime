<?php

namespace App\Controller\Admin;

// use App\Entity\Province;
use App\Repository\ProvinceRepository;
// use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ExampleContentController extends AbstractController
{
    
     /**
     * @Route("/output_province", name="output_province")
     */
    public function index(Environment $twig, ProvinceRepository $provinceRepository): Response
    {
        return new Response($twig->render('admin/bundles/EasyAdminBundle/example_content.html.twig', [
            'page_title' => 'ALIOTH Example',
            'output_province' => $provinceRepository->findAll(),
        ]));
    }
     
}