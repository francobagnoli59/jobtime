<?php

namespace App\Controller\Admin;

use App\Entity\Cantieri;
use App\Entity\CommentiPubblici;
use App\Entity\Province;
use App\Entity\RegoleFatturazione;
use App\Entity\Personale;
use App\Entity\Aziende;
use App\Entity\Clienti;
use App\Entity\Attrezzature;
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
// use App\Entity\User;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;

// use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;


class DashboardController extends AbstractDashboardController
{
    /**
     * @var AdminUrlGenerator
     */
    private AdminUrlGenerator $adminUrlGenerator;
    // private $entityManager; , EntityManagerInterface $entityManager

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
    $this->adminUrlGenerator = $adminUrlGenerator;
    // $this->entityManager = $entityManager;
    }


    /**
     * @Route("/admin",  name="admin" )
     */
    public function index(): Response
    {
        
        // redirect to some CRUD controller
        $routeBuilder = $this->get(AdminUrlGenerator::class);
     
       // $Utente= $this->entityManager->getRepository(User::class)->findOneBy(['email'=> get_current_user()]);
        $azienda = $this->getUser()->getAziendadefault();
       // return $this->redirect($routeBuilder->setController(PersonaleMainChartController::class)->generateUrl());
        if ($azienda !== null) {
            return $this->redirect($routeBuilder->setController(CantieriCrudController::class)->generateUrl());
        } else {
            return $this->redirect($routeBuilder->setController(SceltaAziendaCrudController::class)->generateUrl());
        }
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
    }  

    public function getAziendaSelection(AdminContext $context)
    {
        // ...
    }
