<?php


class FeedItemSolrDataRetriever extends SolrDataRetriever {
  

  protected function getSolrBaseUrl() {
    return "http://localhost:8983/solr/FeedItems/";
  }

  
  // Do a search for feedItems
  public function queryFeedItem(SearchQuery $searchQuery) {
    $response = $this->query($searchQuery);

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
   * Insert an array of FeedItems into solr
   * @param FeedItem[] Items to persist to database
   * @param bool if false, will only exist if unique (no duplicate entries already exist)
   */
  public function persistFeedItems($feedItems, $overwrite = false) {

    $jsonUpdate = array();
    foreach ($feedItems as $feedItem) {
      // Create a query which searches by ID and returns if it hit or not
      // Does not return the items itself
      $searchQuery = SearchQueryFactory::createSearchByIdQuery($feedItem->getLabel("id"));
      $searchQuery->setMaxItems(0);
      $existingItem = $this->query($searchQuery);
      
      if ($existingItem["response"]["numFound"] > 0) {
        print "Item already exists in database, skipping\n";        
      } else {
        print "new item to persist\n";
        $jsonUpdate[] = $feedItem->getSolrUpdateJson();
      }
    }
    // trim trailing comma and add closing bracket
    $jsonUpdate = '['. implode(',', $jsonUpdate). ']';
    $this->persist($jsonUpdate);
  }




}