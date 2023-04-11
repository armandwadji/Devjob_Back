<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Role;
use App\Entity\User;
use Faker\Generator;
use App\Entity\Offer;
use App\Entity\Company;
use App\Entity\Contract;
use App\Entity\RoleItem;
use App\Entity\Candidate;
use App\Entity\Requirement;
use App\Entity\RequirementItem;
use Symfony\Component\Intl\Countries;
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
        // ADMINISTRATEUR
        $admin = new User();
        $admin->setFirstname('Mickaël')
            ->setLastname('AUGER')
            ->setEmail('admin@devjob.com')
            ->setIsVerified(true)
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
            ->setPlainPassword('Php1234#');
        $manager->persist($admin);


        // CONTRACT
        $contracts = [];

        $contract = new Contract();
        $contract->setName('CDI');
        $contracts[] = $contract;
        $manager->persist($contract);

        $contract = new Contract();
        $contract->setName('CDD');
        $contracts[] = $contract;
        $manager->persist($contract);

        $contract = new Contract();
        $contract->setName('Alternance');
        $contracts[] = $contract;
        $manager->persist($contract);

        $contract = new Contract();
        $contract->setName('Stage');
        $contracts[] = $contract;
        $manager->persist($contract);

        $contract = new Contract();
        $contract->setName('Intérim');
        $contracts[] = $contract;
        $manager->persist($contract);

        // USER
        $users = [];
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setFirstname($this->faker->firstName())
                ->setLastname($this->faker->lastName())
                ->setEmail($this->faker->email())
                ->setRoles(['ROLE_USER'])
                ->setIsVerified(true)
                ->setTokenRegistrationLifeTime(new \DateTimeImmutable())
                ->setPlainPassword('password');

            $users[] = $user;
            $manager->persist($user);
        }

        // COMPANY
        $companies = [];
        for ($i = 0; $i < 20; $i++) {
            $company = new Company();
            $company->setName($this->faker->word())
                ->setColor($this->faker->hexColor())
                ->setUser($users[$i])
                ->setCountry( Countries::getAlpha3Name($this->faker->countryISOAlpha3())); //Conversion des initials des pays en nom complet

            $companies[] = $company;
            $manager->persist($company);
        }



        // TABLEAUX REQUIREMENTS AND ROLES
        $requirements = [];
        $roles = [];
        $offers = [];
        for ($k = 0; $k < 50; $k++) {
            $offer = new Offer();
            $offer->setName($this->faker->word())
                ->setDescription($this->faker->text(300))
                ->setUrl($this->faker->url())
                ->setContract($contracts[mt_rand(0, count($contracts) - 1)])
                ->setCompany($companies[mt_rand(0, count($companies) - 1)]);

            $offers[] = $offer;
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

        // TABLEAUX CANDIDATES
        for ($j = 0; $j < 200; $j++) {
            $candidate = new Candidate();
            $candidate->setFirstname($this->faker->firstName())
                ->setLastname($this->faker->lastName())
                ->setEmail($this->faker->email())
                ->setTelephone($this->faker->creditCardNumber())
                ->setDescription($this->faker->text(300))
                ->setOffer($offers[mt_rand(0, count($offers) - 1)])
                ;

            $manager->persist($candidate);
        }

        $manager->flush();
    }
}
