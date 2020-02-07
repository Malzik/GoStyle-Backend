<?php

namespace App\DataFixtures;

use Symfony\Component\Asset\Package;
use App\Entity\Offer;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $marques = ['dcshoes', 'supreme', 'tommyJeans', 'vans', 'adidas', 'bershka'];
        $package = new Package(new EmptyVersionStrategy());
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 20; $i++) {
            $user = new User();

            $user->setFirstName($faker->firstName);
            $user->setLastName($faker->lastName);
            $user->setEmail($faker->email);
            $user->setPassword($faker->password);
            $user->setRoles([]);
            for($l= 0; $l < 3; $l++){
                $offer = new Offer();
                $offer->setCode($faker->sha1);
                $offer->setDeadline($faker->dateTimeThisMonth);
                $offer->setDescription($faker->paragraph);
                $offer->setName($faker->text(15));
                $offer->setLogo('https://gostyle.sepradc-serv.ovh/images/' . $marques[array_rand($marques, 1)] . '.png');
                $manager->persist($offer);
                $user->addOffer($offer);
            }
            $manager->persist($user);
        }
        $manager->flush();
    }
}
