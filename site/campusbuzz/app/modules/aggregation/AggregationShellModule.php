<?php

class AggregationShellModule extends ShellModule {
  protected $id='aggregation';

  public function loadConfig() {

    $dataSourceConfigFile = file_get_contents(SITE_DIR."/app/modules/aggregation/config/feeds.json");
    if ($dataSourceConfigFile === FALSE) {
      print "Failed to open config file\n";
    }
    return json_decode($dataSourceConfigFile, true);
  }

  protected function initializeForCommand() {
    // Load all data sources from file.
    $dataSourceConfigsDecoded = $this->loadConfig();

    // Initialize everything necessary for data retrieval and aggregation
    $feedItemSolrController = DataRetriever::factory('FeedItemSolrDataRetriever', array());
    $manager = new FeedItemManager($dataSourceConfigsDecoded, $feedItemSolrController);

    //instantiate controllers
    switch ($this->command) {
    case "retrieveAll":
      print "retrieving all sources of data\n";

      try {
        $manager->retrieveAndPersistAll();
        print "Finished retrieving and persisting all\n";
      } catch (Exception $e) {
        print "Error retrieving and persisting. ". $e->getMessage(). "\n";
      }

      break;
    case "runtests":
      print "running tests...\n";
      // Initialize controllers for tester
      $tester = Tester::getTester();
      $tester->init($dataSourceConfigsDecoded["fbid"], $dataSourceConfigsDecoded["fbsecret"]);
      $tester->runtests();
      //print_r($feedItemSolrController->queryFeedItem(SearchQueryFactory::createSearchAllQuery()));

      break;
    case "deleteFeedItems":
      print "Deleting all documents in solr\n";
      $feedItemSolrController->deleteAll();
      break;
    case "updateLocationMap":
      print "Updating all locations to Solr\n";
      LocationMapper::getLocationMapper()->updateSolrLocations();
      break;
    case "deleteAll":
      print "deleting everything in solr\n";
      //$this->solrController->deleteAllFeeditems();
      //$this->solrController->deleteAllLocationMappings();
      break;
    default:
      print "Command given is {$this->command}. this command does not exist. Commands include: \n\truntests \n\tdeleteFeedItems \n\tdeleteLocationMap\n";
    }
  }
}