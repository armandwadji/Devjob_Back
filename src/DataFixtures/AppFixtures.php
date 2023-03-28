<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Contract;
use App\Entity\Location;
use App\Entity\Offer;
use App\Entity\Requirement;
use App\Entity\RequirementItem;
use App\Entity\Role;
use App\Entity\RoleItem;
use App\Entity\User;
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

        // COUNTRY
        $locations = [];
        for ($i = 0; $i < 20; $i++) {
            $location = new Location();
            $location->setName($this->faker->countryCode());

            $locations[] = $location;
            $manager->persist($location);
        }

        // USER
        $users = [];
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setFirstname($this->faker->firstName())
                ->setLastname($this->faker->lastName())
                ->setEmail($this->faker->email())
                ->setRoles(['ROLE_USER'])
                ->setPlainPassword('password');

            $users[] = $user;
            $manager->persist($user);
        }

        // COMPANY
        $companies = [];
        for ($i = 0; $i < 20; $i++) {
            $company = new Company();
            $company->setName($this->faker->word())
                ->setColor($this->faker->rgbColor())
                ->setUser($users[$i])
                ->setLocation($locations[mt_rand(0, count($locations) - 1)]);

            $companies[] = $company;
            $manager->persist($company);
        }



        // TABLEAUX REQUIREMENTS AND ROLES
        $requirements = [];
        $roles = [];
        for ($k = 0; $k < 50; $k++) {
            $offer = new Offer();
            $offer->setName($this->faker->word())
                ->setDescription($this->faker->text(300))
                ->setUrl($this->faker->url())
                ->setContract($contracts[mt_rand(0, count($contracts) - 1)])
                ->setCompany($companies[mt_rand(0, count($companies) - 1)]);

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

        // REQUIREMENT ITEMS
        for ($j = 0; $j < 50; $j++) {
            $requirementItem = new RequirementItem();
            $requirementItem->setName($this->faker->text(100))
                ->setRequirement($requirements[mt_rand(0, count($requirements) - 1)]);

            $manager->persist($requirementItem);
        }

        // REQUIREMENT ROLES
        for ($j = 0; $j < 50; $j++) {
            $roleItem = new RoleItem();
            $roleItem->setName($this->faker->text(100))
                ->setRole($roles[mt_rand(0, count($roles) - 1)]);

            $manager->persist($roleItem);
        }

        $manager->flush();
    }
}
