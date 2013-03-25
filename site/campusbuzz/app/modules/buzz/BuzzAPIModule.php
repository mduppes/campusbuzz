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

          // Change this for # of results returned
          $numResultsReturned = 50;

          // for now since everything is official
          //$isOfficial = true;

          // $category, $keywords, etc TODO
          $mapPinsSearchQuery = SearchQueryFactory::createGeoRadiusSearchQuery($lat, $lon, $radius);
          $mapPinsSearchQuery->addFilter(new FieldQueryFilter("officialSource", $isOfficial));
          $mapPinsSearchQuery->setMaxItems($numResultsReturned);

          // Sort results by most recent first
          $mapPinsSearchQuery->addSort(new SearchSort("pubDate", false));

          // Fields we want returned from solr
          $mapPinsSearchQuery->addReturnField("title");
          $mapPinsSearchQuery->addReturnField("id");
          $mapPinsSearchQuery->addReturnField("officialSource");
          $mapPinsSearchQuery->addReturnField("category");
          $mapPinsSearchQuery->addReturnField("locationGeo");
          // TODO: add keywords if given, and category filters

          // Get and convert solr response to php object
          $data = $feedItemSolrController->query($mapPinsSearchQuery);

          if (!isset($data["response"])) {
            throw new KurogoDataException("Error, not a valid response.");
          }

          $pins = json_encode($data["response"]);
          //Kurogo::log(1, "hello", "query result");

          $this->setResponse($pins);
          $this->setResponseVersion(1);

          // Now update query count for all items queried

          $feedItemIds = array();
          foreach ($data["response"]["docs"] as $solrResults) {
            $feedItemIds[] = $solrResults["id"];
          }

          // Batch update query count by one for results shown to user
          $feedItemSolrController->incrementQueryCounts($feedItemIds);

          break;

        case 'filterOut':
            $isOfficial=$this->getArg('isOfficial',0);
            $categoryArray= $this->getArg('categoryList', 0);
            $lat = $this->getArg('lat', 0);
            $lon = $this->getArg('lon', 0);
            $radius= $this->getArg('distance',0); // in metres

            // $category, $keywords, etc TODO
            $filterSearchQuery = SearchQueryFactory::createGeoRadiusSearchQuery($lat, $lon, $radius);
            $filterSearchQuery->addFilter(new FieldQueryFilter("officialSource", $isOfficial));

            //loop through array to filter out categories
            foreach ($categoryArray as $category){
              $filterSearchQuery->addCategory($category);
              // $filterSearchQuery->addFilter(new FieldQueryFilter("category", $category));
            }
              
            // Fields we want returned from solr
            $filterSearchQuery->addReturnField("title");
            $filterSearchQuery->addReturnField("id");
            $filterSearchQuery->addReturnField("officialSource");
            $filterSearchQuery->addReturnField("category");
            $filterSearchQuery->addReturnField("locationGeo");

            // Get and convert solr response to php object
            $data = $feedItemSolrController->query($filterSearchQuery);

            if (!isset($data["response"])) {
              throw new KurogoDataException("Error, not a valid response.");
            }

            $results = json_encode($data["response"]);
            //Kurogo::log(1, "hello", "query result");

            $this->setResponse($results);
            $this->setResponseVersion(1);

            // Now update query count for all items queried

            $feedItemIds = array();
            foreach ($data["response"]["docs"] as $solrResults) {
              $feedItemIds[] = $solrResults["id"];
            }

            // Batch update query count by one for results shown to user
            $feedItemSolrController->incrementQueryCounts($feedItemIds);
          break;

          case 'searchKeyword':

            $isOfficial=$this->getArg('isOfficial',0);
            $keyword= $this->getArg('keyword', 0);
            $lat = $this->getArg('lat', 0);
            $lon = $this->getArg('lon', 0);
            $radius= $this->getArg('distance',0); // in metres

            // $category, $keywords, etc TODO
            $mapPinsSearchQuery = SearchQueryFactory::createGeoRadiusSearchQuery($lat, $lon, $radius);
            $mapPinsSearchQuery->addFilter(new FieldQueryFilter("officialSource", $isOfficial));
            $mapPinsSearchQuery->addKeyword($keyword);

            // Sort results by most recent first
            $mapPinsSearchQuery->addSort(new SearchSort("pubDate", false));

            // Fields we want returned from solr
            $mapPinsSearchQuery->addReturnField("title");
            $mapPinsSearchQuery->addReturnField("id");
            $mapPinsSearchQuery->addReturnField("officialSource");
            $mapPinsSearchQuery->addReturnField("category");
            $mapPinsSearchQuery->addReturnField("locationGeo");

            // Get and convert solr response to php object
            $data = $feedItemSolrController->query($mapPinsSearchQuery);

            if (!isset($data["response"])) {
              throw new KurogoDataException("Error, not a valid response.");
            }

            $results = json_encode($data["response"]);
            //Kurogo::log(1, "hello", "query result");

            $this->setResponse($results);
            $this->setResponseVersion(1);

            // Now update query count for all items queried

            $feedItemIds = array();
            foreach ($data["response"]["docs"] as $solrResults) {
              $feedItemIds[] = $solrResults["id"];
            }

            // Batch update query count by one for results shown to user
            $feedItemSolrController->incrementQueryCounts($feedItemIds);

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