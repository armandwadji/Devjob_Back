<?php

namespace App\Repository;

use App\Entity\RequirementItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RequirementItem>
 *
 * @method RequirementItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method RequirementItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method RequirementItem[]    findAll()
 * @method RequirementItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequirementItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RequirementItem::class);
    }

    public function save(RequirementItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RequirementItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
