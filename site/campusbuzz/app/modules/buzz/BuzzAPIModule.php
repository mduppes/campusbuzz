<?php
class BuzzAPIModule extends APIModule
{
  protected $id='buzz';

  protected $vmin= 1;
  protected $vmax= 1;

  public function initializeForCommand(){
    //instantiate controller

    $feedItemSolrController = DataRetriever::factory('FeedItemSolrDataRetriever', array());
    switch ($this->command){
        case 'index':
            break;
        // AJAX calls
        case 'getMapPins':

          $isOfficial=$this->getArg('isOfficial',0);
          $lat = $this->getArg('lat', 0);
          $lon = $this->getArg('lon', 0);
          $radius= $this->getArg('distance',0); // in metres
          $numResults = 200;

          $solrResult = UserResponse::getGeoRadiusResponse($feedItemSolrController, $lat, $lon, $radius, $isOfficial, $numResults);

          $this->setResponse($solrResult);
          $this->setResponseVersion(1);

          break;

        case 'filterOut':

            $numResults = 200;
            $isOfficial=$this->getArg('isOfficial',0);
            $categoryArray= $this->getArg('categoryList', 0);
            $lat = $this->getArg('lat', 0);
            $lon = $this->getArg('lon', 0);
            $radius= $this->getArg('distance',0); // in metres

            $solrResult = UserResponse::getGeoRadiusResponse($feedItemSolrController, $lat, $lon, $radius, $isOfficial, $numResults, null, $categoryArray);

            $this->setResponse($solrResult);
            $this->setResponseVersion(1);
          break;

          case 'searchKeyword':
            $numResults = 200;
            $isOfficial=$this->getArg('isOfficial',0);
            $keyword= $this->getArg('keyword', 0);
            $lat = $this->getArg('lat', 0);
            $lon = $this->getArg('lon', 0);
            $radius= $this->getArg('distance',0); // in metres

            $solrResult = UserResponse::getGeoRadiusResponse($feedItemSolrController, $lat, $lon, $radius, $isOfficial, $numResults, $keyword, null);

            $this->setResponse($solrResult);
            $this->setResponseVersion(1);

            // save user data to solr, reverse bug
            $userLat= $this->getArg ('userLng', 0);
            $userLng= $this->getArg('userLat', 0);

            $searchCoord = new GeoCoordinate($lat, $lon);

            $userCoord = null;
            if (isset($userLng) && isset($userLat)) {
              $userCoord = new GeoCoordinate($userLng, $userLat);
            }

            $category = '';
            $searchMode = "searchKeyword";
            QueryLogger::getQueryLogger()->logUserData($isOfficial, $keyword, $category, $searchCoord, $userCoord, $searchMode);


          break;

          case 'loadMorePosts':
            $isOfficial=$this->getArg('isOfficial',0);
            $keyword= $this->getArg('keyword', 0);
            $neLng = $this->getArg('neLng', 0);
            $neLat= $this->getArg('neLat', 0);
            $swLng= $this->getArg('swLng',0);
            $swLat = $this->getArg('swLat', 0);
            $category = $this->getArg('category', 0);
            $index= $this->getArg('index',0);
            $sort= $this->getArg('sort',0);

            $numResults = 10;

            // $category, $keywords, etc TODO
            $solrResult = UserResponse::getBoundingBoxResponse($feedItemSolrController, $neLng, $neLat, $swLng, $swLat, $isOfficial, $index, $numResults, $keyword, $category, $sort);

            $this->setResponse($solrResult);
            $this->setResponseVersion(1);
          break;

          case 'sendDetailQueryData':
            // this is called when user goes to detail view
            $userLng=$this->getArg('userLat',0);
            $userLat= $this->getArg('userLng', 0);
            $category= $this->getArg('category');
            $neLng= $this->getArg('neLng');
            $neLat= $this->getArg('neLat');
            $swLng= $this->getArg('swLng');
            $swLat= $this->getArg('swLat');
            $isOfficial=$this->getArg('isOfficial',0);
            $keyword= $this-> getArg('keyword',0);
            $sortBy= $this-> getArg ('sort',0);

            //TODO: update solr with user + query data

            $searchLat = ($neLat + $swLat) / 2;
            $searchLon = ($neLng + $swLng) / 2;
            $searchCoord = new GeoCoordinate($searchLat, $searchLon);

            $userCoord = null;
            if (isset($userLng) && isset($userLat)) {
              $userCoord = new GeoCoordinate($userLng, $userLat);
            }
            $searchMode = "detailsView";
            QueryLogger::getQueryLogger()->logUserData($isOfficial, $keyword, $category, $searchCoord, $userCoord, $searchMode);
            break;
    }
  }

  // For testing
  private $pins= '{
    "numFound": 8,
    "start": 0,
    "docs": [
        {
            "title": "pin1",
            "officialSource": "TRUE",
            "category": "life",
            "locationGeo": "49.25957,-123.25433"
        },
        {
            "title": "pin2",
            "officialSource": "TRUE",
            "category": "health",
            "locationGeo": "49.25957,-123.25433"
        },
        {
            "title": "pin3",
            "officialSource": "FALSE",
            "category": "life",
            "locationGeo": "49.25722,-123.24257"
        },
        {
            "title": "pin4",
            "officialSource": "FALSE",
            "category": "health",
            "locationGeo": "49.25722,-123.24257"
        }
        ,
        {
            "title": "pin5",
            "officialSource": "FALSE",
            "category": "health",
            "locationGeo": "49.26080,-123.24592"
        },
        {
            "title": "pin6",
            "officialSource": "FALSE",
            "category": "health",
            "locationGeo": "49.25722,-123.24257"
        }
        ,
        {
            "title": "pin7",
            "officialSource": "FALSE",
            "category": "health",
            "locationGeo": "49.26080,-123.24592"
        },
        {
            "title": "pin8",
            "officialSource": "FALSE",
            "category": "health",
            "locationGeo": "49.25722,-123.24257"
        }
        ,
        {
            "title": "pin9",
            "officialSource": "FALSE",
            "category": "health",
            "locationGeo": "49.26080,-123.24592"
        },
        {
            "title": "pin10",
            "officialSource": "FALSE",
            "category": "club",
            "locationGeo": "49.25722,-123.24257"
        }
        ,
        {
            "title": "pin11",
            "officialSource": "FALSE",
            "category": "leisure",
            "locationGeo": "49.26080,-123.24592"
        }
        ,
        {
            "title": "pin12",
            "officialSource": "FALSE",
            "category": "life",
            "locationGeo": "49.26080,-123.24592"
        }
    ]
    }';


}