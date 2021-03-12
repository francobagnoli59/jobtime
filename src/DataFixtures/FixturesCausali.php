<?php

// deve essere sotto la cartella:
namespace App\DataFixtures;
// prima di eseguire: symfony console doctrine:fixtures:load 
// ( usare --append se si vogliono aggiungere dati al db senza cancellare i precedenti)

use App\Entity\Causali;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FixturesCausali extends Fixture
{


    public function load(ObjectManager $manager)
    {


        $causali = new Causali();
        $causali->setCode('ORDI');
        $causali->setDescription('ORE ORDINARIE');
        $manager->persist($causali);

        $causali = new Causali();
        $causali->setCode('STRA');
        $causali->setDescription('STRAORDINARIO');
        $manager->persist($causali);

        $causali = new Causali();
        $causali->setCode('*AT');
        $causali->setDescription('ALLATTAMENTO');
        $manager->persist($causali);

        $causali = new Causali();
        $causali->setCode('*NG');
        $causali->setDescription('ASSENZA NON RETRIBUITA');
        $manager->persist($causali);

        $causali = new Causali();
        $causali->setCode('*DS');
        $causali->setDescription('DONAZIONE SANGUE');
        $manager->persist($causali);

        $causali = new Causali();
        $causali->setCode('*EF');
        $causali->setDescription('EX-FESTIVITA\' GODUTE');
        $manager->persist($causali);

        $causali = new Causali();
        $causali->setCode('*FE');
        $causali->setDescription('FERIE GODUTE');
        $manager->persist($causali);

        $causali = new Causali();
        $causali->setCode('*ML');
        $causali->setDescription('MALATTIA');
        $manager->persist($causali);

        $causali = new Causali();
        $causali->setCode('*MO');
        $causali->setDescription('MATERNITA\' FACOLTATTIVA ORE');
        $manager->persist($causali);

        $causali = new Causali();
        $causali->setCode('*MT');
        $causali->setDescription('MATERNITA\' OBBLIGATORIA');
        $manager->persist($causali);

        $causali = new Causali();
        $causali->setCode('*PA');
        $causali->setDescription('PERMESSI AZIENDALI');
        $manager->persist($causali);

        $causali = new Causali();
        $causali->setCode('*PF');
        $causali->setDescription('PERMESSI FIGLI CON HANDICAP');
        $manager->persist($causali);

        $causali = new Causali();
        $causali->setCode('*PE');
        $causali->setDescription('PERMESSI GODUTI');
        $manager->persist($causali);

        $causali = new Causali();
        $causali->setCode('*PL');
        $causali->setDescription('PERMESSI LAVORATORI CON HANDICAP');
        $manager->persist($causali);

        $causali = new Causali();
        $causali->setCode('*PH');
        $causali->setDescription('PERMESSI PARENTI CON HANDICAP');
        $manager->persist($causali);

        $causali = new Causali();
        $causali->setCode('*SC');
        $causali->setDescription('SCIOPERO');
        $manager->persist($causali);


       
        $manager->flush();


     
    }
}
