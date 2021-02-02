<?php

namespace App\Controller\Admin;

use App\Entity\Cantieri;
use App\Entity\CommentiPubblici;
use App\Entity\Province;
use App\Entity\RegoleFatturazione;
use App\Entity\Personale;
use App\Entity\Aziende;
use App\Entity\Clienti;
use App\Entity\Causali;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin")
     */
    public function index(): Response
    {
        
        // redirect to some CRUD controller
        $routeBuilder = $this->get(AdminUrlGenerator::class);

        return $this->redirect($routeBuilder->setController(CantieriCrudController::class)->generateUrl());

        // you can also redirect to different pages depending on the current user
        // if ('jane' === $this->getUser()->getUsername()) {
        //    return $this->redirect('...');
        //}

        // you can also render some template to display a proper Dashboard
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    // ...


    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<img src="/images/logo-alioth-group-ridotto.png"/>');
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setDateFormat('dd/MM/yyyy')
            ->setDateTimeFormat('dd/MM/yyyy HH:mm:ss')
            ->setTimeFormat('HH:mm');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoRoute('Vai ai Feedback', 'fas fa-home', 'homepage');

        yield MenuItem::section('Anagrafiche');
        yield MenuItem::linkToCrud('Cantieri', 'fas fa-building', Cantieri::class);
        yield MenuItem::linkToCrud('Personale', 'fas fa-address-card', Personale::class);
        yield MenuItem::linkToCrud('Clienti', 'fas fa-users', Clienti::class);

        yield MenuItem::section('Verifiche');
        yield MenuItem::linkToCrud('Feedback e segnalazioni', 'fas fa-comments', CommentiPubblici::class);

        yield MenuItem::section('Configurazioni');
        yield MenuItem::linkToCrud('Aziende', 'fas fa-boxes', Aziende::class);
        yield MenuItem::linkToCrud('Province', 'fas fa-map-marker-alt', Province::class);
        yield MenuItem::linkToCrud('Causali Paghe', 'fas fa-pencil-ruler', Causali::class);
        yield MenuItem::linkToCrud('Regole di fatturazione', 'fas fa-wave-square', RegoleFatturazione::class);
       
        // yield MenuItem::section();
        // yield MenuItem::linkToLogout('Logout', 'fa fa-exit');

        yield MenuItem::subMenu('Prove', 'fas fa-question')->setSubItems([
              MenuItem::linkToRoute('Export province', 'fas fa-file-excel', 'export')->setLinkTarget("_blank"),
              MenuItem::linkToRoute('Commenti no CRUD', 'fas fa-comments',  'admin_commenti_edit'),
             //->setController(CommentiNoCrudController::class);
              MenuItem::linkToRoute('Province no CRUD', 'fas fa-table', 'admin_province_edit'),
              MenuItem::linkToRoute('Lista output province', 'fas fa-stream', 'province'),
             ]) ;
       
       //  yield MenuItem::linkToUrl('Link URL Export province', 'fas fa-file-excel', '/admin/excel')->setLinkTarget("_blank");
    }
}

//    
 //  