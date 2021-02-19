<?php

namespace App\Controller;

use App\Entity\MesiAziendali;
use App\Repository\MesiAziendaliRepository;
use App\Entity\FestivitaAnnuali;
use App\Entity\Aziende;
use App\Entity\Personale;
use App\Entity\Causali;
use App\Entity\Orelavorate;
use App\Form\MesiAziendaliType;
use App\ServicesRoutine\DateUtility;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;


class PreparaMensilitaController extends AbstractController
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
     * @Route("/newMese/meseaziendale", methods="GET|POST", name="planning_month")
     */
    public function newMese(Request $request,  MesiAziendaliRepository $mesiAziendaliRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $mesiaziendali = new MesiAziendali();
        $mesiaziendali->setIsHoursCompleted(false);
        $mesiaziendali->setIsInvoicesCompleted(false);
        $mesiaziendali->setCostMonthHuman(0)->setCostMonthMaterial(0)->setIncomeMonth(0)->setNumeroPersone(0)->setNumeroCantieri(0)
        ->setOreLavoro(0)->setOrePianificate(0)->setOreStraordinario(0)->setOreImproduttive(0)->setOreIninfluenti(0);
                     
        $form = $this->createForm(MesiAziendaliType::class, $mesiaziendali);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($mesiaziendali);
            $entityManager->flush();
            $this->addFlash('success',  'Mensilità richiesta inserita nel consolidato mensile ');
          
            // dati pianificazione appena inserita
            $azienda_id = $mesiaziendali->getAzienda()->getId();
            $azienda = $mesiaziendali->getAzienda();
            $festivitaAnnuale_id = $mesiaziendali->getFestivitaAnnuale();
            $mese = $mesiaziendali->getMese();
           
            // legge le festività dell'anno
            $festivita = $entityManager->getRepository(FestivitaAnnuali::class)->findOneBy(['id'=> $festivitaAnnuale_id]);
            $anno = $festivita->getAnno();

            // costruisce keyreference mese anzindale appena inserito
            $keyReference = sprintf("%010d-%s-%s", $azienda_id, $anno, $mese);

            $arrayFestivita = $festivita->getDateFestivita();
            // Costruisce date festività
            $dateFeste = [];
            foreach ($arrayFestivita as $ar) {
                $gg = substr($ar,0,2);
                $mm = substr($ar,2,2);
                $dateholiday = mktime(0,0,0,$mm,$gg,$anno);
                $dateFeste[] = $dateholiday;
            }
           
            // valori iniziali per preparazione fine mese
            $dateutility = new DateUtility ;
            $limitiMese = $dateutility->calculateLimitMonth($anno, $mese);
            $finemese = $limitiMese[0] ;
                     
            // per ogni dipendente dell'azienda richiesta inserisce le ore pianificate nel mese impostato
            $count = 0;   // contatore items
            $countPersone = 0; // contatore persone
            $arrayCantieri = []; // utilizzato per contare i cantieri
            $personaledataset = $entityManager->getRepository(Personale::class)->findBy(['azienda'=> $azienda]);
            foreach ($personaledataset as $personale) {
                   // personale con flag assunto su true  
                  if ( $personale->getIsEnforce() === true ) {
                    // ciclo mese
                    $countPersone++ ;
                    for ($i = 1; $i <= $finemese ; $i++) {   
                        $giorno = mktime(0,0,0,$mese,$i,$anno);
                        $num_gg=(int)date("N",$giorno);//1 (for Monday) through 7 (for Sunday)

                        // ciclo su planning settimanale essendo assegnato ad unico cantiere
                        if ( $personale->getCantiere() !== null ) {
                        $orelavorate = new Orelavorate();
                        $orelavorate->setAzienda($azienda);
                        $orelavorate->setCantiere($personale->getCantiere());
                        $orelavorate->setPersona($entityManager->getRepository(Personale::class)->findOneBy(['id' => $personale->getId()]));
                        $orelavorate->setIsConfirmed(false);
                        $orelavorate->setIsTransfer(false);
                        
                        // aggiunge id cantiere
                        array_push($arrayCantieri, $personale->getCantiere()->getId());
                       
                        // planning settimana sulla persona    
                        $planweek = $personale->getPlanHourWeek();
                            if ( $planweek[$num_gg - 1] != null && $planweek[$num_gg - 1] != '0') { 
                                $keyCausale = 'ORDI';
                                if ($num_gg <= 6 ) { 
                                    if (in_array($giorno, $dateFeste)) { $keyCausale = '*EF';  $orelavorate->setIsConfirmed(true); }
                                } 
                                $orelavorate->setCausale($entityManager->getRepository(Causali::class)->findOneBy(['code' => $keyCausale]));
                                $orelavorate->setOrePianificate($planweek[$num_gg - 1]);
                                $orelavorate->setOreRegistrate($planweek[$num_gg - 1]);
                                $date = new \DateTime();
                                $date->setTime(0,0,0);
                                $date->setDate($anno, $mese, $i);
                                $orelavorate->setGiorno($date);
                                // Verifica data licenziamento 
                                    if ($personale->getDateDismissal() === null || $date <= $personale->getDateDismissal() ) {
                                        // scrive la pianificazione
                                        $entityManager->persist($orelavorate);
                                        $entityManager->flush();
                                        $count++ ;
                                    }             
                            }
                        }  // unico cantiere
                        else {
                        // ciclo su Piani orari per cantieri  
                            if ($personale->getCantiere() === null) {
                                $oreCantieri = $personale->getPianoOreCantieri();
                                foreach ($oreCantieri as $oc) {
                                    if ($oc->getPersona()->getId() === $personale->getId() && $oc->getOrePreviste() > 0 && $oc->getdayOfWeek() === $num_gg) {
                                            
                                       $orelavorate = new Orelavorate();
                                       $orelavorate->setAzienda($azienda);
                                       $orelavorate->setCantiere($oc->getCantiere());
                                       $orelavorate->setPersona($oc->getPersona());
                                       $orelavorate->setIsConfirmed(false);
                                       $orelavorate->setIsTransfer(false);
       
                                       // aggiunge id cantiere
                                       array_push($arrayCantieri, $oc->getCantiere()->getId());

                                       $keyCausale = 'ORDI';
                                       if ($num_gg <= 6 ) { 
                                           if (in_array($giorno, $dateFeste)) { $keyCausale = '*EF';  $orelavorate->setIsConfirmed(true); }
                                       } 
                                       $orelavorate->setCausale($entityManager->getRepository(Causali::class)->findOneBy(['code' => $keyCausale]));
                                       $orelavorate->setOrePianificate($oc->getOrePreviste());
                                       $orelavorate->setOreRegistrate($oc->getOrePreviste());
                                       $date = new \DateTime();
                                       $date->setTime(0,0,0);
                                       $date->setDate($anno, $mese, $i);
                                       $orelavorate->setGiorno($date);
                                       // Verifica data licenziamento 
                                           if ($personale->getDateDismissal() === null || $date <= $personale->getDateDismissal() ) {
                                               // scrive la pianificazione
                                               $entityManager->persist($orelavorate);
                                               $entityManager->flush();
                                               $count++ ;
                                           }             
       
                                       } // stessa persona, ore Previste e stesso giorno della settimana
                                    } // ciclo su collection PianoOreCantieri

                            }  // Piani orari per cantieri
                        }

                    }   // ciclo mese
                  }   // enforce = true
       
            } // ciclo sul personale dell'azienda

            // Aggiorna numero persone / cantieri
            $mesiaziendali = $mesiAziendaliRepository->findOneByKeyReference($keyReference);
            $mesiaziendali->setNumeroPersone($countPersone)->setNumeroCantieri(count(array_unique($arrayCantieri)));
            $entityManager->persist($mesiaziendali);
            $entityManager->flush();

            $this->addFlash('success', sprintf('Sono stati inseriti %d item di pianificazione ore mensili relative all\'azienda richiesta.' , $count ));
                    
           // $url = $this->adminUrlGenerator->unsetAll();   senza altri metodi ritorna all'index del dashboard
            $url = $this->adminUrlGenerator->unsetAll()
           ->setController('App\Controller\Admin\OreLavorateCrudController')
           ->setAction('index')
           ->generateUrl();

        return new RedirectResponse($url);

        } // premuto submit
        
        return $this->render('admin/mesiaziendali/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /* private function addCantieri($id): int 
    {
    }

    private function contaCantieri($id): int {
    }
    */
}