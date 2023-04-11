<?php

namespace App\Repository;

use App\Entity\Requirement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Requirement>
 *
 * @method Requirement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Requirement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Requirement[]    findAll()
 * @method Requirement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequirementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Requirement::class);
    }

    public function save(Requirement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Requirement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
