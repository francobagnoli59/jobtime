<?php

namespace App\Form;

use App\Entity\RaccoltaOrePersone;
use App\Form\ModuliRaccoltaOreCantieriType;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RaccoltaOrePersonaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tipogiorno', CollectionType::class, [
                'entry_type' => HiddenType::class,  'mapped' => false, 'required' => false , 'row_attr' => ['style' => 'visibility:hidden']
            ]) 
            ->add('nomegiorno', CollectionType::class, [
                'entry_type' => HiddenType::class,  'mapped' => false, 'required' => false , 'row_attr' => ['style' => 'visibility:hidden']
            ])
            ->add('altreCausali', CollectionType::class, [
                'entry_type' => TextType::class,  'mapped' => false, 'required' => false 
            ])      
            ->add('totaleXGiorno', CollectionType::class, [
                'entry_type' => TextType::class,  'mapped' => false, 'required' => false 
            ])            
            ->add('oreMeseCantieri', CollectionType::class, [
                'entry_type' => ModuliRaccoltaOreCantieriType::class
            ])        
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RaccoltaOrePersone::class,
        ]);
    }
}