<?php

/**
 * Group of functions that create common search queries, for use when querying Solr.
 */
class SearchQueryFactory {

  public static function createGeoSearchQuery($type, $keyword, $categories, $fields = null) {

  }

  /**
   * Returns a default geospatial search around the UBC campus
   * @return SearchQuery
   */
  public static function createDefaultSearchQuery() {
    $searchQuery = new SearchQuery();
    $centerOfUbc = new GeoCoordinate(49.26, -123.24);
    $radius = 1;
    $searchFilter = new GeoRadiusSearchFilter($centerOfUbc, $radius);
 
    $searchQuery->addFilter($searchFilter);
    return $searchQuery;
  }

  /**
   * Returns a query that returns all stored objects
   * @return SearchQuery
   */
  public static function createSearchAllQuery() {
    $searchQuery = new SearchQuery();
    return $searchQuery;
  }

  /**
   * Create query to map locationName to a geocoordinate
   * @param string Name of location
   * @return SearchQuery
   */
  public static function createLocationToCoordinateQuery($locationName) {
    $searchQuery = new SearchQuery();
    $searchQuery->addKeyword($locationName);
    return $searchQuery;

  }

  public static function createSearchByIdQuery($id) {
    $searchQuery = new SearchQuery();
    $searchQuery->addFilter(new FieldQueryFilter("id", $id));
    return $searchQuery;
  }

}