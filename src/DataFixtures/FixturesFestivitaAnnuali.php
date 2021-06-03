<?php

// deve essere sotto la cartella:
namespace App\DataFixtures;
// prima di eseguire: symfony console doctrine:fixtures:load 
// ( usare --append se si vogliono aggiungere dati al db senza cancellare i precedenti)

use App\Entity\FestivitaAnnuali;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class FixturesFestivitaAnnuali extends Fixture
{


    public function load(ObjectManager $manager)
    {


        $festivita = new FestivitaAnnuali();
        $festivita->setAnno('2021');
        $festivita->setDateFestivita(['0101 Capodanno', '0601 Befana', '0404 Pasqua', '0504 Lunedì dell\'Angelo', '2504 Festa della liberazione', '0105 Festa dei lavoratori', '0206 Festa della Repubblica', '1508 Ferragosto', '2109 San Matteo', '0111 Tutti i Santi', '0812 Festa dell\'Immacolata Concezione', '2512 Natale', '2612 Santo Stefano']);
        $manager->persist($festivita);

     
        $manager->flush();
    }
}
