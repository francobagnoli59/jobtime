<?php

namespace App\Controller\Admin;

use App\Entity\Personale;
use App\Entity\Cantieri;
use App\Entity\PianoOreCantieri;
use App\Form\DocumentiPersonaleType;

use App\Repository\ProvinceRepository;
use App\Repository\CantieriRepository;
use App\Repository\AziendeRepository;
use App\Repository\MansioniRepository;
use App\ServicesRoutine\PhpOfficeStyle;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Vich\UploaderBundle\Form\Type\VichImageType;

use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Filesystem\Filesystem;


class PersonaleDimessoCrudController extends AbstractCrudController
{

 

    /**
     * @var AdminUrlGenerator
     */
    private AdminUrlGenerator $adminUrlGenerator;

    // private CsvService $csvService;

    public function __construct(EntityManagerInterface $entityManager,  AdminUrlGenerator $adminUrlGenerator )  // , CsvService $csvService
    {
    $this->entityManager = $entityManager;
    $this->adminUrlGenerator = $adminUrlGenerator;
    // $this->csvService = $csvService;
    }

    public static function getEntityFqcn(): string
    {
        return Personale::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
 
        $azienda = $this->getUser()->getAziendadefault();
        if ($azienda !== null ) {
            $aziendaNickName = $azienda->getNickName();
        } else { $aziendaNickName = '...seleziona azienda!!!'; } 

        $LabelSing = 'Personale dimesso '.$aziendaNickName ;
        $LabelPlur = 'Personale dimesso '.$aziendaNickName ;
        $LabelList = 'Elenco Personale dimesso '.$aziendaNickName ;

        return $crud
            ->showEntityActionsAsDropdown()
            ->setEntityLabelInSingular($LabelSing)
            ->setEntityLabelInPlural($LabelPlur)
            ->setPageTitle(Crud::PAGE_INDEX, $LabelList)
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Personale $namefull) => sprintf('Visualizza scheda dati di <b>%s</b> (dimesso)', $namefull->getFullName()))
            ->setPageTitle(Crud::PAGE_EDIT, fn (Personale $namefull) => sprintf('Modifica scheda dati di <b>%s</b> (dimesso)', $namefull->getFullName()))
            ->setSearchFields(['matricola', 'name', 'surname', 'cantiere.nameJob', 'mansione.mansioneName' ])
            ->setDefaultSort(['surname' => 'ASC', 'name' => 'ASC'])
            ;
          //  ->setPageTitle(Crud::PAGE_NEW, 'Crea scheda nuovo personale')
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('surname', 'Cognome'))
            ->add(EntityFilter::new('cantiere')->setFormTypeOption('value_type_options.query_builder', 
                 static fn(CantieriRepository $ca) => $ca->createQueryBuilder('cantiere')
                 ->orderBy('cantiere.nameJob', 'ASC') ) )
            ->add(EntityFilter::new('azienda')->setFormTypeOption('value_type_options.query_builder', 
                static fn(AziendeRepository $az) => $az->createQueryBuilder('azienda')
                 ->orderBy('azienda.nickName', 'ASC') ) )
            ->add(ChoiceFilter::new('gender', 'Sesso')->setChoices(['Femmina' => 'F', 'Maschio' => 'M' ]) )
            ->add(BooleanFilter::new('isPartner', 'Socio'))
            ->add(BooleanFilter::new('isInvalid', 'Diversamente abile'))
            ->add(DateTimeFilter::new('birthday', 'Data di nascita'))
            ->add(DateTimeFilter::new('dateHiring', 'Data di assunzione'))
            ->add(ChoiceFilter::new('tipoContratto', 'Tipo Contratto')->setChoices(['Indeterminato' => 'I', 'Determinato' => 'D', 'Stagionale' => 'T' ]) )
            ->add(DateTimeFilter::new('scadenzaContratto', 'Scadenza contratto'))
            ->add(DateTimeFilter::new('scadenzaVisitaMedica', 'Scadenza visita medica'))
            ->add(TextFilter::new('fiscalCode', 'Codice Fiscale'))
            ->add(EntityFilter::new('provincia') 
            ->setFormTypeOption('value_type_options.query_builder', 
                static fn(ProvinceRepository $pr) => $pr->createQueryBuilder('provincia')
                        ->orderBy('provincia.name', 'ASC') ) )
            ->add(EntityFilter::new('mansione')->setFormTypeOption('value_type_options.query_builder', 
                static fn(MansioniRepository $ma) => $ma->createQueryBuilder('mansione')
                 ->orderBy('mansione.mansioneName', 'ASC') ) );
            // ->add(BooleanFilter::new('isEnforce', 'Assunto'))
    }
 
    public function configureActions(Actions $actions): Actions
    {
       
        $view_orelavorate = Action::new('ViewOreLavorate', 'Vedi Ore lavorate', 'fa fa-clock')
        ->linkToCrudAction('ViewOreLavorate')->setCssClass('btn btn-secondary');
        $view_pianoorecantieri = Action::new('ViewPianoOreCantieri', 'Piano Ore Cantieri', 'fa fa-clipboard-list')
        ->linkToCrudAction('ViewPianoOreCantieri')->displayIf(fn ($entity) => !$entity->getCantiere()
        ) ;
        $export = Action::new('exportXlsx', 'Esporta lista')
        ->setIcon('fa fa-file-excel')->setHtmlAttributes(['title' => 'Produce un file di excel con il personale dell\'elenco attuale (usare i filtri per la selezione desiderata)'])
        ->linkToCrudAction('exportXlsx')
        ->setCssClass('btn btn-secondary')
        ->createAsGlobalAction();

        return $actions
            // ...
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $view_orelavorate)->add(Crud::PAGE_EDIT, $view_orelavorate)
            ->add(Crud::PAGE_INDEX, $view_pianoorecantieri)
            ->add(Crud::PAGE_INDEX, $export)
           // ->add(Crud::PAGE_DETAIL,)
            ->add(Crud::PAGE_EDIT,  Action::INDEX )
            // ->add(Crud::PAGE_NEW,   Action::INDEX )

            ->update(Crud::PAGE_INDEX, Action::EDIT,
             fn (Action $action) => $action->setIcon('fa fa-edit')->setHtmlAttributes(['title' => 'Modifica']))
            ->update(Crud::PAGE_INDEX, Action::DELETE,
             fn (Action $action) => $action->setIcon('fa fa-trash')->setHtmlAttributes(['title' => 'Elimina']))
            ->update(Crud::PAGE_INDEX, Action::DETAIL,
             fn (Action $action) => $action->setIcon('fa fa-eye')->setHtmlAttributes(['title' => 'Vedi scheda']))
        ;
    }
