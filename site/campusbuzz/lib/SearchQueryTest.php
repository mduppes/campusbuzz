<?php

/**
 * Test functions for search queries against test data in Solr
 */
class SearchQueryTest {

  public function testSearchAll() {
    Tester::getTester()->feedItemSolrController->persist($this->sampleSolrDocsJson);

    $searchQuery = SearchQueryFactory::createSearchAllQuery();
    $data = Tester::getTester()->feedItemSolrController->query($searchQuery);
    if ($data["response"]["numFound"] !== 5) {
      print "Solr not returning expected number of items:\n";
      print_r($data);
      return false;
    }
    return true;
  }

  public function testSearchById() {
    Tester::getTester()->feedItemSolrController->persist($this->sampleSolrDocsJson);

    $searchQuery = SearchQueryFactory::createSearchByIdQuery("c0366976a8dd744e2a0bb46e0b66edecf6cb3ad0");
    $data = Tester::getTester()->feedItemSolrController->query($searchQuery);
    if ($data["response"]["numFound"] !== 1 && $data["response"]["docs"][0]["id"] !== "c0366976a8dd744e2a0bb46e0b66edecf6cb3ad0") {
      print "Solr not returning expected number of items:\n";
      print_r($data);
      return false;
    }
    return true;
  }

  public function testBoundingBoxSearchQuery() {
    // Persist test database
    Tester::getTester()->feedItemSolrController->persist($this->sampleSolrDocsJson);
    $lat = 49.2612541;
    $lon = -123.234205;

    $mapPinsSearchQuery = SearchQueryFactory::createBoundingBoxSearchQuery($lon - 0.01, $lat - 0.01, $lon + 0.01, $lat + 0.01);
    $data = Tester::getTester()->feedItemSolrController->query($mapPinsSearchQuery);
    if ($data["response"]["numFound"] !== 1) {
      print "Solr not returning expected number of items:\n";
      print_r($data);
      return false;
    }

    $mapPinsSearchQuery = SearchQueryFactory::createBoundingBoxSearchQuery($lon - 1, $lat - 1, $lon + 1, $lat + 1);
    $data = Tester::getTester()->feedItemSolrController->query($mapPinsSearchQuery);
    if ($data["response"]["numFound"] !== 5) {
      print "Solr not returning expected number of items:\n";
      print_r($data);
      return false;
    }
    return true;
  }

  // Sanity test for geoRadius search
  public function testGeoRadiusSearchQuery() {
    // Persist test database
    Tester::getTester()->feedItemSolrController->persist($this->sampleSolrDocsJson);
    $lat = 49.2612541;
    $lon = -123.234205;

    $mapPinsSearchQuery = SearchQueryFactory::createGeoRadiusSearchQuery($lat, $lon , 1000);
    $data = Tester::getTester()->feedItemSolrController->query($mapPinsSearchQuery);
    if ($data["response"]["numFound"] !== 1) {
      print "Solr not returning expected number of items:\n";
      print_r($data);
      return false;
    }

    $mapPinsSearchQuery = SearchQueryFactory::createGeoRadiusSearchQuery($lat, $lon , 3000);
    $data = Tester::getTester()->feedItemSolrController->query($mapPinsSearchQuery);
    if ($data["response"]["numFound"] !== 4) {
      print "Solr not returning expected number of items:\n";
      print_r($data);
      return false;
    }
    return true;

  }

  public function testCategoryFilter() {
    Tester::getTester()->feedItemSolrController->persist($this->sampleSolrDocsJson);

    $mapPinsSearchQuery = SearchQueryFactory::createSearchAllQuery();
    $mapPinsSearchQuery->addCategory("News");

    $data = Tester::getTester()->feedItemSolrController->query($mapPinsSearchQuery);
    if ($data["response"]["numFound"] !== 1) {
      print "Solr not returning expected number of items:\n";
      print_r($data);
      return false;
    }

    // Add a OR category, so return them all now
    $mapPinsSearchQuery->addCategory("Recreation");
    $data = Tester::getTester()->feedItemSolrController->query($mapPinsSearchQuery);
    if ($data["response"]["numFound"] !== 5) {
      print "Solr not returning expected number of items:\n";
      print_r($data);
      return false;
    }
    return true;
  }

  public function testOfficialFilter() {
    Tester::getTester()->feedItemSolrController->persist($this->sampleSolrDocsJson);

    $mapPinsSearchQuery = SearchQueryFactory::createSearchAllQuery();
    $mapPinsSearchQuery->addFilter(new FieldQueryFilter("officialSource", true));

    $data = Tester::getTester()->feedItemSolrController->query($mapPinsSearchQuery);
    if ($data["response"]["numFound"] !== 4) {
      print "Solr not returning expected number of items:\n";
      print_r($data);
      return false;
    }

    $mapPinsSearchQuery = SearchQueryFactory::createSearchAllQuery();
    $mapPinsSearchQuery->addFilter(new FieldQueryFilter("officialSource", false));


    $data = Tester::getTester()->feedItemSolrController->query($mapPinsSearchQuery);
    if ($data["response"]["numFound"] !== 1) {
      print "Solr not returning expected number of items:\n";
      print_r($data);
      return false;
    }
    return true;
  }

