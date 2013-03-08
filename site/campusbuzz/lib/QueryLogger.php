<?php

class QueryLogger {

  private $solrController;
  private static $queryLogger;

  public static function &getQueryLogger() {
    if (self:: === null) {
      self::$queryLogger = new self();
    }
    return self::$queryLogger;
  }

  public function logQuery($query) {
    
  }
  
  private function __construct(){
    $this->solrController = DataRetriever::factory('QueryLogSolrDataRetriever', array());
  }

  

}