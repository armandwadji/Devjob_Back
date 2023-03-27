<?php

namespace App\DataFixtures;

use App\Entity\Contract;
use App\Entity\Offer;
use App\Entity\Requirement;
use App\Entity\RequirementItem;
use App\Entity\Role;
use App\Entity\RoleItem;
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
        // CONTRACT
        $contracts = [];
        for ($i = 0; $i < 5; $i++) {
            $contract = new Contract();
            $contract->setName($this->faker->word());

            $contracts[] = $contract;
            $manager->persist($contract);
        }


        // FIXTURE
        $requirements = [];
        $roles = [];
        for ($k = 0; $k < 50; $k++) {
            $offer = new Offer();
            $offer->setName($this->faker->word())
                ->setDescription($this->faker->text(300))
                ->setUrl($this->faker->url())
                ->setContract($contracts[mt_rand(0, count($contracts) - 1)]);

            $manager->persist($offer);

            // AJOUT DU REQUIREMENT
            $requirement = new Requirement();
            $requirement->setContent($this->faker->text(300))
                        ->setOffer($offer);

            // AJOUT DU ROLE
            $role = new Role();
            $role->setContent($this->faker->text(300))
                ->setOffer($offer);

            $requirements[] = $requirement;
            $roles[] = $role;

            $manager->persist($requirement);
            $manager->persist($role);
        }

        // Ajout des requirements items
        for ($j = 0; $j < 50; $j++) {
            $requirementItem = new RequirementItem();
            $requirementItem->setName($this->faker->text(100))
                            ->setRequirement($requirements[mt_rand(0, count($requirements) - 1)]);

            $manager->persist($requirementItem);
        }

        // Ajout des roles items
        for ($j = 0; $j < 50; $j++) {
            $roleItem = new RoleItem();
            $roleItem->setName($this->faker->text(100))
                            ->setRole($roles[mt_rand(0, count($roles) - 1)]);

            $manager->persist($roleItem);
        }
        
        $manager->flush();
    }
}
