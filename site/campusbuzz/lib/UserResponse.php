<?php

/**
 * Contains methods that handle user queries
 */
class UserResponse {

  /**
   * Handles the queries that ask for map pins.
   */
  public static function getGeoRadiusResponse(&$feedItemSolrController, $lat, $lon, $radius, $isOfficial, $numResults, $keyword = null, $categoryArray = null) {
    // $category, $keywords, etc TODO
    $searchQuery = SearchQueryFactory::createGeoRadiusSearchQuery($lat, $lon, $radius);
    $searchQuery->addFilter(new FieldQueryFilter("officialSource", $isOfficial));
    $searchQuery->setMaxItems($numResults);

    // Sort results by most recent first
    $searchQuery->addSort(new SearchSort("pubDate", false));

    // Fields we want returned from solr
    $searchQuery->addReturnField("title");
    $searchQuery->addReturnField("id");
    $searchQuery->addReturnField("officialSource");
    $searchQuery->addReturnField("category");
    $searchQuery->addReturnField("locationGeo");
    // TODO: add keywords if given, and category filters

    if (isset($categoryArray)) {
      $temp= explode(",", $categoryArray);
      foreach ($temp as $category){
        $searchQuery->addCategory($category);
      }
    }

    if ($keyword != "") {
      $searchQuery->addKeyword($keyword);
    }

    // Get and convert solr response to php object
    $data = $feedItemSolrController->query($searchQuery);

    if (!isset($data["response"])) {
      throw new KurogoDataException("Error, not a valid response.");
    }

    $pins = json_encode($data["response"]);


    // Now update query count for all items queried
    $feedItemIds = array();
    foreach ($data["response"]["docs"] as $solrResults) {
      $feedItemIds[] = $solrResults["id"];
    }

    // Batch update query count by one for results shown to user
    $feedItemSolrController->incrementQueryCounts($feedItemIds);

    return $pins;
  }


  public static function getBoundingBoxResponse($feedItemSolrController, $neLng, $neLat, $swLng, $swLat, $isOfficial, $index, $numResults, $keyword = null, $category = null, $sort = null) {

    // $category, $keywords, etc TODO

    $loadPostQuery = SearchQueryFactory::createBoundingBoxSearchQuery($neLng, $neLat, $swLng, $swLat);
    $loadPostQuery->addFilter(new FieldQueryFilter("officialSource", $isOfficial));

    if (isset($category)) {
      $loadPostQuery->addFilter(new FieldQueryFilter("category", $category));
    }
    $loadPostQuery->setMaxItems($numResults);

    if (isset($index)) {
      $loadPostQuery->setStartIndex($index);
    }

    if ($keyword != "")
      $loadPostQuery->addKeyword($keyword);

    //sort by most recent/popularity
    if ($sort === "time"){
      $loadPostQuery->addSort(new SearchSort("pubDate", false));
    }else{
      $loadPostQuery->addSort(new SearchSort("queryCount", false));
    }

    // Fields we want returned from solr
    $loadPostQuery->addReturnField("title");
    $loadPostQuery->addReturnField("id");
    $loadPostQuery->addReturnField("name");
    $loadPostQuery->addReturnField("sourceType");
    $loadPostQuery->addReturnField("url");
    $loadPostQuery->addReturnField("imageUrl");
    $loadPostQuery->addReturnField("pubDate");
    $loadPostQuery->addReturnField("startDate");
    $loadPostQuery->addReturnField("endDate");
    $loadPostQuery->addReturnField("locationName");
    $loadPostQuery->addReturnField("content");

    // Get and convert solr response to php object
    $data = $feedItemSolrController->query($loadPostQuery);

    if (!isset($data["response"])) {
      throw new KurogoDataException("Error, not a valid response.");
    }

    $results = json_encode($data["response"]);

    // Now update query count for all items queried
    $feedItemIds = array();
    foreach ($data["response"]["docs"] as $solrResults) {
      $feedItemIds[] = $solrResults["id"];
    }

    // Batch update query count by one for results shown to user
    $feedItemSolrController->incrementQueryCounts($feedItemIds);

    return $results;
  }






}