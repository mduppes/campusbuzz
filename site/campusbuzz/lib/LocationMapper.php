<?php

/**
 * LocationMapper contains functions for mapping location names to geo coordinates
 * The mapping is queried from Solr, where location data is stored.
 *
 */
class LocationMapper {

  private $locationCache = array();
  private $solrController;
  private static $locationMapper;

  /**
   * Return singleton locationMapper
   * @return LocationMapper object
   */
  public static function &getLocationMapper() {
    if (self::$locationMapper === null) {
      self::$locationMapper = new self();
    }
    return self::$locationMapper;
  }

  private function __construct() {
        // initialize locationmapper
    $this->solrController = DataRetriever::factory('LocationMapSolrDataRetriever', array());
  }

  /**
   * Query Solr and insert a geocoordinate into the local cache for locaitonName.
   * @param string Name of location.
   */
  public function insertLocationCache($locationName) {
    $searchResults = $this->locationSearch($locationName);
    if (isset($searchResults)) {
      array_push($this->locationCache, $searchResults);
    } else {
      print "Failed to lookup location geocoord from solr for: {$locationName}\n";
    }
  }

  /**
   * Returns the best matching GeoCoordinate for locationName from Solr.
   * @param string Name of location. Building names or codes, and addresses.
   * @return GeoCoordinate of the most relevant location or null if there is no match.
   */
  public function locationSearch($locationName) {

    //lookup cache of source config location names
    if (isset($this->locationCache[$locationName])) {
      return $this->locationCache[$locationName];
    }
    
    // Not found in cache, query solr
    $searchQuery = SearchQueryFactory::createLocationToCoordinateQuery($locationName);
    
    //print "SEARCHING FOR LOCATION\n";    
    $results = $this->solrController->query($searchQuery);

    //print_r($results);
    
    if (isset($results)) {      
      $responseHeader = $results["responseHeader"];
      if (!isset($results["response"])) {
        throw new KurogoDataException("No response set from location search solr");
      }
      $response = $results["response"];
      $numFound = $response["numFound"];
      if ($numFound == 0) {
        print "No results found for query location: {$locationName}\n";
        return null;
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
      
      // No exact match found so return the most relevant result (first result)
      return (isset($mostRelevantResponse)) ? 
        GeoCoordinate::createFromString($mostRelevantResponse) : null;

        
    }
  }


  public function deleteAllLocationsFromSolr() {
    $this->solrController->deleteAll();
  }


}