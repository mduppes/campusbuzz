<?php


class Tester {

  // Populate array with test class names
  private $tests =
    array("TwitterDataRetrieverTest",
          "FacebookDataRetrieverTest",
          "SearchQueryTest");


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

  public function runtests() {
    if (!$this->testing) {
      throw new KurogoDataException("Tester has not been initialized to run tests");
    }

    foreach ($this->tests as $class) {
      print $class. ":\n";
      $testClass = new $class;
      $methods = get_class_methods($class);
      foreach ($methods as $testMethod) {
        print "Test {$class}->{$testMethod}\n";
        $result = $testClass->{$testMethod}();
        $resultString = ($result) ? "PASS": "FAIL";
        print $resultString. "\n";
      }
    }
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
    $this->facebookController = DataRetriever::factory("FacebookDataRetriever", array($fbid, $fbsecret));
    $this->rssController = DataRetriever::factory("RSSDataRetriever", array());
    $this->testing = true;
  }

  // Internal functions
  private function _isTesting() {
    return $this->testing;
  }

}