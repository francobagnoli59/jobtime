<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class FixturesUser extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
         {
         $this->passwordEncoder = $passwordEncoder;
         }


    public function load(ObjectManager $manager)
    {

          $user = new User();
          $user->setEmail('arcenni.matteo@gmail.com');
          $user->setRoles(['ROLE_USER']);
          $user->setPassword($this->passwordEncoder->encodePassword( 
               $user,
          'jobtime1'
               ));
          $manager->persist($user);

          $user = new User();
          $user->setEmail('matte.bagnoli@gmail.com');
          $user->setRoles(['ROLE_USER']);
          $user->setPassword($this->passwordEncoder->encodePassword( 
               $user,
               'jobtime1'
               ));
          $manager->persist($user);

          $user = new User();
          $user->setEmail('mattiacei@hotmail.it');
          $user->setRoles(['ROLE_USER']);
          $user->setPassword($this->passwordEncoder->encodePassword( 
               $user,
               'jobtime1'
               ));
          $manager->persist($user);

          $user = new User();
          $user->setEmail('info@masotech.it');
          $user->setRoles(['ROLE_USER']);
          $user->setPassword($this->passwordEncoder->encodePassword( 
               $user,
               'info1234'
               ));
          $manager->persist($user);

          $user = new User();
          $user->setEmail('f.bagnoli@masotech.it');
          $user->setRoles(['ROLE_ADMIN']);
          $user->setPassword($this->passwordEncoder->encodePassword( 
               $user,
               'franco12'
               ));
          $manager->persist($user);

          $manager->flush();
    }
}
