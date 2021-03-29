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

use Doctrine\ORM\QueryBuilder;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
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
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;

class CantieriCrudController extends AbstractCrudController
{
    
    
    public static function getEntityFqcn(): string
    {
        return Cantieri::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $response = $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $azienda = $this->getUser()->getAziendadefault();
        if ($azienda !== null ) {
            $aziendaId = $azienda->getId();
        $response->andWhere('entity.azienda = '. $aziendaId);
        } else {  $response->andWhere('entity.azienda = 0'); } // così non visualizza niente
        return $response;
    }

    public function createEntity(string $entityFqcn)
    {
        $azienda = $this->getUser()->getAziendadefault();
        if ($azienda !== null ) {
            $cantiere = new Cantieri();
            $cantiere->setAzienda($azienda);
        return $cantiere;
        }
    }
  
    public function configureCrud(Crud $crud): Crud
    {
        $azienda = $this->getUser()->getAziendadefault();
        if ($azienda !== null ) {
            $aziendaNickName = $azienda->getNickName();
        } else { $aziendaNickName = '...seleziona azienda!!!'; } 

        $LabelSing = 'Cantiere '.$aziendaNickName ;
        $LabelPlur = 'Cantieri '.$aziendaNickName ;
        $LabelNew = 'Crea nuovo cantiere '.$aziendaNickName ;
        $Labellist = 'Elenco Cantieri '.$aziendaNickName ;

        return $crud
            ->setEntityLabelInSingular($LabelSing)
            ->setEntityLabelInPlural($LabelPlur)
            ->setPageTitle(Crud::PAGE_INDEX,  $Labellist)
            //->setPageTitle(Crud::PAGE_EDIT, 'Modifica dati di cantiere')
            //->setPageTitle(Crud::PAGE_DETAIL, 'Visualizza dati di cantiere')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Cantieri $nameJob) => (string) $nameJob)
            ->setPageTitle(Crud::PAGE_EDIT, fn (Cantieri $namefull) => sprintf('Modifica Cantiere <b>%s</b>', $namefull->getNameJob()))

            ->setPageTitle(Crud::PAGE_NEW, $LabelNew)
            ->setSearchFields([ 'nameJob', 'city', 'descriptionJob', 'hourlyRate', 'flatRate', 'planningHours'])
            ->setDefaultSort(['nameJob' => 'ASC'])
            ->showEntityActionsAsDropdown();
        }
    
