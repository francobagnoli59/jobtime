<?php

namespace App\Controller\Admin;

use App\Entity\ImportPersonale;
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

   
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $filesystem = new Filesystem();
        // dati import 
         $azienda = $entityInstance->getAzienda();
         $path = $entityInstance->getPathImport();
         $pathfile = 'uploads/files/personale/import/'.$path;
         if ($filesystem->exists($pathfile)) {
            // apre e carica il file di excel
            $reader = new Xlsx();
            $spreadsheet = $reader->load($pathfile);
            
            // cerca la cartella Dati
            if ($spreadsheet->getSheetByName('Dati')) {
                // Array di controllo
                $colExcel = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
                $colDatiObbl = ['Cognome' => 'N' ,'Nome' => 'N' , 'CodiceFiscale' => 'N' , 'Indirizzo' => 'N' , 'CAP' => 'N' , 'Città' => 'N' , 'Provincia' => 'N' , 'Matricola' => 'N' , 'CostoOrario' => 'N' , 'CostoStraordinario' => 'N' , 'PlanningSettimanale' => 'N' ];
                $colDatiFacol = ['E-mail', 'Cellulare', 'Telefono','DataAssunzione','ContoIban'];
                $colDatiAssoc = ['Cognome' => '@' ,'Nome' => '@' , 'CodiceFiscale' => '@' , 'Indirizzo' => '@' , 'CAP' => '@' , 
                'Città' => '@' , 'Provincia' => '@' , 'Matricola' => '@' , 'CostoOrario' => '@' , 'CostoStraordinario' => '@' , 
                'PlanningSettimanale' => '@', 'E-mail' => '@' , 'Cellulare' => '@' , 'Telefono' => '@' , 'DataAssunzione' => '@' , 'ContoIban' => '@' ];
                // cerca sulla prima riga le colonne progettate e crea Array $colDatiAssoc con posizione colonna
                $workSheet = $spreadsheet->getActiveSheet();
                // ciclo colonne obbligatorie
                foreach ($colExcel as $col) {
                    $cellValue = $workSheet->getCell($col.'1')->getValue();  // contenuto cella
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
                // scorre le righe dei dati a colonna A = ZZZ o zzz esce, se Colonna A vuota salta alla successiva 






                

                /* // verifica
                foreach ($arrayDatiObbl as $key) {
                    $this->addflash('info', sprintf('Dato: %s trovata: %s ', $key , $colDatiObbl[$key]));
                }
                $arrayDatiAssoc = array_keys($colDatiAssoc);
                foreach ($arrayDatiAssoc as $key) {
                    $this->addflash('info', sprintf('Dato %s associato alla colonna: %s ', $key , $colDatiAssoc[$key]));
                }
                 */

                } // colonne valide

            } else  {
                $this->addflash('danger', sprintf('Il file excel %s non contiene la cartella Dati', $pathfile));
            }

         } else {
            $this->addflash('danger', sprintf('Non trovato file di import nella cartella %s ', $pathfile));
         }

         // legge anno dalle festività dell'anno
         // $festivita = $entityManager->getRepository(FestivitaAnnuali::class)->findOneBy(['id'=> $festivitaAnnuale_id]);
         
         
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
        
        return $actions
            // ...
            
            //->remove(Crud::PAGE_DETAIL, Action::EDIT)
            //->remove(Crud::PAGE_DETAIL, Action::DELETE)

            //->add(Crud::PAGE_EDIT,  Action::INDEX )
            //->add(Crud::PAGE_NEW,   Action::INDEX )
           
            
            ->update(Crud::PAGE_INDEX, Action::EDIT,
             fn (Action $action) => $action->setIcon('fa fa-upload')->setLabel('Esegui import')->displayIf(fn ($entity) => $entity->getNota()))
            ->update(Crud::PAGE_INDEX, Action::DELETE,
             fn (Action $action) => $action->setIcon('fa fa-trash')->setHtmlAttributes(['title' => 'Elimina la richiesta di import.'])->displayIf(fn ($entity) => $entity->getNota()))
           /*  ->update(Crud::PAGE_INDEX, Action::DETAIL,
             fn (Action $action) => $action->setIcon('fa fa-eye')->setHtmlAttributes(['title' => 'Vedi'])) */
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('CONFIGURA FILE PER IMPORT PERSONALE')->setIcon('fas fa-file-excel');
        $panelElabora = FormField::addPanel('PREMERE CONFERMA PER AVVIARE LA PROCEDURA DI IMPORT')->setIcon('fas fa-upload');
        $azienda = AssociationField::new('azienda', 'Personale assunto nell\'Azienda')->setHelp('NickName dell\'azienda del gruppo')
            ->setFormTypeOptions([
            'query_builder' => function (AziendeRepository $az) {
                return $az->createQueryBuilder('a')
                   ->orderBy('a.nickName', 'ASC');     }, 
            ])
            ->setCustomOptions(array('widget' => 'native'))->setRequired(true);
        $nota = TextField::new('nota', 'Commento all\'import')->setRequired(true)->setHelp('Descrivere il contenuto del file di import. Esempi: Tutto il personale, Nuovi assunti al gg/mm/aaaa, Assunti per il Cantiere...');
        $pathImport = ImageField::new('pathImport', 'File di Import')
        ->setBasePath('uploads\files\personale\import')
        ->setUploadDir('public\uploads\files\personale\import')
        ->setUploadedFileNamePattern('[year]-[month]-[day]-[contenthash].[extension]')
        ->setHelp('Inserire file tipo Excel (xlsx) come da modello fornito nella documentazione di JobTime');
        $importExcel = TextField::new('pathImport', 'File di Import')->setTemplatePath('admin/personale/import.html.twig');
        $importEditExcel = TextField::new('pathImport','File di Import' )->setFormTypeOptions(['disabled' => 'true']);
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);
        if (Crud::PAGE_INDEX === $pageName) {
            return [ $id, $azienda, $nota, $importExcel, $createdAt ];
        }  elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1,  $azienda, $nota, $pathImport ];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panelElabora,  $azienda, $nota, $importEditExcel, $panel_ID, $id, $createdAt];

        }
    }
}
