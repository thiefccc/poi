# Points of Interest HTTP API
Allow to work with *points of interest* (POI) (such as museums, parks, graffity, monuments, etc).  
Represents a few methods:  
  - Create POI
  - Update POI by it's ID
  - Get POI in a Radius (default 1 km)
  - Get POI in a City of requesting
  - Get Points' Types

## Usage

#### Create POI
``` http
http://server/api/points/create
```
Adds a point to a Database. Returns JSON with added point ID or with error.  
Method: POST  
Content-Type: application/json  
Request Body:  
``` json
{
    "pointName": "name",
    "pointLatitude": 43.123456,
    "pointLongitude": 123.123456,
    "pointDescription": "description",
    "pointTypeId": 1,
    "pointCity": "Default city"
}
```
Where:

| ParamName | Format | Required |
| ------ | ------ | ------ |
| pointName | string (64) | V |
| pointLatitude | number (10,8) | V |
| pointLongitude | number (11,8) | V |
| pointDescription | string (4000) | |
| pointTypeId | integer | V |
| pointCity | string (150) | |

#### Update POI
``` http
http://server/api/points/update/{ID}
```
Updates ANY parameter of a point in Database by it's ID. Returns JSON with changed point ID or with error.  
Method: PUT  
Where URL prams:  

| ParamName | Format | Required |
| ------ | ------ | ------ |
| ID | int | V |

Content-Type: application/json  
Request Body:
``` json
{
    "pointName": "name",
    "pointLatitude": 43.123456,
    "pointLongitude": 123.123456,
    "pointDescription": "description",
    "pointTypeId": 1,
    "pointCity": "Default city"
}
```
Where body params:

| ParamName | Format | Required |
| ------ | ------ | ------ |
| pointName | string (64) | |
| pointLatitude | number (10,8) | |
| pointLatitude | number (11,8) | |
| pointDescription | string (4000) | |
| pointTypeId | integer | |
| pointCity | string (150) | |

At least one parameter should be passed in body request.

#### Get POI in Radius
``` http
http://server/api/points/getPointsInRadius?IP=VAL&radius=VAL&lon=VAL&lat=VAL
```
Returns JSON with array of poin-objects laying in circle with **radius** and **center** passed through URL (or determined by IP).    
Method: POST  
Where URL prams:  

| ParamName | Format | Required | Default |
| ------ | ------ | ------ | ------ |
| IP | XXX.XXX.XXX.XXX |  |  |
| radius | int in KM | | 1 |
| lan | number (10,8) | |  |
| lot | number (11,8) | |  |

> **_WARNING:_**
URL parameter: IP will be only used as for determine Requester coordinates only if **lan, lot** (only together) haven't been passed.

#### Get POI in City
``` http
http://server/api/points/getPointsInCity/?city=VAL&limit=VAL&offset=VAL
```
Returns JSON with array of point-objects with a pointCity parameter equals to a **city** from URL-parameter (or from extracted from IP) limited by **limit** parameter with pagination equals **offcet**.    
Method: GET    
Where URL prams:  

| ParamName | Format | Required | Default |
| ------ | ------ | ------ | ------ |
| limit | int |  | 50 |
| pagination | int | | 0 |
| city | string (150) | |  |

Content-Type: application/json

#### Get Points' Types in City
``` http
http://server/api/types
```
Returns JSON with array of all points types.      
Method: GET    
Content-Type: application/json  

### Plugins
GeoIP2.  
GeoPoint package.  

### Todos

 - Many of them  
 - And more  

[//]: # 
