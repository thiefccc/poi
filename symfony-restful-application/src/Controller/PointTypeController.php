<?php

namespace App\Controller;

use App\Services\PointTypeService;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/types", name="api_types_")
 */
class PointTypeController extends AbstractController
{
    private $service;
    private $serializer;

    public function __construct(PointTypeService $service, SerializerInterface $serializer)
    {
        $this->service = $service;
        $this->serializer = $serializer;
    }

    /**
     * @Route("", name="get_all", methods={"GET"})
     *
     * @return Response
     */
    public function getAll(){
        $types = $this->service->getAllPointTypes();

        $data = $this->serializer->serialize($types, 'json');

        return new Response($data, Response::HTTP_OK);
    }
}
