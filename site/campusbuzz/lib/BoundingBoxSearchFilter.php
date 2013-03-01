<?php


class BoundingBoxSearchFilter {
  // GeoCoordinates of corners of the bounding box
  private $corners;

  // Field to filter on solr schema
  private $field;

  protected function getQueryParams() {
    return array("fq" => "{$field}:[{$corners[0]} TO {$corners[1]}]");
                 
  }

  

}