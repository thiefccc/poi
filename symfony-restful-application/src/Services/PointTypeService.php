<?php

namespace App\Services;

use Exception;
use App\Entity\PointType;
use App\Repository\PointTypeRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class PointTypeService
 * @package App\Services
 */
class PointTypeService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var PointTypeRepository
     */
    private $repository;


    /**
     * PointTypeService constructor.
     * @param PointTypeRepository $repository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(PointTypeRepository $repository, EntityManagerInterface $entityManager)
    {
        $this->repository = $repository;
        $this->em = $entityManager;
    }

    /**
     * @return PointType[]
     */
    public function getAllPointTypes(){
        return $this->repository->findAll();
    }

    /**
     * @param $id
     * @return PointType|null
     * @throws Exception
     */
    public function getOneById($id){
        $typeCandidate = $this->repository->find($id);

        if($typeCandidate == null)
            throw new Exception("PointType hasn't been retrieved from Database.");

        return $typeCandidate;
    }
}