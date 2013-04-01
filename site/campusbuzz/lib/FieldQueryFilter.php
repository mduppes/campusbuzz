<?php



class FieldQueryFilter {

  private $field;
  private $value;

  public function getQueryString() {
    return $this->field. ":". $this->value;
  }

  public function __construct($field, $value) {
    $this->field = $field;

    if ($value === false) {
      $value = 0;
    } else if ($value === true) {
      $value = 1;
    }

    $this->value = SearchQuery::escapeSolrValue($value);
  }

}