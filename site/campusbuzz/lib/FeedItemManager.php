<?php

class FeedItemManager {

  private $dataSourceConfigs;
  private $twitterController;
  private $facebookController;
  private $rssController;

  private $feedItemSolrController;

  private $categorizer;

  private $fbId;
  private $fbSecret;

  private function _retrieve($dataSourceConfig) {
    print "Retrieving data for ". $dataSourceConfig->getName(). "\n";
    switch($dataSourceConfig->getSourceType()) {
    case 'RSS':
      return $this->rssController->retrieveSource($dataSourceConfig);
    case 'Facebook':
      break;
    case 'Twitter':
    case 'TwitterGeoSearch':
      return $this->twitterController->retrieveSource($dataSourceConfig);
    default:
      throw new KurogoConfigurationException("Invalid sourceType: for sourceConfig: ". $dataSourceConfig->getName());
    }
  }

  public function retrieveAndPersistAll() {
    // Set start time for use when categorizing
    $aggregationStartTime = new DateTime();
    $aggregationStartTime->setTimezone(new DateTimeZone("UTC"));

    $successes = 0;

    foreach ($this->dataSourceConfigs as $dataSourceConfig) {
      $error = false;
      $feedItems = null;

      // retrieve feed based on config
      try {
        $feedItems = $this->_retrieve($dataSourceConfig);
      } catch (Exception $e) {
        print "Failed to retrieve data for source: ". $dataSourceConfig->getName(). "\n" . $e->getMessage()."\n";
        $error = true;
      }

      
      if ($feedItems == null) {
        print "No feed items extracted for feed. ". $dataSourceConfig->getName(). "\n";
        $error = true;
      } else {
        // Now persist FeedItems into solr
        try {
          $this->feedItemSolrController->persistFeedItems($feedItems);
        } catch (Exception $e) {
          print "Error persisting feed for source {$dataSourceConfig->getName()}. ".$e->getMessage(). "\n";
          $error = true;
        }
        
      }   

      if (!$error) {
        $successes++;
        print "Successfully retrieved and persisted {$dataSourceConfig->getName()}\n";
      } else {
        print "Error Config: \n";
        print_r($dataSourceConfig);
      }
    }

    print "Successfully retrieved and persisted {$successes} / ". count($this->dataSourceConfigs). "\n";    

    // Once the feedItems have been persisted, categorize based on keyword match in solr index
    try {
      print "Categorizing persisted feedItems\n";
      $this->categorizer->categorizeFeedItemsSince($aggregationStartTime);
    } catch (Exception $e) {
      print "Error categorizing feeditems. ". $e->getMessage(). "\n";
    }

  }


  public function __construct($parsedConfigs, $feedItemSolr){
    // init controllers (data retrievers)
    $this->twitterController = DataRetriever::factory('TwitterDataRetriever', array());
    $this->facebookController = DataRetriever::factory('FacebookDataRetriever', array($this->fbId, $this->fbSecret));
    $this->rssController = DataRetriever::factory('RSSDataRetriever', array());
    $this->feedItemSolrController = $feedItemSolr;

    // load already parsed php object config
    $this->_initConfig($parsedConfigs);
  }

  private function _initConfig($dataSourceConfigsDecoded) {

    if ($dataSourceConfigsDecoded == null) {
      throw new KurogoConfigurationException("Invalid JSON file");
    }
    
    if (!is_array($dataSourceConfigsDecoded)) {
      throw new KurogoConfigurationException("Json config is not an array");
    }

    if (!isset($dataSourceConfigsDecoded["fbid"])) {
      throw new KurogoConfigurationException("Missing fbid");
    }

    if (!isset($dataSourceConfigsDecoded["fbsecret"])) {
      throw new KurogoConfigurationException("Missing fbsecret");
    }
    if (!isset($dataSourceConfigsDecoded["buzzDefaultCategory"])) {
      print "Default buzz category (catch all) not set.\n";
    }
    if (!isset($dataSourceConfigsDecoded["buzzCategories"])) { 
      throw new KurogoConfigurationException("Missing buzz category map: buzzCategories");
    }
    if (!isset($dataSourceConfigsDecoded["officialCategories"])) {
      throw new KurogoConfigurationException("Missing official category map: officialCategories");
    }

    // Init categorizer
    $this->categorizer = new Categorizer(
                                         $dataSourceConfigsDecoded["officialCategories"],
                                         $dataSourceConfigsDecoded["buzzCategories"],
                                         $dataSourceConfigsDecoded["buzzDefaultCategory"],
                                         $this->feedItemSolrController);

    $this->fbId = $dataSourceConfigsDecoded["fbid"];    
    $this->fbsecret = $dataSourceConfigsDecoded["fbsecret"];

    $this->dataSourceConfigs = array();
    if (!isset($dataSourceConfigsDecoded["feeds"])) {
      throw new KurogoConfigurationException("No feed configs");
    }

    foreach ($dataSourceConfigsDecoded["feeds"] as $dataSourceConfig) {
      print "Loading config source: {$dataSourceConfig['name']}\n";
      try {
        array_push($this->dataSourceConfigs, new DataSourceConfig($dataSourceConfig));
      } catch (Exception $e) {
        print "Problem in config source {$dataSourceConfig['name']}. \n".$e->getMessage() . "\n";
      }
    }
    print "Loaded configs: ". count($this->dataSourceConfigs). " out of ". count($dataSourceConfigsDecoded["feeds"]). "\n";

  }
    


}