<?php

namespace App\Form;

use App\Entity\CommentiPubblici;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class CommentiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('author', null, ['label' => 'Il tuo nome', ])
            ->add('textComment', null, ['label' => 'Cosa ci dici?', ])
            ->add('email', EmailType::class, ['label' => 'La tua e-mail', ])
            // ->add('photoFilename')
            ->add('photo', FileType::class, ['label' => 'Se vuoi, inviaci una foto', 'required' => false, 'mapped' => false,
                    'constraints' => [ new Image(['maxSize' => '1024k'])  ], ])
           // ->add('cantieri')
            ->add('submit', SubmitType::class)
          ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommentiPubblici::class,
        ]);
    }
}

