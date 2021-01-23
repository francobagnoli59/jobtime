<?php

namespace App\Controller\Admin;

use App\Entity\Cantieri;
use App\Entity\CommentiPubblici;
use App\Entity\Province;
use App\Entity\RegoleFatturazione;
use App\Entity\Personale;
use App\Entity\Aziende;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
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
        yield MenuItem::linkToCrud('Cantieri', 'fas fa-building', Cantieri::class);
        yield MenuItem::linkToCrud('Personale', 'fas fa-address-card', Personale::class);
        yield MenuItem::linkToCrud('Feedback e segnalazioni', 'fas fa-comments', CommentiPubblici::class);
        yield MenuItem::linkToCrud('Aziende', 'fas fa-boxes', Aziende::class);
        yield MenuItem::linkToCrud('Province', 'fas fa-map-marker-alt', Province::class);
        yield MenuItem::linkToCrud('Regole di fatturazione', 'fas fa-wave-square', RegoleFatturazione::class);
       
    }
}
