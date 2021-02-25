<?php

namespace App\Controller\Admin;

use App\Entity\Cantieri;
use App\Admin\Field\MapField;
use App\Form\DocumentiCantieriType;
use App\Repository\ProvinceRepository;
use App\Repository\AziendeRepository;
use App\Repository\ClientiRepository;
use App\Repository\RegoleFatturazioneRepository;
use App\Repository\CategorieServiziRepository;
use App\Repository\DocumentiCantieriRepository;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

class CantieriCrudController extends AbstractCrudController
{
     
    public static function getEntityFqcn(): string
    {
        return Cantieri::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
    
        return $crud
            ->setEntityLabelInSingular('Cantiere')
            ->setEntityLabelInPlural('Cantieri')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Cantieri')
            //->setPageTitle(Crud::PAGE_EDIT, 'Modifica dati di cantiere')
            //->setPageTitle(Crud::PAGE_DETAIL, 'Visualizza dati di cantiere')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Cantieri $nameJob) => (string) $nameJob)
            ->setPageTitle(Crud::PAGE_EDIT, fn (Cantieri $namefull) => sprintf('Modifica Cantiere <b>%s</b>', $namefull->getNameJob()))

            ->setPageTitle(Crud::PAGE_NEW, 'Crea nuovo cantiere')
            ->setSearchFields(['id', 'nameJob', 'city', 'descriptionJob', 'mapsGoogle', 'distance', 'hourlyRate', 'flatRate', 'planningHours', 'planningCostMaterial'])
            ->showEntityActionsAsDropdown();
        }
    
