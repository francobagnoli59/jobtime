<?php

namespace App\Controller\Admin;

use App\Entity\Province;
use App\Repository\ProvinceRepository;
use App\Form\ProvinceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/admin/province")
 */
class ProvinceNoCrudController extends AbstractController
{
    private $session;
    /**
     * @var AdminContextProvider
     */
    private AdminContextProvider $adminContextProvider;
    /**
     * @var AdminUrlGenerator
     */
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(
        SessionInterface $session,
        AdminContextProvider $adminContextProvider,
        AdminUrlGenerator $adminUrlGenerator
    ) {
        $this->session = $session;
        $this->adminContextProvider = $adminContextProvider;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }


    /**
     * @Route("/edit/province", methods="GET|POST", name="admin_province_edit")
     */
    public function edit(Request $request, ProvinceRepository $provinceRepository): Response
    {
       // $context = $this->adminContextProvider->getContext();
        $province = new Province();
        $province = $provinceRepository->findOneByCode('PI');
      
        $form = $this->createForm(ProvinceType::class, $province);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            /* $crud       = $this->adminContextProvider->getContext()->getCrud();
            $controller = $crud->getControllerFqcn();
            $action     = $crud->getCurrentAction();
            */

            $this->addFlash('success',  'Aggiornamento province eseguito ');
            // return $this->redirectToRoute('province');
           // return parent::edit($context);
          
           // $url = $this->adminUrlGenerator->unsetAll();   se non commentato ritorna all'index del dashboard
            $url = $this->adminUrlGenerator->unsetAll()
           ->setController('App\Controller\Admin\ProvinceCrudController')
           ->setAction('index')
           ->generateUrl();

        return new RedirectResponse($url);

        }
        
        return $this->render('admin/province/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}