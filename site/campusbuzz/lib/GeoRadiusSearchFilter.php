<?php


class GeoRadiusSearchFilter {
  // GeoCoordinate
  private $geoCoordinate;
  // km distance radius of filter
  private $radius;
  // Field to filter on solr schema
  private $field;

  public function getQueryParams() {
    return array("fq" => "{!geofilt}",
                 "pt" => (string)$geoCoordinate,
                 "sfield" => $field,
                 "d" => $radius);                 
  }

  public function __construct($geoCoordinate, $radius, $field = 'geoLocation') {
    $this->geoCoordinate = $geoCoordinate;
    $this->radius = $radius;
    $this->field = $field;
  }

}