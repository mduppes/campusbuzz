<?php


class SolrDataRetriever extends URLDataRetriever {
  protected $DEFAULT_PARSER_CLASS = "JSONDataParser";

  private $feedItemsUrl = "http://localhost:8983/solr/FeedItems/";
  private $locationMapUrl = "http://localhost:8983/solr/LocationMap/";
  private $queryLogUrl = "http://localhost:8983/solr/QueryLog/";

  // Do a search
  public function queryFeedItem(SearchQuery $searchQuery) {
    return $this->_query($searchQuery, $this->feedItemsUrl);
  }

  public function queryLocationMap(SearchQuery $searchQuery) {
    return $this->_query($searchQuery, $this->locationMapUrl);
  }

  // internal function to retrieve query
  private function _query(SearchQuery $searchQuery, $baseUrl) {
    $this->setBaseURL($baseUrl. "select");
    $queryParams = $searchQuery->getQueryParams();
    print_r($queryParams);
    foreach ($queryParams as $key => $value) {
      $this->addParameter($key, $value);
    }

    $this->addParameter("wt", "json");
    $this->setMethod("GET");
    $data = $this->getData();

    if ($data === null) {
      throw new KurogoDataException("Failed search query");
    }

    return $data;    
  }

  // insert an array of FeedItem into solr
  public function persistFeedItems($feedItems) {

    $jsonUpdate = array();
    foreach ($feedItems as $feedItem) {
      $jsonUpdate[] = $feedItem->getSolrUpdateJson();
    }
    $jsonUpdate = '['. implode(',', $jsonUpdate). ']';
    // trim trailing comma and add closing bracket
    
    $this->setBaseURL($this->feedItemsUrl. "update/json");
    $this->addHeader("Content-type", "application/json");
    // immediately make data searchable
    $this->addParameter("commit", "true");
    $this->setData($jsonUpdate);
    $this->setMethod("POST");
    $data = $this->getData();

    if ($data === null) {
      throw new KurogoDataException("Failed to persist feed items, no data returned");
    }

    print "Solr response\n";
    print_r($data);
  }

  // delete all documents in solr
  public function deleteAllFeedItems() {
    $this->deleteAll($this->feedItemsUrl);
  }

  private function deleteAll($solrBaseUrl) {
    $this->setBaseURL($solrBaseUrl. "update/json");
    $this->addHeader("Content-type", "application/json");
    // immediately make data searchable
    $this->addParameter("commit", "true");
    $this->setData('{"delete":{"query":"*:*"}}');
    $this->setMethod("POST");
    $data = $this->getData();
    print "Solr response\n";
    print_r($data);    
  }

  // delete all mapping information
  public function deleteAllLocationMappings() {
    $this->deleteAll($this->locationMapUrl);
  }

}