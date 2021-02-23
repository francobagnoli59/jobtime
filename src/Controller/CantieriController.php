<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CantieriController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(): Response
    {
         return new Response(<<<EOF
        <html>
            <body>
                <h1>Cantieri al lavoro</h1>
                <img src="/images/under-construction.gif" />
            </body>
        </html>
        EOF
                );      
    }

    


}
