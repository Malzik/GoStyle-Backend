<?php

namespace App\DataFixtures;

use App\Entity\Offer;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 20; $i++) {
            $user = new User();

            $user->setFirstName($faker->firstName);
            $user->setLastName($faker->lastName);
            $user->setEmail($user->getFirstName().".".$user->getLastName()."@gmail.com");
            $user->setPassword($faker->password);
            for($l= 0; $l < 3; $l++){
                $offer = new Offer();
                $offer->setCode($faker->randomDigit);
                $offer->setDeadline($faker->dateTimeThisMonth);
                $offer->setDescription($faker->paragraph);
                $offer->setName($faker->text(15));
                $offer->setLogo($faker->imageUrl());
                $manager->persist($offer);
                $user->addOffer($offer);
            }
            $manager->persist($user);
        }
        $manager->flush();
    }
}
