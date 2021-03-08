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
use App\Entity\FestivitaAnnuali;
use App\Entity\MesiAziendali;
use App\Entity\OreLavorate;
use App\Entity\ConsolidatiPersonale;
use App\Entity\ConsolidatiCantieri;
use App\Entity\ImportPersonale;
use App\Entity\ImportCantieri;
use App\Entity\DocumentiCantieri;
use App\Entity\DocumentiPersonale;
use App\Entity\CategorieServizi;
use App\Entity\AreeGeografiche;
use App\Entity\Mansioni;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use Symfony\Component\Security\Core\User\UserInterface;


class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin",  name="admin" )
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

  /*
    public function configureUserMenu(UserInterface $user): UserMenu
    {
        // Usually it's better to call the parent method because that gives you a
        // user menu with some menu items already created ("sign out", "exit impersonation", etc.)
        // if you prefer to create the user menu from scratch, use: return UserMenu::new()->...
        return parent::configureUserMenu($user)
            // use the given $user object to get the user name
            //->setName($user->getFullName())
            // use this method if you don't want to display the name of the user
            //->displayUserName(false)

            // you can return an URL with the avatar image
            // ->setAvatarUrl('https://127.0.0.1:8000/uploads/photos/54751afda29c0fe97c2132b7298f8da2da6e58b4.png');
            //->setAvatarUrl($user->getProfileImageUrl())
            // use this method if you don't want to display the user image
            //->displayUserAvatar(false)
            // you can also pass an email address to use gravatar's service
            //->setGravatarEmail($user->getEmail())

            // you can use any type of menu item, except submenus
            ->addMenuItems([
                MenuItem::linkToRoute('My Profile', 'fa fa-id-card', 'cantieri_chart'),
                MenuItem::linkToRoute('Settings', 'fa fa-user-cog', 'output_chart'),  // '...', ['...' => '...']),
                MenuItem::section(),
                MenuItem::linkToLogout('Logout', 'fa fa-sign-out'),
            ]);
    }  */






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
        yield MenuItem::linkToRoute('Dashboard Personale', 'fas fa-chart-pie', 'main_personale_chart');
        yield MenuItem::section('Inserimento orari di lavoro');
        yield MenuItem::linkToRoute('Prepara mensilità', 'fas fa-calendar', 'planning_month');
        yield MenuItem::linkToCrud('Ore lavorate', 'fas fa-clock',  OreLavorate::class);
        yield MenuItem::linkToCrud('Elabora mensilità', 'fas fa-calendar-day', MesiAziendali::class);

        yield MenuItem::section('Anagrafiche');
        yield MenuItem::linkToCrud('Cantieri', 'fas fa-building', Cantieri::class);
        yield MenuItem::linkToCrud('Personale', 'fas fa-address-card', Personale::class);
        // ->setQueryParameter('filters[gender][comparison]', '=')
        // ->setQueryParameter('filters[gender][value]', 'M');
        yield MenuItem::linkToCrud('Clienti', 'fas fa-users', Clienti::class);
        yield MenuItem::subMenu('Import anagrafiche', 'fas fa-upload')->setSubItems([
            MenuItem::linkToCrud('Import cantieri', 'fas fa-file-excel', ImportCantieri::class),
            MenuItem::linkToCrud('Import personale', 'fas fa-file-excel', ImportPersonale::class),
            ]) ;

        yield MenuItem::section('Report');
        yield MenuItem::linkToRoute('Cantieri', 'fas fa-chart-line', 'cantieri_chart');

        yield MenuItem::section();
        yield MenuItem::subMenu('Configurazioni', 'fas fa-cogs ')->setSubItems([
             MenuItem::linkToCrud('Aziende', 'fas fa-industry', Aziende::class),
             MenuItem::linkToCrud('Province', 'fas fa-map-marker-alt', Province::class),
             MenuItem::linkToCrud('Aree e zone geografiche', 'fas fa-map-marked-alt', AreeGeografiche::class),
             MenuItem::linkToCrud('Mansioni personale', 'fas fa-id-card-alt', Mansioni::class),
             MenuItem::linkToCrud('Categorie servizi', 'fas fa-dolly', CategorieServizi::class),
             MenuItem::linkToCrud('Causali Paghe', 'fas fa-pencil-ruler', Causali::class),
             MenuItem::linkToCrud('Festività annuali', 'fas fa-plane-departure', FestivitaAnnuali::class),
             MenuItem::linkToCrud('Regole di fatturazione', 'fas fa-wave-square', RegoleFatturazione::class),
            ]) ;
       
        // yield MenuItem::section();
        yield MenuItem::subMenu('Manutenzioni', 'fas fa-tools')->setSubItems([
             MenuItem::linkToCrud('Feedback e segnalazioni', 'fas fa-comments', CommentiPubblici::class),
             MenuItem::linkToCrud('Consolidati cantieri', 'fas fa-calendar-check', ConsolidatiCantieri::class),
             MenuItem::linkToCrud('Consolidati personale', 'fas fa-calendar-alt', ConsolidatiPersonale::class),
             MenuItem::linkToCrud('Documenti Cantieri', 'fas fa-file-alt', DocumentiCantieri::class),
             MenuItem::linkToCrud('Documenti Personale', 'fas fa-file-alt', DocumentiPersonale::class),
            ]) ;
        // yield MenuItem::section();
        // yield MenuItem::linkToLogout('Logout', 'fa fa-exit');

      /*   yield MenuItem::subMenu('Prove', 'fas fa-question')->setSubItems([
              MenuItem::linkToRoute('Export province', 'fas fa-file-excel', 'export')->setLinkTarget("_blank"),
              MenuItem::linkToRoute('Commenti no CRUD', 'fas fa-comments',  'admin_commenti_edit'),
              //->setController(CommentiNoCrudController::class);
              MenuItem::linkToRoute('Province no CRUD', 'fas fa-table', 'admin_province_edit'),
              MenuItem::linkToRoute('Lista output province', 'fas fa-stream', 'output_province'),
              MenuItem::linkToRoute('Esempio chart', 'fas fa-chart-line', 'output_chart'),
             ]) ;  */
       
       //  yield MenuItem::linkToUrl('Link URL Export province', 'fas fa-file-excel', '/admin/excel')->setLinkTarget("_blank");
    }
}

//    
 //  