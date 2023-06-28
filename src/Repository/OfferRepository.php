<?php

namespace App\Repository;

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
     * This function return offer order by desc
     * @return array
     */
    public function findOfferOrderDesc(): array
    {
        // SELECT * FROM `offer` 
        // JOIN `user`
        // WHERE user.is_deleted = true
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
            ->where('o.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

    // ************ API REQUEST *************
    public function offersApi(int $offset, int $limit, ?string $location = null, ?bool $fulltime = false, ?string $text = null)
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
            $query
            ->andWhere('ct.name = :contract')
            ->setParameter('contract', 'CDI');
        }
        
        if ($text) {
            $query
                ->andWhere('c.name = :name')
                ->setParameter('name', $text);
        }

        return $query->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
