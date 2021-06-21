<?php

namespace App\Form;

use App\Entity\Cantieri;
use App\Entity\ModuliRaccoltaOreCantieri;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModuliRaccoltaOreCantieriType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cantiere', EntityType::class, [
                'class' => Cantieri::class,
                'query_builder' => function (EntityRepository $ca) {
                return $ca->createQueryBuilder('c')
                    ->orderBy('c.nameJob', 'ASC');   },
                'choice_label' => 'nameJob',  'label' => 'Scegli il cantiere' , 
                ])
            ->add('prevIdPlanned', IntegerType::class, [
                 'required' => false , 'row_attr' => ['style' => 'visibility:hidden']
            ])   
            ->add('oreGiornaliere', CollectionType::class, [
                // each entry in the array sono ore lavorate ordinarie
                'entry_type' => TextType::class
            ])
            ->add('isOreConfermate', CollectionType::class, [
                'entry_type' => HiddenType::class,  'required' => false , 'row_attr' => ['style' => 'visibility:hidden']
            ]);        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ModuliRaccoltaOreCantieri::class,
        ]);
    }
}