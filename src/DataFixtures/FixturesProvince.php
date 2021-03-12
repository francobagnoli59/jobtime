<?php

// deve essere sotto la cartella:
namespace App\DataFixtures;
// prima di eseguire: symfony console doctrine:fixtures:load 
// ( usare --append se si vogliono aggiungere dati al db senza cancellare i precedenti)

use App\Entity\Province;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FixturesProvince extends Fixture
{


    public function load(ObjectManager $manager)
    {

        $prov = new Province();
        $prov->setCode('XX');
        $prov->setName('- da assegnare');
        $manager->persist($prov);

        $prov = new Province();
        $prov->setCode('PI');
        $prov->setName('Pisa');
        $manager->persist($prov);

        $prov = new Province();
        $prov->setCode('AR');
        $prov->setName('Arezzo');
        $manager->persist($prov);

        $prov = new Province();
        $prov->setCode('FI');
        $prov->setName('Firenze');
        $manager->persist($prov);

        $prov = new Province();
        $prov->setCode('GR');
        $prov->setName('Grosseto');
        $manager->persist($prov);

        $prov = new Province();
        $prov->setCode('LI');
        $prov->setName('Livorno');
        $manager->persist($prov);

        $prov = new Province();
        $prov->setCode('LU');
        $prov->setName('Lucca');
        $manager->persist($prov);

        $prov = new Province();
        $prov->setCode('MS');
        $prov->setName('Massa e Carrara');
        $manager->persist($prov);
       
        $prov = new Province();
        $prov->setCode('PO');
        $prov->setName('Prato');
        $manager->persist($prov);
       
        $prov = new Province();
        $prov->setCode('PT');
        $prov->setName('Pistoia');
        $manager->persist($prov);

        $prov = new Province();
        $prov->setCode('SI');
        $prov->setName('Siena');
        $manager->persist($prov);

        $prov = new Province();
        $prov->setCode('FC');
        $prov->setName('Forlì-Cesena');
        $manager->persist($prov);

        $manager->flush();

    }
}
