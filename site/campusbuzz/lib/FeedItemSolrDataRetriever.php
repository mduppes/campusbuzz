<?php

/**
 * Solr data retriever specialized for FeedItems.
 * Contains functions specific to FeedItem querying and updating.
 */
class FeedItemSolrDataRetriever extends SolrDataRetriever {

  protected function getSolrBaseUrl() {
    return "http://localhost:8983/solr/FeedItems/";
  }

  public function query(SearchQuery $searchQuery) {
    // Add testing filter
    $searchQuery->addFilter(new FieldQueryFilter("testing", Tester::isTesting() ? "1" : "0"));
    return parent::query($searchQuery);
  }

  /**
   * Query Solr for FeedItems according to searchQuery
   * @return array of FeedItems returned from Solr
   */
  public function queryFeedItem(SearchQuery $searchQuery) {
    $response = $this->query($searchQuery);
    //print "response\n";
    //print_r($response);
    if ($response["response"]["numFound"] === 0) {
      return array();
    }

    $feedItems = array();
    foreach ($response["response"]["docs"] as $feedItemData) {
      try {
        $feedItem = FeedItem::createFromSolr($feedItemData);
        array_push($feedItems, $feedItem);
      } catch (Exception $e) {
        print "Invalid feedItem returned from solr: ". $e->getMessage(). "\n";
        print_r($feedItemData);
      }
    }
    return $feedItems;
  }

  /**
   * Update the categories for the given FeedItem ID's already in solr.
   * @param array of FeedItem ID's to update
   * @param new category to add to this FeedItem
   */
  public function updateCategories($id_array, $newCategory) {
    $combinedUpdateArray = array();
    foreach ($id_array as $id) {
      $combinedUpdateArray[] = array("id" => $id,
                                     "category" => array("add" => $newCategory));
    }
    $this->persist(json_encode($combinedUpdateArray));
  }

  /**
   * Increment the query count by 1 for the given FeedItem ID's already in solr.
   * The query count is the number of times a FeedItem has been shown to a user.
   * @param array of FeedItem ID's to increment the query count
   */
  public function incrementQueryCounts($id_array) {
    $combinedUpdateArray = array();
    foreach ($id_array as $id) {
      $combinedUpdateArray[] = array("id" => $id,
                                     "queryCount" => array("inc" => 1));
    }
    $this->persist(json_encode($combinedUpdateArray));
  }

  /**
   * Insert an array of FeedItems into solr
   * @param FeedItem[] Items to persist to database
   * @param bool if false, will only exist if unique (no duplicate entries already exist)
   */
  public function persistFeedItems($feedItems, $overwrite = false) {

    $jsonUpdate = array();
    $skipCount = 0;
    foreach ($feedItems as $feedItem) {
      // Create a query which searches by ID and returns if it hit or not
      // Does not return the items itself
      if (!$overwrite) {
        $searchQuery = SearchQueryFactory::createSearchByIdQuery($feedItem->getLabel("id"));
        $searchQuery->setMaxItems(0);
        $existingItem = $this->query($searchQuery);

        if ($existingItem["response"]["numFound"] > 0) {
          if ($existingItem["response"]["numFound"] != 1) {
            print_r($existingItem);
            throw new KurogoDataException("Returned more than 1 for solr id query");
          }
          print "Item already exists in database, skipping id: ". $feedItem->getLabel("id"). "\n";
          $skipCount++;
          continue;
        }
      } else {
        print "Overwriting item in database, id: ". $feedItem->getLabel("id"). "\n";
      }
      // Add valid item to persist list
      $jsonUpdate[] = $feedItem->getSolrUpdateJson();
    }

    print "Items to persist: ". count($jsonUpdate). " / ". count($feedItems). ", skipped duplicates: {$skipCount}\n";
    // trim trailing comma and add closing bracket
    $jsonUpdate = '['. implode(',', $jsonUpdate). ']';
    $this->persist($jsonUpdate);
  }




}