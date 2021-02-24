<?php

// da spostare sotto la cartella:
namespace App\DataFixtures;
// prima di eseguire: symfony console doctrine:fixtures:load --append


use App\Entity\Causali;
use App\Entity\Province;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
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

        $manager->flush();
    }
}
