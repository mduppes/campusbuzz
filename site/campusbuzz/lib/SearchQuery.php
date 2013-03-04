<?php


class SearchQuery {

  protected $keywordMap = array();
  protected $categies = array();
  protected $filters = array();
  protected $returnFields = array();

  public function addFilter($filter) {
    array_push($this->filters, $filter);
  }
  
  public function addReturnFields($field) {
    array_push($this->returnFields, $field);
  }

  public function addCategory($category) {
    array_push($this->categies, $category);
  }

  public function addKeyword($keyword, $field = "text") {
    $this->keywordMap[$field] = $keyword;
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

    // Do a AND search of label keywords, for most anticipated cases this shouldn't matter
    // Since we will do a general search on the catchall solr schema label
    $searchParams["q"] = implode(" ", $keywords);
    
    if ($this->returnFields != null) {
      $searchParams["fl"] = implode(',', $this->returnFields);
    }

    // Add filters
    foreach ($this->filters as $filter) {
      // check if intersect
      $filterParams = $filter->getQueryParams();
      $duplicateKeys = array_intersect_key($searchParams, $filterParams);
      if ($duplicateKeys != null) {
        print "filter is intersecting params:\n";
        print_r($duplicateKeys);
      }
      $searchParams = array_merge($searchParams, $filterParams);
    }

    return $searchParams;
  }
}