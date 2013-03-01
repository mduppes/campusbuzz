<?php

class AggregationShellModule extends ShellModule {
  protected $id='aggregation';

  // the 2nd argument can specify parameters for the dataretriever
  private $twitterController;
  private $facebookController;
  private $rssController;
  private $solrController;
  private $dataSourceConfigs;

  private $fbId;
  private $fbSecret;

  public function getAllControllers() {
  }

  protected function preFetchData(DataModel $controller, &$response) {
    
    
  }

  protected function loadConfig() {
    $dataSourceConfigFile = file_get_contents(SITE_DIR."/app/modules/aggregation/config/feeds.json");
    if ($dataSourceConfigFile === FALSE) {
      print "Failed to open config file\n";
    }

    $dataSourceConfigsDecoded = json_decode($dataSourceConfigFile, true);

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
    print "Loaded configs: ". count($this->dataSourceConfigs). " out of ". count($dataSourceConfigsDecoded). "\n";
  }

  protected function retrieveAndPersistAll() {
    $successes = 0;
    foreach ($this->dataSourceConfigs as $dataSourceConfig) {
      print "\n\n\n\n\n\n\n\n";
      print "Retrieving data for ". $dataSourceConfig->getName(). "\n";
      $feedItems = null;
      $error = false;
      try {
        
        switch($dataSourceConfig->getSourceType()) {
        case 'RSS':
          $feedItems = $this->rssController->retrieveSource($dataSourceConfig);
          break;
        case 'Facebook':
          break;
        case 'Twitter':
        case 'TwitterGeoSearch':
          $feedItems = $this->twitterController->retrieveSource($dataSourceConfig);
          break;
        default:
          throw new KurogoConfigurationException("Invalid sourceType: for sourceConfig: ". $dataSourceConfig->getName());
        }
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
          print_r($feedItems);
          $this->solrController->persistFeedItems($feedItems);
        } catch (Exception $e) {
          print "Error persisting feed for source {$dataSourceConfig->getName()}. ".$e->getMessage(). "\n";
          $error = true;
        }
      }
      if (!$error) {
        $successes++;
        print "Successfully retrieved and persisted {$dataSourceConfig->getName()}\n";
      }
    }
    print "Successfully retrieved and persisted {$successes} / {count($this->dataSourceConfigs)}\n";
  }

  protected function initializeForCommand() {

    // Load all data sources from file.
    $this->loadConfig();

    //instantiate controllers
    $this->twitterController = DataRetriever::factory('TwitterDataRetriever', array());
    $this->facebookController = DataRetriever::factory('FacebookDataRetriever', array($this->fbId, $this->fbSecret));
    $this->rssController = DataRetriever::factory('RSSDataRetriever', array());
    $this->solrController = DataRetriever::factory('SolrDataRetriever', array());

    switch ($this->command) {
    case "retrieveAll":
      print "retrieving all sources of data\n";

      try {
        $this->retrieveAndPersistAll();
      } catch (Exception $e) {
        print "Error retrieving and persisting. ". $e->getMessage(). "\n";
      }
      break;
    case "runtests":
      print "running tests...\n";
      print_r($this->solrController->queryFeedItem(SearchQueryFactory::createSearchAllQuery()));
      break;
    case "deleteFeedItems":
      print "Deleting all documents in solr\n";
      $this->solrController->deleteAllFeeditems();
      break;
    case "deleteLocationMap":
      print "Deleting all location mappings in solr\n";
      $this->solrController->deleteAllLocationMappings();
    case "deleteAll":
      print "deleting everything in solr\n";
      $this->solrController->deleteAllFeeditems();
      $this->solrController->deleteAllLocationMappings();
      break;
    default:
      print "Command given is {$this->command}. this command does not exist. Commands include: \n\truntests \n\tdeleteFeedItems \n\tdeleteLocationMap\n";
    }
  }
}