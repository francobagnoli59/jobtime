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
            ->add('textComment', null, ['label' => 'un tuo commento', ])
            ->add('email', EmailType::class, ['label' => 'la tua e-mail', ])
            // ->add('photoFilename')
            ->add('photo', FileType::class, ['label' => 'se vuoi, inviaci una foto', 'required' => false, 'mapped' => false,
                    'constraints' => [ new Image(['maxSize' => '2048k'])  ], ])
           // ->add('cantieri')
            ->add('submit', SubmitType::class, ['label' => 'Conferma', 'attr' => ['class' => "mt-2 text-white btn-blue" ], ] )
          ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommentiPubblici::class,
        ]);
    }
}

