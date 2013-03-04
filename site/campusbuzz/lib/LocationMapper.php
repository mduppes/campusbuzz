<?php

class LocationMapper {

  private $locationCache = array();
  private $solrController;
  private static $locationMapper;

  public static function &getLocationMapper() {
    if (self::$locationMapper === null) {
      self::$locationMapper = new self();
    }
    return self::$locationMapper;
  }

  private function __construct() {
    $this->solrController = DataRetriever::factory('SolrDataRetriever', array());
  }

  public function insertLocationCache($locationName) {
    $searchResults = $this->locationSearch($locationName);
  }

  public function locationSearch($locationName) {
    $searchQuery = SearchQueryFactory::createLocationToCoordinateQuery($locationName);

    
    print "SEARCHING FOR LOCATION\n";    
    try {
      $results = $this->solrController->queryLocationMap($searchQuery);
    } catch (Exception $e) {
      print "Failed to query location map: ". $e->getMessage(). "\n";
    }

    print_r($results);
    
    if (isset($results)) {      
      $responseHeader = $results["responseHeader"];
      if (!isset($results["response"])) {
        throw new KurogoDataException("No response set from location search solr");
      }
      $response = $results["response"];
      $numFound = $response["numFound"];
      if ($numFound == 0) {
        print "No results found for query location: {$locationName}\n";
      }
      
      $mostRelevantResponse = null;
      foreach ($response["docs"] as $locationMapping) {
        if (!isset($locationMapping["locationGeo"])) {
          throw new KurogoDataException("No coordinates retrieved");
        }
        if (isset($locationMapping["locationName"])) {
          $responseName = $locationMapping["locationName"];
          if ($responseName == $locationName) {
            return GeoCoordinate::createFromString($locationMapping["locationGeo"]);
          } else if (!$mostRelevantResponse) {
            $mostRelevantResponse = $locationMapping["locationGeo"];         
          }
        }
      }
      
      if (!$mostRelevantResponse) {
        return GeoCoordinate::createFromString($mostRelevantResponse);
      }
        
    }
  }




}