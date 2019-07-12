<?php

namespace App\Repository;

use App\Entity\Point;
use App\Entity\Points;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Point|null find($id, $lockMode = null, $lockVersion = null)
 * @method Point|null findOneBy(array $criteria, array $orderBy = null)
 * @method Point[]    findAll()
 * @method Point[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PointRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Point::class);
    }

    /**
     * @param $minLat
     * @param $minLon
     * @param $maxLat
     * @param $maxLon
     * @return array
     */
    public function getFirstCut($minLat, $minLon, $maxLat, $maxLon): array
    {
        $coordinatesParameters = array(
            $minLat, $maxLat,
            $minLon, $maxLon
        );

        return $this->createQueryBuilder('pt')
            ->andWhere('pt.pointLatitude >= ?0')
            ->andWhere('pt.pointLatitude <= ?1')
            ->andWhere('pt.pointLongitude >= ?2')
            ->andWhere('pt.pointLongitude <= ?3')
            ->setParameters($coordinatesParameters)
            ->getQuery()
            ->getResult();
    }
}
