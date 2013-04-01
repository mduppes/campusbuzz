<?php

/**
 * Performs sanity tests against a preconfigured Solr LocationMap index.
 * The index must first be populated by a script campusbuzz/scripts/
 */
class LocationMapperTest {

  /**
   * Checks to see Solr is responding with proper results.
   */
  public function sanityTestSolrLocationMap() {
    $locations = Tester::getTester()->locationMapSolrController->query(new SearchQuery());

    if (count($locations["response"]["docs"]) == 0) {
      print "No location to geocoordinate mapping returned from solr. Check to see if locationMap index is populated.\n";
      return false;
    }

    $sampleLocation = $locations["response"]["docs"][0];
    if (!isset($sampleLocation["locationName"]) || !isset($sampleLocation["locationGeo"])) {
      print "Not a valid location mapping returned from Solr:\n";
      print_r($sampleLocation);
      return false;
    }

    return true;
  }

  /**
   * Check several keywords to see if it returns the expected geocoordinate from Solr
   */
  public function testLocationSearch() {
    $keyword = "UBC";
    $expectedGeo = new GeoCoordinate(49.26487850,-123.25249630);

    $retrievedGeo = LocationMapper::getLocationMapper()->locationSearch($keyword);

    if (!$retrievedGeo->isEqual($expectedGeo)) {
      print "incorrect geoCoord returned for {$keyword}\n";
      return false;
    }


    $keyword = "Ontario, Canada";
    $retrievedGeo = LocationMapper::getLocationMapper()->locationSearch($keyword);

    if (isset($retrievedGeo)) {
      print "incorrect geoCoord returned for {$keyword}\n";
      return false;
    }

    $keyword = "Student Union Building";
    $expectedGeo = new GeoCoordinate(49.2676114,-123.250492);
    $retrievedGeo = LocationMapper::getLocationMapper()->locationSearch($keyword);

    if (!$retrievedGeo->isEqual($expectedGeo)) {
      print "incorrect geoCoord returned for {$keyword}\n";
      return false;
    }

    $keyword = "SUB";
    $retrievedGeo = LocationMapper::getLocationMapper()->locationSearch($keyword);

    if (!$retrievedGeo->isEqual($expectedGeo)) {
      print "incorrect geoCoord returned for {$keyword}\n";
      return false;
    }

    $keyword = "Kaiser";
    $expectedGeo = new GeoCoordinate(49.2631572, -123.2512517);
    $retrievedGeo = LocationMapper::getLocationMapper()->locationSearch($keyword);

    if (!$retrievedGeo->isEqual($expectedGeo, 0.001)) {
      print "incorrect geoCoord returned for {$keyword}\n";
      return false;
    }

    $keyword = "Walter Gage";
    $expectedGeo = new GeoCoordinate(49.2694628,-123.2495977);
    $retrievedGeo = LocationMapper::getLocationMapper()->locationSearch($keyword);

    if (!$retrievedGeo->isEqual($expectedGeo, 0.001)) {
      print $retrievedGeo;
      print "incorrect geoCoord returned for {$keyword}\n";
      return false;
    }

    return true;

  }

}