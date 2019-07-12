<?php


namespace App\Services;


use Exception;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use GeoIp2\Model\City;
use MaxMind\Db\Reader\InvalidDatabaseException;

class GeoIpService
{
    public const NOT_A_CITY = 'Not a city';

    /**
     * @param $ip
     * @return array
     * @throws AddressNotFoundException
     * @throws InvalidDatabaseException
     */
    public function getIpCoordinates($ip): array
    {
        $record = $this->getGeoIp2Record($ip);
        return array(
            'lat' => $record->location->latitude,
            'lon' => $record->location->longitude
        );
    }

    /**
     * @param $ip
     * @param string $localizatoin
     * @return string
     * @throws AddressNotFoundException
     * @throws InvalidDatabaseException
     */
    public function getCityNameByIp($ip, $localizatoin = 'ru'): string
    {
        $record = $this->getGeoIp2Record($ip);

        if (array_key_exists($localizatoin, $record->city->names)) {
            return $record->city->names[$localizatoin];
        }

        return self::NOT_A_CITY;
    }

    /**
     * @param $ip
     * @return City
     * @throws AddressNotFoundException
     * @throws InvalidDatabaseException
     */
    private function getGeoIp2Record($ip): City
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new Exception("Error in getGeoIp2Record: IP address '$ip' is not valid.", 9);
        }

        $gipReader = new Reader($this->getGeoIP2BasePath());

        return $gipReader->city($ip);
    }

    /**
     * @return string
     */
    private function getGeoIP2BasePath(): string
    {
        $ds = DIRECTORY_SEPARATOR;
        // "/home/virtwww/w_vrv-poi-ru_858fed1a/http"
        return $_SERVER['DOCUMENT_ROOT'] . $ds . '..' . $ds . 'vendor' . $ds . 'geoip2' . $ds . 'geoip2' . $ds . 'maxmind-db' . $ds . 'GeoLite2-City.mmdb';
    }
}