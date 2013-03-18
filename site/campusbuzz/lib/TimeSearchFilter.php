<?php

class TimeSearchFilter {

  private $startTime;
  private $endTime;
  private $field;

  private $solrFormatString = "Y-m-d\TH:i:s\Z";

  public function getQueryString() {
    $queryString = "[";
    $queryString .= (isset($this->startTime)) ? $this->startTime->format($this->solrFormatString) :"*";
    $queryString .= " TO ";
    $queryString .= (isset($this->endTime)) ? $this->endTime->format($this->solrFormatString) : "*";
    $queryString .= "]";

    return $this->field. ":". $queryString;
  }

  public function __construct($startTime, $endTime, $field) {
    $this->startTime = $startTime;
    $this->endTime = $endTime;
    $this->field = $field;

    if (isset($this->startTime)) {
      $this->startTime->setTimezone(new DateTimeZone("UTC"));
    }
    if (isset($this->endTime)) {
      $this->endTime->setTimezone(new DateTimeZone("UTC"));
    }
  }
}