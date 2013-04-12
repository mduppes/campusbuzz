<?php

/**
 * Class that executes data retrieval from various data sources.
 */
class FeedItemManager {

  // Controllers to retrieve each type of datasource
  private $dataSourceConfigs;
  private $twitterController;
  private $facebookController;
  private $rssController;

  // Controller to access solr feed items index
  private $feedItemSolrController;

  private $categorizer;

  // Facebook credentials
  private $fbId;
  private $fbSecret;

  private function _retrieve($dataSourceConfig) {
    print "Retrieving data for ". $dataSourceConfig->getSourceName(). "\n";
    print "URL or ID: ". $dataSourceConfig->getSourceUrl(). "\n";
    switch($dataSourceConfig->getSourceType()) {
    case 'RSS':
    case 'RSSEvents':
      return $this->rssController->retrieveSource($dataSourceConfig);
    case 'Facebook':
      return $this->facebookController->retrieveSource($dataSourceConfig);
    case 'Twitter':
    case 'TwitterGeoSearch':
      return $this->twitterController->retrieveSource($dataSourceConfig);
    default:
      throw new KurogoConfigurationException("Invalid sourceType: for sourceConfig: ". $dataSourceConfig->getSourceName());
    }
  }

  public function retrieveAndPersistAll() {
    // Set start time for use when categorizing
    $aggregationStartTime = new DateTime();
    $aggregationStartTime->setTimezone(new DateTimeZone("UTC"));

    $successes = 0;

    $totalUpdateCount = 0;
    foreach ($this->dataSourceConfigs as $dataSourceConfig) {
      $error = false;
      $feedItems = null;
      // retrieve feed based on config
      try {
        print "\nobtaining feed for source: ". $dataSourceConfig->getSourceName(). "\n";
        $feedItems = $this->_retrieve($dataSourceConfig);
        print "obtained feed for source: ". $dataSourceConfig->getSourceName(). "\n";
      } catch (Exception $e) {
        print "Failed to retrieve data for source: ". $dataSourceConfig->getSourceName(). "\n" . $e->getMessage()."\n";
        $error = true;
      }

      if ($feedItems == null) {
        print "No feed items extracted for feed. ". $dataSourceConfig->getSourceName(). "\n";
        $error = true;
      } else {
        // Now persist FeedItems into solr
        print "Attempting to persist FeedItems into solr for ". $dataSourceConfig->getSourceName(). "\n";
        try {
          $duplicateFeedItemsSkipped = $this->feedItemSolrController->persistFeedItems($feedItems);
          $numUpdated = count($feedItems) - count($duplicateFeedItemsSkipped);
          print "Updated ". $numUpdated. " FeedItems to Solr\n";
          // Although inefficient, check to see if we get the same result back
          $numValidUpdates = 0;
          if ($numUpdated > 0) {
            foreach ($feedItems as $feedItem) {
              $id = $feedItem->getLabel("id");
              foreach ($duplicateFeedItemsSkipped as $skippedItems) {
                if ($feedItem->isEqual($skippedItems)) {
                  print "macthing\n";
                  continue;
                }
              }
              $returnedFeedItem = $this->feedItemSolrController->queryById($id);
              if (!isset($returnedFeedItem)) {
                print "No matching id from Solr returned. Did not persist FeedItem: {$id}\n";
                print_r($feedItem);
              } else if ($feedItem->isEqual($returnedFeedItem)) {
                print "Valid update for id: {$id}\n";
                $numValidUpdates++;
              }
            }
            print "Number of valid updates: {$numValidUpdates} / {$numUpdated}\n";
          }

          $totalUpdateCount += $numUpdated;
        } catch (Exception $e) {
          print "Error persisting feed for source {$dataSourceConfig->getSourceName()}. ".$e->getMessage(). "\n";
          $error = true;
        }
      }

      if (!$error) {
        $successes++;
        print "Retrieved and persisted {$dataSourceConfig->getSourceName()}\n";
      } else {
        print "Error Retrieving and Persisting Config: ". $dataSourceConfig->getSourceName(). "\n";
        print_r($dataSourceConfig);
      }
    }

    print "\n\nSuccessfully retrieved and persisted {$successes} / ". count($this->dataSourceConfigs). " sources\n";
    print "Total of {$totalUpdateCount} FeedItems updated\n\n";

    // Once the feedItems have been persisted, categorize based on keyword match in solr index
    try {
      print "Categorizing persisted FeedItems by querying Solr for keyword matches:\n";
      $this->categorizer->categorizeFeedItemsSince($aggregationStartTime);
    } catch (Exception $e) {
      print "Error categorizing feeditems. ". $e->getMessage(). "\n";
    }

  }


  public function __construct($parsedConfigs, $feedItemSolr){
    $this->feedItemSolrController = $feedItemSolr;

    // load already parsed php object config
    $this->_initConfig($parsedConfigs);

    // init controllers (data retrievers)
    $this->twitterController = DataRetriever::factory('TwitterDataRetriever', array());
    $this->facebookController = DataRetriever::factory('FacebookDataRetriever', array('FB_ID' => $this->fbId, "FB_SECRET" => $this->fbSecret));
    $this->rssController = DataRetriever::factory('RSSDataRetriever', array());


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
                                         $this->feedItemSolrController);

    $this->fbId = $dataSourceConfigsDecoded["fbid"];
    $this->fbSecret = $dataSourceConfigsDecoded["fbsecret"];

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