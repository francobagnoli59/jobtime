<?php

// deve essere sotto la cartella:
namespace App\DataFixtures;
// prima di eseguire: symfony console doctrine:fixtures:load 
// ( usare --append se si vogliono aggiungere dati al db senza cancellare i precedenti)


use App\Entity\CategorieServizi;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FixturesCategorieServizi extends Fixture
{


    public function load(ObjectManager $manager)
    {
        $categorie = new CategorieServizi();
        $categorie->setCategoria('Ambiente');
        $manager->persist($categorie);

        $categorie = new CategorieServizi();
        $categorie->setCategoria('Assistenza');
        $manager->persist($categorie);

        $categorie = new CategorieServizi();
        $categorie->setCategoria('Educazione');
        $manager->persist($categorie);

        $categorie = new CategorieServizi();
        $categorie->setCategoria('Sociale');
        $manager->persist($categorie);

        $categorie = new CategorieServizi();
        $categorie->setCategoria('Arte e Turismo');
        $manager->persist($categorie);

        $categorie = new CategorieServizi();
        $categorie->setCategoria('Organizzazione');
        $manager->persist($categorie);

        $categorie = new CategorieServizi();
        $categorie->setCategoria('Verde');
        $manager->persist($categorie);

       
        $manager->flush();
    }
}
