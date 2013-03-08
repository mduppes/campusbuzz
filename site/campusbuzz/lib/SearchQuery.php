<?php


class SearchQuery {

  protected $keywordMap = array();
  protected $categies = array();
  protected $filters = array();
  protected $returnFields = array();
  protected $rows;

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
    array_push($searchParams, 
               array( "q" => implode(" ", $keywords)));
    
    if ($this->returnFields != null) {
      array_push($searchParams,
                 array("fl" => implode(',', $this->returnFields)));
    }

    // set number of max items to return
    if ($this->rows !== null) {
      array_push($searchParams,
                 array("rows" => $this->rows));
    }

    // Add filters
    foreach ($this->filters as $filter) {
      // check if intersect
      $filterParams = $filter->getQueryParams();
      foreach ($filterParams as $attribute => $value) {
        array_push($searchParams,
                   array($attribute => $value));
      }
    }

    return $searchParams;
  }
}