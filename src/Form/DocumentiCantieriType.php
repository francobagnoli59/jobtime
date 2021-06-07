<?php

namespace App\Form;

use App\Entity\DocumentiCantieri;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Vich\UploaderBundle\Form\Type\VichFileType; 
use Symfony\Component\Validator\Constraints\File;
// use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;


class DocumentiCantieriType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tipologia', ChoiceType::class, ['label' => 'Tipo documento', 'choices' => [  'come descritto nel titolo' => 'NUL'  ,  'Contratto pubblica amministrazione'  => 'CPA', 'Determina pubblica amministrazione'  => 'DPA' ,
            'Contratto con società non P.A.' => 'CPR'  , 'Commessa/Ordine con società non P.A.' => 'OPR'  ,  'Altro documento'  => 'OTH'],
            'help' => '<mark>Scegli un tipo di documento da caricare, se il tipo non è presente nella lista utilizza il titolo per descriverlo.</mark>' ])
            ->add('titolo', TextType::class, ['label' => 'Tipo documento',  'constraints' => [ new Length(['max' => 80])], 'help' => '<mark>Inserisci un titolo o scegli il tipo documento, una volta caricato per visualizzare il documento usa il right-click del mouse e Apri il Link in una nuova scheda</mark>' ]) // new NotBlank() 
            ->add('documentoFile', VichFileType::class, ['constraints' => [ new File(['maxSize' => '4096k']) ] , 'allow_delete' => false ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DocumentiCantieri::class,
        ]);
    }
}

