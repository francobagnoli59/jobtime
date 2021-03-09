<?php

namespace App\Controller\Admin;

use App\Entity\ImportCantieri;
use App\Entity\Cantieri;
use App\Entity\RegoleFatturazione;
use App\Entity\Province;
use App\Entity\Clienti;
use App\Entity\CategorieServizi;
use App\Repository\AziendeRepository;

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

class ImportCantieriCrudController extends AbstractCrudController
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
        return ImportCantieri::class;
    }

 
    public function importFromExcel(AdminContext $context)
    {

        $ImportCantieri = $context->getEntity()->getInstance();

        $filesystem = new Filesystem();
        // dati import 
         $azienda = $ImportCantieri->getAzienda();
         $path = $ImportCantieri->getPathImport();
         $pathfile = 'uploads/files/cantieri/import/'.$path;
         if ($filesystem->exists($pathfile)) {
            // apre e carica il file di excel
            $reader = new Xlsx();
            $spreadsheet = $reader->load($pathfile);
            
            // cerca la cartella Dati
            if ($spreadsheet->getSheetByName('Dati')) {
                // Array di controllo
                $colExcel = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
                $colDatiObbl = ['NomeCantiere' => 'N' , 'Provincia' => 'N' , 'DataAvvio' => 'N', 'DataFine' => 'N', 'TariffaOraria' => 'N' ,
                 'PrezzoaCorpo' => 'N' , 'MonteOrePreviste' => 'N' , 'CostoMateriali' => 'N' ];
                $colDatiFacol = ['Città', 'Cliente', 'CategoriaServizi' ];
                $colDatiAssoc = ['NomeCantiere' => '@' , 'Provincia' => '@' , 'DataAvvio' => '@', 'DataFine' => '@', 'TariffaOraria' => '@' ,
                'PrezzoaCorpo' => '@' , 'MonteOrePreviste' => '@' , 'CostoMateriali' => '@' , 'Città'  => '@' , 'Cliente'  => '@', 'CategoriaServizi'  => '@'  ];
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
                       if ($cellValue !== null && $cellValue !== ""  && $cellValue !== "ZZZ" ) {  
                        $rowFound ++;
                        // Ciclo sulle colonne
                        foreach ($keyDatiAssoc as $key) {
                        // determina cella secondo la key
                        $cellValue = $workSheet->getCell($colDatiAssoc[$key].sprintf('%d',$row) )->getValue();  
                        // controlli specifici per tipo di colonna
                        switch ($key){
                            case "NomeCantiere":
                                if ($cellValue === null || $cellValue === '' ) {  $commentiImport[] = sprintf('Riga: %d , Colonna: %s - dato nullo o inesistente, invece è obbligatorio', $row, $key) ;}
                                break;
                            case "Provincia":
                                $testPR = $this->controlProvincia($cellValue, $row);
                                if ($testPR[0] !== 'OK') { $commentiImport[] = $testPR[1] ;}
                                break;
                            case "DataAvvio":
                                $testDataAvvio = $this->controlDataAvvio($cellValue, $row);
                                if ($testDataAvvio[0] !== 'OK') { $commentiImport[] = $testDataAvvio[1] ;}
                                break;
                            case "DataFine":
                                if ($testDataAvvio[0] === 'OK') {
                                    $testDataFine = $this->controlDataFine($cellValue, $row, $testDataAvvio[1], $testDataAvvio[2] );
                                    if ($testDataFine[0] !== 'OK') { $commentiImport[] = $testDataFine[1] ;}
                                }
                                break;
                            case "TariffaOraria":
                            case "PrezzoaCorpo":
                            case "MonteOrePreviste":
                            case "CostoMateriali":
                                $testCostoOra = $this->controlValoreNumerico($cellValue, $row, $key);
                                if ($testCostoOra[0] !== 'OK') { $commentiImport[] = $testCostoOra[1] ;}
                                break;
                            case "Cliente":
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                    $testCliente = $this->controlCliente($cellValue, $row);
                                    if ($testCliente[0] !== 'OK') { $commentiImport[] = $testCliente[1] ;}
                                }
                                break;
                            case "CategoriaServizi":
                                if ($cellValue !== null && $cellValue !== '' ) {
                                    $testCategoria = $this->controlCategoria($cellValue, $row);
                                    if ($testCategoria[0] !== 'OK') { $commentiImport[] = $testCategoria[1] ;}
                                }
                                    break;                                                                             
                            case "Città":
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                $testCity = $this->controlCity($cellValue, $row);
                                if ($testCity[0] !== 'OK') { $commentiImport[] = $testCity[1] ;}
                                }
                                break;

                        }  // end switch
                        } //  ciclo sulle colonne
                     } // ennesima riga della colonna A non vuota ( altrimenti scarta tutta la riga)
                    } // riga da leggere
                } // ciclo di 500 righe dati

                // Se non ci sono errori ripete il ciclo ed importa i dati nella entity cantiere
                if (count($commentiImport) === 0 ) {

                // ciclo di REGISTRAZIONE 
                for ($row = 2; $row < 501; $row++) {
                     // determina cella colonna A per capire se uscire dal ciclo
                     $cellValue = $workSheet->getCell('A'.sprintf('%d',$row) )->getValue();  
                     if ($cellValue === "ZZZ" ) { $row = 501 ; }   // fine ciclo, cella A(n) ===  ZZZ }
                       else {  // riga da leggere
                       if ($cellValue !== null && $cellValue !== "" && $cellValue !== "ZZZ" ) {  

                        // ASSEGNA specifici valori per tipo di colonna
                        $cantiere = new Cantieri();
                        $cantiere->setAzienda($azienda);
                        $cantiere->setIsPublic(false);
                        $cantiere->setIsPlanningPerson(true);
                        $cantiere->setIsPlanningMaterial(true);
                        $cantiere->setRegolaFatturazione($this->entityManager->getRepository(RegoleFatturazione::class)->findOneBy(['billingCadence'=> 'MENSILE']));
                        // Ciclo sulle colonne  
                        foreach ($keyDatiAssoc as $key) {
                        // determina cella secondo la key
                        $cellValue = $workSheet->getCell($colDatiAssoc[$key].sprintf('%d',$row) )->getValue();  
                        $cellValue = trim($cellValue);    
                        switch ($key){
                            case "NomeCantiere":
                                $cantiere->setNameJob($cellValue);
                                break;
                            case "Provincia":
                                $cantiere->setProvincia($this->entityManager->getRepository(Province::class)->findOneBy(['code'=> strtoupper($cellValue)]));
                                break;
                            case "DataAvvio":
                                $date = new \DateTime();
                                $date->setTimestamp(($cellValue-25569)*86400);
                                $cantiere->setDateStartJob($date);  
                                break;
                            case "DataFine":
                                $date = new \DateTime();
                                $date->setTimestamp(($cellValue-25569)*86400);
                                $cantiere->setDateEndJob($date);  
                                break;
                            case "TariffaOraria":
                                $cantiere->setHourlyRate($cellValue*100);
                                break;
                            case "PrezzoaCorpo":
                                $cantiere->setFlatRate($cellValue*100);
                                break;
                            case "MonteOrePreviste":
                                $cantiere->setPlanningHours($cellValue);
                                if ($cantiere->getPlanningHours() === 0) { $cantiere->setIsPlanningPerson(false);}
                                break;                              
                            case "CostoMateriali":
                                $cantiere->setPlanningCostMaterial($cellValue*100);
                                if ($cantiere->getPlanningCostMaterial() === 0) { $cantiere->setIsPlanningMaterial(false);}
                                break;
                            case "CategoriaServizi":
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                     $cantiere->setCategoria($this->entityManager->getRepository(CategorieServizi::class)->findOneBy(['categoria'=> $cellValue]) ) ;
                                    } 
                                break;
                            case "Cliente":
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                $cantiere->setCliente($this->entityManager->getRepository(Clienti::class)->findOneBy(['name'=> $value]));
                                }
                                break;
                            case "Città":
                                if ($cellValue !== null && $cellValue !== '' ) { 
                                $cantiere->setCity($cellValue);
                                }
                                break;

                            }  // end switch
                        } //  ciclo sulle colonne
                        // verifica se esiste nel db 
                        if ( $this->entityManager->getRepository(Cantieri::class)->findOneBy(['nameJob'=> strtoupper($cellValue)]) !== null ) {
                            $rowExist ++;
                        } else { 
                        // registra il cantiere
                        $this->entityManager->persist($cantiere);
                        $this->entityManager->flush();
                        $rowInsert ++;
                        }
                     } // ennesima riga della colonna A non vuota ( altrimenti scarta tutta la riga)
                    }  // riga da leggere
                  } // ciclo di 500 righe dati

                    if ($rowExist > 0) {   $this->addflash('warning', sprintf('Il file excel %s contiene %d cantieri che già esistevano in JobTime, che di conseguenza non sono staie inseriti, pertanto %d cantieri sono stati importate senza errori.', $pathfile, $rowExist, $rowInsert));
                    }
                     else { 
                    $this->addflash('success', sprintf('Il file excel %s contiene %d cantieri che sono stati importati senza errori', $pathfile, $rowInsert));
                    }
                    $url = $this->adminUrlGenerator->unsetAll()
                    ->setController(CantieriCrudController::class)
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
                    $filelogerror = 'downloads/errorImport/'.'Log_Import_Cantieri_'.$azienda .'.txt';
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
            ->setController(ImportCantieriCrudController::class)
            ->setAction(Action::INDEX);
         return $this->redirect($url);
         
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


    private function controlValoreNumerico($value, $row, $key): array
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

    private function controlDataAvvio($value, $row ): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if (is_int($value)) {
        $date = new \DateTime();
        $date->setTimestamp(($value-25569)*86400);
        $dateformat = $date->format('d/m/Y');
        $dataAvvio = explode ("/", $dateformat ); // divide la data in item di array
        if ( count($dataAvvio) === 3 )  { 
            if ( checkdate($dataAvvio[1], $dataAvvio[0], $dataAvvio[2], ) === true) {
               $comment[0] = 'OK';
               $comment[1] = $dateformat ; $comment[2] = $date; 
            } else {
                $comment[1] = sprintf('Riga: %d , Colonna: DataAvvio - data non valida %d - %s ', $row, $value, $dateformat) ;
            }
            } else
            {  $comment[1] = sprintf('Riga: %d , Colonna: DataAvvio - non è nel formato dd/mm/aaaa %d - %s', $row, $value, $dateformat) ; }
        } else
        {  $comment[1] = sprintf('Riga: %d , Colonna: DataAvvio - non contiene una data: %s', $row, $value) ; }
        return $comment ;
    }
  
    private function controlDataFine($value, $row, $datePrevForm, $datePrevius ): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if (is_int($value)) {
        $date = new \DateTime();
        $date->setTimestamp(($value-25569)*86400);
        $dateformat = $date->format('d/m/Y');
        $dataFine = explode ("/", $dateformat ); // divide la data in item di array
        if ( count($dataFine) === 3 )  { 
            if ( checkdate($dataFine[1], $dataFine[0], $dataFine[2], ) === true) {
               if ($date < $datePrevius ) {
                 $comment[1] = sprintf('Riga: %d , Colonna: DataFine  %s - inferiore a data Avvio %s ', $row, $dateformat, $datePrevForm) ;
               } else {$comment[0] = 'OK'; }
            } else {
               $comment[1] = sprintf('Riga: %d , Colonna: DataFine - data non valida %d - %s ', $row, $value, $dateformat) ;
            }
            } else
            {  $comment[1] = sprintf('Riga: %d , Colonna: DataFine - non è nel formato dd/mm/aaaa %d - %s', $row, $value, $dateformat) ; }
        } else
        {  $comment[1] = sprintf('Riga: %d , Colonna: DataFine - non contiene una data: %s', $row, $value) ; }
        return $comment ;
    }

    private function controlCity($value, $row ): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if (strlen($value) > 60 ) { 
            $comment[1] = sprintf('Riga: %d , Colonna: Città - valore non valido, maggiore di 60 caratteri', $row) ;
        } else
        { 
            $comment[0] = 'OK';
        }
        return $comment ;
    }
    

    private function controlCategoria($value, $row): array
    {
        $comment = [];
        $comment[0] = 'ER';
        if ( $this->entityManager->getRepository(CategorieServizi::class)->findOneBy(['categoria'=> $value]) ) {
            $comment[0] = 'OK';
        }
        else {$comment[1] = sprintf('Riga: %d , Colonna: CategoriaServizi - Valore %s non presente nella tabella di configurazione Categorie Servizi', $row, $value);
           }
        return $comment ;
    }

    private function controlCliente($value, $row): array
    {
        $comment = [];
        $comment[0] = 'ER';
        $value = strtoupper($value); 
            if ( $this->entityManager->getRepository(Clienti::class)->findOneBy(['name'=> $value]) ) {
                 $comment[0] = 'OK';
                 }
             else {  $comment[1] = sprintf('Riga: %d , Colonna: Cliente - Valore non presente nell\'anagrafica Clienti', $row) ;}
        return $comment ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        
        return $crud
            ->setEntityLabelInSingular('Import Cantieri')
            ->setEntityLabelInPlural('Import Cantieri')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco import')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (ImportCantieri $nota) => sprintf('Import Cantieri <b>%s</b>', $nota ))
            ->setPageTitle(Crud::PAGE_EDIT, fn (ImportCantieri $nota) => sprintf('Import Cantieri <b>%s</b>', $nota))
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
        $panel1 = FormField::addPanel('CONFIGURA FILE PER IMPORT CANTIERI')->setIcon('fas fa-file-excel');
        // $panelElabora = FormField::addPanel('PREMERE CONFERMA PER AVVIARE LA PROCEDURA DI IMPORT')->setIcon('fas fa-upload');
        $azienda = AssociationField::new('azienda', 'Cantieri riferito all\'Azienda')->setHelp('NickName dell\'azienda del gruppo')
            ->setFormTypeOptions([
            'query_builder' => function (AziendeRepository $az) {
                return $az->createQueryBuilder('a')
                   ->orderBy('a.nickName', 'ASC');     }, 
            ])
            ->setCustomOptions(array('widget' => 'native'))->setRequired(true);
        $nota = TextField::new('nota', 'Commento all\'import')->setHelp('Descrivere il contenuto del file di import. Esempi: Tutti i cantieri, Nuove acquisizioni al gg/mm/aaaa ...');
        $pathImport = ImageField::new('pathImport', 'File di Import')
        ->setBasePath('uploads/files/cantieri/import')
        ->setUploadDir('public/uploads/files/cantieri/import')
        ->setUploadedFileNamePattern('[year]-[month]-[day]-[contenthash].[extension]')
        ->setHelp('Inserire file tipo Excel (xlsx) come da modello fornito nella documentazione di JobTime');
        $importExcel = TextField::new('pathImport', 'File di Import')->setTemplatePath('admin/cantieri/import.html.twig');
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
