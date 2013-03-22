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
    if (count($corners) != 2) {
      throw new KurogoDataException("Need 2 corners for bounding box search");
    }

    $smallerPoint = $corners[0];
    $largerPoint = $corners[1];
    if ($corners[0]->latitude > $corners[1]->latitude) {
      // swap
      $temp = $corners[0];
      $corners[0] = $corners[1];
      $corners[1] = $temp;
    }

    if ($corners[0]->longitude > $corners[1]->longitude) {
      throw new KurogoDataException("invalid bounding box coordinates");
    }

    $this->corners = $corners;
    $this->field = $field;
  }

}