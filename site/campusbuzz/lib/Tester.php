<?php


class Tester {

  // Populate array with test class names
  private $tests =
    array("TwitterDataRetrieverTest",
          "FacebookDataRetrieverTest",
          "RSSDataRetrieverTest",
          "SearchQueryTest",
          "LocationMapperTest",
          "CategorizerTest",
          "FeedItemManagerTest");

  // Solr Controllers
  public $feedItemSolrController;
  public $locationMapSolrController;
  public $queryLogSolrController;

  // URL data retrievers for feeds
  public $twitterController;
  public $facebookController;
  public $rssController;

  private $testing = false;
  private static $tester;

  private function _deleteTestFeedItems() {
    $this->feedItemSolrController->deleteAll("testing:1");
  }

  public function runtests() {
    if (!$this->testing) {
      throw new KurogoDataException("Tester has not been initialized to run tests");
    }

    $passed = 0;
    $total = 0;
    foreach ($this->tests as $class) {
      $testClass = new $class;
      $methods = get_class_methods($class);

      foreach ($methods as $testMethod) {
        print "Testing {$class}->{$testMethod}: \n\n";
        // Delete all that was in solr for testing
        $this->_deleteTestFeedItems();
        $result = $testClass->{$testMethod}();
        $resultString = "\n                                                     .......";
        $resultString .= ($result) ? "PASS": "###### FAIL ######";
        if ($result) {
          $passed++;
        }
        $total++;
        print $resultString. "\n---------------------------------------------------------------------------------------------\n";
      }
    }
    print "Passed {$passed} / {$total} tests.\n";
  }

  // Get singleton tester
  public static function &getTester() {
    if (self::$tester === null) {
      self::$tester = new self;
      return self::$tester;
    }
    return self::$tester;
  }

  public static function isTesting() {
    if (self::$tester === null) {
      return false;
    }
    return self::getTester()->_isTesting();
  }

  public function init($fbid, $fbsecret) {
    $this->feedItemSolrController = DataRetriever::factory("FeedItemSolrDataRetriever", array());
    $this->locationMapSolrController = DataRetriever::factory("LocationMapSolrDataRetriever", array());
    $this->queryLogSolrController = DataRetriever::factory("QueryLogSolrDataRetriever", array());

    $this->twitterController = DataRetriever::factory("TwitterDataRetriever", array());
    $this->facebookController = DataRetriever::factory("FacebookDataRetriever", array("FB_ID" => $fbid, "FB_SECRET" => $fbsecret));
    $this->rssController = DataRetriever::factory("RSSDataRetriever", array());
    $this->testing = true;
  }

  // Internal functions
  private function _isTesting() {
    return $this->testing;
  }

}