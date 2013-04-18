<?php


class GeoRadiusSearchFilter {
  // GeoCoordinate
  private $geoCoordinate;
  // km distance radius of filter
  private $radius;
  // Field to filter on solr schema
  private $field;

  public function getQueryString() {
    $filterString = "{!geofilt";
    $filterString .= " pt=". (string)$this->geoCoordinate;
    $filterString .= " sfield=". $this->field;
    $filterString .= " d=". $this->radius;
    $filterString .= "}";

    return $filterString;
  }

  /**
   * @param GeoCoordinate of center
   * @param radius in meters around center
   * @param the solr field name that this query searches on
   */
  public function __construct(GeoCoordinate $geoCoordinate, $radius, $field = 'locationGeo') {
    $this->geoCoordinate = $geoCoordinate;
    $this->radius = SearchQuery::escapeSolrValue($radius) / 1000;
    $this->field = $field;
  }

}