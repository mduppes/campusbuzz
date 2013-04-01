<?php


class GeoRadiusSearchFilter {
  // GeoCoordinate
  private $geoCoordinate;
  // km distance radius of filter
  private $radius;
  // Field to filter on solr schema
  private $field;

  public function getQueryString() {
    $filterString = "{!geofilt";
    $filterString .= " pt=". (string)$this->geoCoordinate;
    $filterString .= " sfield=". $this->field;
    $filterString .= " d=". $this->radius;
    $filterString .= "}";

    return $filterString;

  }

  public function __construct($geoCoordinate, $radius, $field = 'locationGeo') {
    $this->geoCoordinate = $geoCoordinate;
    $this->radius = SearchQuery::escapeSolrValue($radius);
    $this->field = $field;
  }

}