<?php



class FieldQueryFilter {

  private $field;
  private $value;

  public function getQueryParams() {
    $filterString = $this->field. ":". $this->value;
    return array("fq" => $filterString);
  }

  public function __construct($field, $value) {
    $this->field = $field;
    $this->value = $value;
  }

}