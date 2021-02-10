<?php

namespace App\Controller;

use App\Entity\MesiAziendali;
use App\Entity\FestivitaAnnuali;
use App\Entity\Aziende;
use App\Entity\Personale;
use App\Entity\Causali;
use App\Entity\Orelavorate;
use App\Form\MesiAziendaliType;
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
    public function newMese(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $mesiaziendali = new MesiAziendali();
        $mesiaziendali->setIsHoursCompleted(false);
        $mesiaziendali->setIsInvoicesCompleted(false);
        $mesiaziendali->setCostMonthHuman('0')->setCostMonthMaterial('0')->setIncomeMonth('0');
                     
        $form = $this->createForm(MesiAziendaliType::class, $mesiaziendali);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($mesiaziendali);
            $entityManager->flush();
            $this->addFlash('success',  'Mensilità richiesta inserita nel consolidato mensile ');

            // dati pianificazione appena inserita
            $azienda_id = $mesiaziendali->getAzienda();
            $festivitaAnnuale_id = $mesiaziendali->getFestivitaAnnuale();
            $mese = $mesiaziendali->getMese();
            // legge le festività dell'anno
            $festivita = $entityManager->getRepository(FestivitaAnnuali::class)->findOneBy(['id'=> $festivitaAnnuale_id]);
            $anno = $festivita->getAnno();
            $arrayFestivita = $festivita->getDateFestivita();
            // Costruisce date festività
            $dateFeste = [];
            foreach ($arrayFestivita as $ar) {
                $gg = substr($ar,0,2);
                $mm = substr($ar,2,2);
                $dateholiday = mktime(0,0,0,$mm,$gg,$anno);
                $dateFeste[] = $dateholiday;
            }
           
            // valori iniziali per preparazione mese
            $finemese = 31;
            if ($mese === '04' || $mese === '06' || $mese === '09' || $mese === '11') {
              $finemese = 30;
            }
            if ($mese === '02' ) {
                $finemese = 28;
                if ($anno === '2024' || $anno === '2028' || $anno === '2032' || $anno === '2036' || $anno === '2040' || $anno === '2044' || $anno === '2048') {
                    $finemese = 29;
                }
            }
           
            // per ogni dipendente dell'azienda richiesta inserisce le ore pianificate nel mese impostato
            $count = 0;
            $personaledataset = $entityManager->getRepository(Personale::class)->findBy(['azienda'=> $azienda_id]);
                foreach ($personaledataset as $personale) {
                  if ( $personale->getIsEnforce() === true && $personale->getCantiere() != null) {
                    for ($i = 1; $i <= $finemese ; $i++) {
                      $orelavorate = new Orelavorate();
                      $orelavorate->setAzienda($azienda_id);
                      $orelavorate->setCantiere($personale->getCantiere());
                      $orelavorate->setPersona($entityManager->getRepository(Personale::class)->findOneBy(['id' => $personale->getId()]));
                      $orelavorate->setIsConfirmed(false);
                      $planweek = $personale->getPlanHourWeek();
                      $giorno = mktime(0,0,0,$mese,$i,$anno);
                      $num_gg=(int)date("N",$giorno);//1 (for Monday) through 7 (for Sunday)
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
                   }
                  }
            }


            $this->addFlash('success', sprintf('Sono stati inseriti %d item di pianificazione ore mensili relative all\'azienda richiesta.' , $count ));
                    
           // $url = $this->adminUrlGenerator->unsetAll();   senza altri metodi ritorna all'index del dashboard
            $url = $this->adminUrlGenerator->unsetAll()
           ->setController('App\Controller\Admin\OreLavorateCrudController')
           ->setAction('index')
           ->generateUrl();

        return new RedirectResponse($url);

        }
        
        return $this->render('admin/mesiaziendali/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /* private function giornodellasettimana($d){
 
        //attendo la data deve essere nel formato yyyy-mm-gg stringa
        $d_ex=explode("-", $d);//separatore (-)
        $d_ts=mktime(0,0,0,$d_ex[1],$d_ex[2],$d_ex[0]);
        $num_gg=(int)date("N",$d_ts);//1 (for Monday) through 7 (for Sunday)
        return $num_gg ;
        //per nomi in italiano
        //$giorno=array('','lunedì','martedì','mercoledì','giovedì','venerdì','sabato','domenica');//0 vuoto
        //return $giorno[$num_gg];
    }
 */

}