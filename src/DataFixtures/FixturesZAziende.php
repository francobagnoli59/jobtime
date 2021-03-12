<?php

// deve essere sotto la cartella:
namespace App\DataFixtures;
// prima di eseguire: symfony console doctrine:fixtures:load 
// ( usare --append se si vogliono aggiungere dati al db senza cancellare i precedenti)

use App\Entity\Aziende;
use App\Entity\Province;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FixturesZAziende extends Fixture
{


    public function load(ObjectManager $manager)
    {


 
        $prov = new Province();
        $prov = $manager->getRepository(Province::class)->findOneBy(['code'=>'PI']);
 
        $aziende = new Aziende();
        $aziende->setCompanyName('Alioth Società Cooperativa Sociale Onlus');
        $aziende->setNickName('ALIOTH');
        $aziende->setAddress('Via Cavallotti, 3');
        $aziende->setZipCode('56025');
        $aziende->setCity('Pontedera');
        $aziende->setProvincia($prov);
        $aziende->setPartitaIVA('02197770502');
        $aziende->setFiscalCode('02197770502');
        $aziende->setCodeTransferPaghe('1208');
        $aziende->setRangeAnalisi('-12');
        $manager->persist($aziende);
        
        $aziende = new Aziende();
        $aziende->setCompanyName('Polaris Cooperativa Sociale ONLUS');
        $aziende->setNickName('POLARIS');
        $aziende->setAddress('Via dei Mugnai, 12');
        $aziende->setZipCode('56025');
        $aziende->setCity('Pontedera');
        $aziende->setProvincia($prov);
        $aziende->setPartitaIVA('02314650504');
        $aziende->setFiscalCode('02314650504');
        $aziende->setCodeTransferPaghe('1245');
        $aziende->setRangeAnalisi('-12');
        $manager->persist($aziende);

        $manager->flush();
    }
}
