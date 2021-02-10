<?php

namespace App\Controller\Admin;

use App\Entity\Personale;
use App\Repository\ProvinceRepository;
use App\Repository\CantieriRepository;
use App\Repository\AziendeRepository;
 
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\Constraints\Image;
use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Component\Validator\Constraints\File;
// use Symfony\Component\Form\Extension\Core\Type\RangeType;

    

class PersonaleCrudController extends AbstractCrudController
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
            ->add(DateTimeFilter::new('birthday', 'Data di nascita'))
            ->add(DateTimeFilter::new('dateHiring', 'Data di assunzione'))
            ->add(TextFilter::new('fiscalCode', 'Codice Fiscale'))
            ->add(EntityFilter::new('provincia') 
            ->setFormTypeOption('value_type_options.query_builder', 
                static fn(ProvinceRepository $pr) => $pr->createQueryBuilder('provincia')
                        ->orderBy('provincia.name', 'ASC') )
             );
          
    }
 
    public function configureActions(Actions $actions): Actions
    {
       
        $view_orelavorate = Action::new('ViewOreLavorate', 'Vedi Ore lavorate', 'fa fa-clock')
        ->linkToCrudAction('ViewOreLavorate');
        
        return $actions
            // ...
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $view_orelavorate)->add(Crud::PAGE_EDIT, $view_orelavorate)
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

        $url = $this->adminUrlGenerator->unsetAll()
            ->setController(OreLavorateCrudController::class)
            ->setAction(Action::INDEX)
            ->set('filters[persona][comparison]', '=')
            ->set('filters[persona][value]', $personale->getId());
            return $this->redirect($url);

    }

    public function configureFields(string $pageName): iterable
    {
        
            $panel1 = FormField::addPanel('INFORMAZIONI ANAGRAFICHE')->setIcon('fas fa-address-card');
            $name = TextField::new('name', 'Nome di battesimo');
            $surname = TextField::new('surname', 'Cognome');
            $fullName = TextField::new('fullName', 'Nominativo');
            $eta = TextField::new('eta', 'Età');
            $totalHourWeek = IntegerField::new('totalHourWeek', 'Ore settimana')->setTextAlign('right');
            $gender = ChoiceField::new('gender', 'Sesso M/F')->setChoices(['Femmina' => 'F', 'Maschio' => 'M' ]);
            $birthday = DateField::new('birthday', 'Data di nascita');
            $fiscalCode = TextField::new('fiscalCode', 'Codice Fiscale');
            $isEnforce = BooleanField::new('isEnforce', 'In forza/assunto');
            //  $photoFile = ImageField::new('photoAvatar', 'Foto')
            $photoFile = ImageField::new('photoAvatar', 'Upload Foto')
            ->setBasePath('uploads\photos')
            ->setUploadDir('public\uploads\photos')
            ->setUploadedFileNamePattern('[contenthash].[extension]');

            // $slider = TextField::new('xxxxxxxxx', 'slide')->setFormType(RangeType::class);
            //->setFormTypeOptions(['disabled' => 'true']);

            // ->setFormTypeOptions(['constraints' => [ new Image(['maxSize' => '2048k']) ] ]);
            // ->setUploadedFileNamePattern('[year]-[month]-[day]-[contenthash].[extension]')
            // ->setFormTypeOption('multiple', true);
            $panelContact = FormField::addPanel('DATI CONTATTO')->setIcon('fas fa-id-card');
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
                    return $az->createQueryBuilder('p')
                        ->orderBy('az.nickName', 'ASC');
                },
                 ])->setRequired(true)->setCustomOptions(array('widget' => 'native'));
    
            $cantiere = AssociationField::new('cantiere', 'Cantiere')->setHelp('Indicare se lavora per un solo Cantiere.');

            // $linkPhoto = (function($entity) {
            // $link = $this->entityManager->getRepository(Personale::class)->find($entity->getPhotoAvatar());
            //return $link;
            // });
            $collapse = false ;
         
            $panelPortrait = FormField::addPanel('FOTO RITRATTO')->setIcon('fas fa-id-badge')->renderCollapsed($collapse);
            $imagePortrait = TextField::new('imageVichFile', 'Ritratto')->setFormType(VichImageType::class)
            ->setFormTypeOptions(['constraints' => [ new Image(['maxSize' => '2048k']) ] , 'allow_delete' => false] );

            $panel2 = FormField::addPanel('DATI RETRIBUTIVI')->setIcon('fas fa-clock');
            $cvFile = ImageField::new('curriculumVitae', 'Upload Curriculum')
            ->setBasePath('uploads\files\personale\cv')
            ->setUploadDir('public\uploads\files\personale\cv')
            ->setUploadedFileNamePattern('[year]-[month]-[day]-[contenthash].[extension]')
            ->setHelp('Inserire file tipo pdf');
            // ->setFormTypeOptions(['constraints' => [ new File(['maxSize' => '1024k']) ] ])
            $cvPdf = TextField::new('curriculumVitae')->setTemplatePath('admin/personale/cv.html.twig');
            // $cvPdf = UrlField::new('cvPathPdf', 'Curriculum Vitae');  
            // TRATTATO COME LINK ad una nuova  scheda del browser, definita proprietà cvPathPdf su entità personale
            $matricola = TextField::new('matricola', 'Codice Matricola')->setHelp('Inserire solo numeri - (verrà formattata con zeri a sinistra).');
            $fullCostHour = MoneyField::new('fullCostHour', 'Costo orario lordo')->setCurrency('EUR')->setHelp('Indicare il costo orario comprensivo di ferie/tfr ');
            $planHourWeek = ArrayField::new('planHourWeek', 'Piano ore settimanali')->setHelp('<mark><b>Inserire 7 numeri intesi come ore intere dal lunedì alla domenica, se è necessario indicare la mezz\'ora inserire .5  (usare il punto, non la virgola)</b></mark>');
            $dateHiring = DateField::new('dateHiring', 'Data di assunzione');
            $dateDismissal = DateField::new('dateDismissal', 'Data di licenziamento');
            $ibanConto = TextField::new('ibanConto', 'Conto Bancario (IBAN)')->setHelp('Per bonifici inserire le coordinate bancarie (senza spazi)');
            $intestatarioConto = TextField::new('intestatarioConto', 'Intestatario Conto')->setHelp('Inserire il nome intestatario se diverso dal nominativo della scheda personale');
            $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
            $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
            $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);

            if (Crud::PAGE_INDEX === $pageName) {
                return [$fullName,  $gender, $photoFile, $isEnforce, $azienda, $eta, $cantiere, $planHourWeek, $totalHourWeek ];
            } elseif (Crud::PAGE_DETAIL === $pageName) {
                return [$panel1, $name, $surname, $gender, $fiscalCode, $birthday, $panelPortrait, $photoFile, $panelContact, $mobile, $email, $phone, $address, $zipCode, $city, $provincia, $panel2, $cvPdf, $isEnforce, $azienda, $matricola,  $dateHiring, $dateDismissal, $ibanConto, $intestatarioConto, $cantiere, $fullCostHour, $planHourWeek, $panel_ID, $id, $createdAt ];
            } elseif (Crud::PAGE_NEW === $pageName) {
                return [$panel1, $name, $surname, $gender, $fiscalCode, $birthday, $panelContact, $mobile, $email, $phone, $address, $zipCode, $city, $provincia, $panelPortrait, $photoFile, $panel2, $cvFile, $isEnforce, $azienda, $matricola, $dateHiring, $dateDismissal, $ibanConto, $intestatarioConto, $cantiere, $fullCostHour, $planHourWeek ];
            } elseif (Crud::PAGE_EDIT === $pageName) {
                return [$panel1, $name, $surname, $gender, $fiscalCode, $birthday, $panelContact, $mobile, $email, $phone, $address, $zipCode, $city, $provincia, $panelPortrait, $photoFile, $imagePortrait, $panel2, $cvFile, $isEnforce, $azienda, $matricola, $dateHiring, $dateDismissal, $ibanConto, $intestatarioConto, $cantiere, $fullCostHour, $planHourWeek, $panel_ID, $id, $createdAt];
            }
    }
    
}
