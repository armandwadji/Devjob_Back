<?php

namespace App\Repository;

use App\Entity\Candidate;
use App\Entity\Company;
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
     * This method return all candidates and or by company
     * @param int|null $companyId
     * @return array
     */
    public function findCandidatesGroupByEmail(?Company $company = null): array
    {
        // SELECT * FROM `offer`
        // INNER JOIN `candidate` ON `candidate`.`offer_id` = `offer`.`id`
        // INNER JOIN `company` ON `company`.`id` = `offer`.`company_id`
        // WHERE `company`.`id` = 127
        // AND `candidate`.`email` LIKE 'anastasie99@bodin.com';

        $query = $this->createQueryBuilder('ca')
            ->join('ca.offer', 'o')
            ->join('o.company', 'co');

        if ($company) {
            $query->Where('co.id = :id')
                ->setParameter('id', $company->getId());
        }

        return $query->groupBy('ca.email')
            ->getQuery()
            ->getResult();
    }

    public function findCandidatesForOneOffer(Candidate $candidate)
    {

        // SELECT * FROM `offer`
        // INNER JOIN `candidate` ON `candidate`.`offer_id` = `offer`.`id`
        // INNER JOIN `company` ON `company`.`id` = `offer`.`company_id`
        // WHERE `company`.`id` = 127
        // AND `candidate`.`email` LIKE 'anastasie99@bodin.com';

        $query = $this->createQueryBuilder('ca')
            ->join('ca.offer', 'ca')
            ->join('o.company', 'co')
            ->Where('co.id = :id')
            ->andWhere('ca.email = :email')
            ->setParameter('id', $candidate->getOffer()->getCompany()->getId())
            ->setParameter('email', $candidate->getEmail());

        return $query->getQuery()
            ->getResult();
    }
}
