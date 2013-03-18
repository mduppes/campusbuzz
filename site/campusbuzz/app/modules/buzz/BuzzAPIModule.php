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
        case 'detail':
            
            break;

        // AJAX calls
        case 'getPosts':
        break;
        case 'getMapPins':
          $isOfficial=$this->getArg('isOfficial',0);
          $lat = $this->getArg('lat', 0);
          $lon = $this->getArg('lon', 0);
          $radius= $this->getArg('distance',0); // in metres

          // Change this for # of results returned
          $numResultsReturned = 50;

          // for now since everything is official
          $isOfficial = true;
          
          // $category, $keywords, etc TODO
          $mapPinsSearchQuery = SearchQueryFactory::createGeoRadiusSearchQuery($lat, $lon, $radius);
          $mapPinsSearchQuery->addFilter(new FieldQueryFilter("officialSource", $isOfficial));
          $mapPinsSearchQuery->setMaxItems($numResultsReturned);

          // Fields we want returned from solr
          //$mapPinsSearchQuery->addReturnField("title");
          $mapPinsSearchQuery->addReturnField("officialSource");
          $mapPinsSearchQuery->addReturnField("category");
          $mapPinsSearchQuery->addReturnField("locationGeo");
          // TODO: add keywords if given, and category filters
          
          // Get and convert solr response to php object
          $data = $feedItemSolrController->query($mapPinsSearchQuery);          

          if (isset($data["response"])) {
            $pins = json_encode($data["response"]);
          } else {
            throw new KurogoDataException("Error, not a valid response.");
          }
          
          $this->setResponse($pins);
          $this->setResponseVersion(1);

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