*/



    public function configureDashboard(): Dashboard
    {
    /*     $context = $this->adminContextProvider->getContext();
        if ($context !== null) {
            $azienda_nickName = $this->adminUrlGenerator->get('azienda_selection_nickName');
             $title = 'Jobtime attivato per '. $azienda_nickName ; }
         else {  $title = 'Scegli un\'azienda del gruppo'; } */
        $title = 'Jobtime' ;
        return Dashboard::new()
            ->setTitle('<img src="/images/logo-alioth-group-ridotto.png"/> '.$title.'<span class="text-small"></span>')
            ->renderContentMaximized();
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
        // yield MenuItem::linkToDashboard('Home', 'fa fa-home');
       
        $azienda = $this->getUser()->getAziendadefault();  
        // $azienda_nickName = $this->adminUrlGenerator->get('azienda_selection_nickName');
        if ($azienda !== null ) {
            $azienda_nickName = $azienda->getNickName(); $home = 'Azienda: '. $azienda_nickName ;
            yield MenuItem::linkToDashboard($home, 'fa fa-home')->setCssClass('list-group-item-light');
            // yield MenuItem::section('Azienda: '. $azienda_nickName)->setCssClass('list-group-item-light');
            $labelAz = 'Cambia Azienda';
         } else {
            yield MenuItem::linkToDashboard('Scegli un\'Azienda ', 'fa fa-home')->setCssClass('list-group-item-danger'); 
            // yield MenuItem::section('Scegli un\'Azienda ')->setCssClass('list-group-item-danger');
            $labelAz = 'Seleziona Azienda';
         }
         yield MenuItem::linkToCrud($labelAz, 'fas fa-industry', Aziende::class)
         ->setController(SceltaAziendaCrudController::class);
         
         if ($azienda !== null) {
        yield MenuItem::linkToRoute('Dashboard Personale', 'fas fa-chart-pie', 'main_personale_chart');
        yield MenuItem::linkToRoute('Planning Cantieri', 'fas fa-stream', 'main_cantieri_planchart');
        yield MenuItem::linkToRoute('Analisi Cantieri', 'fas fa-chart-bar', 'main_cantieri_barchart');
        yield MenuItem::linktoRoute('Portale Feedback', 'fas fa-comment-alt', 'homepage');
         } 
                
        yield MenuItem::section('Inserimento orari di lavoro')->setCssClass('list-group-item-dark');
        yield MenuItem::linkToRoute('Prepara mensilità', 'fas fa-calendar', 'planning_month');
        yield MenuItem::linkToCrud('Ore lavorate', 'fas fa-clock',  OreLavorate::class);
        yield MenuItem::linkToCrud('Elabora mensilità', 'fas fa-calendar-day', MesiAziendali::class);
       /*  yield MenuItem::section();
        yield MenuItem::linkToRoute('Aggiorna ore mese', 'fas fa-clock', 'person_hour_month', ['persona' => '1', 'anno' => '2021', 'mese' => '01']); */
        yield MenuItem::section(); 
        yield MenuItem::section('Anagrafiche')->setCssClass('list-group-item-dark');
        yield MenuItem::linkToCrud('Cantieri', 'fas fa-building', Cantieri::class);
        yield MenuItem::linkToCrud('Personale', 'fas fa-address-card', Personale::class)
        ->setController(PersonaleCrudController::class);
        // ->setQueryParameter('filters[isEnforce][value]', 1)
        yield MenuItem::linkToCrud('Clienti', 'fas fa-users', Clienti::class);
        yield MenuItem::linkToCrud('Attrezzature', 'fas fa-blender', Attrezzature::class);
        yield MenuItem::subMenu('Import anagrafiche', 'fas fa-upload')->setSubItems([
            MenuItem::linkToCrud('Import cantieri', 'fas fa-file-excel', ImportCantieri::class),
            MenuItem::linkToCrud('Import personale', 'fas fa-file-excel', ImportPersonale::class),
            ]) ;

       // 
        //yield MenuItem::linkToRoute('Analisi Cantiere', 'fas fa-chart-bar', 'cantiere_chart');

        yield MenuItem::section();
        yield MenuItem::subMenu('Configurazioni', 'fas fa-cogs ')->setSubItems([
             MenuItem::linkToCrud('Aziende', 'fas fa-industry', Aziende::class)
             ->setController(AziendeCrudController::class)->setPermission('ROLE_ADMIN'),
             MenuItem::linkToCrud('Province', 'fas fa-map-marker-alt', Province::class),
             MenuItem::linkToCrud('Aree e zone geografiche', 'fas fa-map-marked-alt', AreeGeografiche::class),
             MenuItem::linkToCrud('Mansioni personale', 'fas fa-id-card-alt', Mansioni::class),
             MenuItem::linkToCrud('Categorie servizi', 'fas fa-dolly', CategorieServizi::class),
             MenuItem::linkToCrud('Causali Paghe', 'fas fa-pencil-ruler', Causali::class),
             MenuItem::linkToCrud('Festività annuali', 'fas fa-plane-departure', FestivitaAnnuali::class),
             MenuItem::linkToCrud('Regole di fatturazione', 'fas fa-wave-square', RegoleFatturazione::class),
            ]) ;
       
        // yield MenuItem::section();
        yield MenuItem::subMenu('Recovery', 'fas fa-tools')->setSubItems([
             MenuItem::linkToCrud('Feedback', 'fas fa-comments', CommentiPubblici::class),
             MenuItem::linkToCrud('Consolidati cantieri', 'fas fa-calendar-check', ConsolidatiCantieri::class),
             MenuItem::linkToCrud('Consolidati personale', 'fas fa-calendar-alt', ConsolidatiPersonale::class),
             MenuItem::linkToCrud('Documenti Cantieri', 'fas fa-file-alt', DocumentiCantieri::class),
             MenuItem::linkToCrud('Documenti Personale', 'fas fa-file-alt', DocumentiPersonale::class),
            ]) ;
        // yield MenuItem::section();
        // yield MenuItem::linkToLogout('Logout', 'fa fa-exit');

        yield MenuItem::subMenu('Storico', 'fas fa-history')->setSubItems([
            MenuItem::linkToCrud('Personale licenziato', 'fas fa-address-book', Personale::class)
            ->setController(PersonaleDimessoCrudController::class),
           // ->setQueryParameter('filters[isEnforce][value]', 0),
           ]) ;
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