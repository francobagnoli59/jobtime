<?php

// deve essere sotto la cartella:
namespace App\DataFixtures;
// prima di eseguire: symfony console doctrine:fixtures:load 
// ( usare --append se si vogliono aggiungere dati al db senza cancellare i precedenti)


use App\Entity\RegoleFatturazione;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class FixturesRegoleFatturazione extends Fixture
{


    public function load(ObjectManager $manager)
    {

        $regole = new RegoleFatturazione();
        $regole->setBillingCadence('MENSILE');
        $regole->setDaysRange(30);
        $manager->persist($regole);

        $regole = new RegoleFatturazione();
        $regole->setBillingCadence('UNA TANTUM');
        $regole->setDaysRange(0);
        $manager->persist($regole);

     
        $manager->flush();
    }
}
