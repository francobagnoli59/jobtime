<?php

namespace App\Controller;

use App\Entity\Personale;
use App\Entity\ConsolidatiPersonale;
use App\Entity\Aziende;
use App\Entity\Orelavorate;
use App\Entity\MesiAziendali;
use App\Entity\FestivitaAnnuali;

use App\ServicesRoutine\DateUtility;
use App\Controller\Admin\MesiAziendaliCrudController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;


class FlowSalaryExportController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var AdminUrlGenerator
     */
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(EntityManagerInterface $entityManager,  AdminUrlGenerator $adminUrlGenerator ) 
    {
    $this->entityManager = $entityManager;
    $this->adminUrlGenerator = $adminUrlGenerator;
    }


     private function getData(): array
    {
        // dati pianificazione selezionata
        $keyreference =  $this->adminUrlGenerator->get('keyreference');
        $mesiaziendali = $this->entityManager->getRepository(MesiAziendali::class)->findOneByKeyReference($keyreference);
        $azienda = $mesiaziendali->getAzienda();
        $festivitaAnnuale_id = $mesiaziendali->getFestivitaAnnuale();
        $mese = $mesiaziendali->getMese();
        // legge anno dalle festività dell'anno
        $festivita = $this->entityManager->getRepository(FestivitaAnnuali::class)->findOneBy(['id'=> $festivitaAnnuale_id]);
        $anno = $festivita->getAnno();
        // valori iniziali per preparazione periodo mensile (primo e ultimo giorno)
        $dateutility = new DateUtility ;
        $limitiMese = $dateutility->calculateLimitMonth($anno, $mese);
        $dataInizio = $limitiMese[1] ;
        $dataFine = $limitiMese[2] ;
        // codice paghe azienda
        $aziendaRecord = $this->entityManager->getRepository(Aziende::class)->findOneBy(['id'=> $azienda]);
        $codicePaghe = $aziendaRecord->getCodeTransferPaghe();

        // contatori e array flusso
        $countTotalPersone = $mesiaziendali->getNumeroPersone();
        $countConfirmedPersone = 0;
        $countTransfer = 0;
        $list = [];
        // entra nel ciclo solo se ci sono orari confermati nel mese selezionato
        if ( $this->entityManager->getRepository(OreLavorate::class)->countConfirmed($azienda, true, $dataInizio, $dataFine) > 0 )
        { 
             // ciclo sul personale dell'azienda
             $personaledataset = $this->entityManager->getRepository(Personale::class)->findBy(['azienda'=> $azienda]);
             foreach ($personaledataset as $persona) {
                   // personale assunto
                   $valido = false ;  
                   if ( $persona->getIsEnforce() === true ) {
                     // solo personale che è già stato consolidato nel mese 
                     $keyRef_Pers_MeseAz = sprintf("%010d-%010d", $persona->getId(), $mesiaziendali->getId()); 
                     if ( $this->entityManager->getRepository(ConsolidatiPersonale::class)->findOneByKeyReference($keyRef_Pers_MeseAz) !== null ) {
                        
                        $matricola = $persona->getMatricola();

                        // calcola ore giornaliere teoriche      
                        $oreweek = $persona->getTotalHourWeek();
                        $hourdayarray = $persona->getPlanHourWeek();
                        $totdays = 0;
                        foreach ($hourdayarray as $d) {
                            if (is_numeric($d) && $d > 0 ) {
                                $totdays++ ;
                            }
                        }
                        $oremedieday = round(floatval($oreweek / $totdays),2);
                        $oreggteoriche = sprintf('%04d', $oremedieday*100 );
                        
                        // Ore lavorate nel mese tutte confermate
                        $count = $this->entityManager->getRepository(OreLavorate::class)->countPersonaConfirmed($persona, true , $dataInizio, $dataFine);
                        if ($count > 0) {
                            $count = $this->entityManager->getRepository(OreLavorate::class)->countPersonaConfirmed($persona, false , $dataInizio, $dataFine);
                            if ($count === 0) {
                                $countConfirmedPersone++ ;
                                // Seleziona le ore lavorate e confermate del mese
                                $oreLavorateCollection = $this->entityManager->getRepository(OreLavorate::class)->collectionPersonaConfirmed($persona, true , $dataInizio, $dataFine);
                                // ciclo sulla collection delle ore lavorate
                                $causaleRottura = ''; $dataRottura =''; $ore = 0;
                                foreach ($oreLavorateCollection as $ol ){
                                  if (($ol->getOreRegistrate()*100) > 0 ) {
                                    $causale = $ol->getCausale()->getCode();
                                    $lc = strlen($causale);  $causaleFill = (5 - $lc);
                                    $giorno = $ol->getGiorno()->format('d/m/Y');
                                    if ($causaleRottura === '') {
                                        // primo ciclo assegna rotture
                                        $causaleRottura = $causale; $dataRottura = $giorno ;
                                        // prepara prima parte della linea
                                        $linea = $this->prepareLine($codicePaghe, $matricola, $causale, $causaleFill, $giorno);
                                        // assegna ore
                                        $ore = $ol->getOreRegistrate()*100;
                                    } else {
                                        // verifica se siamo a rottura di causale o data
                                        if ($causaleRottura === $causale && $dataRottura === $giorno) {
                                            // stessa causale e stessa data
                                            $ore += $ol->getOreRegistrate()*100;
                                        } else {
                                            // siamo a rottura di causale o di giorno, completa la linea
                                            $oreReg = sprintf('%010d', $ore);
                                            $linea = $linea.'H0000000000'.$oreReg.'0000000000'.$oreggteoriche.'G '; // da valutare come aggungere S come inizio malattia
                                            $list[] = $linea ;
                                            // prepara dati linea successiva
                                            $causaleRottura = $causale; $dataRottura = $giorno ;
                                            // prepara prima parte della linea
                                            $linea = $this->prepareLine($codicePaghe, $matricola, $causale, $causaleFill, $giorno);
                                            // assegna ore
                                            $ore = $ol->getOreRegistrate()*100;
                                        }
                                    }
                                  } // ore lavorate > 0
                                } // ciclo ore lavorate
                                // completa linea ultimo ciclo
                                $oreReg = sprintf('%010d', $ore);
                                $linea = $linea.'H0000000000'.$oreReg.'0000000000'.$oreggteoriche.'G '; // da valutare come aggungere S come inizio malattia
                                $list[] = $linea ;
                                // set Transfer a true per persona, filtra i record con isTransfer = false
                                $itemTransfer = $this->entityManager->getRepository(OreLavorate::class)->setMonthPersonaTransfer($persona, false , $dataInizio, $dataFine);
                                $countTransfer += $itemTransfer;

                             } // nessuna ora da confermare
                        } // ore confermate
                    } // persona assunta
                } // personale assunto
            } // ciclo persone

        }
        // indica il numero delle persone trattate
        if ($countTransfer > 0 ) {
        $this->addflash('success', sprintf('Sono stati elaborati numero %d orari relativi a %d persone',  $countTransfer, $countConfirmedPersone))  ;
         } else { 
            $this->addflash('info', sprintf('Orari relativi a %d persone già trattati in una precedente elaborazione, file prodotto come in precedenza.',  $countConfirmedPersone))  ;
         }
        return $list;
    }

    private function prepareLine($codicePaghe, $matricola, $causale, $causaleFill, $giorno): string
    {
        $linestandard = '00000'.$codicePaghe.'0000'.$matricola.$causale;
        switch ($causaleFill) {
            case 1:
                $linestandard = $linestandard." ";
                break;
            case 2:
                $linestandard = $linestandard."  ";
                break;
            case 3:
                $linestandard = $linestandard."   ";
                break;
            case 4:
                $linestandard = $linestandard."    ";
                break;
            }
            $linestandard = $linestandard.$giorno;
        return $linestandard;
    }

    /**
     * @Route("/admin/exportflowsalary",  name="exportflowsalary")
     */
    public function exportflowsalary()
    {
        // $value = json_encode($this->getData());
        
        $value = '';
        foreach ($this->getData() as $row) {
            $value = $value.$row."\r\n" ;
        }

        $filesystem = new Filesystem();
        $filename = $this->adminUrlGenerator->get('aziendamese');
        $pathfile = 'downloads/flowsalary/'.'Timbrature_'.$filename.'.txt';
        $filesystem->dumpFile($pathfile,  $value);
        $link = '<a href="'.$pathfile.'" download> Clicca qui per scaricarlo</a>';

        $this->addflash('info', 'File generato '.$link)  ;

        $url = $this->adminUrlGenerator->unsetAll()
            ->setController(MesiAziendaliCrudController::class)
            ->setAction(Action::INDEX);
            return $this->redirect($url);
    }
}