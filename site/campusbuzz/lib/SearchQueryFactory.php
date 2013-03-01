<?php

class SearchQueryFactory {

  public static function createGeoSearchQuery($type, $keyword, $categories, $fields = null) {

  }

  public static function createDefaultSearchQuery() {
    $searchQuery = new SearchQuery();
    $centerOfUbc = new GeoCoordinate(49.26, -123.24);
    $radius = 1;
    $searchFilter = new GeoRadiusSearchFilter($centerOfUbc, $radius);
 
    $searchQuery->addFilter($searchFilter);
    return $searchQuery;
  }

  public static function createSearchAllQuery() {
    $searchQuery = new SearchQuery();
    return $searchQuery;
  }

  public static function createLocationToCoordinateQuery($locationName) {
    $searchQuery = new SearchQuery();
    $searchQuery->addKeyword($locationName);
    return $searchQuery;
  }


}