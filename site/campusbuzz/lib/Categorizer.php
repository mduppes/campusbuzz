<?php

/**
 * Singleton that manages categorizing FeedItems.
 * @Note Can only categorize items that are persisted in Solr
 */
class Categorizer {

  private $officialCategoryMap;
  private $buzzCategoryMap;
  private $buzzDefaultCategory;


  private $solrController;

  private function _createMostRecentQuery($startTime, $keywords) {
    $searchQuery = SearchQueryFactory::createSearchAllQuery();
    $timeFilter = new TimeSearchFilter($startTime, null, "createDate");
    $searchQuery->addFilter($timeFilter);
    $searchQuery->setMaxItems(1000);
    $searchQuery->addKeyword(implode(" ", $keywords));

    $searchQuery->addReturnField("id");
    return $searchQuery;
  }


  /**
   * Categorizes feed's stored in solr more recent than $startTime.
   * The filter is a simple keyword based filter
   * @param DateTime only categorize feeditems that were obtained past this time
   */
  public function categorizeFeedItemsSince(DateTime $startTime) {
    print "Categorizing feed items since: ". $startTime->format('Y-m-d H:i:s'). " UTC\n";
    foreach ($this->officialCategoryMap as $category => $keywords) {
      print "  Category = {$category}\n";
      $searchQuery = $this->_createMostRecentQuery($startTime, $keywords);
      //$searchQuery->addFilter(new FieldQueryFilter("officialSource", "1"));
      $results = $this->solrController->query($searchQuery);

      if (!isset($results) || !isset($results["response"])) {
        print "Error querying for category: {$category}\n";
      } else {
        $numFound = $results["response"]["numFound"];
        $numReturned = count($results["response"]["docs"]);
        if ($numFound > $numReturned) {
          print "     numfound: {$numFound} > numReturned: {$numReturned}\n";
        }

        print "      Matches found for category: {$category} = ". $numFound . "\n";
        if ($numReturned > 0) {
          $feedIds = array();
          foreach ($results["response"]["docs"] as $feedId) {
            $id = $feedId["id"];
            print "         Updating id: {$id} with category: {$category}\n";
            $feedIds[] = $id;
          }
          // updates category for each match into solr
          $this->solrController->updateCategories($feedIds, $category);
        }
      }
    }

    /*
    foreach ($this->buzzCategoryMap as $category => $keywords) {
      $searchQuery = $this->_createMostRecentQuery($startTime, $keywords);
      $searchQuery->addFilter(new FieldQueryFilter("officialSource", "0"));
      $results = $this->solrController->queryFeedItem($searchQuery);
      print_r($results);
    }
    */
    print "Done categorizing FeedItems in Solr\n";
  }

  public function __construct($official, $buzz, $buzzDefaultCategory, $solrController) {
    if (!(is_array($official) && is_array($buzz) && is_string($buzzDefaultCategory))) {
      throw new KurogoConfigurationException("Invalid category mappings\n");
    }

    $this->officialCategoryMap = $official;
    $this->buzzCategoryMap = $buzz;
    $this->buzzDefaultCategory = $buzzDefaultCategory;
    $this->solrController = $solrController;
    print "Initialized categorizer\n";
  }
}