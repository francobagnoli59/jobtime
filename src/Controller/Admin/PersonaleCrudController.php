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

use App\Service\CsvService;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
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
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Image;
use Doctrine\ORM\EntityManagerInterface;

// use Symfony\Component\Form\Extension\Core\Type\RangeType;

    

class PersonaleCrudController extends AbstractCrudController
{

 

    /**
     * @var AdminUrlGenerator
     */
    private AdminUrlGenerator $adminUrlGenerator;

    private CsvService $csvService;

    public function __construct(EntityManagerInterface $entityManager,  AdminUrlGenerator $adminUrlGenerator, CsvService $csvService ) 
    {
    $this->entityManager = $entityManager;
    $this->adminUrlGenerator = $adminUrlGenerator;
    $this->csvService = $csvService;
    }

    public static function getEntityFqcn(): string
    {
        return Personale::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsAsDropdown()
            ->setEntityLabelInSingular('Personale')
            ->setEntityLabelInPlural('Personale')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Personale')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Personale $surname) => (string) $surname)
            ->setPageTitle(Crud::PAGE_EDIT, fn (Personale $namefull) => sprintf('Modifica scheda dati di <b>%s</b>', $namefull->getFullName()))
            ->setPageTitle(Crud::PAGE_NEW, 'Crea scheda nuovo personale')
            ->setSearchFields(['id', 'name', 'surname', 'gender', 'birthday'])
            ->setDefaultSort(['surname' => 'ASC', 'name' => 'ASC'])
            ;
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
            ->add(BooleanFilter::new('isEnforce', 'In forza/assunto'))
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
          
    }
 
    public function configureActions(Actions $actions): Actions
    {
       
        $view_orelavorate = Action::new('ViewOreLavorate', 'Vedi Ore lavorate', 'fa fa-clock')
        ->linkToCrudAction('ViewOreLavorate');
        $view_pianoorecantieri = Action::new('ViewPianoOreCantieri', 'Piano Ore Cantieri', 'fa fa-clipboard-list')
        ->linkToCrudAction('ViewPianoOreCantieri')->displayIf(fn ($entity) => !$entity->getCantiere()
        ) ;
        $export = Action::new('export', 'Esporta lista')
        ->setIcon('fa fa-download')
        ->linkToCrudAction('export')
        ->setCssClass('btn')
        ->createAsGlobalAction();

        return $actions
            // ...
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $view_orelavorate)->add(Crud::PAGE_EDIT, $view_orelavorate)
            ->add(Crud::PAGE_INDEX, $view_pianoorecantieri)
            ->add(Crud::PAGE_INDEX, $export)
           // ->add(Crud::PAGE_DETAIL,)
            ->add(Crud::PAGE_EDIT,  Action::INDEX )
            ->add(Crud::PAGE_NEW,   Action::INDEX )