  public function testKeywordSearch() {
    Tester::getTester()->feedItemSolrController->persist($this->sampleSolrDocsJson);

    $mapPinsSearchQuery = SearchQueryFactory::createSearchAllQuery();
    $mapPinsSearchQuery->addKeyword("defending");

    $data = Tester::getTester()->feedItemSolrController->query($mapPinsSearchQuery);
    if ($data["response"]["numFound"] !== 1) {
      print "Solr not returning expected number of items:\n";
      print_r($data);
      return false;
    }

    $mapPinsSearchQuery = SearchQueryFactory::createSearchAllQuery();
    $mapPinsSearchQuery->addKeyword("Thunderbirds");

    $data = Tester::getTester()->feedItemSolrController->query($mapPinsSearchQuery);
    if ($data["response"]["numFound"] !== 3) {
      print "Solr not returning expected number of items:\n";
      print_r($data);
      return false;
    }

    return true;
  }

  public function maxItemsTest() {
    Tester::getTester()->feedItemSolrController->persist($this->sampleSolrDocsJson);

    $mapPinsSearchQuery = SearchQueryFactory::createSearchAllQuery();
    $mapPinsSearchQuery->setMaxItems(3);

    $data = Tester::getTester()->feedItemSolrController->query($mapPinsSearchQuery);
    if ($data["response"]["numFound"] !== 5 && count($data["response"]["docs"]) !== 3) {
      print "Solr not returning expected number of items:\n";
      print_r($data);
      return false;
    }
    return true;
  }

  public function sortTest() {
    Tester::getTester()->feedItemSolrController->persist($this->sampleSolrDocsJson);

    $mapPinsSearchQuery = SearchQueryFactory::createSearchAllQuery();
    $mapPinsSearchQuery->addSort(new SearchSort("pubDate", false));

    $data = Tester::getTester()->feedItemSolrController->query($mapPinsSearchQuery);
    // Result should be ordered by descenging pubDate
    $expected =
      array("18fbcf6f7658ce1d2415c5144855e55c2aeebf6b",
            "52f98df75c8b7211d65033ce22197689dcdf7d66",
            "2b813ddea094d871e76c69b7b868ca7c3f6a173f",
            "c0366976a8dd744e2a0bb46e0b66edecf6cb3ad0",
            "3d59a01807ee66b010aeada6a0805c37734306a5");

    for ($i = 0; $i < count($data["response"]["docs"]); $i++) {
      if ($data["response"]["docs"][$i]["id"] !== $expected[$i]) {
        print "Solr not returning expected items:\n";
        print_r($data);
        return false;
      }
    }

    // Now reverse to ascending
    $mapPinsSearchQuery = SearchQueryFactory::createSearchAllQuery();
    $mapPinsSearchQuery->addSort(new SearchSort("pubDate", true));
    $expected = array_reverse($expected);
    $data = Tester::getTester()->feedItemSolrController->query($mapPinsSearchQuery);
    for ($i = 0; $i < count($data["response"]["docs"]); $i++) {
      if ($data["response"]["docs"][$i]["id"] !== $expected[$i]) {

        print "Solr not returning expected items:\n";
        print_r($data);
        return false;
      }
    }

    return true;
  }

  public function returnFieldsTest() {
    Tester::getTester()->feedItemSolrController->persist($this->sampleSolrDocsJson);

    $mapPinsSearchQuery = SearchQueryFactory::createSearchAllQuery();
    $mapPinsSearchQuery->addReturnField("id");

    $data = Tester::getTester()->feedItemSolrController->query($mapPinsSearchQuery);
    foreach ($data["response"]["docs"] as $doc) {
      if (count($doc) !== 1 || !isset($doc["id"])) {
        print "Solr not returning expected number of items:\n";
        print_r($data);
        return false;
      }
    }

    // add more return fields
    $mapPinsSearchQuery->addReturnField("category");
    $mapPinsSearchQuery->addReturnField("content");

    $data = Tester::getTester()->feedItemSolrController->query($mapPinsSearchQuery);
    foreach ($data["response"]["docs"] as $doc) {
      if (count($doc) !== 3 || !isset($doc["id"]) || !isset($doc["category"]) || !isset($doc["content"])) {
        print "Solr not returning expected number of items:\n";
        print_r($data);
        return false;
      }
    }
    return true;
  }

  /*
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
  */


