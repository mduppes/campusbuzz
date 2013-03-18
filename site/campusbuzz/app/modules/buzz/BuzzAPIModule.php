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
          // $isOfficial=$this->getArg('isOfficial',0);
          // $neLng = $this->getArg('neLng', 0); // northeast bound lat
          // $neLat = $this->getArg('neLat', 0); // northeast bound lng
          // $swLng = $this->getArg('swLng', 0); // northeast bound lng
          // $swLat = $this->getArg('swLat', 0); // southwest bound lat
          // $category= $this->getArg('category',0);


          $isOfficial=$this->getArg('isOfficial',0);
          $lat = $this->getArg('neLat', 0);
          $lon = $this->getArg('neLng', 0);
          $radius= 1000; // in metres

          // Change this for # of results returned
          $numResultsReturned = 1;
          
          // test radius
          $getPostsSearchQuery = SearchQueryFactory::createGeoRadiusSearchQuery($lat, $lon, $radius);
          $getPostsSearchQuery->addFilter(new FieldQueryFilter("officialSource", $isOfficial));
          $getPostsSearchQuery->setMaxItems($numResultsReturned);

          //create bbox search query
          //Kurogo::log(1, "hello", "query result");
          //$getPostsSearchQuery = SearchQueryFactory::createBoundingBoxSearchQuery($neLng, $neLat, $swLng, $swLat);

          // Fields we want returned from solr
          $getPostsSearchQuery->addReturnField("title");
          $getPostsSearchQuery->addReturnField("id");
          $getPostsSearchQuery->addReturnField("author");
          $getPostsSearchQuery->addReturnField("sourceType");
          $getPostsSearchQuery->addReturnField("url");
          $getPostsSearchQuery->addReturnField("imageUrl");
          $getPostsSearchQuery->addReturnField("pubDate");
          $getPostsSearchQuery->addReturnField("startDate");
          $getPostsSearchQuery->addReturnField("endDate");
          $getPostsSearchQuery->addReturnField("content");

          // Get and convert solr response to php object
          $data = $feedItemSolrController->query($getPostsSearchQuery);

          if (!isset($data["response"])) {
            throw new KurogoDataException("Error, not a valid response.");
          }

          $posts = json_encode($data["response"]);



          //Kurogo::log(1, "hello", "query result");

          //test
    //       $posts= '{
    // "numFound": 8,
    // "start": 0,
    // "docs": [
    //     {
    //         "title": "news 1",
    //         "officialSource": "TRUE",
    //         "category": "life",
    //         "locationGeo": "49.25957,-123.25433"
    //     },
    //     {
    //         "title": "pin2",
    //         "officialSource": "TRUE",
    //         "category": "health",
    //         "locationGeo": "49.25957,-123.25433"
    //     },
    //     {
    //         "title": "pin3",
    //         "officialSource": "FALSE",
    //         "category": "life",
    //         "locationGeo": "49.25722,-123.24257"
    //     },
    //     {
    //         "title": "pin4",
    //         "officialSource": "FALSE",
    //         "category": "health",
    //         "locationGeo": "49.25722,-123.24257"
    //     }
    //     ,
    //     {
    //         "title": "pin5",
    //         "officialSource": "FALSE",
    //         "category": "health",
    //         "locationGeo": "49.26080,-123.24592"
    //     },
    //     {
    //         "title": "pin6",
    //         "officialSource": "FALSE",
    //         "category": "health",
    //         "locationGeo": "49.25722,-123.24257"
    //     }
    //     ,
    //     {
    //         "title": "pin7",
    //         "officialSource": "FALSE",
    //         "category": "health",
    //         "locationGeo": "49.26080,-123.24592"
    //     },
    //     {
    //         "title": "pin8",
    //         "officialSource": "FALSE",
    //         "category": "health",
    //         "locationGeo": "49.25722,-123.24257"
    //     }
    //     ,
    //     {
    //         "title": "pin9",
    //         "officialSource": "FALSE",
    //         "category": "health",
    //         "locationGeo": "49.26080,-123.24592"
    //     },
    //     {
    //         "title": "pin10",
    //         "officialSource": "FALSE",
    //         "category": "club",
    //         "locationGeo": "49.25722,-123.24257"
    //     }
    //     ,
    //     {
    //         "title": "pin11",
    //         "officialSource": "FALSE",
    //         "category": "leisure",
    //         "locationGeo": "49.26080,-123.24592"
    //     }
    //     ,
    //     {
    //         "title": "pin12",
    //         "officialSource": "FALSE",
    //         "category": "life",
    //         "locationGeo": "49.26080,-123.24592"
    //     }
    // ]
    // }';
          
          $this->setResponse($posts);
          $this->setResponseVersion(1);


        break;
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

          // Fields we want returned from solr
          //$mapPinsSearchQuery->addReturnField("title");
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