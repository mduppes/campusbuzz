<?php


class GeoRadiusSearchFilter {
  // GeoCoordinate
  private $geoCoordinate;
  // km distance radius of filter
  private $radius;
  // Field to filter on solr schema
  private $field;

  public function getQueryParams() {
    $filterString = "{!geofilt";
    $filterString .= " pt=". (string)$this->geoCoordinate;
    $filterString .= " sfield=". $this->field;
    $filterString .= " d=". $this->radius;
    $filterString .= "}";

    return array("fq" => $filterString);

  }

  public function __construct($geoCoordinate, $radius, $field = 'locationGeo') {
    $this->geoCoordinate = $geoCoordinate;
    $this->radius = $radius;
    $this->field = $field;
  }

}