            ->update(Crud::PAGE_INDEX, Action::EDIT,
             fn (Action $action) => $action->setIcon('fa fa-edit')->setHtmlAttributes(['title' => 'Modifica']))
            ->update(Crud::PAGE_INDEX, Action::DELETE,
             fn (Action $action) => $action->setIcon('fa fa-trash')->setHtmlAttributes(['title' => 'Elimina']))
            ->update(Crud::PAGE_INDEX, Action::DETAIL,
             fn (Action $action) => $action->setIcon('fa fa-eye')->setHtmlAttributes(['title' => 'Vedi scheda']))
        ;
    }

    public function ViewOreLavorate(AdminContext $context)
    {
        $personale = $context->getEntity()->getInstance();

       /*  $ds = new \Datetime('-20 day');
        $d1 = $ds->format('YmgHi');           HO PROVATO IN PIU' MODI MA LA COMPONENTE EA3 MI DICE CHE NON TROVA L?INDEX VALUE
        $de = new \Datetime('-1 day');
        $d2 = $de->format('YmgHi');
        ->set('filters[giorno][comparison]', 'between')
            ->set('filters[giorno][value]', $d1);
            ->set('filters[giorno][value2]', $d2);
            NEMMENO COSI' FUNZIONA
         ->set('filters[giorno][comparison]', '>')
            ->set('filters[giorno][value]', $personale->getBirthday()->format('Y-m-d'));
 */
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

    public function export(Request $request)
    {
        $context = $request->attributes->get(EA::CONTEXT_REQUEST_ATTRIBUTE);
        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
        $filters = $this->get(FilterFactory::class)->create($context->getCrud()->getFiltersConfig(), $fields, $context->getEntity());
        $listpersonale = $this->createIndexQueryBuilder($context->getSearch(), $context->getEntity(), $fields, $filters)
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($listpersonale as $persona) {
            $data[] = $persona->getExportData();
        }
        return $this->csvService->export($data, 'export_personale_'.date_create()->format('d-m-y').'.csv');
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
            $name = TextField::new('name', 'Nome di battesimo');
            $surname = TextField::new('surname', 'Cognome');
            $fullName = TextField::new('fullName', 'Nominativo');
            $eta = TextField::new('eta', 'Età');
            $stringTotalHourWeek = TextField::new('stringTotalHourWeek', 'Totale Ore')->setTextAlign('right');
            $pianoOreCantieri = AssociationField::new('pianoOreCantieri', 'Impegni');
            $gender = ChoiceField::new('gender', 'Sesso')->setChoices(['Femmina' => 'F', 'Maschio' => 'M' ]);
            $birthday = DateField::new('birthday', 'Data di nascita');
            $fiscalCode = TextField::new('fiscalCode', 'Codice Fiscale');
            $isEnforce = BooleanField::new('isEnforce', 'In forza/assunto');
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
            $phone = TelephoneField::new('phone', 'Tel. abitazione');
            $mobile = TelephoneField::new('mobile', 'Cellulare');
            $email = EmailField::new('email', 'E-mail');
            $address = TextField::new('address', 'Indirizzo');
            $city = TextField::new('city', 'Città');
            $zipCode = TextField::new('zipCode', 'Codice Avviamento Postale');
            $provincia = AssociationField::new('provincia', 'Provincia')
                ->setFormTypeOptions([
                'query_builder' => function (ProvinceRepository $pr) {
                    return $pr->createQueryBuilder('p')
                        ->orderBy('p.name', 'ASC');
                },
                 ])->setRequired(true)->setCustomOptions(array('widget' => 'native'));
            $azienda = AssociationField::new('azienda', 'Azienda')
            ->setFormTypeOptions([
                'query_builder' => function (AziendeRepository $az) {
                    return $az->createQueryBuilder('az')
                        ->orderBy('az.nickName', 'ASC');
                },
                 ])->setRequired(true)->setCustomOptions(array('widget' => 'native'));
    
            $cantiere = AssociationField::new('cantiere', 'Cantiere')
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
            $isPartner = BooleanField::new('isPartner', 'Socio');
            $isInvalid = BooleanField::new('isInvalid', 'Diversamente abile');
            $areaGeografica = AssociationField::new('areaGeografica', 'Area/Zona geografica');
            $tipoContratto = ChoiceField::new('tipoContratto', 'Tipo Contratto')->setChoices(['Indeterminato' => 'I', 'Determinato' => 'D', 'Stagionale' => 'T' ]) ;
            $scadenzaContratto = DateField::new('scadenzaContratto', 'Data scadenza Contratto')->setHelp('Indicare solo se tipo contratto a tempo Determinato o Stagionale');
            $livello = TextField::new('livello', 'Livello retributivo') ;   
            $mansione = AssociationField::new('mansione', 'Mansione')
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
            $ultimaVisitaMedica = DateField::new('ultimaVisitaMedica', 'Data ultima visita medica');
            $scadenzaVisitaMedica = DateField::new('scadenzaVisitaMedica', 'Data scadenza visita medica');
            $isReservedVisita = BooleanField::new('isReservedVisita', 'Visita medica prenotata');
            $dataPrevistaVisita = DateField::new('dataPrevistaVisita', 'Data pianificata visita medica');
            $noteVisita = TextareaField::new('noteVisita', 'Annotazioni visite mediche') ;  
           
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
            $matricola = TextField::new('matricola', 'Codice Matricola')->setHelp('Inserire solo numeri - (verrà formattata con zeri a sinistra).');
            $fullCostHour = MoneyField::new('fullCostHour', 'Costo orario lordo')->setNumDecimals(2)->setCurrency('EUR')->setHelp('Indicare il costo orario comprensivo di ferie/tfr ');
            $costoStraordinario = MoneyField::new('costoStraordinario', 'Costo orario straordinario')->setNumDecimals(2)->setCurrency('EUR')->setHelp('Indicare il costo orario straordinario');
            $planHourWeek = ArrayField::new('planHourWeek', 'Ore settimanali')->setHelp('<mark><b>Inserire 7 numeri intesi come ore intere dal lunedì alla domenica, se è necessario indicare la mezz\'ora inserire .5  (usare il punto, non la virgola)</b></mark>');
            $dateHiring = DateField::new('dateHiring', 'Data di assunzione')->setRequired(true);
            $dateDismissal = DateField::new('dateDismissal', 'Data di licenziamento');
            $ibanConto = TextField::new('ibanConto', 'Conto Bancario (IBAN)')->setHelp('Per bonifici inserire le coordinate bancarie (senza spazi)');
            $intestatarioConto = TextField::new('intestatarioConto', 'Intestatario Conto')->setHelp('Inserire il nome intestatario se diverso dal nominativo della scheda personale');
            $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
            $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
            $keyReference = TextField::new('keyReference', 'Chiave registrazione')->setFormTypeOptions(['disabled' => 'true']);
            $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);

            if (Crud::PAGE_INDEX === $pageName) {
                return [$id, $fullName,  $gender, $photoFile, $isEnforce, $isPartner, $azienda, $eta, $cantiere, $pianoOreCantieri, $planHourWeek, $stringTotalHourWeek ];
            } elseif (Crud::PAGE_DETAIL === $pageName) {
                return [$panel1, $name, $surname, $gender, $fiscalCode, $birthday, $isPartner, $panelPortrait, $photoFile, $panelContact, $mobile, $email, $phone, $address, $zipCode, $city, $provincia, $comboAddr, $areaGeografica, $panel2, $azienda, $isEnforce, $matricola, $isInvalid, $mansione, $dateHiring, $tipoContratto, $livello, $scadenzaContratto, $dateDismissal, $cantiere, $fullCostHour, $costoStraordinario, $planHourWeek, $panel3, $cvPdf, $collectionDocView, $ibanConto, $intestatarioConto, $panel4, $ultimaVisitaMedica, $scadenzaVisitaMedica, $isReservedVisita, $dataPrevistaVisita, $noteVisita, $panel_ID, $id, $keyReference, $createdAt ];
            } elseif (Crud::PAGE_NEW === $pageName) {
                return [$panel1, $name, $surname, $gender, $fiscalCode, $birthday, $isPartner, $panelContact, $mobile, $email, $phone, $address, $zipCode, $city, $provincia, $areaGeografica, $panelPortrait, $photoFile, $panel2, $azienda, $isEnforce, $matricola, $isInvalid, $mansione, $dateHiring, $tipoContratto, $livello, $scadenzaContratto, $dateDismissal,  $cantiere, $fullCostHour, $costoStraordinario, $planHourWeek, $panel3, $cvFile, $collectionDoc, $ibanConto, $intestatarioConto, $panel4, $ultimaVisitaMedica, $scadenzaVisitaMedica, $isReservedVisita, $dataPrevistaVisita, $noteVisita ];
            } elseif (Crud::PAGE_EDIT === $pageName) {
                return [$panel1, $name, $surname, $gender, $fiscalCode, $birthday, $isPartner, $panelContact, $mobile, $email, $phone, $address, $zipCode, $city, $provincia, $areaGeografica, $panelPortrait, $photoFile, $imagePortrait, $panel2, $azienda, $isEnforce, $matricola, $isInvalid, $mansione, $dateHiring, $tipoContratto, $livello, $scadenzaContratto, $dateDismissal, $cantiere, $fullCostHour, $costoStraordinario, $planHourWeek, $panel3, $cvFile, $collectionDoc, $ibanConto, $intestatarioConto, $panel4, $ultimaVisitaMedica, $scadenzaVisitaMedica, $isReservedVisita, $dataPrevistaVisita, $noteVisita, $panel_ID, $id, $keyReference, $createdAt];
            }
    }
    
}
