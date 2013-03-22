<?php


class BoundingBoxSearchFilter {
  // GeoCoordinates of corners of the bounding box
  private $corners;

  // Field to filter on solr schema
  private $field;

  public function getQueryString() {
    $filterString = $this->field;
    $filterString .= ":[";
    $filterString .= (string) $this->corners[0]. " TO ". (string) $this->corners[1];
    $filterString .= "]";
    return $filterString;
  }

  public function __construct($corners, $field = 'locationGeo') {
    $this->corners = $corners;
    $this->field = $field;
  }

}