  private $sampleSolrDocsJson = '
[
      {
        "title": "UBC wins Keg Spring Cup after a pair of four-goal wins",
        "name": "UBC Thunderbirds News",
        "content": "The defending CIS champion UBC Thunderbirds mens soccer team won the Keg Spring Cup in Victoria on Sunday with a second consecutive 4-0 win.",
        "url": "http://gothunderbirds.ca/news/2013/3/25/SOCM_0325134617.aspx",
    "imageUrl": "http://gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&image_path=/images/2012/11/9//Web_UBC-LAVAL_SF2%20-%20credit%20Yan%20Doublet%20(2).jpg",
    "pubDate": "2013-03-25T00:30:00Z",
    "category": [
                 "Soccer (m)",
                 "Recreation",
          "Leisure"
                ],
    "locationName": "Test",
    "locationGeo": " 49.16487850, -123.25249630",
    "officialSource": false,
    "sourceType": "RSS",
    "id": "c0366976a8dd744e2a0bb46e0b66edecf6cb3ad0",
    "testing": true,
    "queryCount": 0,
    "createDate": "2013-03-27T19:44:30.48Z"
    },
{
  "title": "Women Finish 3rd, Men Finish 9th At The Primm Battle",
    "name": "UBC Thunderbirds News",
    "content": "Primm, Nevada - The final day of play wrapped up at the Primm Valley Golf Club for The Primm Battle. The UBC womens golf team finished in third place, just four shots behind the winners, while the mens team finished in ninth, 30 shots off the pace.\n",
    "url": "http://gothunderbirds.ca/news/2013/3/26/GOLFM_0326132237.aspx",
    "imageUrl": "http://gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&image_path=/images/2012/3/14//WEB-WG20111020-054.jpg",
    "pubDate": "2013-03-26T15:54:00Z",
    "category": [
                 "Golf (m), Golf (w)",
                 "Recreation",
          "Leisure"
                 ],
    "locationName": "UBC",
    "locationGeo": " 49.26487850, -123.25249630",
    "officialSource": true,
    "sourceType": "RSS",
    "id": "52f98df75c8b7211d65033ce22197689dcdf7d66",
    "testing": true,
    "queryCount": 0,
    "createDate": "2013-03-27T19:44:30.48Z"
    },
{
  "title": "Bookstore Easter Egg Hunt ",
    "name": "UBCEvents",
    "content": "Wed, March 27, 2013 1:00 PM - 3:00 PM Administration Building.  WHERE: Okanagan campus Bookstore\n\nThe UBC Bookstore is holding a sweet promotion on March 27.\n\nHop over for a fun and sweet Easter egg hunt! ",
    "url": "http://www.calendar.events.ubc.ca:80/cal/event/eventView.do?subid=95014&calPath=%2Fpublic%2FEvents+Calendar%2FUBC+Okanagan&guid=CAL-09d22401-3da266fc-013d-a309e9aa-00000041myubc-team@interchange.ubc.ca&recurrenceId=",
    "imageUrl": "http://www.hr.ubc.ca/hr-networks/files/2011/10/ubc-logo-189x300.png",
    "pubDate": "2013-03-27T20:00:00Z",
    "startDate": "2013-03-27T20:00:00Z",
    "endDate": "2013-03-27T22:00:00Z",
    "locationName": "Administration Building",
    "locationGeo": "49.2612541,-123.234205",
    "officialSource": true,
    "sourceType": "RSSEvents",
    "id": "18fbcf6f7658ce1d2415c5144855e55c2aeebf6b",
    "category": [
                 "News",
                 "News",
          "Leisure"
                 ],
    "testing": true,
    "queryCount": 0,
    "createDate": "2013-03-27T19:44:28.362Z"
    },
{
  "title": "UBC and College of Idaho split Monday doubleheader",
    "name": "UBC Thunderbirds News",
    "content": "The UBC Thunderbirds softball team split a doubleheader, the first of five this week, against the visiting College of Idaho Coyotes on Monday at North Delta Community Park.",
    "url": "http://gothunderbirds.ca/news/2013/3/25/SOFT_0325134606.aspx",
    "imageUrl": "http://gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&image_path=/images/2011/5/3//McElroy4_050111.jpg",
    "pubDate": "2013-03-25T21:21:00Z",
    "category": [
                 "Softball",
                 "Recreation",
          "Leisure"
                 ],
    "locationName": "UBC",
    "locationGeo": " 49.26487850, -123.25249630",
    "officialSource": true,
    "sourceType": "RSS",
    "id": "2b813ddea094d871e76c69b7b868ca7c3f6a173f",
    "testing": true,
    "queryCount": 0,
    "createDate": "2013-03-27T19:44:30.48Z"
    },
{
  "title": "UBC sweeps doubleheader to take series from Idaho",
    "name": "UBC Thunderbirds News",
    "content": "The UBC Thunderbirds picked up a pair of 8-2 wins in their doubleheader with the College of Idaho Coyotes on Sunday, giving the Birds three wins in the four-game set to take over second place in the Cascade Conference.",
    "url": "http://gothunderbirds.ca/news/2013/3/24/BASE_0324133236.aspx",
    "imageUrl": "http://gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&image_path=/images/2013/3/17//_WWG7252.jpg",
    "pubDate": "2013-03-24T16:49:00Z",
    "category": [
                 "Baseball",
                 "Recreation",
          "Leisure"
                 ],
    "locationName": "UBC",
    "locationGeo": " 49.26487850, -123.25249630",
    "officialSource": true,
    "sourceType": "RSS",
    "id": "3d59a01807ee66b010aeada6a0805c37734306a5",
    "testing": true,
    "queryCount": 0,
    "createDate": "2013-03-27T19:44:30.48Z"
    }]
';
}