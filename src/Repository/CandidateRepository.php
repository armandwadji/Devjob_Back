<?php

namespace App\Repository;

use App\Entity\Candidate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Candidate>
 *
 * @method Candidate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Candidate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Candidate[]    findAll()
 * @method Candidate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CandidateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidate::class);
    }

    public function save(Candidate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Candidate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * This function return all candidates by company
     * @param int|null $companyId
     * @return array
     */
    public function findCandidatesByCompany(?int $companyId): array
    {
        $query = $this->createQueryBuilder('ca')
            // ->join('ca.offer', 'o')
            // ->join('o.company', 'co')
            // ->Where('co.id = :id')
            // ->setParameter('id', $companyId )
        ;

        return $query->groupBy('ca.email')
            ->getQuery()
            ->getResult();

        // SELECT firstname, lastname, email, company_id, COUNT(`candidate`.`email`) as count FROM `candidate`
        //     INNER JOIN `candidate_offer` ON `candidate_offer`.`candidate_id` = `candidate`.`id`
        //     INNER JOIN `offer` ON `offer`.`id` = `candidate_offer`.`offer_id`
        //     WHERE `offer`.`company_id` = 748
        //     GROUP BY `candidate`.`email`  
        //     ORDER BY `count` DESC;
        
        // $conn = $this->getEntityManager()->getConnection();

        // $sql = '
        //             SELECT * , COUNT(`candidate`.`email`) as count FROM `candidate` 
        //             INNER JOIN `offer` ON `candidate`.`offer_id` = `offer`.`id`
        //             INNER JOIN `company` ON `offer`.`company_id` = `company`.`id`
        //             WHERE `company`.id = :id
        //             GROUP BY `candidate`.`email`
        //         ';


        // $stmt = $conn->prepare($sql);
        // $resultSet = $stmt->executeQuery(['id' => $companyId]);

        // return $resultSet->fetchAllAssociative();
    }
}
