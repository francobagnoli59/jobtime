<?php

namespace App\Controller\Admin;

use App\Entity\CommentiPubblici;
use App\Repository\CommentiPubbliciRepository;
use App\Form\CommentiType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
// use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

/**
 * @Route("/admin/commenti")
 */
class CommentiNoCrudController extends AbstractController
{
   
    /**
     * @Route("/edit", methods="GET|POST", name="admin_commenti_edit")
     */
    public function edit(Request $request, CommentiPubbliciRepository $commentiPubbliciRepository): Response
    {
      
        $commento = new CommentiPubblici();
        $commento = $commentiPubbliciRepository->findOneByEmail('f.bagnoli@masotech.it');
        $form = $this->createForm(CommentiType::class, $commento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success',  'Aggiornamento eseguito');
           // return $this->redirectToRoute('admin_commenti_edit', $request->query->all());
        }
        
        return $this->render('admin/commenti/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
