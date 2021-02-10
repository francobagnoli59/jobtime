<?php

namespace App\Controller\Admin;

use App\Entity\MesiAziendali;
use App\Entity\OreLavorate;
use App\Entity\FestivitaAnnuali;
use App\Repository\AziendeRepository;
use App\Repository\FestivitaAnnualiRepository;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;


class MesiAziendaliCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MesiAziendali::class;
    }

   
    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {

        // dati pianificazione selezionata
        $keyreference =  $entityInstance->getKeyReference();
        $azienda_id = $entityInstance->getAzienda();
        $festivitaAnnuale_id = $entityInstance->getFestivitaAnnuale();
        $mese = $entityInstance->getMese();
        // legge anno dalle festività dell'anno
        $festivita = $entityManager->getRepository(FestivitaAnnuali::class)->findOneBy(['id'=> $festivitaAnnuale_id]);
        $anno = $festivita->getAnno();
         // valori iniziali per preparazione mese
         $finemese = 31;
         if ($mese === '04' || $mese === '06' || $mese === '09' || $mese === '11') {
           $finemese = 30;
         }
         if ($mese === '02' ) {
             $finemese = 28;
             if ($anno === '2024' || $anno === '2028' || $anno === '2032' || $anno === '2036' || $anno === '2040' || $anno === '2044' || $anno === '2048') {
                 $finemese = 29;
             }
         }
        // Determina periodo e elimina Ore lavorate se non confermate
        $datestart = new \DateTime();
        $datestart->setTime(0,0,0);
        $dateend = new \DateTime();
        $dateend->setTime(0,0,0);
        $datestart->setDate($anno , $mese, 1);  
        $dateend->setDate($anno , $mese, $finemese);

        $count = $entityManager->getRepository(OreLavorate::class)->countDeleted($azienda_id, true, $datestart, $dateend);
        if ($count === 0) {
            $count = $entityManager->getRepository(OreLavorate::class)->countDeleted($azienda_id, false , $datestart, $dateend);
            if ($count > 0) {
            $countdeleted = $entityManager->getRepository(OreLavorate::class)->deleteOreLavorate($azienda_id, false , $datestart, $dateend);
            $this->addFlash('success',  sprintf('Sono stati eliminati %d item di Ore lavorate', $countdeleted));    
            } else {
                 $this->addFlash('info',  'Nel mese selezionato NON ci sono ore lavorate registrate.');
            }
            // elimina Consolidato mese
            $entityManager->remove($entityInstance);
            $entityManager->flush();
            $this->addFlash('success',  sprintf('Consolidato del mese con riferimento registrazione %s eliminato con successo', $keyreference));   
        } else {
              $this->addFlash('warning', sprintf('Nel mese selezionato ci sono %d item di ore lavorate CONFERMATE. Eliminazione non eseguibile', $count ));
        }

        return  ;
    }

    public function configureCrud(Crud $crud): Crud
    {
         //->setPageTitle(Crud::PAGE_NEW, 'Crea nuova pianificazione mensile')
        return $crud
            ->setEntityLabelInSingular('Consolidato mensile')
            ->setEntityLabelInPlural('Consolidati mensili')
            ->setPageTitle(Crud::PAGE_INDEX, 'Elenco Mesi consolidati')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (MesiAziendali $name) => sprintf('Consolidato mensile <b>%s</b>', $name ))
            ->setPageTitle(Crud::PAGE_EDIT, fn (MesiAziendali $name) => sprintf('Modifica consolidato mensile <b>%s</b>', $name->getKeyReference()))
            ->setSearchFields(['festivitaAnnuale', 'mese', 'azienda' ])
            ->setDefaultSort(['festivitaAnnuale' => 'ASC', 'mese' => 'ASC', 'azienda' => 'ASC'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
         
        return $actions
            // ...
            //->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)

            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            //->add(Crud::PAGE_EDIT,  Action::INDEX )
            //->add(Crud::PAGE_NEW,   Action::INDEX )
           
            
            // ->update(Crud::PAGE_INDEX, Action::EDIT,
            // fn (Action $action) => $action->setIcon('fa fa-edit')->setLabel(false)->setHtmlAttributes(['title' => 'Modifica']))
            // ->update(Crud::PAGE_INDEX, Action::DELETE,
            // fn (Action $action) => $action->setIcon('fa fa-trash')->setLabel(false)->setHtmlAttributes(['title' => 'Elimina']))
            ->update(Crud::PAGE_INDEX, Action::DETAIL,
             fn (Action $action) => $action->setIcon('fa fa-eye')->setLabel(false)->setHtmlAttributes(['title' => 'Vedi']))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('CONSOLIDATO AZIENDA NEL MESE')->setIcon('fas fa-calendar');
        $azienda = AssociationField::new('azienda', 'Azienda del gruppo')->setHelp('NickName dell\'azienda del gruppo')
            ->setFormTypeOptions([
            'query_builder' => function (AziendeRepository $az) {
                return $az->createQueryBuilder('a')
                   ->orderBy('a.nickName', 'ASC');     },
                                 ])
            ->setCustomOptions(array('widget' => 'native'))->setRequired(true);
        $festivitaAnnuale = AssociationField::new('festivitaAnnuale', 'Anno')
            ->setFormTypeOptions([
            'query_builder' => function (FestivitaAnnualiRepository $fa) {
                return $fa->createQueryBuilder('f')
                   ->orderBy('f.anno', 'ASC');     },
                                 ])
            ->setCustomOptions(array('widget' => 'native'))->setRequired(true); 
        $isHoursCompleted = BooleanField::new('isHoursCompleted', 'Orari del mese completati')->setHelp('Se non attivato significa che mancano ancora orari dipendenti da inserire');
        $isInvoicesCompleted = BooleanField::new('isInvoicesCompleted', 'Fatture del mese completate')->setHelp('Se non attivato significa che mancano ancora fatture mensili da produrre');
        $mese = ChoiceField::new('mese', 'Mese')->setChoices(['Gennaio' => '01', 'Febbraio' => '02', 'Marzo' => '03', 'Aprile' => '04', 'Maggio' => '05', 'Giugno' => '06',
        'Luglio' => '07', 'Agosto' => '08', 'Settembre' => '09', 'Ottobre' => '10', 'Novembre' => '11', 'Dicembre' => '12',]);
        $costMonthHuman = MoneyField::new('costMonthHuman', 'Costo risorse umane')->setCurrency('EUR')->setHelp('Calcolato sull\'ammontare delle ore mensili effettive');
        $costMonthMaterial = MoneyField::new('costMonthMaterial', 'Costo risorse materieli')->setCurrency('EUR')->setHelp('Calcolato sull\'ammontare medio pianificato per cantiere');
        $incomeMonth = MoneyField::new('incomeMonth', 'Ricavi mensili')->setCurrency('EUR')->setHelp('Calcolato sull\'ammontare delle entrate mensili fatturate');
       
        $panel_ID = FormField::addPanel('INFORMAZIONI RECORD')->setIcon('fas fa-database')->renderCollapsed('true');
        $id = IntegerField::new('id', 'ID')->setFormTypeOptions(['disabled' => 'true']);
        $keyReference = TextField::new('keyReference', 'Chiave registrazione')->setFormTypeOptions(['disabled' => 'true']);
        $createdAt = DateTimeField::new('createdAt', 'Data ultimo aggiornamento')->setFormTypeOptions(['disabled' => 'true']);
        if (Crud::PAGE_INDEX === $pageName) {
            return [ $festivitaAnnuale, $azienda, $mese, $costMonthHuman, $costMonthMaterial, $incomeMonth ];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$panel1, $festivitaAnnuale, $azienda, $mese, $isHoursCompleted, $costMonthHuman, $isInvoicesCompleted, $costMonthMaterial, $incomeMonth, $panel_ID, $id, $keyReference, $createdAt];
         } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $festivitaAnnuale, $azienda, $mese, $isHoursCompleted, $costMonthHuman, $isInvoicesCompleted, $costMonthMaterial, $incomeMonth];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $festivitaAnnuale, $azienda, $mese, $isHoursCompleted, $costMonthHuman, $isInvoicesCompleted, $costMonthMaterial, $incomeMonth, $panel_ID, $id, $keyReference, $createdAt];

        }
    }
}
