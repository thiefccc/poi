<?php

namespace App\Repository;

use App\Entity\PointType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PointType|null find($id, $lockMode = null, $lockVersion = null)
 * @method PointType|null findOneBy(array $criteria, array $orderBy = null)
 * @method PointType[]    findAll()
 * @method PointType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PointTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PointType::class);
    }
}
