<?php

namespace App\Tests;

use GuzzleHttp;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class PointControllerTest extends TestCase
{
    private $affectedPointId;
    private $client;

    public function createPointParamsProvider()
    {
        return [
            [array(), Response::HTTP_BAD_REQUEST],
            [array('pointLatitude' => 2.1, 'pointLongitude' => 1.1, 'pointType' => array('pointTypeId' => 1)), Response::HTTP_BAD_REQUEST],
            [array('pointName' => 'Uncreatable', 'pointLongitude' => 1.1, 'pointType' => array('pointTypeId' => 1)), Response::HTTP_BAD_REQUEST],
            [array('pointName' => 'Uncreatable', 'pointLatitude' => 2.1, 'pointType' => array('pointTypeId' => 1)), Response::HTTP_BAD_REQUEST],
            [array('pointName' => 'Uncreatable', 'pointLatitude' => 2.1, 'pointLongitude' => 1.1), Response::HTTP_BAD_REQUEST],
            [array('pointName' => 'Uncreatable', 'pointLatitude' => 233.1, 'pointLongitude' => 1.1), Response::HTTP_BAD_REQUEST]
        ];
    }

    /**
     * @dataProvider createPointParamsProvider
     */
    public function testCreateClassic($data, $expectedStatusCode)
    {
        $request = $this->client->post('create', [GuzzleHttp\RequestOptions::JSON => $data]);

        $this->assertEquals($expectedStatusCode, $request->getStatusCode());
        if ($expectedStatusCode == Response::HTTP_OK) {
            $responseData = json_decode($request->getBody(true), true);
            $this->assertArrayHasKey('createdPointId', $responseData);
            if (array_key_exists('createdPointId', $responseData)) {
                $affectedPointId = $responseData['createdPointId'];
                return $affectedPointId;
            }
        }
    }


    // Service: Checking if point with checking coordinates exists in $array
    public function updatePointParamsProvider()
    {
        return [
            [array(), $this->affectedPointId, Response::HTTP_BAD_REQUEST],
            [array(
                'pointName' => 'Test Point Name',
                'pointLatitude' => 99.12345678,
                'pointLongitude' => 123.1234546,
                'pointCity' => 'Test City',
                'pointDescription' => 'Test Description',
                'pointType' => array('pointTypeId' => 1)
            ), null, Response::HTTP_BAD_REQUEST],
            [array(
                'pointName' => 'Test Point Name',
                'pointLatitude' => 99.12345678,
                'pointLongitude' => 123.1234546,
                'pointCity' => 'Test City',
                'pointDescription' => 'Test Description',
                'pointType' => array('pointTypeId' => 1)
            ), -96325789, Response::HTTP_BAD_REQUEST]
        ];
    }

    /* 1. Empty point info
     * 2. No name
     * 3. No latitude
     * 4. No longitude
     * 5. No type
     * 6. Too big Latitude
     * 7. TODO Uniqueness constraint violation
     * */
    /**
     * @dataProvider updatePointParamsProvider
     */
    public function testUpdate($data, $pointId, $expectedStatusCode)
    {
        $request = $this->client->put("update/$pointId", [GuzzleHttp\RequestOptions::JSON => $data]);
        $this->assertEquals($expectedStatusCode, $request->getStatusCode());
        if ($expectedStatusCode == Response::HTTP_OK) {
            $responseData = json_decode($request->getBody(true), true);
            $this->assertArrayHasKey('updatedPointId', $responseData);
        }
    }


    public function testCreateSuccess()
    {
        $data = array(
            'pointName' => 'Test Point Name',
            'pointLatitude' => 23.12345678,
            'pointLongitude' => 120.1234546,
            'pointCity' => 'Test City',
            'pointDescription' => 'Test Description',
            'pointTypeId' => 1);

        $request = $this->client->post('create', [GuzzleHttp\RequestOptions::JSON => $data]);

        $this->assertEquals(Response::HTTP_OK, $request->getStatusCode());
        if ($request->getStatusCode() == Response::HTTP_OK) {
            $responseData = json_decode($request->getBody(true), true);
            $this->assertArrayHasKey('createdPointId', $responseData);
            return $responseData['createdPointId'];
        }
    }


    /*
     * 1. No Passed point data
     * 2. No Point ID param
     * 3. Unexistence Point ID
     * 4. Success Update TODO
     * */

    /**
     * @depends  testCreateSuccess
     */
    public function testUpdateSuccess($pointId)
    {
        $data = array(
            'pointName' => 'Test Point Name UPDATED',
            'pointLatitude' => 99.12345678,
            'pointLongitude' => 111.1234546,
            'pointCity' => 'Test City UPDATED',
            'pointDescription' => 'Trying to UPDATE point with same Uniqueness index',
            'pointTypeId' => 2);

        $request = $this->client->put("update/$pointId", [GuzzleHttp\RequestOptions::JSON => $data]);
        $this->assertEquals(Response::HTTP_OK, $request->getStatusCode());
        if ($request->getStatusCode() == Response::HTTP_OK) {
            $responseData = json_decode($request->getBody(true), true);
            $this->assertArrayHasKey('updatedPointId', $responseData);
            $this->deleteTestPoint($pointId);
        }
    }

    private function deleteTestPoint($pointId)
    {
        // TODO implement
    }

    // Success point creation
    public function getPointsInRadiusProvider()
    {
        return [
            ['radius=5&lon=104.18235500&lat=52.33954600', 52.30217400, 104.24742800, false, Response::HTTP_OK],
            ['radius=5&lon=104.18235500&lat=52.33954600', 52.32989900, 104.24970900, true, Response::HTTP_OK],
            ['radius=5&lon=99.18235500&lat=52.33954600', null, null, null, Response::HTTP_BAD_REQUEST]
        ];
    }

    /**
     * @dataProvider getPointsInRadiusProvider
     */
    public function testGetPointsInRadius($paramString, $checkingLat, $checkingLon, $layInCircle, $expectedStatusCode): void
    {
        $request = $this->client->get("getPointsInRadius?$paramString");//, [GuzzleHttp\RequestOptions::JSON => $data]);
        $this->assertEquals($expectedStatusCode, $request->getStatusCode());
        if ($expectedStatusCode == Response::HTTP_OK) {
            $responseData = json_decode($request->getBody(true), true);
            $this->assertTrue($this->checkPointInArray($responseData, $checkingLat, $checkingLon) == $layInCircle);
        }
    }


    /* 1. Point doesn't lay in radius
     * 2. Point lay in radius
     * 3. There are no points
     * 4. A bunch of radius and IP checks
     * */

    private function checkPointInArray($array, $checkingLat, $checkingLon)
    {
        foreach ($array as $point) {
            if ((float)$point['point_latitude'] == $checkingLat && (float)$point['point_longitude'] == $checkingLon)
                return true;
        }

        return false;
    }


    protected function setUp(): void
    {
        // Create Guzzle http client
        $this->client = new Client(array(
            'base_uri' => 'http://localhost:8084/api/points/',
            'http_errors' => false,
            'request.options' => array(
                'exceptions' => false
            )
        ));
    }

    protected function tearDown(): void
    {
        $this->client = null;
        // TODO Check row for test point and delete it
    }
}
