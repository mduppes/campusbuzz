<?php

class SearchQueryTest {

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
    $data = Tester::getTester()->feedItemSolrController->query($mapPinsSearchQuery);

    if (!isset($data["response"])) {
      throw new KurogoDataException("Error, not a valid response.");
    }


    $feedItemIds = array();
    foreach ($data["response"]["docs"] as $solrResults) {
      $feedItemIds[] = $solrResults["id"];
    }

    print_r($feedItemIds);
    // Batch update query count by one for results shown to user
    Tester::getTester()->feedItemSolrController->incrementQueryCounts($feedItemIds);

  }


  public function testBoundingBoxSearchQuery() {
    $lat = 49.26;
    $lon = -123.24;
    $mapPinsSearchQuery = SearchQueryFactory::createBoundingBoxSearchQuery($lat - 1, $lon - 1, $lat + 1, $lon + 1);
    $data = Tester::getTester()->feedItemSolrController->query($mapPinsSearchQuery);
    print_r($data);

  }




}