<?php


// Class that represents a geocoordinate consisting of 
// float latitude, float longitude
class GeoCoordinate
{
  public $latitude;
  public $longitude;

  public function __construct($lat, $long) {
    $this->latitude = $lat;
    $this->longitude = $long;
  }
}