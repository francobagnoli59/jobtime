<?php
//
// IMPORTANTE, AL MOMENTO QUESTA COMPONENTE NON E' USATA 
//  05/02/2021
//
namespace App\Form;

use App\Entity\FestivitaAnnuali;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
// use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FestivitaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('listFestivita', DateType::class, ['label' => 'Data festa'])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FestivitaAnnuali::class,
        ]);
    }
}