<?php

class AggregationShellModule extends ShellModule {
  protected $id='aggregation';

  // the 2nd argument can specify parameters for the dataretriever
  private $twitterController;
  private $facebookController;
  private $rssController;
  private $solrController;
  private $dataSourceConfigs;

  public function getAllControlers() {
    return array();
  }

  protected function preFetchData(DataModel $controller, &$response) {
    
    
  }

  protected function loadConfig() {
    $dataSourceConfigFile = file_get_contents("./config/feeds.json");
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

    
    $this->dataSourceConfigs = array();
    foreach ($dataSourceConfigsDecoded as $dataSourceConfig) {
      print "Loading config source: {$dataSourceConfig['title']}\n";
      try {
        array_push($this->dataSourceConfigs, new DataSourceConfig($dataSourceConfig));
      } catch (Exception $e) {
        print "Problem in config source {$dataSourceConfig['title']}. \n".$e->getMessage() . "\n";
      }
    }
    print "Loaded configs: ". count($this->dataSourceConfigs). " out of ". count($dataSourceConfigsDecoded). "\n";
  }

  protected function retrieveAndPersistAll() {
    $successes = 0;
    foreach ($this->dataSourceConfigs as $dataSourceConfig) {
      print "\n\n\n\n\n\n\n\n";
      print "Retrieving data for ". $dataSourceConfig->getTitle(). "\n";
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
          throw new KurogoConfigurationException("Invalid sourceType: for sourceConfig: ". $dataSourceConfig->getTitle());
        }
      } catch (Exception $e) {
        print "Failed to retrieve data for source: ". $dataSourceConfig->getTitle(). "\n" . $e->getMessage()."\n";
        $error = true;
      }

      if ($feedItems == null) {
        print "No feed items extracted for feed. ". $dataSourceConfig->getTitle(). "\n";
        $error = true;
      } else {
        // Now persist FeedItems into solr
        try {
          print_r($feedItems);
          $this->solrController->persistFeedItems($feedItems);
        } catch (Exception $e) {
          print "Error persisting feed for source {$dataSourceConfig->getTitle()}. ".$e->getMessage(). "\n";
          $error = true;
        }
      }
      if (!$error) {
        $successes++;
        print "Successfully retrieved and persisted {$dataSourceConfig->getTitle()}\n";
      }
    }
    print "Successfully retrieved and persisted {$successes} / {count($this->dataSourceConfigs)}\n";
  }

  protected function initializeForCommand() {

    // Load all data sources from file.
    $this->loadConfig();

    //instantiate controllers
    $this->twitterController = DataRetriever::factory('TwitterDataRetriever', array());
    $this->facebookController = DataRetriever::factory('FacebookDataRetriever', array());
    $this->rssController = DataRetriever::factory('RSSDataRetriever', array());
    $this->solrController = DataRetriever::factory('SolrDataRetriever', array());

    try {
      $this->retrieveAndPersistAll();
    } catch (Exception $e) {
      print "Error retrieving and persisting. ". $e->getMessage(). "\n";
    }

    switch ($this->command) {
    case "runtests":
      print "running tests...\n";
      break;
    case "delete":
      print "Deleting all documents in solr\n";
      $this->solrController->deleteAll();
      break;
    }
  }
}