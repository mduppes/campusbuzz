<?php

class QueryLogger {

  private $solrController;
  private static $queryLogger;

  public static function &getQueryLogger() {
    if (self::$queryLogger === null) {
      self::$queryLogger = new self();
    }
    return self::$queryLogger;
  }

  public function logUserData($isOfficial, $keyword, $category, $searchCoordinate, $userCoordinate, $searchMode) {
    $updateParams = array();

    $updateParams["officialSource"] = $isOfficial ? "true" :"false";
    if ($keyword != '') {
      $updateParams["searchKeyword"] = $keyword;
    }


    if ($category != '') {
      $updateParams["searchCategories"] = array();
      $temp= explode(",", $category);
      foreach ($temp as $cat){
        $updateParams["searchCategories"][] = $cat;
      }
    }


    $updateParams["searchMode"] = $searchMode;

    if (isset($userCoordinate)) {
      $updateParams["userGeoCoordinate"] = (string) $userCoordinate;
    }

    $updateParams["searchGeoCoordinate"] = (string) $searchCoordinate;


    // bug in solr have to add mandatory id.. make random
    $updateParams["id"] = sha1(rand(). $keyword. $searchMode. rand());
    $json = '['. json_encode($updateParams). ']';

    $this->solrController->persist($json);
  }

  private function __construct(){
    $this->solrController = DataRetriever::factory('QueryLogSolrDataRetriever', array());
  }



}