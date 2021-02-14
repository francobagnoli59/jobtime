<?php

namespace App\Controller\Admin;

use App\Entity\PianoOreCantieri;
use App\Entity\Personale;
use App\Entity\Cantieri;
use App\Repository\CantieriRepository;
use App\Repository\PersonaleRepository;
use Doctrine\ORM\EntityManagerInterface;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;



class PianoOreCantieriCrudController extends AbstractCrudController
{

    protected EntityManagerInterface $entityManager;
    
    /**
     * @var AdminUrlGenerator
     */
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(EntityManagerInterface $entityManager,  AdminUrlGenerator $adminUrlGenerator)
    {
    $this->entityManager = $entityManager;
    $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return PianoOreCantieri::class;
    }


    public function createEntity(string $entityFqcn)
    {
        $pianoorecantieri = new PianoOreCantieri();

        $persona_id = $this->adminUrlGenerator->get('persona');
        $pianoorecantieri->setPersona($this->entityManager->getRepository(Personale::class)->findOneBy(['id'=> $persona_id]));
        $cantiere_id = $this->adminUrlGenerator->get('cantiere');
        $pianoorecantieri->setCantiere($this->entityManager->getRepository(Cantieri::class)->findOneBy(['id'=> $cantiere_id]));
        $pianoorecantieri->setOrePreviste('0');
       
        return  $pianoorecantieri;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
          // key Piano ore cantieri
        $keyreference =  $entityInstance->getKeyReference();
        $message = $this->verifyEntity($entityManager, $entityInstance);
        if ($message === '') {
            $entityManager->persist($entityInstance);
            $entityManager->flush();
        } else { $this->addFlash('danger', sprintf('%s Piano ore cantiere con key %s non modificato!!!', $message, $keyreference )); }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $message = $this->verifyEntity($entityManager, $entityInstance);
        if ($message === '') {
            $entityManager->persist($entityInstance);
            $entityManager->flush();
        } else { $this->addFlash('danger', sprintf('%s Piano ore cantiere non inserito!!!', $message)); }
    }

    private function verifyEntity(EntityManagerInterface $entityManager, $entityInstance): string
    {

         // controlla che il nominativo appartenga alla stessa azienda del cantiere
        $recordpersona = $entityManager->getRepository(Personale::class)->findOneBy(['id'=> $entityInstance->getPersona()->getId()]);
        $recordcantiere = $entityManager->getRepository(Cantieri::class)->findOneBy(['id'=> $entityInstance->getCantiere()->getId()]);
        
        $azienda_persona = $recordpersona->getAzienda()->getId();
        $azienda_cantiere = $recordcantiere->getAzienda()->getId();
         if ($azienda_persona === $azienda_cantiere ) {
             $message = '';
         } else { $message = 'L\'azienda del cantiere non corrisponde all\'azienda della persona.</br>'; }

        return $message ;
    }


