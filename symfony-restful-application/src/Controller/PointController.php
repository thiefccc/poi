<?php

namespace App\Controller;

use App\Entity\Points;
use App\Services\PointService;
use Exception;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class PointController. Contains allowed Points of Interest operations.
 * @package App\Controller
 *
 * @Route("/api/points", name="api_points_")
 */
class PointController extends AbstractController
{
    private $service;
    private $serializer;

    public function __construct(PointService $service, SerializerInterface $serializer)
    {
        $this->service = $service;
        $this->serializer = $serializer;
    }

    /**
     *  Try to create POI and write to Database
     * @param Request $request
     * @return JsonResponse
     * @Route("/create", name="create", methods={"POST"}, format="application/json")
     */
    public function create(Request $request): JsonResponse
    {
        $inputJson = json_decode($request->getContent(), true);
        $createdPoint = $this->service->createPoint($inputJson);
        return new JsonResponse(['createdPointId' => $createdPoint->getPointId()], Response::HTTP_OK, []);
    }

    /**
     *  Try to update POI by it's ID
     * @Route("/update/{pointId}",
     *     requirements={"pointId"="\d+"}, name="update", methods={"PUT"}, format="application/json")
     * @param Request $request
     * @param $pointId
     * @return JsonResponse
     * @throws Exception
     */
    public function update(Request $request, $pointId): JsonResponse
    {
        $inputJson = json_decode($request->getContent(), true);
        $updatedPoint = $this->service->updatePoint($inputJson, $pointId);
        return new JsonResponse(['updatedPointId' => $updatedPoint->getPointId()], Response::HTTP_OK);
    }

    /**
     * @Route("/getPointsInRadius", name="getpointsinradius", methods={"GET"})
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function getPointsInRadius(Request $request): ?Response
    {
        // Get params from GET query
        $IP = $request->query->get('IP') ?? '';
        $radius = $request->query->get('radius') ?? 1;
        $lat = $request->query->get('lat');
        $lon = $request->query->get('lon');

        $resultArray = $this->service->getPointsInRadius($IP, $radius, $lat, $lon);
        $data = $this->serializer->serialize($resultArray, 'json');

        if (!isset($resultArray) || count($resultArray) <= 0) {
            throw new Exception('No one point has been found.');
        }

        return new Response($data, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/getPointsInCity", name="getpointsincity", methods={"GET"})
     */
    public function getPointsInCity(Request $request)
    {
        $city = $request->query->get('city');
        $limit = $request->query->get('limit') ?? 50;
        $offset = $request->query->get('offset') ?? 0;

        $pointsInCity = $this->service->getPointsInCity($city, $limit, $offset);
        $data = $this->serializer->serialize($pointsInCity, 'json');

        if (!isset($pointsInCity) || count($pointsInCity) <= 0) {
            throw new Exception('No one point has been found in city '. $city . '.');
        }

        return new Response($data, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }
}