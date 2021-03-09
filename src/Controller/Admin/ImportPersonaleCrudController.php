<?php

namespace App\Controller\Admin;

use App\Entity\ImportPersonale;
use App\Entity\Personale;
use App\Entity\Province;
use App\Entity\Cantieri;
use App\Entity\Mansioni;
use App\Repository\AziendeRepository;
use App\Validator\Routine\CodiceFiscaleValidation;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\Filesystem\Filesystem;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportPersonaleCrudController extends AbstractCrudController
{
    /**
     * @var AdminUrlGenerator
     */
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(EntityManagerInterface $entityManager,  AdminUrlGenerator $adminUrlGenerator ) 
    {
    $this->entityManager = $entityManager;
    $this->adminUrlGenerator = $adminUrlGenerator;

  
    }


    public static function getEntityFqcn(): string
    {
        return ImportPersonale::class;
    }

   // 
  /*  public function createEntity(string $entityFqcn)
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists('/public/uploads/file/personale/import') === false) {
            // la crea 
            $filesystem->mkdir('/public/uploads/file/personale/import');

        }

        $importPersonale = new ImportPersonale();
        $importPersonale->setNota('...motivo ?');
        return $importPersonale;
    }
 */
    public function importFromExcel(AdminContext $context)
    {

        $importPersonale = $context->getEntity()->getInstance();

        $filesystem = new Filesystem();
        // dati import 
         $azienda = $importPersonale->getAzienda();
         $path = $importPersonale->getPathImport();
         $pathfile = 'uploads/files/personale/import/'.$path;
         if ($filesystem->exists($pathfile)) {
            // apre e carica il file di excel
            $reader = new Xlsx();
            $spreadsheet = $reader->load($pathfile);
            
            // cerca la cartella Dati
            if ($spreadsheet->getSheetByName('Dati')) {
                // Array di controllo
                $colExcel = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
                $colDatiObbl = ['Cognome' => 'N' ,'Nome' => 'N' , 'CodiceFiscale' => 'N' , 'Indirizzo' => 'N' , 'CAP' => 'N' , 'Città' => 'N',
                 'Provincia' => 'N' , 'Matricola' => 'N' , 'DataAssunzione' => 'N', 'Contratto' => 'N', 'DataScadenzaContratto' => 'N',
                 'CostoOrario' => 'N' , 'CostoStraordinario' => 'N' , 'PlanningSettimanale' => 'N', 'Socio' => 'N', 'DivAb' => 'N', 'Mansione' => 'N' ];
                $colDatiFacol = ['Livello', 'Cantiere', 'E-mail', 'Cellulare', 'Telefono','ContoIban',  'DataUltimaVisitaMedica', 'DataScadenzaVisitaMedica'];
                $colDatiAssoc = ['Cognome' => '@' ,'Nome' => '@' , 'CodiceFiscale' => '@' , 'Indirizzo' => '@' , 'CAP' => '@' , 
                 'Città' => '@' , 'Provincia' => '@' , 'Matricola' => '@' ,  'DataAssunzione' => '@', 'Contratto' => '@', 'DataScadenzaContratto' => '@',
                 'CostoOrario' => '@' , 'CostoStraordinario' => '@' , 'PlanningSettimanale' => '@', 'Socio' => '@', 'DivAb' => '@', 'Mansione' => '@',
                 'Livello' => '@' , 'Cantiere' => '@' , 'E-mail' => '@' , 'Cellulare' => '@' , 'Telefono' => '@' ,  'ContoIban' => '@', 'DataUltimaVisitaMedica' => '@' , 'DataScadenzaVisitaMedica' => '@'  ];
                $commentiImport = []; // commenti/messaggi di errore relativi all'import
                // cerca sulla prima riga le colonne progettate e crea Array $colDatiAssoc con posizione colonna
                $workSheet = $spreadsheet->getActiveSheet();
                // ciclo colonne obbligatorie
                foreach ($colExcel as $col) {
                    $cellValue = $workSheet->getCell($col.'1')->getValue();  // contenuto cella
                    $cellValue = trim($cellValue);
                    $arrayDatiObbl = array_keys($colDatiObbl);
                    foreach ($arrayDatiObbl as $key) {
                        if ( $cellValue === $key) {
                            // assegna colonna al dato obbligatorio
                            $colDatiAssoc[$key] = $col;
                            // imposta Y come conferma alla presenza della colonna
                            $colDatiObbl[$key] = 'Y';
                        }
                    } // ciclo array key colonne obbl
                } // ciclo su prima riga Colonne obbligatorie
                // ciclo colonne facoltative
                foreach ($colExcel as $col) {
                    $cellValue = $workSheet->getCell($col.'1')->getValue();  // contenuto cella
                    $cellValue = trim($cellValue);
                    foreach ($colDatiFacol as $key) {
                        if ( $cellValue === $key) {
                            // assegna colonna al dato facoltativo
                            $colDatiAssoc[$key] = $col;
                       }
                    } // ciclo array key colonne facoltative
                } // ciclo su prima riga Colonne facoltative

                $colonneValide = true;
                // se una colonna obbligatoria non c'è si ferma
                foreach ($arrayDatiObbl as $key) {
                    if ( $colDatiObbl[$key] === 'N') {
                        $this->addflash('danger', sprintf('Colonna obbligatoria %s NON TROVATA ', $key ));
                        $colonneValide = false;
                    }
                  }

                if ($colonneValide === true) {
                // scorre le righe dei dati e le colonne identificate da colDatiAssoc. Si ferma nella lettura 
                // appena trova nella cella di colonna A un valore uguale a 'ZZZ' - Se cella di colonna A è vuoto passa alla riga successiva 
                $rowFound = 0;
                $rowInsert = 0;
                $rowExist = 0;
                $keyDatiAssoc = array_keys($colDatiAssoc);

                // ciclo di CONTROLLO comunque limitato a 500 righe 
                for ($row = 2; $row < 501; $row++) {
                     // determina cella colonna A per capire se uscire dal ciclo
                     $cellValue = $workSheet->getCell('A'.sprintf('%d',$row) )->getValue();
                     $cellValue = trim($cellValue);  
                     if ($cellValue === "ZZZ" ) { $row = 501 ; }  // fine ciclo, cella A(n) ===  ZZZ }
                      else {  // riga da leggere
                       if ($cellValue !== null && $cellValue !== "" ) {  
                        $rowFound ++;
                        // Ciclo sulle colonne
                        foreach ($keyDatiAssoc as $key) {
                        // determina cella secondo la key
                        $cellValue = $workSheet->getCell($colDatiAssoc[$key].sprintf('%d',$row) )->getValue();  
                        // controlli specifici per tipo di colonna
                        $cellValue = trim($cellValue); 
                        switch ($key){
                            case "Cognome":
                            case "Nome":
                            case "Indirizzo":
                            case "Città":
                                if ($cellValue === null || $cellValue === '' ) {  $commentiImport[] = sprintf('Riga: %d , Colonna: %s - dato nullo o inesistente, invece è obbligatorio', $row, $key) ;}
                                break;
                            case "CAP":
                                $testCap = $this->controlCAP($cellValue, $row);
                                if ($testCap[0] !== 'OK') { $commentiImport[] = $testCap[1] ;}
                                break;
                            case "CodiceFiscale":
                                $testCF = $this->controlFiscalCode($cellValue, $row);
                                if ($testCF[0] !== 'OK') { $commentiImport[] = $testCF[1] ;}
                                break;
                            case "Provincia":
                                $testPR = $this->controlProvincia($cellValue, $row);
                                if ($testPR[0] !== 'OK') { $commentiImport[] = $testPR[1] ;}
                                break;
                            case "Matricola":
                                $testMatricola = $this->controlMatricola($cellValue, $row);
                                if ($testMatricola[0] !== 'OK') { $commentiImport[] = $testMatricola[1] ;}
                                break;
                            case "DataAssunzione":
                                $testAssunzione = $this->controlDataAssunzione($cellValue, $row);
                                if ($testAssunzione[0] !== 'OK') { $commentiImport[] = $testAssunzione[1] ;}
                                break;
                            case "Contratto":
                                $testContratto = $this->controlContratto($cellValue, $row);
                                if ($testContratto[0] !== 'OK') { $commentiImport[] = $testContratto[1] ;}
                                break;
                            case "DataScadenzaContratto":
                                if ($testContratto[0] === 'OK' && ($testContratto[1] === 'D' || $testContratto[0] === 'I')) {
                                    $testScadenzaContratto = $this->controlDataScadenzaContratto($cellValue, $row);
                                    if ($testScadenzaContratto[0] !== 'OK') { $commentiImport[] = $testScadenzaContratto[1] ;}
                                }
                                break;
                            case "Socio":
                                $testSocio = $this->controlSocio($cellValue, $row);
                                if ($testSocio[0] !== 'OK') { $commentiImport[] = $testSocio[1] ;}
                                break;
                            case "DivAb":
                                $testDivAb = $this->controlDivAb($cellValue, $row);
                                if ($testDivAb[0] !== 'OK') { $commentiImport[] = $testDivAb[1] ;}
                                break;
                            case "Mansione":
                                $testMansione = $this->controlMansione($cellValue, $row, $testDivAb[1]);
                                if ($testMansione[0] !== 'OK') { $commentiImport[] = $testMansione[1] ;}
                                break;                               
                            case "CostoOrario":
                            case "CostoStraordinario":
                                $testCostoOra = $this->controlCostoOrario($cellValue, $row, $key);
                                if ($testCostoOra[0] !== 'OK') { $commentiImport[] = $testCostoOra[1] ;}
                                break;
                            case "PlanningSettimanale":
                                $testPlanning = $this->controlPlanningSettimanale($cellValue, $row);
                                if ($testPlanning[0] !== 'OK') { $commentiImport[] = $testPlanning[1] ;}
                                break;
                            case "Cantiere":
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                    $testCantiere = $this->controlCantiere($cellValue, $row);
                                    if ($testCantiere[0] !== 'OK') { $commentiImport[] = $testCantiere[1] ;}
                                    }
                                break;                                                             
                            case "E-mail":
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                $testEmail = $this->controlEmail($cellValue, $row);
                                if ($testEmail[0] !== 'OK') { $commentiImport[] = $testEmail[1] ;}
                                }
                                break;
                            case "Cellulare":
                            case "Telefono" :
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                $testPhone = $this->controlPhone($cellValue, $row, $key);
                                if ($testPhone[0] !== 'OK') { $commentiImport[] = $testPhone[1] ;}
                                }
                                break;
                            case "Livello" :
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                $testLivello = $this->controlLivello($cellValue, $row);
                                if ($testLivello[0] !== 'OK') { $commentiImport[] = $testLivello[1] ;}
                                }
                                break;                           
                            case "ContoIban":
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                $testConto = $this->controlContoIban($cellValue, $row);
                                if ($testConto[0] !== 'OK') { $commentiImport[] = $testConto[1] ;}
                                }
                                break;
                            case "DataUltimaVisitaMedica":
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                    $testUltimaVisita = $this->controlDataUltimaVisitaMedica($cellValue, $row);
                                    if ($testUltimaVisita[0] !== 'OK') { $commentiImport[] = $testUltimaVisita[1] ;}
                                    }
                            break;
                            case "DataScadenzaVisitaMedica":
                                if ($cellValue !== null && $cellValue !== '' && $testUltimaVisita[0] !== 'OK' ) { 
                                        $testScadenzaVisita = $this->controlDataScadenzaVisitaMedica($cellValue, $row, $testUltimaVisita[1],$testUltimaVisita[2] );
                                        if ($testScadenzaVisita[0] !== 'OK') { $commentiImport[] = $testScadenzaVisita[1] ;}
                                    }
                                break;                                
                        }  // end switch
                        } //  ciclo sulle colonne
                     } // ennesima riga della colonna A non vuota ( altrimenti scarta tutta la riga)
                    } // fine ciclo cella A(n) = ZZZ
                } // ciclo di 500 righe dati

                // Se non ci sono errori ripete il ciclo ed importa i dati nella entity personale
                if (count($commentiImport) === 0 ) {

                // ciclo di REGISTRAZIONE 
                for ($row = 2; $row < 501; $row++) {
                     // determina cella colonna A per capire se uscire dal ciclo
                     $cellValue = $workSheet->getCell('A'.sprintf('%d',$row) )->getValue();  
                     if ($cellValue === "ZZZ" ) { $row = 501 ; }  // fine ciclo, cella A(n) ===  ZZZ }
                      else {  // riga da leggere
                       if ($cellValue !== null && $cellValue !== "" ) {  

                        // ASSEGNA specifici valori per tipo di colonna
                        $personale = new Personale();
                        $personale->setAzienda($azienda);
                        $personale->setIsEnforce(true);
                        $personale->setIsReservedVisita(false);

                        // Ciclo sulle colonne
                        foreach ($keyDatiAssoc as $key) {
                        // determina cella secondo la key
                        $cellValue = $workSheet->getCell($colDatiAssoc[$key].sprintf('%d',$row) )->getValue();  
                        $cellValue = trim($cellValue);    
                        switch ($key){
                            case "Cognome":
                                $personale->setSurname($cellValue);
                                break;
                            case "Nome":
                                $personale->setName($cellValue);
                                break;
                            case "Indirizzo":
                                $personale->setAddress($cellValue);
                                break;
                            case "Città":
                                $personale->setCity($cellValue);
                                break;
                            case "CAP":
                                $personale->setZipCode($cellValue);
                                break;
                            case "CodiceFiscale":
                                $personale->setFiscalCode($cellValue);
                                $codicefiscale = new CodiceFiscaleValidation;
                                $codiceFiscaleVerify = $codicefiscale->verifyFiscalCode(strtoupper($cellValue));
                                $personale->setGender($codiceFiscaleVerify['Gender']);
                                $personale->setBirthday($codiceFiscaleVerify['Birthday']);
                                break;
                            case "Provincia":
                                $personale->setProvincia($this->entityManager->getRepository(Province::class)->findOneBy(['code'=> strtoupper($cellValue)]));
                                break;
                            case "Matricola":
                                $personale->setMatricola($cellValue);
                                break;
                            case "DataAssunzione":
                                $date = new \DateTime();
                                $date->setTimestamp(($cellValue-25569)*86400);
                                $personale->setDateHiring($date);  
                                break;
                            case "Contratto":
                                $personale->setTipoContratto($cellValue);
                                break;
                            case "DataScadenzaContratto":
                                if ($personale->getTipoContratto() === 'D' || $personale->getTipoContratto() === 'T' ) {
                                    $date = new \DateTime();
                                    $date->setTimestamp(($cellValue-25569)*86400);
                                    $personale->setScadenzaContratto($date);  
                                }
                                break;
                            case "Livello":
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                    $personale->setLivello($cellValue); }
                                break;
                            case "CostoOrario":
                                $personale->setFullCostHour($cellValue*100);
                                break;
                            case "CostoStraordinario":
                                $personale->setCostoStraordinario($cellValue*100);
                                break;
                            case "PlanningSettimanale":
                                $personale->setPlanHourWeek( explode ("-", $cellValue ));
                                break;
                            case "Socio":
                                if ($cellValue === 1 || $cellValue === '1' || strtoupper($cellValue) === 'TRUE' || strtoupper($cellValue) === 'SI' ) { 
                                $personale->setIsPartner(true);
                                } else {  $personale->setIsPartner(false); }
                                break;
                            case "DivAb":
                                if ($cellValue === 1 || $cellValue === '1' || strtoupper($cellValue) === 'TRUE' || strtoupper($cellValue) === 'SI' ) { 
                                $personale->setIsInvalid(true);
                                } else {  $personale->setIsInvalid(false); }
                                break;
                            case "Mansione":
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                    $personale->addMansione($this->entityManager->getRepository(Mansioni::class)->findOneBy(['mansioneName'=> trim($cellValue)]) ) ;
                                }
                                break;
                            case "Cantiere":
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                $personale->setCantiere($this->entityManager->getRepository(Cantieri::class)->findOneBy(['nameJob'=> strtoupper($cellValue)]));
                                }
                                break;
                            case "E-mail":
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                    $personale->setEmail(trim($cellValue)); }
                                break;
                            case "Cellulare":
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                    $personale->setMobile(trim($cellValue)); }
                                break;
                            case "Telefono" :
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                    $personale->setPhone(trim($cellValue)); }
                                break;
                            case "DataUltimaVisitaMedica":
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                    $date = new \DateTime();
                                    $date->setTimestamp(($cellValue-25569)*86400);
                                    $personale->setUltimaVisitaMedica($date);  }
                                break;
                            case "DataScadenzaVisitaMedica":
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                    $date = new \DateTime();
                                    $date->setTimestamp(($cellValue-25569)*86400);
                                    $personale->setScadenzaVisitaMedica($date);  }
                                    else {
                                        if ($personale->getUltimaVisitaMedica() !== null) {
                                            $date =$personale->getUltimaVisitaMedica();
                                            $date->add(new DateInterval('+364 days'));
                                            $personale->setScadenzaVisitaMedica($date);
                                        } 
                                    }
                                break;
                            case "ContoIban":
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                    $personale->setIbanConto(trim(str_replace(" ", "", $cellValue))); }
                                break;
                            }  // end switch
                        } //  ciclo sulle colonne
                        // verifica se esiste nel db 
                        $keyreference =  sprintf("%010d-%s-%s", $personale->getAzienda()->getId(), $personale->getFiscalCode(), $personale->getMatricola());
                        if ( $this->entityManager->getRepository(Personale::class)->findOneByKeyReference($keyreference) !== null ) {
                            $rowExist ++;
                        } else { 
                        // registra la persona
                        $this->entityManager->persist($personale);
                        $this->entityManager->flush();
                        $rowInsert ++;
                        }
                     } // ennesima riga della colonna A non vuota ( altrimenti scarta tutta la riga)
                    } // fine ciclo cella A(n) = ZZZ
                } // ciclo di 500 righe dati

                    if ($rowExist > 0) {   $this->addflash('warning', sprintf('Il file excel %s contiene %d persone che già esistevano in JobTime, che di conseguenza non sono state inserite, pertanto %d persone sono state importate senza errori.', $pathfile, $rowExist, $rowInsert));
                    }
                     else { 
                    $this->addflash('success', sprintf('Il file excel %s contiene %d persone che sono state importate senza errori', $pathfile, $rowInsert));
                    }
                    $url = $this->adminUrlGenerator->unsetAll()
                    ->setController(PersonaleCrudController::class)
                    ->setAction(Action::INDEX)
                    ->set('filters[azienda][comparison]', '=')
                    ->set('filters[azienda][value]', $azienda->getId());
                    return $this->redirect($url);

                } else {
                    // costruisce file log di errore scaricabile dal link
                    $logerror = '';
                    foreach ($commentiImport as $log) {
                        $logerror =  $logerror.$log."\r\n" ;
                    }
                    $filelogerror = 'downloads/errorImport/'.'Log_Import_Personale_'.$azienda .'.txt';
                    $filesystem->dumpFile($filelogerror,  $logerror);
                    $link = '<a href="'.$filelogerror.'" download> Clicca qui per vedere gli errori</a>';
                    $this->addflash('danger', 'Il file excel è stato scartato.'.$link)  ;

                    }

                } // colonne valide

            } else  {
                $this->addflash('danger', sprintf('Il file excel %s non contiene la cartella Dati', $pathfile));
            }

         } else {
            $this->addflash('danger', sprintf('Non trovato file di import %s ', $pathfile));
         }


         $url = $this->adminUrlGenerator->unsetAll()
            ->setController(ImportPersonaleCrudController::class)
            ->setAction(Action::INDEX);
         return $this->redirect($url);
         
    }    
   
    private function controlCAP($value, $row): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if ($value === null || $value === '' ) { 
            $comment[1] = sprintf('Riga: %d , Colonna: CAP - dato nullo o inesistente, invece è obbligatorio', $row) ;}
             else
             {
                if (strlen($value) !== 5) { 
                    $comment[1] = sprintf('Riga: %d , Colonna: CAP - numero non valido, minore di 5 caratteri', $row) ;
                } else
                { 
                    if (is_numeric($value) ) {
                        $comment[0] = 'OK';
                    }
                    else {  $comment[1] = sprintf('Riga: %d , Colonna: CAP - contiene caratteri non validi', $row) ;}
                }
             }
        return $comment ;
    }

    private function controlFiscalCode($value, $row): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if ($value === null || $value === '' ) { 
            $comment[1] = sprintf('Riga: %d , Colonna: CodiceFiscale - dato nullo o inesistente, invece è obbligatorio', $row) ;}
             else
             {  $codicefiscale = new CodiceFiscaleValidation;
                $value = strtoupper($value); 
                $codiceFiscaleVerify = $codicefiscale->verifyFiscalCode($value);
                if ($codiceFiscaleVerify['Retcode'] === 'ER') {
                    $comment[1] = sprintf('Riga: %d , Colonna: CodiceFiscale - %s ', $row, $codiceFiscaleVerify['Message']) ;}
                else {
                    $comment[0] = 'OK';
                }
             }
        return $comment ;
    }

    private function controlProvincia($value, $row): array
    {
        $comment = [];
        $comment[0] = 'ER';
        $value = strtoupper($value); 
        if ($value === null || $value === '' ) { 
            $comment[1] = sprintf('Riga: %d , Colonna: Provincia - dato nullo o inesistente, invece è obbligatorio', $row) ;}
             else
             {
                if (strlen($value) !== 2) { 
                    $comment[1] = sprintf('Riga: %d , Colonna: Provincia - indicare la sigla di due caratteri ', $row) ;
                } else
                { 
                    if ( $this->entityManager->getRepository(Province::class)->findOneBy(['code'=> $value]) ) {
                        $comment[0] = 'OK';
                    }
                    else {  $comment[1] = sprintf('Riga: %d , Colonna: Provincia - Sigla non presente nella tabella di configurazione Province', $row) ;}
                }
             }
        return $comment ;
    }


    private function controlMatricola($value, $row): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if ($value === null || $value === '' ) { 
            $comment[1] = sprintf('Riga: %d , Colonna: Matricola - dato nullo o inesistente, invece è obbligatorio', $row) ;}
             else
             {
                if (is_numeric($value) ) {
                    if (strlen($value) > 6) { 
                        $comment[1] = sprintf('Riga: %d , Colonna: Matricola - numero non valido, maggiore di 999999', $row) ;
                    } else
                      {   $comment[0] = 'OK'; }
                } else { $comment[1] = sprintf('Riga: %d , Colonna: Matricola - sono ammessi solo numeri', $row) ; }
             }
        return $comment ;
    }

    private function controlCostoOrario($value, $row, $key): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if ($value === null || $value === '' ) { 
            $comment[1] = sprintf('Riga: %d , Colonna: %s - dato nullo o inesistente, invece è obbligatorio', $row, $key ) ;}
             else
             {
                if (is_numeric( $value)) {
                    $comment[0] = 'OK';
                } else
                {   $comment[1] = sprintf('Riga: %d , Colonna: %s - valore non valido', $row, $key ) ;
                   }
             }
        return $comment ;
    }

    private function controlPlanningSettimanale($value, $row): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if ($value === null || $value === '' ) { 
            $comment[1] = sprintf('Riga: %d , Colonna: PlanningSettimanale - dato nullo o inesistente, invece è obbligatorio', $row) ;}
             else
             {
                $planning = explode ("-", $value ); 
                if (count($planning) !== 7) { 
                    $comment[1] = sprintf('Riga: %d , Colonna: PlanningSettimanale - numero di orari/giorni errati, devono essere 7 numeri separati dal simbolo (-)', $row) ;
                } else
                {   
                    foreach ($planning as $hday) {
                        if (is_numeric( $hday)) {
                            $comment[0] = 'OK';
                        } else
                        {   $comment[1] = sprintf('Riga: %d , Colonna: PlanningSettimanale - orario %s errato', $row, $hday) ;
                            break;
                        }
                    }
                }
             }
        return $comment ;
    }

    private function controlEmail($value, $row): array
    {
        $comment = [];
        $comment[0] = 'ER';
        // cerca una @ e dopo almenu un punto
        if ( (substr_count($value, "@")  === 1)  && (substr_count( substr( $value, strpos($value, "@")+1 ), ".") >= 1) ) {
            $comment[0] = 'OK';
        }
        else {  $comment[1] = sprintf('Riga: %d , Colonna: E-mail - email non valida, deve contenere solo un simbolo (@) e almeno un punto (.)', $row) ;}
        return $comment ;
    }


    private function controlPhone($value, $row, $key): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if (strlen($value) > 20 ) { 
            $comment[1] = sprintf('Riga: %d , Colonna: %s - numero non valido, maggiore di 20 caratteri', $row, $key) ;
        } else
        { 
            if ( preg_match( "/^[0-9+()\s]*$/" ,  $value ) === 1 ) {
                $comment[0] = 'OK';
            }
            else {  $comment[1] = sprintf('Riga: %d , Colonna: %s - numero non valido, sono solo ammessi: numeri, parentesi (), spazi e il simbolo (+)', $row, $key) ;}
        }
        return $comment ;
    }


    private function controlDataAssunzione($value, $row ): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if (is_numeric($value)) {
        $date = new \DateTime();
        $date->setTimestamp(($value-25569)*86400);
        $dateformat = $date->format('d/m/Y');
        $dataHiring = explode ("/", $dateformat ); // divide la data in item di array
        if ( count($dataHiring) === 3 )  { 
            if ( checkdate($dataHiring[1], $dataHiring[0], $dataHiring[2], ) === true) {
               $comment[0] = 'OK';
               // $comment[1] = sprintf('Riga: %d , Colonna: DataAssunzione - Data valida %d - %s ', $row, $value, $dateformat) ;
            } else {
                $comment[1] = sprintf('Riga: %d , Colonna: DataAssunzione - data non valida %d - %s ', $row, $value, $dateformat) ;
            }
            } else
            {  $comment[1] = sprintf('Riga: %d , Colonna: DataAssunzione - non è nel formato dd/mm/aaaa %d - %s', $row, $value, $dateformat) ; }
        } else
        {  $comment[1] = sprintf('Riga: %d , Colonna: DataAssunzione - non contiene una data: %s', $row, $value) ; }
        return $comment ;
    }
  
    
    private function controlDataUltimaVisitaMedica($value, $row ): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if (is_numeric($value)) {
        $date = new \DateTime();
        $date->setTimestamp(($value-25569)*86400);
        $dateformat = $date->format('d/m/Y');
        $dataUltimaVisita = explode ("/", $dateformat ); // divide la data in item di array
        if ( count($dataUltimaVisita) === 3 )  { 
            if ( checkdate($dataUltimaVisita[1], $dataUltimaVisita[0], $dataUltimaVisita[2], ) === true) {
               $comment[0] = 'OK';
               $comment[1] = $dateformat ; $comment[2] = $date ;
            } else {
               $comment[1] = sprintf('Riga: %d , Colonna: DataUltimaVisitaMedica - data non valida %d - %s ', $row, $value, $dateformat) ;
            }
            } else
            {  $comment[1] = sprintf('Riga: %d , Colonna: DataUltimaVisitaMedica - non è nel formato dd/mm/aaaa %d - %s', $row, $value, $dateformat) ; }
        } else
        {  $comment[1] = sprintf('Riga: %d , Colonna: DataUltimaVisitaMedica - non contiene una data: %s', $row, $value) ; }
        return $comment ;
    }

    private function controlDataScadenzaVisitaMedica($value, $row, $datePrevForm, $datePrevius ): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if (is_numeric($value)) {
        $date = new \DateTime();
        $date->setTimestamp(($value-25569)*86400);
        $dateformat = $date->format('d/m/Y');
        $dataScadenzaVisita = explode ("/", $dateformat ); // divide la data in item di array
        if ( count($dataScadenzaVisita) === 3 )  { 
            if ( checkdate($dataScadenzaVisita[1], $dataScadenzaVisita[0], $dataScadenzaVisita[2], ) === true) {
               if ($date < $datePrevius ) {
                 $comment[1] = sprintf('Riga: %d , Colonna: DataScadenzaVisitaMedica  %s - inferiore a data ultima visita %s ', $row, $dateformat, $datePrevForm) ;
               } else {$comment[0] = 'OK'; }
            } else {
               $comment[1] = sprintf('Riga: %d , Colonna: DataScadenzaVisitaMedica - data non valida %d - %s ', $row, $value, $dateformat) ;
            }
            } else
            {  $comment[1] = sprintf('Riga: %d , Colonna: DataScadenzaVisitaMedica - non è nel formato dd/mm/aaaa %d - %s', $row, $value, $dateformat) ; }
        } else
        {  $comment[1] = sprintf('Riga: %d , Colonna: DataScadenzaVisitaMedica - non contiene una data: %s', $row, $value) ; }
        return $comment ;
    }

    private function controlContoIban($value, $row ): array
    {
        $comment = [];
        $comment[0] = 'ER';
        $value = str_replace(" ", "", $value); // toglie gli spazi 
        if (strlen($value) > 27 ) { 
            $comment[1] = sprintf('Riga: %d , Colonna: ContoIban - Codice non valido, maggiore di 27 caratteri', $row) ;
        } else
        { 
            $comment[0] = 'OK';
        }
        return $comment ;
    }
    
    private function controlContratto($value, $row ): array
    {
        $comment = [];
        $comment[0] = 'ER';
        $value = strtoupper($value); 
        if ($value !== 'I' && $value !== 'D' && $value !== 'T' ) { 
            $comment[1] = sprintf('Riga: %d , Colonna: Contratto - valore non valido, deve contenere soloun carattere=  I / D / T', $row) ;
        } else
        { 
            $comment[0] = 'OK'; $comment[1] = $value;
        }
        return $comment ;
    }
  
    private function controlDataScadenzaContratto($value, $row ): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if (is_numeric($value)) {
        $date = new \DateTime();
        $date->setTimestamp(($value-25569)*86400);
        $dateformat = $date->format('d/m/Y');
        $dataScadContratto = explode ("/", $dateformat ); // divide la data in item di array
        if ( count($dataScadContratto) === 3 )  { 
            if ( checkdate($dataScadContratto[1], $dataScadContratto[0], $dataScadContratto[2], ) === true) {
               $comment[0] = 'OK';
               } else {
                $comment[1] = sprintf('Riga: %d , Colonna: DataScadenzaContratto - data non valida %d - %s ', $row, $value, $dateformat) ;
            }
            } else
            {  $comment[1] = sprintf('Riga: %d , Colonna: DataScadenzaContratto - non è nel formato dd/mm/aaaa %d - %s', $row, $value, $dateformat) ; }
        } else
        {  $comment[1] = sprintf('Riga: %d , Colonna: DataScadenzaContratto - non contiene una data: %s', $row, $value) ; }
        return $comment ;
    }

    private function controlLivello($value, $row ): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if (strlen($value) > 5 ) { 
            $comment[1] = sprintf('Riga: %d , Colonna: Livello - valore non valido, maggiore di 5 caratteri', $row) ;
        } else
        { 
            $comment[0] = 'OK';
        }
        return $comment ;
    }

    private function controlSocio($value, $row ): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if ($value !== 1 && $value !== 0 && $value !== '1' && $value !== '0' && strtoupper($value) !== 'TRUE' && strtoupper($value) !== 'FALSE' && strtoupper($value) !== 'SI' && strtoupper($value) !== 'NO' ) { 
            $comment[1] = sprintf('Riga: %d , Colonna: Socio - valore non valido, deve contenere: 1 / 0 oppure Si / No  oppure true / false', $row) ;
        } else
        { 
            $comment[0] = 'OK';
        }
        return $comment ;
    }

    private function controlDivAb($value, $row ): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if ($value !== 1 && $value !== 0 && $value !== '1' && $value !== '0' && strtoupper($value) !== 'TRUE' && strtoupper($value) !== 'FALSE' && strtoupper($value) !== 'SI' && strtoupper($value) !== 'NO' ) { 
            $comment[1] = sprintf('Riga: %d , Colonna: DivAb - valore non valido, deve contenere: 1 / 0 oppure Si / No  oppure true / false', $row) ;
        } else
        { 
            $comment[0] = 'OK';  $comment[1] = $value; 
        }
        return $comment ;
    }

    private function controlMansione($value, $row, $DivAb): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if (($value === null || $value === '' ) && ($DivAb === 1 || $DivAb === '1' || strtoupper($DivAb) === 'SI' || strtoupper($DivAb) === 'TRUE' ) ) { 
            $comment[1] = sprintf('Riga: %d , Colonna: Mansione - dato nullo o inesistente, invece è obbligatorio nel caso di Invalidità segnalata', $row ) ;}
             else
             {
      
                if ( $this->entityManager->getRepository(Mansioni::class)->findOneBy(['mansioneName'=> trim($value)]) ) {
                    $comment[0] = 'OK';
                    }
                else {$comment[1] = sprintf('Riga: %d , Colonna: Mansione - Valore %s non presente nella tabella di configurazione Mansioni', $row, $value);
                    }
            }
        return $comment ;
    }

    private function controlCantiere($value, $row): array
    {
        $comment = [];
        $comment[0] = 'ER';
        $value = strtoupper($value); 
            if ( $this->entityManager->getRepository(Cantieri::class)->findOneBy(['nameJob'=> $value]) ) {
                 $comment[0] = 'OK';
                 }
             else {  $comment[1] = sprintf('Riga: %d , Colonna: Cantiere - Valore non presente nell\'anagrafica Cantieri', $row) ;}
        return $comment ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        
        return $crud
            ->setEntityLabelInSingular('Import personale')
            ->setEntityLabelInPlural('Import personale')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco import')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (ImportPersonale $nota) => sprintf('Import personale <b>%s</b>', $nota ))
            ->setPageTitle(Crud::PAGE_EDIT, fn (ImportPersonale $nota) => sprintf('Import personale <b>%s</b>', $nota))
            ->showEntityActionsAsDropdown();
    }

    public function configureActions(Actions $actions): Actions
    {
        $importExcel = Action::new('importFromExcel', 'Esegui import', 'fas  fa-upload')
        ->linkToCrudAction('importFromExcel')
        ->displayIf(fn ($entity) => $entity->getPathImport()
        );
        
        return $actions
            // ...
            
            //->remove(Crud::PAGE_DETAIL, Action::EDIT)
            //->remove(Crud::PAGE_DETAIL, Action::DELETE)

            //->add(Crud::PAGE_EDIT,  Action::INDEX )
            //->add(Crud::PAGE_NEW,   Action::INDEX )
            ->add(Crud::PAGE_INDEX,   $importExcel  )
            
            ->update(Crud::PAGE_INDEX, Action::EDIT,
             fn (Action $action) => $action->setIcon('fa fa-edit') )
            ->update(Crud::PAGE_INDEX, Action::DELETE,
             fn (Action $action) => $action->setIcon('fa fa-trash')->setHtmlAttributes(['title' => 'Elimina la richiesta di import.']) )
           /*  ->update(Crud::PAGE_INDEX, Action::DETAIL,
             fn (Action $action) => $action->setIcon('fa fa-eye')->setHtmlAttributes(['title' => 'Vedi'])) */
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('CONFIGURA FILE PER IMPORT PERSONALE')->setIcon('fas fa-file-excel');
        // $panelElabora = FormField::addPanel('PREMERE CONFERMA PER AVVIARE LA PROCEDURA DI IMPORT')->setIcon('fas fa-upload');
        $azienda = AssociationField::new('azienda', 'Personale assunto nell\'Azienda')->setHelp('NickName dell\'azienda del gruppo')
            ->setFormTypeOptions([
            'query_builder' => function (AziendeRepository $az) {
                return $az->createQueryBuilder('a')
                   ->orderBy('a.nickName', 'ASC');     }, 
            ])
            ->setCustomOptions(array('widget' => 'native'))->setRequired(true);
        $nota = TextField::new('nota', 'Commento all\'import')->setHelp('Descrivere il contenuto del file di import. Esempi: Tutto il personale, Nuovi assunti al gg/mm/aaaa, Assunti per il Cantiere...');
        $pathImport = ImageField::new('pathImport', 'File di Import')
        ->setBasePath('uploads/files/personale/import')
        ->setUploadDir('public/uploads/files/personale/import')
        ->setUploadedFileNamePattern('[year]-[month]-[day]-[contenthash].[extension]')
        ->setHelp('Inserire file tipo Excel (xlsx) come da modello fornito nella documentazione di JobTime');
        $importExcel = TextField::new('pathImport', 'File di Import')->setTemplatePath('admin/personale/import.html.twig');
        // $importEditExcel = TextField::new('pathImport','File di Import' )->setFormTypeOptions(['disabled' => 'true']);
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);
        if (Crud::PAGE_INDEX === $pageName) {
            return [ $id, $azienda, $nota, $importExcel, $createdAt ];
        }  elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1,  $azienda, $nota, $pathImport ];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1,  $azienda, $nota, $pathImport, $panel_ID, $id, $createdAt];

        }
    }
}
