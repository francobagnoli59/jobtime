<?php

namespace App\EventSubscriber\Dashboard;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Exception\EntityRemoveException;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DashboardExceptionSubscriber implements EventSubscriberInterface {

    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var AdminContextProvider
     */
    private $adminContextProvider;
    /**
     * @var AdminUrlGenerator
     */
    private $adminUrlGenerator;

    public function __construct(SessionInterface $session, AdminContextProvider $adminContextProvider, AdminUrlGenerator $adminUrlGenerator
    ) {
        $this->session = $session;
        $this->adminContextProvider = $adminContextProvider;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getSubscribedEvents() {
        return [ KernelEvents::EXCEPTION => ['onKernelException'] ];
    }

    public function sendFlashPrimary  ($title = "", $message = "") { return $this->sendFlash("primary",   $title, $message); }
    public function sendFlashSecondary($title = "", $message = "") { return $this->sendFlash("secondary", $title, $message); }
    public function sendFlashDark     ($title = "", $message = "") { return $this->sendFlash("dark",      $title, $message); }
    public function sendFlashLight    ($title = "", $message = "") { return $this->sendFlash("light",     $title, $message); }
    public function sendFlashSuccess  ($title = "", $message = "") { return $this->sendFlash("success",   $title, $message); }
    public function sendFlashInfo     ($title = "", $message = "") { return $this->sendFlash("info",      $title, $message); }
    public function sendFlashNotice   ($title = "", $message = "") { return $this->sendFlash("notice",    $title, $message); }
    public function sendFlashWarning  ($title = "", $message = "") { return $this->sendFlash("warning",   $title, $message); }
    public function sendFlashDanger   ($title = "", $message = "") { return $this->sendFlash("danger",    $title, $message); }

    public function sendFlash($type, $title = "", $message = "")
    {
        if($title instanceof ExceptionEvent) {

            $event     = $title;
            $exception = $event->getThrowable();
            
            $title   = get_class($exception)."<br/>";
            $title  .= "(".$exception->getFile().":".$exception->getLine().")";
            
            $message = $exception->getMessage();
        }
        // Get back crud information only for diaplay in message
        $crud       = $this->adminContextProvider->getContext()->getCrud();
        if($crud) {
        $controller = $crud->getControllerFqcn();
        $action     = $crud->getCurrentAction();
        $arrEntity = ['Aziende', 'Cantieri', 'Causali', 'Clienti', 'FestivitaAnnuali', 'MesiAziendali', 'OreLavorate', 'Personale', 'Province', 'RegoleFatturazione'];
           }
        if(!empty($title)) $title = "<b>".$title."</b><br/>";
        if(!empty($title.$message))
        if (str_contains($title.$message, 'ForeignKeyConstraintViolationException')) {
            if($crud) {
                foreach ($arrEntity as $entityname) {
                    if (str_contains($title.$message, $entityname)) {
                      $title = "<b>".'Tentativo di eliminare una entità referenziata.'."</b><br/>";
                      $message = 'Richiesta non eseguibile per l\'entità <b>'. $entityname .'</b> in quanto utilizzata in altre entità'.'<br/>'. 'Componente: '. $controller .' '.$action;
                   break;
                    }
                } 
            } else { $title = "<b>".'Tentativo di eliminare una entità referenziata.'."</b><br/>";
                     $message = 'Richiesta non eseguibile per l\'entità confermata in quanto utilizzata in altre entità'.'<br/>';
 
            }
        }
        if (str_contains($title.$message, 'UniqueConstraintViolationException')) {
            $title = "<b>".'Chiave duplicata per l\'entità confermata.'."</b><br/>";
            $pos = stripos($message, '(key_reference)');
            if ($pos) {
                    if($crud) {
                      $message = 'Valore key duplicata: <b>' . substr($message, $pos+18 ).'</b><br/>'. 'Componente: '. $controller .' '.$action;
                    } else {
                      $message = 'Valore key duplicata: <b>' . substr($message, $pos+18 ).'</b><br/>';
                     }
                } 
        }

            $this->session->getFlashBag()->add($type, $title.$message);
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // Check if exception happened in EasyAdmin (avoid warning outside EA)
        if(!$this->adminContextProvider) return;
        if(!$this->adminContextProvider->getContext()) return;

        // Get back exception & send flash message
        $this->sendFlashDanger($event);

        // Get back crud information
        $crud       = $this->adminContextProvider->getContext()->getCrud();
        if(!$crud) {
            $url = $this->adminUrlGenerator->unsetAll(); 
            $event->setResponse(new RedirectResponse($url));

            }  else { 

        $controller = $crud->getControllerFqcn();
        $action     = $crud->getCurrentAction();
        $url = $this->adminUrlGenerator
        ->setController($controller)
        ->setAction('index')
        ->generateUrl();

        $event->setResponse(new RedirectResponse($url));
        }
 

        // Avoid infinite redirection
        // - If exception happened in "index", redirect to dashboard
        // - If exception happened in an other section, redirect to index page first
        // - If exception happened after submitting a form, just redirect to the initial page

        /* $url = $this->adminUrlGenerator->unsetAll();
        switch($action) {
            case 'index': break;  
            default:
                $url = $url->setController($controller);
                if(isset($_POST) && !empty($_POST)) $url = $url->setAction($action);
        }

        $event->setResponse(new RedirectResponse($url)); */
    }
}