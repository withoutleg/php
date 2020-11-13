<?php

namespace App\Repository;

use App\Entity\Establishment;
use App\Entity\EstablishmentRaw;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Establishment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Establishment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Establishment[]    findAll()
 * @method Establishment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstablishmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Establishment::class);
    }

    public function findBySquareAndRadius($coordinates, $coordinates_square, $radius):array
    {
        $conn = $this->getEntityManager()->getConnection();

        $stmt = $conn->prepare(
            'SELECT id, name, CAST(6371000 * 2 * ASIN(SQRT( POWER(SIN((:latitude - e.latitude) *
            pi()/180 / 2), 2) +
            COS(:latitude * pi()/180) * COS(e.latitude * pi()/180) *
            POWER(SIN((:longitude - e.longitude) * PI()/180 / 2), 2) )) AS INT) as distance
            FROM establishment e
            WHERE e.latitude >= :from_Y AND e.latitude <= :to_Y 
            AND e.longitude >= :from_X AND e.longitude <= :to_X AND
            (6371000 * 2 * ASIN(SQRT( POWER(SIN((:latitude - e.latitude) *
            pi()/180 / 2), 2) +
            COS(:latitude * pi()/180) * COS(e.latitude * pi()/180) *
            POWER(SIN((:longitude - e.longitude) * PI()/180 / 2), 2) ))) <= :radius
            ORDER BY distance'
        );
        $stmt->execute($coordinates + $coordinates_square + ['radius' => $radius]);

        foreach ($stmt as $values) {
            $ret[] = new EstablishmentRaw($values['id'], $values['name'], $values['distance']);
        }

        return $ret;
    }

    // /**
    //  * @return Establishment[] Returns an array of Establishment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Establishment
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
