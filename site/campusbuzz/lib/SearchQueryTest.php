<?php

class SearchQueryTest extends Test {

  public function testMapPinSearchQuery() {
    // for now since everything is official
    $isOfficial = true;
    $lat = 49.1;
    $lon = -120;
    $radius = 2000;

    // Change this for # of results returned
    $numResultsReturned = 50;


    // $category, $keywords, etc TODO
    $mapPinsSearchQuery = SearchQueryFactory::createGeoRadiusSearchQuery($lat, $lon, $radius);
    $mapPinsSearchQuery->addFilter(new FieldQueryFilter("officialSource", $isOfficial));
    $mapPinsSearchQuery->setMaxItems($numResultsReturned);

    // Fields we want returned from solr
    //$mapPinsSearchQuery->addReturnField("title");
    $mapPinsSearchQuery->addReturnField("id");
    $mapPinsSearchQuery->addReturnField("officialSource");
    $mapPinsSearchQuery->addReturnField("category");
    $mapPinsSearchQuery->addReturnField("locationGeo");
    // TODO: add keywords if given, and category filters

    // Get and convert solr response to php object
    $data = $this->feedItemSolrController->query($mapPinsSearchQuery);

    if (!isset($data["response"])) {
      throw new KurogoDataException("Error, not a valid response.");
    }


    $feedItemIds = array();
    foreach ($data["response"]["docs"] as $solrResults) {
      $feedItemIds[] = $solrResults["id"];
    }

    print_r($feedItemIds);
    // Batch update query count by one for results shown to user
    $feedItemSolrController->incrementQueryCounts($feedItemIds);

  }






}