    public function configureActions(Actions $actions): Actions
    {
           //   
     /*    $viewInvoice = Action::new('viewinvoice', 'View Invoice', 'fas fa-file-invoice')
        ->linktoRoute('homepage')
        ->setHtmlAttributes(['title' => 'Vedi Fattura'])
        ->displayIf(fn ($entity) => $entity->getIsPublic()
        ); */
    
          
        return $actions
                // ...
              
                ->add(Crud::PAGE_INDEX, Action::DETAIL)
                // ->add(Crud::PAGE_INDEX, $viewInvoice)
               // ->add(Crud::PAGE_DETAIL,)
                ->add(Crud::PAGE_EDIT,  Action::DELETE )
                ->add(Crud::PAGE_NEW,   Action::INDEX )
    
                ->update(Crud::PAGE_INDEX, Action::EDIT,
                 fn (Action $action) => $action->setIcon('fa fa-edit') )
                ->update(Crud::PAGE_INDEX, Action::DELETE,
                 fn (Action $action) => $action->setIcon('fa fa-trash') )
                ->update(Crud::PAGE_INDEX, Action::DETAIL,
                 fn (Action $action) => $action->setIcon('fa fa-eye') )
            ;
    }


    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('azienda')
            ->add(BooleanFilter::new('isPublic', 'Accetta feedback'))
            ->add(DateTimeFilter::new('dateStartJob', 'Data inizio lavoro'))
            ->add(DateTimeFilter::new('dateEndJob', 'Data fine lavoro'))
            ->add('city')
            ->add(EntityFilter::new('provincia') 
            ->setFormTypeOption('value_type_options.query_builder', 
                static fn(ProvinceRepository $pr) => $pr->createQueryBuilder('provincia')
                        ->orderBy('provincia.name', 'ASC') )
            );
    }

  
    /* public function createEntity(string $entityFqcn)
    {
    $foo = new $entityFqcn();
    $foo->getCity($this->getCantieri());

    return $foo;
    } */

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('INFORMAZIONI DI BASE')->setIcon('fas fa-building');
        $nameJob = TextField::new('nameJob', 'Nome Cantiere')->setHelp('<mark><b>Indicare un nome che identifica univocamente il cantiere</b></mark>');
        $city = TextField::new('city', 'Città');
        $provincia = AssociationField::new('provincia', 'Provincia')
                    ->setFormTypeOptions([
                    'query_builder' => function (ProvinceRepository $pr) {
                        return $pr->createQueryBuilder('p')
                            ->orderBy('p.name', 'ASC');
                    },
                ])->setRequired(true)->setCustomOptions(array('widget' => 'native'));
       /*          
               // disable sort option because of this issue
                // https://github.com/EasyCorp/EasyAdminBundle/issues/3379
                //->setSortable(false)
                ->setFormTypeOptions([
                    'query_builder' => function (ProvinceRepository $er) {
                        return $er->createQueryBuilder('p')
                            ->andWhere('p.name = :name OR p.name = :name2')
                            ->setParameter('name', 'Pisa')
                            ->setParameter('name2', 'Livorno')
                            ->orderBy('p.name', 'ASC');
                    },
                ])->setRequired(true);        */

        $isPublic = BooleanField::new('isPublic', 'Accetta feedback');
        $dateStartJob = DateField::new('dateStartJob', 'Data inizio lavoro');
        $dateEndJob = DateField::new('dateEndJob', 'Data fine lavoro');
        $descriptionJob = TextEditorField::new('descriptionJob', 'Descrizione')->setHelp('Descrivere brevemente il progetto di cantiere');
        // $mapsGoogle = UrlField::new('mapsGoogle');  FUNZIONA MA IL CAMPO E' GRANDE E LA FORM SI ALLARGA DI CONSEGUENZA
        $mapsGoogle = MapField::new('mapsGoogle', 'Localizzazione');
        $distance = IntegerField::new('distance', 'Distanza')->setHelp('Indicare la distanza A/R dalla sede in Km');
        $cliente = AssociationField::new('cliente', 'Cliente')->setHelp('Scegliere il committente del cantiere')
        ->setFormTypeOptions([
        'query_builder' => function (ClientiRepository $cl) {
            return $cl->createQueryBuilder('c')
               ->orderBy('c.name', 'ASC');     },
                             ])
        ->setCustomOptions(array('widget' => 'native'))->setRequired(true);
        $categoria = AssociationField::new('categoria', 'Categoria Servizi forniti')->setHelp('Classifica il cantiere nell\'ambito dei servizi prevalenti')
        ->setFormTypeOptions([
        'query_builder' => function (CategorieServiziRepository $cs) {
            return $cs->createQueryBuilder('c')
               ->orderBy('c.categoria', 'ASC');     },
                             ]);
        $collectionDoc = CollectionField::new('documentiCantieri', 'Contratti/Documenti')
        ->setEntryType(DocumentiCantieriType::class)->setHelp('<mark>Caricare file tipo pdf o immagini ( max. 3MB ciascuno)</mark>');
        $collectionDocView = CollectionField::new('documentiCantieri', 'Contratti/Documenti')
        ->setTemplatePath('admin/cantieri/documenti.html.twig');

        $panel2 = FormField::addPanel('PIANIFICAZIONE CANTIERE')->setIcon('fas fa-wallet')->setHelp('Inserire i dati per una corretta pianificazione');
        $azienda = AssociationField::new('azienda', 'Azienda del gruppo')->setHelp('Scegliere l\'azienda del gruppo che eroga i servizi')
            ->setFormTypeOptions([
            'query_builder' => function (AziendeRepository $az) {
                return $az->createQueryBuilder('a')
                   ->orderBy('a.nickName', 'ASC');     },
                                 ])
            ->setCustomOptions(array('widget' => 'native'))->setRequired(true);                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  
        $hourlyRate = MoneyField::new('hourlyRate', 'Tariffa Oraria')->setNumDecimals(2)->setCurrency('EUR')->setHelp('Prezzo orario di vendita in alternativa alla tariffa a corpo');
        $extraRate = MoneyField::new('extraRate', 'Tariffa Ora straordinaria')->setNumDecimals(2)->setCurrency('EUR')->setHelp('Prezzo ora straordinario, indicare se ammesso dal contratto, se 0 le ore straordinarie non saranno aggiunte come ricavo extra commessa.');
        $flatRate = MoneyField::new('flatRate', 'Prezzo a Corpo')->setNumDecimals(2)->setCurrency('EUR')->setHelp('Prezzo di vendita secondo la regola del ciclo di fatturazione, è in alternativa alla tariffa oraria, se indicato prevale sulla tariffa oraria.');
        $regolaFatturazione = AssociationField::new('regolaFatturazione', 'Ciclo di fatturazione')
            ->setFormTypeOptions([
            'query_builder' => function (RegoleFatturazioneRepository $rf) {
                return $rf->createQueryBuilder('r')
                   ->orderBy('r.billingCadence', 'ASC');     },
                                 ])
            ->setCustomOptions(array('widget' => 'native'))->setRequired(true)                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  
            ->setHelp('<mark>Si applica sul prezzo a corpo o alla tariffa oraria sul monte ore consuntivo.</mark>');
      
        $isPlanningPerson = BooleanField::new('isPlanningPerson', 'Pianificazione del personale')->setHelp('Se non attivato il lavoro si intende a consuntivo');
        // dump($regolaFatturazione) ;
        $planningHours = IntegerField::new('planningHours', 'Ore previste' )->setHelp('Indicare le ore pianificate per ciclo di fatturazione');
        $isPlanningMaterial = BooleanField::new('isPlanningMaterial', 'Pianificazione dei materiali')->setHelp('Se non attivato i materiali sono forniti dal cliente/committente');
        $planningCostMaterial = MoneyField::new('planningCostMaterial', 'Costo materiali a budget')->setNumDecimals(2)->setCurrency('EUR')->setHelp('Indicare il costo dei materiali previsti per la completa fornitura di tutto il periodo del progetto');
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);
        $commentiPubblici = AssociationField::new('commentiPubblici', 'Commenti');
                
        $typeOrderPA = ChoiceField::new('typeOrderPA', 'Tipologia appalto P.A.')->setChoices(['Nessun contratto P.A.' => 'N', 'Contratto pubblico' => 'C', 'Convenzione pubblica' => 'E', 'Ordine di acquisto' => 'O']);
        $numDocumento = TextField::new('numDocumento', 'Numero Documento')->setHelp('Si riferisce alla tipologia di appalto');
        $dateDocumento = DateField::new('dateDocumento', 'Data Documento')->setHelp('Si riferisce alla tipologia di appalto');
        $codiceCIG = TextField::new('codiceCIG', 'Codice C.I.G.')->setHelp('Codice Identificativo Gara');
        $codiceCUP = TextField::new('codiceCUP', 'Codice C.U.P.')->setHelp('Codice Unitario Progetto (CIPE)');
        $codiceIPA = TextField::new('codiceIPA', 'Identificativo univoco Ufficio')->setHelp('<mark>Se inserito prevale sul codice PA impostato nella scheda cliente Pubblica Amministrazione</mark>');
        $typeOrder = Field::new('typeOrderPA');
        // $cantiere = Cantieri::class ;
        // $collapsePA =  $cantiere::getIsNotPA('N') ; DEPRECATO
        $collapsePA = false;
        $panelPA = FormField::addPanel('CANTIERE PUBBLICA AMMINISTRAZIONE')->setIcon('fas fa-landmark')->setHelp('Inserire i dati se il committente è una pubblica amministrazione')->renderCollapsed($collapsePA);
        // dump($panelPA) ;
        if (Crud::PAGE_INDEX === $pageName) {
            return [$nameJob, $city, $mapsGoogle, $azienda, $isPublic, $commentiPubblici, $cliente, $dateStartJob, $dateEndJob];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $nameJob, $city, $provincia, $isPublic, $cliente, $dateStartJob, $dateEndJob, $categoria, $descriptionJob, $collectionDocView, $mapsGoogle, $distance, $panel2, $azienda, $hourlyRate, $extraRate, $flatRate, $regolaFatturazione, $isPlanningPerson, $planningHours, $isPlanningMaterial, $planningCostMaterial, $panelPA, $typeOrderPA, $numDocumento, $dateDocumento, $codiceCIG, $codiceCUP, $codiceIPA, $panel_ID, $id, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $nameJob, $city, $provincia, $isPublic, $cliente, $dateStartJob, $dateEndJob, $categoria, $descriptionJob, $collectionDoc, $mapsGoogle, $distance, $panel2, $azienda, $hourlyRate, $extraRate, $flatRate, $regolaFatturazione, $isPlanningPerson, $planningHours, $isPlanningMaterial, $planningCostMaterial, $panelPA, $typeOrderPA, $numDocumento, $dateDocumento, $codiceCIG, $codiceCUP,];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $nameJob, $city, $provincia, $isPublic, $cliente, $dateStartJob, $dateEndJob, $categoria, $descriptionJob, $collectionDoc, $mapsGoogle, $distance, $panel2, $azienda, $hourlyRate, $extraRate, $flatRate, $regolaFatturazione, $isPlanningPerson, $planningHours, $isPlanningMaterial, $planningCostMaterial, $panelPA, $typeOrderPA, $numDocumento, $dateDocumento, $codiceCIG, $codiceCUP, $codiceIPA, $panel_ID, $id, $createdAt];
        }
    }
}