    public function configureCrud(Crud $crud): Crud
    {
    
        return $crud
            ->setEntityLabelInSingular('Piano ore cantiere')
            ->setEntityLabelInPlural('Piani ore cantieri')
            ->setPageTitle(Crud::PAGE_INDEX,  'Elenco Cantieri con piano orari ')
            ->setPageTitle(Crud::PAGE_NEW, 'Aggungi piano ore di cantiere per la persona scelta')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (PianoOreCantieri $piano) => sprintf('Piano ore di <b>%s</b>', $piano->getPersona()->getFullName()))
            ->setPageTitle(Crud::PAGE_EDIT, fn (PianoOreCantieri $piano) => sprintf('Modifica piano ore di <b>%s</b>', $piano->getPersona()->getFullName()))
            ->setSearchFields(['id', 'dayOfWeek', 'cantiere.nameJob', 'persona.surname', 'orePreviste'])
            ->setDefaultSort(['persona' => 'ASC', 'dayOfWeek' => 'ASC', 'cantiere' => 'ASC'])
            ->showEntityActionsAsDropdown();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('dayOfWeek', 'Giorno')->setChoices(['Lunedì' => 1, 'Martedì' => 2, 'Mercoledì' => 3, 'Giovedì' => 4, 'Venerdì' => 5, 'Sabato' => 6, 'Domenica' => 7 ]))
            ->add(EntityFilter::new('cantiere')->setFormTypeOption('value_type_options.query_builder', 
                static fn(CantieriRepository $ca) => $ca->createQueryBuilder('cantiere')
                     ->orderBy('cantiere.nameJob', 'ASC') ) )
            ->add(EntityFilter::new('persona')->setFormTypeOption('value_type_options.query_builder', 
               static fn(PersonaleRepository $pe) => $pe->createQueryBuilder('personale')
                    ->orderBy('personale.surname', 'ASC') ) )
             ;
        
    }
 
    
    public function configureActions(Actions $actions): Actions
    {
          $add_piano = Action::new('addPiano', 'Aggiungi Piano', 'fa fa-clipboard-list')
         ->linkToCrudAction('addPiano');
           
       
        return $actions
                ->remove(Crud::PAGE_INDEX, Action::NEW)
                //->remove(Crud::PAGE_INDEX, Action::DELETE)
                ->remove(Crud::PAGE_DETAIL, Action::DELETE)
                ->remove(Crud::PAGE_DETAIL, Action::INDEX)
                
                ->add(Crud::PAGE_INDEX, $add_piano)->add(Crud::PAGE_EDIT, $add_piano)
                // ...
                ->add(Crud::PAGE_INDEX, Action::DETAIL)
                // ->add(Crud::PAGE_DETAIL,)
                //->add(Crud::PAGE_EDIT,  Action::INDEX )
                ->add(Crud::PAGE_NEW,   Action::INDEX )
    
                ->update(Crud::PAGE_INDEX, Action::EDIT,
                 fn (Action $action) => $action->setIcon('fa fa-edit') )
                ->update(Crud::PAGE_INDEX, Action::DETAIL,
                 fn (Action $action) => $action->setIcon('fa fa-eye') )
                ->update(Crud::PAGE_INDEX, Action::DELETE,
                 fn (Action $action) => $action->setIcon('fa fa-trash') )
            ;
    }

    public function addPiano(AdminContext $context)
    {
        $oreitemPiano = $context->getEntity()->getInstance();

        $url = $this->adminUrlGenerator->unsetAll()
            ->setController(PianoOreCantieriCrudController::class)
            ->setAction(Action::NEW)
            ->set('cantiere', $oreitemPiano->getCantiere()->getId())
            ->set('persona', $oreitemPiano->getPersona()->getId());
            return $this->redirect($url);
    }

     public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('PIANO ORE CANTIERE')->setIcon('fas fa-clipboard-list');
        $orePreviste = TextField::new('orePreviste', 'Ore previste');
        $keyReference = TextField::new('keyReference', 'Chiave registrazione')->setFormTypeOptions(['disabled' => 'true']);
        $dayOfWeek = ChoiceField::new('dayOfWeek', 'Giorno')->setChoices(['Lunedì' => 1, 'Martedì' => 2, 'Mercoledì' => 3, 'Giovedì' => 4, 'Venerdì' => 5, 'Sabato' => 6, 'Domenica' => 7 ]);
      
        $cantiere = AssociationField::new('cantiere', 'Cantiere')
            ->setFormTypeOptions([
            'query_builder' => function (CantieriRepository $ca) {
                 return $ca->createQueryBuilder('c')
                     ->orderBy('c.nameJob', 'ASC');
            },
            ])->setRequired(true)->setCustomOptions(array('widget' => 'native'));
        $persona = AssociationField::new('persona', 'Nome persona')
            ->setFormTypeOptions([
            'query_builder' => function (PersonaleRepository $pe) {
                  return $pe->createQueryBuilder('p')
                      ->orderBy('p.surname', 'ASC');
            }, 'disabled' => 'true'
        ] )->setRequired(true)->setCustomOptions(array('widget' => 'native'));
        
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);
        
        if (Crud::PAGE_INDEX === $pageName) {
            return [$id,  $persona, $cantiere, $dayOfWeek,  $orePreviste];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $persona, $cantiere, $dayOfWeek,  $orePreviste,  $panel_ID, $id, $keyReference, $createdAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $persona, $cantiere, $dayOfWeek,  $orePreviste ];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1,  $persona, $cantiere, $dayOfWeek,  $orePreviste,  $panel_ID, $id, $keyReference, $createdAt];
        }
    }
}


