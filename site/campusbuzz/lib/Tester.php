<?php


class Tester {
  
  private $testing = false;
  private static $tester;

  // Get singleton tester
  public static function &getTester() {
    if (self::$tester === null) {
      self::$tester = new self();
    }
    return self::$tester;
  }

  public static function isTesting() {
    if (self::$tester === null) {
      return false;
    }
    return self::getTester()->_isTesting();
  }

  // Internal functions
  private function _isTesting() {
    return $this->testing;
  }

}