<?php


// Class that represents a geocoordinate consisting of 
// float latitude, float longitude
class GeoCoordinate
{
  public $latitude;
  public $longitude;

  public static function createFromString($string) {
    $coords = explode(',', $string);
    if (count($coords) != 2) {
      throw new KurogoDataException("Invalid string: {$string} for geocoordinate creation");
    }
    return new self($coords[0], $coords[1]);
  }

  public function __construct($lat, $long) {
    $this->latitude = $lat;
    $this->longitude = $long;
  }

  public function __toString() {
    return "{$this->latitude},{$this->longitude}";
  }
}