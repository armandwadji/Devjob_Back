<?php

namespace App\Repository;

use App\Entity\Candidate;
use App\Entity\Company;
use App\Entity\Offer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Offer>
 *
 * @method Offer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Offer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Offer[]    findAll()
 * @method Offer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offer::class);
    }

    public function save(Offer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Offer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * This function return offer for company don't delete subscribe, order by desc
     * @return array
     */
    public function findOfferOrderDesc(): array
    {
        // SELECT * FROM `offer` 
        // JOIN `user`
        // WHERE user.is_deleted = false
        // ORDER BY `offer`.created_At DESC;

        return $this->createQueryBuilder('o')
            ->join('o.company', 'c')
            ->join('c.user', 'u')
            ->andWhere('u.isDeleted = :delete')
            ->setParameter('delete', false)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findCandidateGroupByEmail($company): array
    {
        return $this->createQueryBuilder('o')
            ->join('o.candidates', 'ca')
            ->join('o.company', 'co')
            ->where('co.id = :id')
            ->setParameter('id', $company->getId())
            ->groupBy('ca.email')
            ->getQuery()
            ->getResult();
    }

    /**
     * This method returns the list of offers from a company ti which a candidate has applied
     * @param Candidate $candidate
     * @return array
     */
    public function findOffersForCandidateOfOneCompany( Candidate $candidate ): array
    {
        // SELECT * FROM `offer`
        // INNER JOIN `candidate` ON `candidate`.`offer_id` = `offer`.`id`
        // INNER JOIN `company` ON `company`.`id` = `offer`.`company_id`
        // WHERE `company`.`id` = 127
        // AND `candidate`.`email` LIKE 'anastasie99@bodin.com';

        $query = $this->createQueryBuilder('o')
            ->join('o.candidates', 'ca')
            ->join('o.company', 'co')
            ->Where('co.id = :id')
            ->andWhere('ca.email = :email')
            ->setParameter('id', $candidate->getOffer()->getCompany()->getId())
            ->setParameter('email', $candidate->getEmail());

        return $query->getQuery()
            ->getResult();
    }

    // ************ API REQUEST *************
    /**
     * This method show all offers by different filters
     * @param int $offset
     * @param int $limit
     * @param string|null $location
     * @param bool|null $fulltime
     * @param string|null $text
     * @return array
     */
    public function offersApi(int $offset, int $limit, ?string $location = null, ?bool $fulltime = false, ?string $text = null): array
    {
        //SELECT * FROM `offer` 
        // JOIN `company`
        // JOIN `contract`
        // WHERE `company`.`country` LIKE 'canada' AND `contract`.`name` LIKE 'CDI' AND `company`.`name` LIKE 'itaque'
        // ORDER BY `offer`.created_At DESC
        // LIMIT 2
        // OFFSET 3;

        $query = $this->createQueryBuilder('o')
            ->join('o.company', 'c')
            ->join('o.contract', 'ct')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($location) {
            $query->Where('c.country LIKE :location')
                ->setParameter('location', '%' . $location . '%');
        }

        if ($fulltime) {
            $query->andWhere('ct.name = :contract')
                ->setParameter('contract', 'CDI');
        }

        if ($text) {
            $query->andWhere('c.name LIKE :name')
                ->setParameter('name', '%' . $text . '%');
        }

        return $query->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
