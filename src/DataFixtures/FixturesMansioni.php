<?php

// deve essere sotto la cartella:
namespace App\DataFixtures;
// prima di eseguire: symfony console doctrine:fixtures:load 
// ( usare --append se si vogliono aggiungere dati al db senza cancellare i precedenti)

use App\Entity\Mansioni;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class FixturesMansioni extends Fixture
{

    public function load(ObjectManager $manager)
    {

  
        $mansioni = new Mansioni();
        $mansioni->setMansioneName('Amministrazione');
        $mansioni->setIsValidDA(false);
        $manager->persist($mansioni);

        $mansioni = new Mansioni();
        $mansioni->setMansioneName('Assistenza (non scuola)');
        $mansioni->setIsValidDA(false);
        $manager->persist($mansioni);

        $mansioni = new Mansioni();
        $mansioni->setMansioneName('Assistenza scuola');
        $mansioni->setIsValidDA(false);
        $manager->persist($mansioni);

        $mansioni = new Mansioni();
        $mansioni->setMansioneName('Ausiliari scolastici');
        $mansioni->setIsValidDA(false);
        $manager->persist($mansioni);

        $mansioni = new Mansioni();
        $mansioni->setMansioneName('Cuoco');
        $mansioni->setIsValidDA(true);
        $manager->persist($mansioni);

        $mansioni = new Mansioni();
        $mansioni->setMansioneName('Educatore');
        $mansioni->setIsValidDA(true);
        $manager->persist($mansioni);

        $mansioni = new Mansioni();
        $mansioni->setMansioneName('Impiegata d.a.');
        $mansioni->setIsValidDA(true);
        $manager->persist($mansioni);

        $mansioni = new Mansioni();
        $mansioni->setMansioneName('Informazioni Turistiche');
        $mansioni->setIsValidDA(true);
        $manager->persist($mansioni);

        $mansioni = new Mansioni();
        $mansioni->setMansioneName('Mensa');
        $mansioni->setIsValidDA(true);
        $manager->persist($mansioni);

        $mansioni = new Mansioni();
        $mansioni->setMansioneName('Programma Sociale');
        $mansioni->setIsValidDA(true);
        $manager->persist($mansioni);

        $mansioni = new Mansioni();
        $mansioni->setMansioneName('Pulizie');
        $mansioni->setIsValidDA(true);
        $manager->persist($mansioni);

        $mansioni = new Mansioni();
        $mansioni->setMansioneName('Sorveglianza/Manov.');
        $mansioni->setIsValidDA(false);
        $manager->persist($mansioni);

        $mansioni = new Mansioni();
        $mansioni->setMansioneName('Varie d.a.');
        $mansioni->setIsValidDA(true);
        $manager->persist($mansioni);

        $mansioni = new Mansioni();
        $mansioni->setMansioneName('Verde');
        $mansioni->setIsValidDA(true);
        $manager->persist($mansioni);
       
        $manager->flush();
    }
}