    public function configureActions(Actions $actions): Actions
    {
 
            /*   $show_chartcantiere = Action::new('showChartCantiere', 'Vedi analisi mensile', 'fa fa-chart-bar')
              ->linkToCrudAction('showChartCantiere')->displayIf(fn ($entity) => $entity->getOrelavorate());
     */
          
        return $actions
                // ...
              
                ->add(Crud::PAGE_INDEX, Action::DETAIL)
               // ->add(Crud::PAGE_INDEX, $show_chartcantiere)
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
            // ->add('azienda')
            ->add(BooleanFilter::new('isPublic', 'Accetta feedback'))
            ->add(DateTimeFilter::new('dateStartJob', 'Data inizio lavoro'))
            ->add(DateTimeFilter::new('dateEndJob', 'Data fine lavoro'))
            ->add('city')
            ->add(EntityFilter::new('provincia') 
                ->setFormTypeOption('value_type_options.query_builder', 
                static fn(ProvinceRepository $pr) => $pr->createQueryBuilder('provincia')
                        ->orderBy('provincia.name', 'ASC') ) )
            ->add(EntityFilter::new('categoria') 
                ->setFormTypeOption('value_type_options.query_builder', 
                static fn(CategorieServiziRepository $cs) => $cs->createQueryBuilder('categoria')
                        ->orderBy('categoria.categoria', 'ASC') ) )
            ->add(EntityFilter::new('cliente') 
                ->setFormTypeOption('value_type_options.query_builder', 
                static fn(ClientiRepository $cl) => $cl->createQueryBuilder('cliente')
                        ->orderBy('cliente.name', 'ASC') ) )
            ;
                        
    }

 
    public function configureFields(string $pageName): iterable
    {

        $azienda = $this->getUser()->getAziendadefault();
        if ($azienda !== null ) {
            $statusAzienda = true ; $helpAz = '';}
            else { $statusAzienda = false ; $helpAz = 'Scegliere l\'azienda del gruppo che eroga i servizi'; }
      
        $panel1 = FormField::addPanel('INFORMAZIONI DI BASE')->setIcon('fas fa-building');
        $nameJob = TextField::new('nameJob', 'Nome Cantiere')->setHelp('<mark><b>Indicare un nome che identifica univocamente il cantiere</b></mark>')->addCssClass('list-group-item-primary');
        $i_nameJob = TextField::new('nameJob', 'Nome Cantiere');
        $city = TextField::new('city', 'Città')->addCssClass('list-group-item-primary');
        $i_provincia = AssociationField::new('provincia', 'Provincia');
        $provincia = AssociationField::new('provincia', 'Provincia')
                    ->setFormTypeOptions([
                    'query_builder' => function (ProvinceRepository $pr) {
                        return $pr->createQueryBuilder('p')
                            ->orderBy('p.name', 'ASC');
                    },
                ])->setRequired(true)->setCustomOptions(array('widget' => 'native'))->addCssClass('list-group-item-primary');
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

        $isPublic = BooleanField::new('isPublic', 'Accetta feedback')->addCssClass('list-group-item-primary');
        $i_isPublic = BooleanField::new('isPublic', 'Accetta feedback');
        $dateStartJob = DateField::new('dateStartJob', 'Data inizio lavoro')->addCssClass('list-group-item-primary');
        $dateEndJob = DateField::new('dateEndJob', 'Data fine lavoro')->addCssClass('list-group-item-primary');
        $i_dateEndJob = DateField::new('dateEndJob', 'Data fine lavoro');
        $descriptionJob = TextEditorField::new('descriptionJob', 'Descrizione')->setHelp('Descrivere brevemente il progetto di cantiere')->addCssClass('list-group-item-primary');
        // $mapsGoogle = UrlField::new('mapsGoogle');  FUNZIONA MA IL CAMPO E' GRANDE E LA FORM SI ALLARGA DI CONSEGUENZA
        $mapsGoogle = MapField::new('mapsGoogle', 'Localizzazione')->addCssClass('list-group-item-primary');
        $distance = IntegerField::new('distance', 'Distanza')->setHelp('Indicare la distanza A/R dalla sede in Km')->addCssClass('list-group-item-primary');
        $i_cliente = AssociationField::new('cliente', 'Cliente');
        $cliente = AssociationField::new('cliente', 'Cliente')->setHelp('Scegliere il committente del cantiere. (Necessario per il processo di fatturazione)')
        ->setFormTypeOptions([
        'query_builder' => function (ClientiRepository $cl) {
            return $cl->createQueryBuilder('c')
               ->orderBy('c.name', 'ASC');     },
                             ])->addCssClass('list-group-item-primary');
        //->setCustomOptions(array('widget' => 'native'))->setRequired(true);
        $i_categoria = AssociationField::new('categoria', 'Categoria Servizi forniti');
        $categoria = AssociationField::new('categoria', 'Categoria Servizi forniti')->setHelp('Classifica il cantiere nell\'ambito dei servizi prevalenti')
        ->setFormTypeOptions([
        'query_builder' => function (CategorieServiziRepository $cs) {
            return $cs->createQueryBuilder('c')
               ->orderBy('c.categoria', 'ASC');     },
                             ])->addCssClass('list-group-item-primary');
        $collectionDoc = CollectionField::new('documentiCantiere', 'Contratti/Documenti')
        ->setEntryType(DocumentiCantieriType::class)->setHelp('<mark>Caricare file tipo pdf o immagini ( max. 3MB ciascuno)</mark>')->addCssClass('list-group-item-primary');
        $collectionDocView = CollectionField::new('documentiCantiere', 'Contratti/Documenti')
        ->setTemplatePath('admin/cantieri/documenti.html.twig')->addCssClass('list-group-item-primary');
     

        $panel2 = FormField::addPanel('PIANIFICAZIONE CANTIERE')->setIcon('fas fa-wallet')->setHelp('Inserire i dati per una corretta pianificazione');
        $azienda = AssociationField::new('azienda', 'Azienda del gruppo')->setHelp($helpAz)
            ->setFormTypeOptions([
            'query_builder' => function (AziendeRepository $az) {
                return $az->createQueryBuilder('a')
                   ->orderBy('a.nickName', 'ASC');     },
                                 ])
            ->setCustomOptions(array('widget' => 'native'))->setRequired(true)->addCssClass('list-group-item-warning')->setFormTypeOptions(['disabled' => $statusAzienda]);                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  
        $hourlyRate = MoneyField::new('hourlyRate', 'Tariffa Oraria')->setNumDecimals(2)->setCurrency('EUR')->setHelp('Prezzo orario di vendita in alternativa alla tariffa a corpo')->addCssClass('list-group-item-warning');
        $extraRate = MoneyField::new('extraRate', 'Tariffa Ora straordinaria')->setNumDecimals(2)->setCurrency('EUR')->setHelp('Prezzo ora straordinario, indicare se ammesso dal contratto, se 0 le ore straordinarie non saranno aggiunte come ricavo extra commessa.')->addCssClass('list-group-item-warning');
        $flatRate = MoneyField::new('flatRate', 'Prezzo a Corpo')->setNumDecimals(2)->setCurrency('EUR')->setHelp('Prezzo di vendita secondo la regola del ciclo di fatturazione, è in alternativa alla tariffa oraria, se indicato prevale sulla tariffa oraria.')->addCssClass('list-group-item-warning');
        $i_flatRate = MoneyField::new('flatRate', 'Prezzo a Corpo')->setNumDecimals(2)->setCurrency('EUR');
        $regolaFatturazione = AssociationField::new('regolaFatturazione', 'Ciclo di fatturazione')
            ->setFormTypeOptions([
            'query_builder' => function (RegoleFatturazioneRepository $rf) {
                return $rf->createQueryBuilder('r')
                   ->orderBy('r.billingCadence', 'ASC');     },
                                 ])
            ->setCustomOptions(array('widget' => 'native'))->setRequired(true)                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  
            ->setHelp('<mark>Si applica sul prezzo a corpo o alla tariffa oraria sul monte ore consuntivo.</mark>')->addCssClass('list-group-item-warning');
      
        $isPlanningPerson = BooleanField::new('isPlanningPerson', 'Pianificazione del personale')->setHelp('Se non attivato il lavoro si intende a consuntivo')->addCssClass('list-group-item-warning');
        // dump($regolaFatturazione) ;
        $planningHours = IntegerField::new('planningHours', 'Ore previste' )->setHelp('Indicare le ore lavoro pianificate per tutto il periodo contrattuale')->addCssClass('list-group-item-warning');
        $isPlanningMaterial = BooleanField::new('isPlanningMaterial', 'Pianificazione dei materiali')->setHelp('Se non attivato i materiali sono forniti dal cliente/committente')->addCssClass('list-group-item-warning');
        $planningCostMaterial = MoneyField::new('planningCostMaterial', 'Costo materiali a budget')->setNumDecimals(2)->setCurrency('EUR')->setHelp('Indicare il costo dei materiali previsti per la completa fornitura di tutto il periodo del progetto')->addCssClass('list-group-item-warning');
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true'])->addCssClass('list-group-item-dark');
        $i_id = IntegerField::new('id', 'ID');
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true'])->addCssClass('list-group-item-dark');
        $commentiPubblici = AssociationField::new('commentiPubblici', 'Commenti');
        $i_commentiPubblici = AssociationField::new('commentiPubblici', 'Commenti');        
        $typeOrderPA = ChoiceField::new('typeOrderPA', 'Tipologia appalto P.A.')->setChoices(['Nessun contratto P.A.' => 'N', 'Contratto pubblico' => 'C', 'Convenzione pubblica' => 'E', 'Ordine di acquisto' => 'O'])->addCssClass('list-group-item-info');
        // ->allowMultipleChoices() per poter scegliere più opzioni ( non è questo il caso, ma documentato come promemoria)
        $numDocumento = TextField::new('numDocumento', 'Numero Documento')->setHelp('Si riferisce alla tipologia di appalto')->addCssClass('list-group-item-info');
        $dateDocumento = DateField::new('dateDocumento', 'Data Documento')->setHelp('Si riferisce alla tipologia di appalto')->addCssClass('list-group-item-info');
        $codiceCIG = TextField::new('codiceCIG', 'Codice C.I.G.')->setHelp('Codice Identificativo Gara')->addCssClass('list-group-item-info');
        $codiceCUP = TextField::new('codiceCUP', 'Codice C.U.P.')->setHelp('Codice Unitario Progetto (CIPE)')->addCssClass('list-group-item-info');
        $codiceIPA = TextField::new('codiceIPA', 'Identificativo univoco Ufficio')->setHelp('<mark>Se inserito prevale sul codice PA impostato nella scheda cliente Pubblica Amministrazione</mark>')->addCssClass('list-group-item-info');
        $typeOrder = Field::new('typeOrderPA')->addCssClass('list-group-item-info');
        // $cantiere = Cantieri::class ;
        // $collapsePA =  $cantiere::getIsNotPA('N') ; DEPRECATO
        $collapsePA = false;
        $panelPA = FormField::addPanel('CANTIERE PUBBLICA AMMINISTRAZIONE')->setIcon('fas fa-landmark')->setHelp('Inserire i dati se il committente è una pubblica amministrazione')->renderCollapsed($collapsePA);
        // dump($panelPA) ;
        if (Crud::PAGE_INDEX === $pageName) {
            return [$i_id, $i_nameJob, $i_provincia, $i_categoria, $i_cliente, $i_isPublic, $i_commentiPubblici, $i_dateEndJob,  $i_flatRate];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $nameJob, $city, $provincia, $isPublic, $cliente, $dateStartJob, $dateEndJob, $categoria, $descriptionJob, $collectionDocView, $mapsGoogle, $distance, $panel2, $azienda, $hourlyRate, $extraRate, $flatRate, $regolaFatturazione, $isPlanningPerson, $planningHours, $isPlanningMaterial, $planningCostMaterial, $panelPA, $typeOrderPA, $numDocumento, $dateDocumento, $codiceCIG, $codiceCUP, $codiceIPA, $panel_ID, $id, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $nameJob, $city, $provincia, $isPublic, $cliente, $dateStartJob, $dateEndJob, $categoria, $descriptionJob, $collectionDoc, $mapsGoogle, $distance, $panel2, $azienda, $hourlyRate, $extraRate, $flatRate, $regolaFatturazione, $isPlanningPerson, $planningHours, $isPlanningMaterial, $planningCostMaterial, $panelPA, $typeOrderPA, $numDocumento, $dateDocumento, $codiceCIG, $codiceCUP,];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $nameJob, $city, $provincia, $isPublic, $cliente, $dateStartJob, $dateEndJob, $categoria, $descriptionJob, $collectionDoc, $mapsGoogle, $distance, $panel2, $azienda, $hourlyRate, $extraRate, $flatRate, $regolaFatturazione, $isPlanningPerson, $planningHours, $isPlanningMaterial, $planningCostMaterial, $panelPA, $typeOrderPA, $numDocumento, $dateDocumento, $codiceCIG, $codiceCUP, $codiceIPA, $panel_ID, $id, $createdAt];
        }
    }
}