// 
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $response = $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $azienda = $this->getUser()->getAziendadefault();
        if ($azienda !== null ) {
            $aziendaId = $azienda->getId();
        $response->andWhere('entity.azienda = '. $aziendaId);
        $response->andWhere('entity.isEnforce = false');
        } else {  $response->andWhere('entity.azienda = 0');
            $response->andWhere('entity.isEnforce = false'); } // così non visualizza niente
        return $response;
    }

    public function ViewOreLavorate(AdminContext $context)
    {
        $personale = $context->getEntity()->getInstance();

         $url = $this->adminUrlGenerator->unsetAll()
            ->setController(OreLavorateCrudController::class)
            ->setAction(Action::INDEX)
            ->set('filters[persona][comparison]', '=')
            ->set('filters[persona][value]', $personale->getId())
            ->set('filters[isTransfer][value]', 0);// 0 = false, 1= true
            return $this->redirect($url);   
    }

    public function ViewPianoOreCantieri(AdminContext $context)
    {
        $personale = $context->getEntity()->getInstance();
        $pianoCantieri = []; 
        $pianoCantieri = $personale->getPianoOreCantieri();
        if (count($pianoCantieri) === 0) { 
            $url = $this->adminUrlGenerator->unsetAll()
            ->setController(PianoOreCantieriCrudController::class)
            ->setAction(Action::NEW)
            ->set('persona', $personale->getId());
            return $this->redirect($url);  
        } else { 
            $url = $this->adminUrlGenerator->unsetAll()
            ->setController(PianoOreCantieriCrudController::class)
            ->setAction(Action::INDEX)
            ->set('filters[persona][comparison]', '=')
            ->set('filters[persona][value]', $personale->getId());
            return $this->redirect($url);  
        }
        
    }

    public function exportXlsx(Request $request):Response
    {
        $context = $request->attributes->get(EA::CONTEXT_REQUEST_ATTRIBUTE);
        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
        $filters = $this->get(FilterFactory::class)->create($context->getCrud()->getFiltersConfig(), $fields, $context->getEntity());
        $listpersonale = $this->createIndexQueryBuilder($context->getSearch(), $context->getEntity(), $fields, $filters)
            ->getQuery()
            ->getResult();

        $item = 0;
        if ($request !== null ){
        // stili configurati
        $styleArray = new PhpOfficeStyle ;

        $first = true; 
        $aziendaNickName = ''; $aziendaRagSociale = '';
        // scorre solo il primo per prendere id Azienda
        foreach ($listpersonale as $persona) {
            if ($first === true) {
                $first = false; 
                $aziendaNickName = $persona->getAzienda()->getNickName();
                $aziendaRagSociale = $persona->getAzienda()->getCompanyName();
            } else {
                break;  
            }
        } 
        
        $col = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S',
        'T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM',
        'AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'];    
        // proprietà documento
        $spreadsheet = new Spreadsheet(); $indexsheet = 0;
        $locale = 'it';
        $validLocale = \PhpOffice\PhpSpreadsheet\Settings::setLocale($locale);
        $spreadsheet->getProperties()
            ->setCreator("Produced by Masotech (c)")
            ->setLastModifiedBy("Masotech")
            ->setTitle("Lista dati del personale")
            ->setSubject("Informazioni sul personale selezionato ".$aziendaNickName)
            ->setDescription(
                "Documento strettamente riservato."
            )
            ->setManager("JobTime")
            ->setCompany($aziendaRagSociale);
        
        $sheet = $spreadsheet->getActiveSheet(); $sheet->setTitle('LISTA PERSONALE');
        $sheet->getCell('A1')->setValue('LISTA PERSONALE');
        $spreadsheet->getSheet($indexsheet)->getStyle('A1')->applyFromArray($styleArray->title1());
        $sheet->getCell('D1')->setValue($aziendaNickName);
        $spreadsheet->getSheet($indexsheet)->getStyle('D1')->applyFromArray($styleArray->title2());
        $sheet->getCell('B1')->setValue(date_create()->format('d/m/Y\ H:i:s'));
        $spreadsheet->getSheet($indexsheet)->getStyle('B1')->applyFromArray($styleArray->title3());
        // intestazione
        $sheet->getCell('A3')->setValue('Cognome');
        $spreadsheet->getSheet($indexsheet)->getStyle('A3')->applyFromArray($styleArray->columnPrimary());
        $sheet->getCell('B3')->setValue('Nome');
        $spreadsheet->getSheet($indexsheet)->getStyle('B3')->applyFromArray($styleArray->columnPrimary());
        $sheet->getCell('C3')->setValue('Genere');
        $spreadsheet->getSheet($indexsheet)->getStyle('C3')->applyFromArray($styleArray->columnPrimary());
        $sheet->getCell('D3')->setValue('Codice Fiscale');
        $spreadsheet->getSheet($indexsheet)->getStyle('D3')->applyFromArray($styleArray->columnPrimary());
        $sheet->getCell('E3')->setValue('Data di nascita');
        $spreadsheet->getSheet($indexsheet)->getStyle('E3')->applyFromArray($styleArray->columnPrimary());
        $sheet->getCell('F3')->setValue('Socio');
        $spreadsheet->getSheet($indexsheet)->getStyle('F3')->applyFromArray($styleArray->columnPrimary());

        $sheet->getCell('G3')->setValue('Cellulare');
        $spreadsheet->getSheet($indexsheet)->getStyle('G3')->applyFromArray($styleArray->columnSuccess());
        $sheet->getCell('H3')->setValue('E-mail');
        $spreadsheet->getSheet($indexsheet)->getStyle('H3')->applyFromArray($styleArray->columnSuccess());
        $sheet->getCell('I3')->setValue('Tel. abitazione');
        $spreadsheet->getSheet($indexsheet)->getStyle('I3')->applyFromArray($styleArray->columnSuccess());
        $sheet->getCell('J3')->setValue('Indirizzo');
        $spreadsheet->getSheet($indexsheet)->getStyle('J3')->applyFromArray($styleArray->columnSuccess());
        $sheet->getCell('K3')->setValue('CAP');
        $spreadsheet->getSheet($indexsheet)->getStyle('K3')->applyFromArray($styleArray->columnSuccess());
        $sheet->getCell('L3')->setValue('Città');
        $spreadsheet->getSheet($indexsheet)->getStyle('L3')->applyFromArray($styleArray->columnSuccess());
        $sheet->getCell('M3')->setValue('Provincia');
        $spreadsheet->getSheet($indexsheet)->getStyle('M3')->applyFromArray($styleArray->columnSuccess());
        $sheet->getCell('N3')->setValue('Area geografica');
        $spreadsheet->getSheet($indexsheet)->getStyle('N3')->applyFromArray($styleArray->columnSuccess());

        $sheet->getCell('P3')->setValue('Matricola');
        $spreadsheet->getSheet($indexsheet)->getStyle('P3')->applyFromArray($styleArray->columnTitleCoral());
        $sheet->getCell('R3')->setValue('Attivo');
        $spreadsheet->getSheet($indexsheet)->getStyle('R3')->applyFromArray($styleArray->columnWarning());
        $sheet->getCell('S3')->setValue('Disabile');
        $spreadsheet->getSheet($indexsheet)->getStyle('S3')->applyFromArray($styleArray->columnWarning());
        $sheet->getCell('T3')->setValue('Mansione');
        $spreadsheet->getSheet($indexsheet)->getStyle('T3')->applyFromArray($styleArray->columnWarning());
        $sheet->getCell('U3')->setValue('Data di assunzione');
        $spreadsheet->getSheet($indexsheet)->getStyle('U3')->applyFromArray($styleArray->columnWarning());
        $sheet->getCell('V3')->setValue('Tipo contratto');
        $spreadsheet->getSheet($indexsheet)->getStyle('V3')->applyFromArray($styleArray->columnWarning());
        $sheet->getCell('W3')->setValue('Livello');
        $spreadsheet->getSheet($indexsheet)->getStyle('W3')->applyFromArray($styleArray->columnWarning());
        $sheet->getCell('X3')->setValue('Scadenza contratto');
        $spreadsheet->getSheet($indexsheet)->getStyle('X3')->applyFromArray($styleArray->columnWarning());
        $sheet->getCell('Y3')->setValue('Data licenziamento');
        $spreadsheet->getSheet($indexsheet)->getStyle('Y3')->applyFromArray($styleArray->columnWarning());
        $sheet->getCell('Z3')->setValue('Cantiere');
        $spreadsheet->getSheet($indexsheet)->getStyle('Z3')->applyFromArray($styleArray->columnWarning());
        $sheet->getCell('AA3')->setValue('Costo orario');
        $spreadsheet->getSheet($indexsheet)->getStyle('AA3')->applyFromArray($styleArray->columnWarning());
        $sheet->getCell('AB3')->setValue('Costo straordinario');
        $spreadsheet->getSheet($indexsheet)->getStyle('AB3')->applyFromArray($styleArray->columnWarning());
        $sheet->getCell('AC3')->setValue('Ore settimanali');
        $spreadsheet->getSheet($indexsheet)->getStyle('AC3')->applyFromArray($styleArray->columnWarning());
         
        $sheet->getCell('AD3')->setValue('Iban');
        $spreadsheet->getSheet($indexsheet)->getStyle('AD3')->applyFromArray($styleArray->columnGreyLight());
        $sheet->getCell('AE3')->setValue('Intestatario conto');
        $spreadsheet->getSheet($indexsheet)->getStyle('AE3')->applyFromArray($styleArray->columnGreyLight());

        $sheet->getCell('AF3')->setValue('Data ultima visita');
        $spreadsheet->getSheet($indexsheet)->getStyle('AF3')->applyFromArray($styleArray->columnInfo());
        $sheet->getCell('AG3')->setValue('Data scadenza visita');
        $spreadsheet->getSheet($indexsheet)->getStyle('AG3')->applyFromArray($styleArray->columnInfo());
        $sheet->getCell('AH3')->setValue('Visita prenotata');
        $spreadsheet->getSheet($indexsheet)->getStyle('AH3')->applyFromArray($styleArray->columnInfo());
        $sheet->getCell('AI3')->setValue('Data pianificata visita');
        $spreadsheet->getSheet($indexsheet)->getStyle('AI3')->applyFromArray($styleArray->columnInfo());
        $sheet->getCell('AJ3')->setValue('Annotazioni visite');
        $spreadsheet->getSheet($indexsheet)->getStyle('AJ3')->applyFromArray($styleArray->columnInfo());

        $sheet->getCell('AK3')->setValue('Id Record');
        $spreadsheet->getSheet($indexsheet)->getStyle('AK3')->applyFromArray($styleArray->columnDark());
        $sheet->getCell('AL3')->setValue('Chiave registrazione');
        $spreadsheet->getSheet($indexsheet)->getStyle('AL3')->applyFromArray($styleArray->columnDark());
        $sheet->getCell('AM3')->setValue('Data aggiornamento');
        $spreadsheet->getSheet($indexsheet)->getStyle('AM3')->applyFromArray($styleArray->columnDark());

        // scorre tutto l'array persone ( salta se persona di altra azienda) 
        $row = 4;
        foreach ($listpersonale as $persona) {
            if (  $aziendaNickName === $persona->getAzienda()->getNickName() ) {
                $item++ ;
                $sheet->getCell('A'.sprintf('%s',$row))->setValue($persona->getSurname());
                $spreadsheet->getSheet($indexsheet)->getStyle('A'.sprintf('%s',$row))->applyFromArray($styleArray->rowPrimary());
                $sheet->getCell('B'.sprintf('%s',$row))->setValue($persona->getName());
                $spreadsheet->getSheet($indexsheet)->getStyle('B'.sprintf('%s',$row))->applyFromArray($styleArray->rowPrimary());
                $sheet->getCell('C'.sprintf('%s',$row))->setValue($persona->getGender());
                $spreadsheet->getSheet($indexsheet)->getStyle('C'.sprintf('%s',$row))->applyFromArray($styleArray->rowPrimary());
                $sheet->getCell('D'.sprintf('%s',$row))->setValue($persona->getFiscalCode());
                $spreadsheet->getSheet($indexsheet)->getStyle('D'.sprintf('%s',$row))->applyFromArray($styleArray->rowPrimary());
                $sheet->getCell('E'.sprintf('%s',$row))->setValue($persona->getBirthday()->format('d/m/Y'));
                $spreadsheet->getSheet($indexsheet)->getStyle('E'.sprintf('%s',$row))->applyFromArray($styleArray->rowPrimary());
                if ($persona->getIsPartner() === true ) { $state = 1 ;} else { $state = 0; }
                $sheet->getCell('F'.sprintf('%s',$row))->setValue($state);
                $spreadsheet->getSheet($indexsheet)->getStyle('F'.sprintf('%s',$row))->applyFromArray($styleArray->rowPrimary());
                $spreadsheet->getSheet($indexsheet)->getStyle('F'.sprintf('%s',$row))->applyFromArray($styleArray->alignHCenter());
        
                $sheet->getCell('G'.sprintf('%s',$row))->setValue(' '.$persona->getMobile());
                $spreadsheet->getSheet($indexsheet)->getStyle('G'.sprintf('%s',$row))->applyFromArray($styleArray->rowSuccess());
                $spreadsheet->getSheet($indexsheet)->getStyle('G'.sprintf('%s',$row))->applyFromArray($styleArray->alignHCenter());
                $sheet->getCell('H'.sprintf('%s',$row))->setValue($persona->getEmail());
                $spreadsheet->getSheet($indexsheet)->getStyle('H'.sprintf('%s',$row))->applyFromArray($styleArray->rowSuccess());
                $sheet->getCell('I'.sprintf('%s',$row))->setValue(' '.$persona->getPhone());
                $spreadsheet->getSheet($indexsheet)->getStyle('I'.sprintf('%s',$row))->applyFromArray($styleArray->rowSuccess());
                $spreadsheet->getSheet($indexsheet)->getStyle('I'.sprintf('%s',$row))->applyFromArray($styleArray->alignHCenter());
                $sheet->getCell('J'.sprintf('%s',$row))->setValue($persona->getAddress());
                $spreadsheet->getSheet($indexsheet)->getStyle('J'.sprintf('%s',$row))->applyFromArray($styleArray->rowSuccess());
                $sheet->getCell('K'.sprintf('%s',$row))->setValue($persona->getZipCode());
                $spreadsheet->getSheet($indexsheet)->getStyle('K'.sprintf('%s',$row))->applyFromArray($styleArray->rowSuccess());
                $sheet->getCell('L'.sprintf('%s',$row))->setValue($persona->getCity());
                $spreadsheet->getSheet($indexsheet)->getStyle('L'.sprintf('%s',$row))->applyFromArray($styleArray->rowSuccess());
                $sheet->getCell('M'.sprintf('%s',$row))->setValue($persona->getProvincia());
                $spreadsheet->getSheet($indexsheet)->getStyle('M'.sprintf('%s',$row))->applyFromArray($styleArray->rowSuccess());
                $sheet->getCell('N'.sprintf('%s',$row))->setValue($persona->getAreaGeografica());
                $spreadsheet->getSheet($indexsheet)->getStyle('N'.sprintf('%s',$row))->applyFromArray($styleArray->rowSuccess());

                $sheet->getCell('O'.sprintf('%s',$row))->setValue(' ');
                $sheet->getCell('P'.sprintf('%s',$row))->setValue($persona->getMatricola());
                $spreadsheet->getSheet($indexsheet)->getStyle('P'.sprintf('%s',$row))->applyFromArray($styleArray->rowCoral());
                $spreadsheet->getSheet($indexsheet)->getStyle('P'.sprintf('%s',$row))->applyFromArray($styleArray->alignHCenter());
                $sheet->getCell('Q'.sprintf('%s',$row))->setValue(' ');
                if ($persona->getIsEnforce() === true ) { $state = 'SI' ;} else { $state = 'NO'; }
                $sheet->getCell('R'.sprintf('%s',$row))->setValue($state);
                $spreadsheet->getSheet($indexsheet)->getStyle('R'.sprintf('%s',$row))->applyFromArray($styleArray->rowWarning());
                $spreadsheet->getSheet($indexsheet)->getStyle('R'.sprintf('%s',$row))->applyFromArray($styleArray->alignHCenter());
                if ($persona->getIsInvalid() === true ) { $state = 1 ;} else { $state = 0; }
                $sheet->getCell('S'.sprintf('%s',$row))->setValue($state);
                $spreadsheet->getSheet($indexsheet)->getStyle('S'.sprintf('%s',$row))->applyFromArray($styleArray->rowWarning());
                $spreadsheet->getSheet($indexsheet)->getStyle('S'.sprintf('%s',$row))->applyFromArray($styleArray->alignHCenter());
                $sheet->getCell('T'.sprintf('%s',$row))->setValue($persona->getMansione());
                $spreadsheet->getSheet($indexsheet)->getStyle('T'.sprintf('%s',$row))->applyFromArray($styleArray->rowWarning());
                $sheet->getCell('U'.sprintf('%s',$row))->setValue($persona->getDateHiring()->format('d/m/Y'));
                $spreadsheet->getSheet($indexsheet)->getStyle('U'.sprintf('%s',$row))->applyFromArray($styleArray->rowWarning());
                $sheet->getCell('V'.sprintf('%s',$row))->setValue($persona->getTipoContratto());
                $spreadsheet->getSheet($indexsheet)->getStyle('V'.sprintf('%s',$row))->applyFromArray($styleArray->rowWarning());
                $sheet->getCell('W'.sprintf('%s',$row))->setValue($persona->getLivello());
                $spreadsheet->getSheet($indexsheet)->getStyle('W'.sprintf('%s',$row))->applyFromArray($styleArray->rowWarning());
                $spreadsheet->getSheet($indexsheet)->getStyle('W'.sprintf('%s',$row))->applyFromArray($styleArray->alignHCenter());
                if ($persona->getScadenzaContratto() !== null) {
                $sheet->getCell('X'.sprintf('%s',$row))->setValue($persona->getScadenzaContratto()->format('d/m/Y'));
                 } else {$sheet->getCell('X'.sprintf('%s',$row))->setValue(' '); }
                $spreadsheet->getSheet($indexsheet)->getStyle('X'.sprintf('%s',$row))->applyFromArray($styleArray->rowWarning());
                if ($persona->getDateDismissal() !== null) {
                $sheet->getCell('Y'.sprintf('%s',$row))->setValue($persona->getDateDismissal()->format('d/m/Y'));
                 } else {$sheet->getCell('Y'.sprintf('%s',$row))->setValue(' '); }
                $spreadsheet->getSheet($indexsheet)->getStyle('Y'.sprintf('%s',$row))->applyFromArray($styleArray->rowWarning());
                if ($persona->getCantiere() !== null) {
                $sheet->getCell('Z'.sprintf('%s',$row))->setValue($persona->getCantiere());
                 } else {$sheet->getCell('Z'.sprintf('%s',$row))->setValue('lavora su più cantieri'); }
                $spreadsheet->getSheet($indexsheet)->getStyle('Z'.sprintf('%s',$row))->applyFromArray($styleArray->rowWarning());
                $spreadsheet->getActiveSheet()->getCell('AA'.sprintf('%s',$row))
                ->setValueExplicit($persona->getFullCostHour()/100, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC );
                $spreadsheet->getActiveSheet()->getStyle('AA'.sprintf('%s',$row))->getNumberFormat()->setFormatCode('#,##0.00');
                $spreadsheet->getSheet($indexsheet)->getStyle('AA'.sprintf('%s',$row))->applyFromArray($styleArray->rowWarning());
                $sheet->getCell('AB'.sprintf('%s',$row))->setValue($persona->getCostoStraordinario()/100);
                $spreadsheet->getActiveSheet()->getStyle('AB'.sprintf('%s',$row))->getNumberFormat()->setFormatCode('#,##0.00');
                $spreadsheet->getSheet($indexsheet)->getStyle('AB'.sprintf('%s',$row))->applyFromArray($styleArray->rowWarning());
                $sheet->getCell('AC'.sprintf('%s',$row))->setValue($persona->getStringTotalHourWeek());
                $spreadsheet->getSheet($indexsheet)->getStyle('AC'.sprintf('%s',$row))->applyFromArray($styleArray->rowWarning());
                $spreadsheet->getSheet($indexsheet)->getStyle('AC'.sprintf('%s',$row))->applyFromArray($styleArray->alignHCenter());

                $sheet->getCell('AD'.sprintf('%s',$row))->setValue($persona->getIbanConto());
                $spreadsheet->getSheet($indexsheet)->getStyle('AD'.sprintf('%s',$row))->applyFromArray($styleArray->rowGreyLight());
                $sheet->getCell('AE'.sprintf('%s',$row))->setValue($persona->getIntestatarioConto());
                $spreadsheet->getSheet($indexsheet)->getStyle('AE'.sprintf('%s',$row))->applyFromArray($styleArray->rowGreyLight());

                if ($persona->getUltimaVisitaMedica() !== null) {
                $sheet->getCell('AF'.sprintf('%s',$row))->setValue($persona->getUltimaVisitaMedica()->format('d/m/Y'));
                 } else {$sheet->getCell('AF'.sprintf('%s',$row))->setValue(' '); }
                $spreadsheet->getSheet($indexsheet)->getStyle('AF'.sprintf('%s',$row))->applyFromArray($styleArray->rowInfo());
                if ($persona->getScadenzaVisitaMedica() !== null) {
                $sheet->getCell('AG'.sprintf('%s',$row))->setValue($persona->getScadenzaVisitaMedica()->format('d/m/Y'));
                 } else {$sheet->getCell('AG'.sprintf('%s',$row))->setValue(' '); }
                $spreadsheet->getSheet($indexsheet)->getStyle('AG'.sprintf('%s',$row))->applyFromArray($styleArray->rowInfo());
                if ($persona->getIsReservedVisita() === true ) { $state = 'SI' ;} else { $state = 'NO'; } 
                $sheet->getCell('AH'.sprintf('%s',$row))->setValue($state);
                $spreadsheet->getSheet($indexsheet)->getStyle('AH'.sprintf('%s',$row))->applyFromArray($styleArray->rowInfo());
                if ($persona->getDataPrevistaVisita() !== null) {
                $sheet->getCell('AI'.sprintf('%s',$row))->setValue($persona->getDataPrevistaVisita()->format('d/m/Y'));
                 } else {$sheet->getCell('AI'.sprintf('%s',$row))->setValue(' '); }
                $spreadsheet->getSheet($indexsheet)->getStyle('AI'.sprintf('%s',$row))->applyFromArray($styleArray->rowInfo());
                $sheet->getCell('AJ'.sprintf('%s',$row))->setValue($persona->getNoteVisita());
                $spreadsheet->getSheet($indexsheet)->getStyle('AJ'.sprintf('%s',$row))->applyFromArray($styleArray->rowInfo());

                $sheet->getCell('AK'.sprintf('%s',$row))->setValue($persona->getId());
                $spreadsheet->getSheet($indexsheet)->getStyle('AK'.sprintf('%s',$row))->applyFromArray($styleArray->rowDark());
                $sheet->getCell('AL'.sprintf('%s',$row))->setValue($persona->getKeyReference());
                $spreadsheet->getSheet($indexsheet)->getStyle('AL'.sprintf('%s',$row))->applyFromArray($styleArray->rowDark());
                $sheet->getCell('AM'.sprintf('%s',$row))->setValue($persona->getCreatedAt()->format('d/m/Y\ H:i:s'));
                $spreadsheet->getSheet($indexsheet)->getStyle('AM'.sprintf('%s',$row))->applyFromArray($styleArray->rowDark());
                
                $row++ ;
            }
        }

        for ($ic=0; $ic < count($col); $ic++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col[$ic])->setAutoSize(true); 
        }
            // crea il file
            $writer = new Xlsx($spreadsheet);
            $filename = $aziendaNickName.'_elenco_personale_'.date_create()->format('Y_m_d\TH_i_s').'.xlsx';
            $writer->save('downloads/excelExport/'.$filename);
        
            $filesystem = new Filesystem();
            $pathfile = 'downloads/excelExport/'.$filename;
            $link = '<a href="'.$pathfile.'" download> Clicca qui per scaricarlo</a>';

        }
        // risultati   
        if ($item > 0 ) {   
            $success =  'File excel prodotto.' ; 
            $this->addFlash('success', $success.$link );     
        } else { $this->addFlash('info', 'Lista non rappresentabile con nessun risultato trovato.'); }

        // rimane sul crud attuale
        $crud = $context->getCrud();
        $controller = $crud->getControllerFqcn();
        $action     = $crud->getCurrentAction();
        $url = $this->adminUrlGenerator
        ->setController($controller)
        ->setAction('index')
        ->generateUrl();

        return (new RedirectResponse($url));

    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
          // key Piano ore cantieri
        $fullName =  $entityInstance->getFullName();
        $message = $this->verifyEntity($entityManager, $entityInstance);
        if ($message === '') {
            $entityManager->persist($entityInstance);
            $entityManager->flush();
        } else { $this->addFlash('danger', sprintf('%s Dati %s da correggere!!!', $message, $fullName )); }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $message = $this->verifyEntity($entityManager, $entityInstance);
        if ($message === '') {
            $entityManager->persist($entityInstance);
            $entityManager->flush();
        } else { $this->addFlash('danger', sprintf('%s Dati scheda persona non confermati!!!', $message)); }
    }

    private function verifyEntity(EntityManagerInterface $entityManager, $entityInstance): string
    {

         // controlla che il nominativo appartenga alla stessa azienda del cantiere
        $message = '';
        if ($entityInstance->getCantiere() !== null) {
            $recordcantiere = $entityManager->getRepository(Cantieri::class)->findOneBy(['id'=> $entityInstance->getCantiere()->getId()]);
            if ( $recordcantiere !== null) {
            $azienda_persona = $entityInstance->getAzienda()->getId();
            $azienda_cantiere = $recordcantiere->getAzienda()->getId();
            if ($azienda_persona === $azienda_cantiere ) {
                $message = '';
            } else { $message = 'L\'azienda del cantiere non corrisponde all\'azienda della persona.</br>'; }
            }
        }
        return $message ;
    }



    public function configureFields(string $pageName): iterable
    {
        
            $panel1 = FormField::addPanel('INFORMAZIONI ANAGRAFICHE')->setIcon('fas fa-address-card');
            $name = TextField::new('name', 'Nome di battesimo')->addCssClass('list-group-item-primary');
            $surname = TextField::new('surname', 'Cognome')->addCssClass('list-group-item-primary');
            $fullName = TextField::new('fullName', 'Nominativo');
            $eta = TextField::new('eta', 'Età');
            $stringTotalHourWeek = TextField::new('stringTotalHourWeek', 'Totale Ore')->setTextAlign('right');
            $pianoOreCantieri = AssociationField::new('pianoOreCantieri', 'Impegni');
            $gender = ChoiceField::new('gender', 'Sesso')->setChoices(['Femmina' => 'F', 'Maschio' => 'M' ])->addCssClass('list-group-item-primary');
            $birthday = DateField::new('birthday', 'Data di nascita')->addCssClass('list-group-item-primary');
            $fiscalCode = TextField::new('fiscalCode', 'Codice Fiscale')->addCssClass('list-group-item-primary');
            $isEnforce = BooleanField::new('isEnforce', 'Attivo/Dimesso')->addCssClass('list-group-item-warning');
            $isAtt = BooleanField::new('isEnforce', 'Attivo/Dimesso')->onlyOnIndex();
            //  $photoFile = ImageField::new('photoAvatar', 'Foto')
            $photoFile = ImageField::new('photoAvatar', 'Upload Foto')
            ->setBasePath('uploads/photos')
            ->setUploadDir('public/uploads/photos')
            ->setUploadedFileNamePattern('[contenthash].[extension]');

            // $slider = TextField::new('xxxxxxxxx', 'slide')->setFormType(RangeType::class);
            //->setFormTypeOptions(['disabled' => 'true']);

            // ->setFormTypeOptions(['constraints' => [ new Image(['maxSize' => '2048k']) ] ]);
            // ->setUploadedFileNamePattern('[year]-[month]-[day]-[contenthash].[extension]')
            // ->setFormTypeOption('multiple', true);
            $panelContact = FormField::addPanel('DATI CONTATTO')->setIcon('fas fa-address-book');
            $phone = TelephoneField::new('phone', 'Tel. abitazione')->addCssClass('list-group-item-success');
            $mobile = TelephoneField::new('mobile', 'Cellulare')->addCssClass('list-group-item-success');
            $email = EmailField::new('email', 'E-mail')->addCssClass('list-group-item-success');
            $address = TextField::new('address', 'Indirizzo')->addCssClass('list-group-item-success');
            $city = TextField::new('city', 'Città')->addCssClass('list-group-item-success');
            $zipCode = TextField::new('zipCode', 'Codice Avviamento Postale')->addCssClass('list-group-item-success');
            $provincia = AssociationField::new('provincia', 'Provincia')->addCssClass('list-group-item-success')
                ->setFormTypeOptions([
                'query_builder' => function (ProvinceRepository $pr) {
                    return $pr->createQueryBuilder('p')
                        ->orderBy('p.name', 'ASC');
                },
                 ])->setRequired(true)->setCustomOptions(array('widget' => 'native'));
            
            $azienda = AssociationField::new('azienda', 'Azienda')->addCssClass('list-group-item-warning')
            ->setFormTypeOptions([
                'query_builder' => function (AziendeRepository $az) {
                    return $az->createQueryBuilder('az')
                        ->orderBy('az.nickName', 'ASC');
                },
                 ])->setRequired(true)->setCustomOptions(array('widget' => 'native'));
            $cant = AssociationField::new('cantiere', 'Cantiere')->onlyOnIndex();
            $cantiere = AssociationField::new('cantiere', 'Cantiere')->addCssClass('list-group-item-warning')
            ->setFormTypeOptions([
                'query_builder' => function (CantieriRepository $ca) {
                    return $ca->createQueryBuilder('c')
                        ->orderBy('c.nameJob', 'ASC');
                },
                 ])
            ->setHelp('<mark><b>Indicare solo nel caso la persona lavori prevalentemente per un unico Cantiere. Per più cantieri una volta inserita la persona utilizzare la funzione [Piano Ore Cantieri]</b></mark>');


            $collapse = false ;
         
            $panelPortrait = FormField::addPanel('FOTO RITRATTO')->setIcon('fas fa-id-badge')->renderCollapsed($collapse);
            $imagePortrait = TextField::new('imageVichFile', 'Ritratto')->setFormType(VichImageType::class)
            ->setFormTypeOptions(['constraints' => [ new Image(['maxSize' => '2048k']) ] , 'allow_delete' => false] );

            $panel2 = FormField::addPanel('DATI LAVORATIVI')->setIcon('fas fa-sitemap');
            $isPartner = BooleanField::new('isPartner', 'Socio')->addCssClass('list-group-item-primary');
            $isInvalid = BooleanField::new('isInvalid', 'Diversamente abile')->addCssClass('list-group-item-warning');
            $areaGeografica = AssociationField::new('areaGeografica', 'Area/Zona geografica')->addCssClass('list-group-item-success');
            $tipoContratto = ChoiceField::new('tipoContratto', 'Tipo Contratto')->addCssClass('list-group-item-warning')->setChoices(['Indeterminato' => 'I', 'Determinato' => 'D', 'Stagionale' => 'T' ]) ;
            $scadenzaContratto = DateField::new('scadenzaContratto', 'Data scadenza Contratto')->addCssClass('list-group-item-warning')->setHelp('Indicare solo se tipo contratto a tempo Determinato o Stagionale');
            $livello = TextField::new('livello', 'Livello retributivo')->addCssClass('list-group-item-warning') ;   
            $mans = AssociationField::new('mansione', 'Mansione')->onlyOnIndex();
            $mansione = AssociationField::new('mansione', 'Mansione')->addCssClass('list-group-item-warning')
            ->setFormTypeOptions([
                'query_builder' => function (MansioniRepository $ma) {
                    return $ma->createQueryBuilder('m')
                        ->orderBy('m.mansioneName', 'ASC');
                },
                 ]);
            $comboAddr = TextField::new('combineAddress')
            ->formatValue(
                function ($value) {
                    return sprintf(
                        '<a href="https://www.google.com/maps/place/%s" target="_blank">%s</a>',
                        $value,
                        $value
                    );
                }
            )
            ->renderAsHtml(true)->setLabel('google Maps');

            $panel4 = FormField::addPanel('VISITE MEDICHE')->setIcon('fas fa-user-md');
            $ultimaVisitaMedica = DateField::new('ultimaVisitaMedica', 'Data ultima visita medica')->addCssClass('list-group-item-info');
            $scadenzaVisitaMedica = DateField::new('scadenzaVisitaMedica', 'Data scadenza visita medica')->addCssClass('list-group-item-info');;
            $isReservedVisita = BooleanField::new('isReservedVisita', 'Visita medica prenotata')->addCssClass('list-group-item-info');;
            $dataPrevistaVisita = DateField::new('dataPrevistaVisita', 'Data pianificata visita medica')->addCssClass('list-group-item-info');;
            $noteVisita = TextareaField::new('noteVisita', 'Annotazioni visite mediche')->addCssClass('list-group-item-info'); ;  
           
//  
            $panel3 = FormField::addPanel('DATI/DOCUMENTI PERSONALI')->setIcon('fas fa-file-pdf');
            $cvFile = ImageField::new('curriculumVitae', 'Upload Curriculum')
            ->setBasePath('uploads/files/personale/cv')
            ->setUploadDir('public/uploads/files/personale/cv')
            ->setUploadedFileNamePattern('[year]-[month]-[day]-[contenthash].[extension]')
            ->setHelp('Inserire file tipo pdf');
            // ->setFormTypeOptions(['constraints' => [ new File(['maxSize' => '1024k']) ] ])
            $cvPdf = TextField::new('curriculumVitae')->setTemplatePath('admin/personale/cv.html.twig');
            // $cvPdf = UrlField::new('cvPathPdf', 'Curriculum Vitae');  
            // TRATTATO COME LINK ad una nuova  scheda del browser, definita proprietà cvPathPdf su entità personale
            $collectionDoc = CollectionField::new('documentiPersonale', 'Documenti/Certificazioni')
            ->setEntryType(DocumentiPersonaleType::class)->setHelp('<mark>Caricare file tipo pdf o immagini ( max. 3MB)</mark>');
            $collectionDocView = CollectionField::new('documentiPersonale', 'Documenti/Certificazioni')
            ->setTemplatePath('admin/personale/documenti.html.twig');
            $matr = TextField::new('matricola', 'Id Matr.')->onlyOnIndex();
            $matricola = TextField::new('matricola', 'Codice Matricola')->addCssClass('list-group-item-warning')->setHelp('Inserire solo numeri - (verrà formattata con zeri a sinistra).');
            $fullCostHour = MoneyField::new('fullCostHour', 'Costo orario lordo')->setNumDecimals(2)->setCurrency('EUR')->setHelp('Indicare il costo orario comprensivo di ferie/tfr ')->addCssClass('list-group-item-warning');
            $costoStraordinario = MoneyField::new('costoStraordinario', 'Costo orario straordinario')->setNumDecimals(2)->setCurrency('EUR')->setHelp('Indicare il costo orario straordinario')->addCssClass('list-group-item-warning');
            $planHourWeek = ArrayField::new('planHourWeek', 'Ore settimanali')->setHelp('<mark><b>Inserire 7 numeri intesi come ore intere dal lunedì alla domenica, se è necessario indicare la mezz\'ora inserire .5  (usare il punto, non la virgola)</b></mark>')->addCssClass('list-group-item-warning');
            $planHW = ArrayField::new('planHourWeek', 'Ore settimanali')->onlyOnIndex();
            $dateHiring = DateField::new('dateHiring', 'Data di assunzione')->setRequired(true)->addCssClass('list-group-item-warning');
            $dateDismissal = DateField::new('dateDismissal', 'Dimesso il')->addCssClass('list-group-item-warning');
            $ibanConto = TextField::new('ibanConto', 'Conto Bancario (IBAN)')->setHelp('Per bonifici inserire le coordinate bancarie (senza spazi)');
            $intestatarioConto = TextField::new('intestatarioConto', 'Intestatario Conto')->setHelp('Inserire il nome intestatario se diverso dal nominativo della scheda personale');
            $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
            $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true'])->addCssClass('list-group-item-dark');
            $keyReference = TextField::new('keyReference', 'Chiave registrazione')->setFormTypeOptions(['disabled' => 'true'])->addCssClass('list-group-item-dark');
            $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true'])->addCssClass('list-group-item-dark');

            if (Crud::PAGE_INDEX === $pageName) {
                return [$matr, $fullName, $photoFile, $eta, $dateDismissal,  $cant, $mans, $pianoOreCantieri, $planHW, $stringTotalHourWeek ];
            } elseif (Crud::PAGE_DETAIL === $pageName) {
                return [$panel1, $name, $surname, $gender, $fiscalCode, $birthday, $isPartner, $panelPortrait, $photoFile, $panelContact, $mobile, $email, $phone, $address, $zipCode, $city, $provincia, $comboAddr, $areaGeografica, $panel2, $azienda, $isEnforce, $matricola, $isInvalid, $mansione, $dateHiring, $tipoContratto, $livello, $scadenzaContratto, $dateDismissal, $cantiere, $fullCostHour, $costoStraordinario, $planHourWeek, $panel3, $cvPdf, $collectionDocView, $ibanConto, $intestatarioConto, $panel4, $ultimaVisitaMedica, $scadenzaVisitaMedica, $isReservedVisita, $dataPrevistaVisita, $noteVisita, $panel_ID, $id, $keyReference, $createdAt ];
            } elseif (Crud::PAGE_NEW === $pageName) {
                return [$panel1, $name, $surname, $gender, $fiscalCode, $birthday, $isPartner, $panelContact, $mobile, $email, $phone, $address, $zipCode, $city, $provincia, $areaGeografica, $panelPortrait, $photoFile, $panel2, $azienda, $isEnforce, $matricola, $isInvalid, $mansione, $dateHiring, $tipoContratto, $livello, $scadenzaContratto, $dateDismissal,  $cantiere, $fullCostHour, $costoStraordinario, $planHourWeek, $panel3, $cvFile, $collectionDoc, $ibanConto, $intestatarioConto, $panel4, $ultimaVisitaMedica, $scadenzaVisitaMedica, $isReservedVisita, $dataPrevistaVisita, $noteVisita ];
            } elseif (Crud::PAGE_EDIT === $pageName) {
                return [$panel1, $name, $surname, $gender, $fiscalCode, $birthday, $isPartner, $panelContact, $mobile, $email, $phone, $address, $zipCode, $city, $provincia, $areaGeografica, $panelPortrait, $photoFile, $imagePortrait, $panel2, $azienda, $isEnforce, $matricola, $isInvalid, $mansione, $dateHiring, $tipoContratto, $livello, $scadenzaContratto, $dateDismissal, $cantiere, $fullCostHour, $costoStraordinario, $planHourWeek, $panel3, $cvFile, $collectionDoc, $ibanConto, $intestatarioConto, $panel4, $ultimaVisitaMedica, $scadenzaVisitaMedica, $isReservedVisita, $dataPrevistaVisita, $noteVisita, $panel_ID, $id, $keyReference, $createdAt];
            }
    }
    
}
