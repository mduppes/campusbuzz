<?php

/**
 * Singleton that manages categorizing FeedItems.
 * @Note Can only categorize items that are persisted in Solr
 */
class Categorizer {

  private $officialCategoryMap;
  private $buzzCategoryMap;

  private $solrController;

  /**
   * Creates prototype search query to retrieve all documents in Solr that m
   */
  private function _createMostRecentQuery($startTime, $keywords) {
    $searchQuery = SearchQueryFactory::createSearchAllQuery();
    $timeFilter = new TimeSearchFilter($startTime, null, "createDate");
    $searchQuery->addFilter($timeFilter);
    $searchQuery->setMaxItems(1000);

    foreach ($keywords as $keyword) {
      $searchQuery->addKeyword($keyword);
    }

    $searchQuery->addReturnField("id");
    $searchQuery->addReturnField("category");
    return $searchQuery;
  }


  /**
   * Categorizes feed's stored in solr more recent than $startTime.
   * It does so by querying Solr for keywords that are preconfigured for each category, and updating the feedItems that match
   * @param DateTime only categorize feeditems that were obtained past this time
   */
  public function categorizeFeedItemsSince(DateTime $startTime) {
    print "Categorizing feed items since: ". $startTime->format('Y-m-d H:i:s'). " UTC\n";

    $this->_categorize($this->officialCategoryMap, true, $startTime);

    $this->_categorize($this->buzzCategoryMap, false, $startTime);

    print "Done categorizing FeedItems in Solr\n";
  }

  /**
   * Internal function that is only exposed for testing. TODO: fix testing issue
   * @param array mapping each category to its keywords
   * @param boolean to signal that the set of categories is from an official source
   */
  public function _categorize($categoryMap, $official, DateTime $startTime) {
    foreach ($categoryMap as $category => $keywords) {
      print $official ? "Official" : "Buzz";
      print "  Category = {$category}\n";
      $searchQuery = $this->_createMostRecentQuery($startTime, $keywords);
      $searchQuery->addFilter(new FieldQueryFilter("officialSource", $official));
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
          foreach ($results["response"]["docs"] as $feedItem) {
            if (!isset($feedItem["id"]) || !isset($feedItem["category"]) || !is_array($feedItem["category"])) {
              throw new KurogoDataException("Categorizer: Invalid solr return values for id and category");
            }
            $alreadyExists = false;
            foreach($feedItem["category"] as $existingCategory) {
              if ($existingCategory === $category) {
                // category already exists, skip
                $alreadyExists = true;
              }
            }

            if (!$alreadyExists) {
              $id = $feedItem["id"];
              print "         Updating id: {$id} with category: {$category}\n";
              $feedIds[] = $id;
            }
          }
          // updates category for each match into solr
          $this->solrController->updateCategories($feedIds, $category);
        }
      }
    }
  }

  /**
   * @param array mapping official categories to its keywords
   * @param array mapping unofficial (buzz) categories to its keywords
   * @param
   */
  public function __construct($official, $buzz, $solrController) {
    if (!(is_array($official) && is_array($buzz))) {
      throw new KurogoConfigurationException("Invalid category mappings\n");
    }

    $this->officialCategoryMap = $official;
    $this->buzzCategoryMap = $buzz;
    $this->solrController = $solrController;
    print "Initialized categorizer\n";
  }
}