<?php


class SearchQuery {

  protected $keyword;
  protected $category;
  protected $filters;

  public function addFilter($filter) {
    $filters[] = $filter;
  }

  protected function getQueryString() {
    // process filters
  }
}