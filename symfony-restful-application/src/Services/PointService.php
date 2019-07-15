<?php


namespace App\Services;


use App\Entity\Point;
use App\Services\ThirdParty\GeoPoint;
use Exception;
use App\Repository\PointRepository;
use Doctrine\ORM\EntityManagerInterface;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class PointService
 * @package App\Services
 */
class PointService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var PointRepository
     */
    private $pointRepository;

    /**
     * @var PointTypeService
     */
    private $pointTypeService;

    /**
     * @var GeoIpService
     */
    private $geoIpService;

    /**
     * PointService constructor.
     * @param PointRepository $repository
     * @param PointTypeService $pointTypeService
     * @param EntityManagerInterface $entityManager
     * @param GeoIpService $geoIpService
     */
    public function __construct(
        PointRepository $repository,
        PointTypeService $pointTypeService,
        EntityManagerInterface $entityManager,
        GeoIpService $geoIpService)
    {
        $this->pointRepository = $repository;
        $this->pointTypeService = $pointTypeService;
        $this->em = $entityManager;
        $this->geoIpService = $geoIpService;
    }

    /**
     * @param array $data
     * @return Point
     */
    public function createPoint(array $data): Point
    {
        try{
            $point = $this->buildForCreate($data);
        } catch (Exception $ex){
            throw new HttpException(Response::HTTP_NOT_ACCEPTABLE, $ex->getMessage());
        }

        $this->em->persist($point);
        try {
            $this->em->flush();
        } catch (Exception $ex) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Error during saving point to DB: ' . $ex->getMessage());
        }

        return $point;
    }

    /**
     * @param array $data
     * @param $pointId
     * @return Point|null
     * @throws Exception
     */
    public function updatePoint(array $data, $pointId): ?Point
    {
        if (isset($pointId)) {
            $point = $this->pointRepository->find($pointId);
        } else {
            throw new Exeption("A 'PointId' hasn't been received.");
        }

        if (!isset($point)) {
            throw new Exception("Point with Id '$pointId' hasn't been found in Database.");
        }

        $this->buildForUpdate($data, $point);
        $this->em->persist($point);
        try {
            $this->em->flush();
        } catch (Exception $ex) {
            throw new Exception("Error during updating point '$pointId' in: " . $ex->getMessage(), 5);
        }

        return $point;
    }

    /**
     * @param $IP
     * @param $radius
     * @param $lat
     * @param $lon
     * @return array
     * @throws AddressNotFoundException
     * @throws InvalidDatabaseException
     */
    public function getPointsInRadius($IP, $radius, $lat, $lon): array
    {
        // If lan or lot haven't been passed, try to get them from IP
        if ($lat === null || $lon === null) {
            $ip = isset($IP) && $IP === '' ? $_SERVER['REMOTE_ADDR'] : $IP;
            $coords = $this->geoIpService->getIpCoordinates($ip);
            $lat = $coords['lat'];
            $lon = $coords['lon'];
        }

        // If here lan and lot yet haven't been calculated: throw an error
        if ($lat === null || $lon === null) {
            throw new Exeption("Error in getPointsInRadius. Coordinates of a user geo point haven't been defined.", 7);
        }

        // Get a Bound box with radius-deltas from the current point with a third party GeoPoint class
        $coordArray = (new GeoPoint($lat, $lon, GeoPoint::DEGREES))
            ->boundingCoordinates($radius, GeoPoint::KILOMETERS);

        $firstCutPoints = $this->pointRepository->getFirstCut(
                $coordArray[0]->getLatitude(), $coordArray[0]->getLongitude(),
                $coordArray[1]->getLatitude(), $coordArray[1]->getLongitude());

        // First Cut: select all geo Points in this box as a First Cut log(n)
        $latRad = GeoPoint::convertDegreesToRadians($lat);
        $lonRad = GeoPoint::convertDegreesToRadians($lon);

        $resultArray = array();

        // Does a point lay in a circle with the radius? (Check distance for each point in First Cut)
        foreach ($firstCutPoints as $point) {
            // Extract part of the GeoPoint class for less convertions
            $pointLatRad = GeoPoint::convertDegreesToRadians($point->getPointLatitude());
            $pointLonRad = GeoPoint::convertDegreesToRadians($point->getPointLongitude());
            $distance = acos(sin($pointLatRad) * sin($latRad) + cos($pointLatRad) * cos($latRad) * cos($lonRad - $pointLonRad)) * GeoPoint::EARTH_RADIUS_KM;
            if ($distance <= $radius) {
                $resultArray[] = $point;
            }
        }

        return $resultArray;
    }

    /**
     * @param $city
     * @param $limit
     * @param $offset
     * @return array
     * @throws AddressNotFoundException
     * @throws InvalidDatabaseException
     */
    public function getPointsInCity($city, $limit, $offset): array {
        if (!isset($city) || $city === '') {
            $ip = $_SERVER['REMOTE_ADDR'];
            if (!isset($ip) || $ip === '') {
                throw new Exeption('City param hasn\'t been received and IP address hasn\'t been determined. '
                    . 'There is no way to find Points in undefined city.');
            }

            $locCity = $this->geoIpService->getCityNameByIp($ip);
        } else {
            $locCity = $city;
        }

        return $this->pointRepository->findBy(
                array('pointCity' => $locCity)
                , null
                , $limit
                , $offset
            );
    }


    /**
     * @param array $data
     * @return Point
     * @throws Exception
     */
    private function buildForCreate(array $data): Point{
        $point = new Point();
        try{
            $pointType = $this->pointTypeService->getOneById(
                $this->checkParam('pointTypeId', $data)
            );
        }catch (Exception $ex){
            throw $ex;
        }

        try {
            if (array_key_exists('pointCity', $data)) {
                $cityName = $data['pointCity'];
            } else {
                $cityName = $this->getCityNameByIp($_SERVER['REMOTE_ADDR']);
            }
        } catch (Exception $ex) {
            $cityName = $this->geoIpService::NOT_A_CITY;
        }

        return $point
            ->setPointName($this->checkParam('pointName', $data))
            ->setPointLatitude($this->checkParam('pointLatitude', $data))
            ->setPointLongitude($this->checkParam('pointLongitude', $data))
            ->setPointDescription($data['pointDescription'])
            ->setPointCity($cityName)
            ->setPointType($pointType);
    }

    /**
     * @param array $data
     * @param Point $point
     * @throws Exception
     */
    private function buildForUpdate(array $data, Point $point): void{
        // TODO create builder for point with Null check
        $key = 'pointName';
        if (array_key_exists($key, $data)) {
            $point->setPointName($data[$key]);
        }
        $key = 'pointLatitude';
        if (array_key_exists($key, $data)) {
            $point->setPointLatitude($data[$key]);
        }
        $key = 'pointLongitude';
        if (array_key_exists($key, $data)){
            $point->setPointLongitude($data[$key]);
        }
        $key = 'pointCity';
        if (array_key_exists($key, $data)) {
            $point->setPointCity($data[$key]);
        }
        $key = 'pointDescription';
        if (array_key_exists($key, $data)) {
            $point->setPointDescription($data[$key]);
        }
        $key = 'pointTypeId';
        if (array_key_exists($key, $data)) {
            $pointTypeId = $data[$key];
            if($point->getPointType() !== null && $point->getPointType()->getPointTypeId() !== $pointTypeId){
                try{
                    $pointType = $this->pointTypeService->getOneById($data['pointTypeId']);
                }catch (Exception $ex){
                    throw $ex;
                }

                $point->setPointType($pointType);
            }
        }
    }

    /**
     * Check if array of received params contains key
     * @param $key
     * @param $array
     * @return mixed
     * @throws Exception
     */
    private function checkParam($key, $array)
    {
        if (!array_key_exists($key, $array)) {
            throw new Exception("No '$key' param recieved.");
        }

        return $array[$key];
    }
}