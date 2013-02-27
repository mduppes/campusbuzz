<?php


class SolrDataRetriever extends URLDataRetriever {
  protected $DEFAULT_PARSER_CLASS = "JSONDataParser";

  private $solrUpdateUrl = "http://localhost:8983/solr/update/json";

  // insert an array of FeedItem into solr
  public function persistFeedItems($feedItems) {
    print_r($feedItems);

    $jsonUpdate = array();
    foreach ($feedItems as $feedItem) {
      $jsonUpdate[] = $feedItem->getSolrUpdateJson();
    }
    $jsonUpdate = '['. implode(',', $jsonUpdate). ']';
    // trim trailing comma and add closing bracket
    
    $this->setBaseURL($this->solrUpdateUrl);
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
  public function deleteAll() {
    $this->setBaseURL($this->solrUpdateUrl);
    $this->addHeader("Content-type", "text/xml");
    $this->addHeader("charset", "utf-8");
    // immediately make data searchable
    $this->addParameter("commit", "true");
    $this->setData('{"delete":{"query":"*:*"}}');
    $this->setMethod("POST");
    $data = $this->getData();
    print "Solr response\n";
    print_r($data);
  }

  public function search($searchQuery) {

  }


}