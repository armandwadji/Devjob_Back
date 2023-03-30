<?php

namespace App\Repository;

use App\Entity\RoleItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RoleItem>
 *
 * @method RoleItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method RoleItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method RoleItem[]    findAll()
 * @method RoleItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoleItem::class);
    }

    public function save(RoleItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RoleItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
