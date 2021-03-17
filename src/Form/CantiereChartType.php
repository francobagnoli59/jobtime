<?php

namespace App\Form;

// N O N   U S A T A //
// Prove modal pop_up //

use App\Entity\Cantieri;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CantiereChartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nameJob', TextType::class, ['disabled' => true, ] )
            ->add('city', TextType::class, ['disabled' => true, ] )
           // ->add('chart', FormType::class)->setMapped(false)
               
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cantieri::class,
        ]);
    }
}