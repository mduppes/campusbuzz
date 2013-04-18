<?php


// Class that represents a geocoordinate consisting of
// float latitude, float longitude
class GeoCoordinate
{
  public $latitude;
  public $longitude;

  /**
   * Checks whether $other GeoCoordinate is closer than $distance to this GeoCoordinate
   */
  public function isEqual(GeoCoordinate $other, $distance = 0.0001) {
    $latDiff = $this->latitude - $other->latitude;
    $lonDiff = $this->longitude - $other->longitude;
    $euclideanDiff = sqrt($latDiff * $latDiff + $lonDiff * $lonDiff);

    if ($euclideanDiff < $distance) {
      return true;
    } else {
      return false;
    }
  }

  public static function createFromString($string) {
    $coords = explode(',', $string);
    if (count($coords) != 2) {
      throw new KurogoDataException("Invalid string: {$string} for geocoordinate creation");
    }
    return new self($coords[0], $coords[1]);
  }

  public function __construct($lat, $long) {
    if (!is_numeric($lat) || !is_numeric($long)) {
      throw new KurogoDataException("Invalid non numeric coordinates: {$lat}, {$long}.");
    }

    $this->latitude = $lat;
    $this->longitude = $long;
  }

  public function __toString() {
    return "{$this->latitude},{$this->longitude}";
  }
}