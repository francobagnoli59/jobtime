<?php

namespace App\Controller;

use App\Entity\Cantieri;
use App\Repository\CommentiPubbliciRepository;
use App\Repository\CantieriRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class CantieriController extends AbstractController
{   
    private $twig;

    public function __construct(Environment $twig)
    {
    $this->twig = $twig; 
    }


    /**
     * @Route("/", name="homepage")
     */
    public function index( CantieriRepository $cantieriRepository): Response
    {
        
        return new Response($this->twig->render('cantieri/index.html.twig', [
            'cantieri' => $cantieriRepository->findBy(['isPublic' => true], ['createdAt' => 'DESC']),
            //  'cantieri' => $cantieriRepository->findAll(),
        ]));

        /* return new Response(<<<EOF  
        <html>
            <body>
                <h1>Cantieri al lavoro</h1>
                <img src="/images/under-construction.gif" />
            </body>
        </html>
        EOF  );       */
    }


    /**
     * @Route("/cantieri/{id}", name="cantieri")
     */
     public function show(Request $request, Cantieri $cantieri, CommentiPubbliciRepository $commentiPubbliciRepository): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentiPubbliciRepository->getCommentPaginator($cantieri, $offset);
        

        return new Response($this->twig->render('cantieri/show.html.twig', [
            'cantieri' => $cantieri,
            'commenti' => $paginator,
            'previous' => $offset - $commentiPubbliciRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + $commentiPubbliciRepository::PAGINATOR_PER_PAGE),
             ])); 
        }   

}
