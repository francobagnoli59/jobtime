<?php

namespace App\Form;

use App\Entity\DocumentiPersonale;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Vich\UploaderBundle\Form\Type\VichFileType; 
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;


class DocumentiPersonaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titolo', TextType::class, ['label' => 'Tipo documento', 'constraints' => [ new NotBlank(), new Length(['max' => 80])], 'help' => '<mark>Inserisci un titolo e scegli il documento, una volta caricato per visualizzare il documento usa il right-click del mouse e Apri il Link in una nuova scheda</mark>' ])
            ->add('documentoFile', VichFileType::class, ['constraints' => [ new File(['maxSize' => '3072k']) ] , 'allow_delete' => false ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DocumentiPersonale::class,
        ]);
    }
}

