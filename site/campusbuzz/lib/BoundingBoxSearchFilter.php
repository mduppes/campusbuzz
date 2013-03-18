<?php


class BoundingBoxSearchFilter {
  // GeoCoordinates of corners of the bounding box
  private $corners;

  // Field to filter on solr schema
  private $field;

  protected function getQueryString() {
    return "{$field}:[{$corners[0]} TO {$corners[1]}]";

  }

  public function __construct($corners, $field = 'locationGeo') {
    $this->corners = $corners;
    $this->field = $field;
  }

}