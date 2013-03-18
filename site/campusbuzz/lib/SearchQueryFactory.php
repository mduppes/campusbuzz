<?php

/**
 * Group of functions that create common search queries, for use when querying Solr.
 */
class SearchQueryFactory {

  /**
   * Returns a default geospatial search around the UBC campus
   * @return SearchQuery
   */
  public static function createGeoRadiusSearchQuery($lat, $long, $radius) {
    $searchQuery = new SearchQuery();
    $center = new GeoCoordinate($lat, $long);
    $searchFilter = new GeoRadiusSearchFilter($center, $radius);
 
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