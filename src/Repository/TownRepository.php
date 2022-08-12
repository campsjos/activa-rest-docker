<?php

namespace App\Repository;

use App\Entity\Town;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Town>
 *
 * @method Town|null find($id, $lockMode = null, $lockVersion = null)
 * @method Town|null findOneBy(array $criteria, array $orderBy = null)
 * @method Town[]    findAll()
 * @method Town[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TownRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Town::class);
    }

    public function add(Town $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Town $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find Town by it's name and Province id
     *
     * @param string $name Town name
     * @param ?string $parentId Province id
     * @return Town|null
     */
    public function findByNameAndParent(string $name, ?string $parentId): ?Town
    {
        if (!$parentId) return null;

        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT * FROM property p
            WHERE p.province_id = :parent_id
            LIMIT 1
            ';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['parent_id' => $parentId]);

        $result = $resultSet->fetchOne();
    
        if(!$result) {
            $result = null;
        }
        return $result;
    }

    //    /**
    //     * @return Town[] Returns an array of Town objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Town
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
