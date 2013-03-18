<?php


class SearchQuery {

  protected $keywordMap = array();
  protected $categies = array();
  protected $filters = array();
  protected $geoFilters = array();
  protected $returnFields = array();
  protected $rows;

  public function addFilter($filter) {
    // Due to problem specific to geospatial queries separating them.
    if ($filter instanceof GeoRadiusSearchFilter ||
        $filter instanceof BoundingBoxSearchFilter) {
      array_push($this->geoFilters, $filter);
    } else {
      array_push($this->filters, $filter);
    }
  }
  
  public function addReturnField($field) {
    array_push($this->returnFields, $field);
  }

  public function addCategory($category) {
    array_push($this->categies, $category);
  }

  public function addKeyword($keyword, $field = "text") {
    // False booleans print out as "", we don't want that
    $this->keywordMap[$field] = $keyword;
  }

  public function setMaxItems($max) {
    $this->rows = $max;
  }

  // Return query parameters as an associative array of key to value
  public function getQueryParams() {
    $searchParams  = array();
  
    $keywords = array();
    foreach ($this->keywordMap as $searchField => $keyword) {
      array_push($keywords, "{$searchField}:{$keyword}");
    }
    // if no keywords, the default is to search for anything
    if (empty($keywords)) {
      array_push($keywords, "*:*");
    }

    // Do a OR search of label keywords, for most anticipated cases this shouldn't matter
    // Since we will do a general search on the catchall solr schema label
    $searchParams["q"] = implode(" ", $keywords);
    
    if ($this->returnFields != null) {
      $searchParams["fl"] = implode(',', $this->returnFields);
    }

    // set number of max items to return
    if ($this->rows !== null) {
      $searchParams["rows"] = $this->rows;
    }

    // Add filters
    $fq = array();
    foreach ($this->filters as $filter) {
      $filterParams = $filter->getQueryParams();
      foreach ($filterParams as $attribute => $value) {
        if ($attribute == "fq") {
          array_push($fq, $value);
        } else {
          $searchParams[$attribute] = $value;
        }
      }
    }

    // Add geo filters separately (For some reason query is incorrect if placed before
    foreach ($this->geoFilters as $filter) {
      $filterParams = $filter->getQueryParams();
      array_push($fq, $filterParams["fq"]);
    }

    // Although solr allows multiple fields such as fq, Kurogo's representation uses a PHP array
    // So cannot have multiple fields. To workaround we combine all fq's with logical and
    if (!empty($fq)) {
      $searchParams["fq"] = implode(" AND ", $fq);
    }

    Kurogo::log(1, "search params: ". print_r($searchParams, true), "SearchParams");
    return $searchParams;
  }
}