<?php

namespace App\Form;

use App\Entity\MesiAziendali;
use App\Entity\Aziende;
use App\Entity\FestivitaAnnuali;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MesiAziendaliType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('azienda', EntityType::class, [
                'class' => Aziende::class,
                'query_builder' => function (EntityRepository $az) {
                return $az->createQueryBuilder('a')
                    ->orderBy('a.nickName', 'ASC');   },
                'choice_label' => 'nickName',
                ])
            ->add('festivitaAnnuale', EntityType::class, [ 'label' => 'Anno',
                'class' => FestivitaAnnuali::class,
                'query_builder' => function (EntityRepository $fe) {
                return $fe->createQueryBuilder('f')
                    ->orderBy('f.anno', 'ASC');   },
                ])
            ->add('mese', ChoiceType::class, ['label' => 'Mese',  'choices'  => [
                'Gennaio' => '01',
                'Febbraio' => '02',
                'Marzo' => '03',
                'Aprile' => '04',
                'Maggio' => '05',
                'Giugno' => '06',
                'Luglio' => '07',
                'Agosto' => '08',
                'Settembre' => '09',
                'Ottobre' => '10',
                'Novembre' => '11',
                'Dicembre' => '12',
            ],])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MesiAziendali::class,
        ]);
    }
}