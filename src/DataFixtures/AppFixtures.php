<?php

namespace App\DataFixtures;

use App\Entity\Contract;
use App\Entity\Offer;
use Faker\Factory;
use Faker\Generator;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    /**
     * @var Generator
     */
    private Generator $faker;


    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $contracts = [];
        for ($i=0; $i < 5; $i++) {
            $contract = new Contract();
            $contract->setName($this->faker->word());

            $contracts[] = $contract;
            $manager->persist($contract);
        }
        
        for ($i = 0; $i < 50; $i++) {
            $offer = new Offer();
            $offer->setName($this->faker->word())
                ->setDescription($this->faker->text(300))
                ->setUrl($this->faker->url())
                ->setContract($contracts[mt_rand(0, count($contracts) - 1)]);

            $manager->persist($offer);
        }

        $manager->flush();
    }
}
