<?php


// Class that represents a geocoordinate consisting of 
// float latitude, float longitude
class DataSourceConfig
{
  private $configMap;

  // Get configs
  public function getTitle() {
    return isset($this->configMap["title"]) ? $this->configMap["title"] : null;
  }

  public function getSourceUrl() {
    return isset($this->configMap["sourceUrl"]) ? $this->configMap["sourceUrl"] : null;
  }

  public function getSourceImageUrl() {
    return isset($this->configMap["sourceImageUrl"]) ? $this->configMap["sourceImageUrl"] : null;
  }

  public function getSourceLocation() {
    return isset($this->configMap["sourceLocation"]) ? $this->configMap["sourceLocation"] : null;
  }

  public function isOfficialSource() {
    return isset($this->configMap["officialSource"]) ? $this->configMap["officialSource"] : null;
  }

  public function getSourceCategory() {
    return isset($this->configMap["sourceCategory"]) ? $this->configMap["sourceCategory"] : null;
  }

  public function getSourceType() {
    return isset($this->configMap["sourceType"]) ? $this->configMap["sourceType"] : null;
  }

  public function getLabelMap() {
    return isset($this->configMap["labelMap"]) ? $this->configMap["labelMap"] : null;
  }

  // Validation Mappings
  private function getConfigValidateMap() {
    return array("title" =>"validateAndSetString",
                 "sourceUrl" => "validateAndSetString",
                 "sourceImageUrl" => "validateAndSetOptionalString",
                 "sourceType" => "validateAndSetSourceType",
                 "officialSource" => "validateAndSetBool",
                 "sourceLocation" => "validateAndSetString",
                 "labelMap" => "validateAndSetLabelMap",
                 "sourceCategory" => "validateAndSetOptionalString",
          );
  }
  
  private function getValidSourceTypes() {
    return array("Facebook", "RSS", "Twitter");
  }

  private function getLabelValidateMap() {
    return array("title" => "validateAndSetString",
          "content" => "validateAndSetOptionalString",
          "url" => "validateAndSetString",
          "imageUrl" => "validateAndSetOptionalString",
          "pubDate" => "validateAndSetOptionalString",
          "startDate" => "validateAndSetOptionalString",
          "endDate" => "validateAndSetOptionalString",
          "category" => "validateAndSetOptionalString",
          "locationName" => "validateAndSetOptionalString",
          "locationGeo" => "validateAndSetOptionalString"
          );
  }

  // Returns true if string is nonempty
  private function isValidString($string) {
    if (is_string($string) && strlen($string) > 0) {
      return true;
    }
    return false;
  }
  private function validateAndSetString(&$output, $value) {
    if ($this->isValidString($value)) {
      $output = $value;
    } else {
      throw new KurogoConfigurationException("Invalid String");
    }
  }

  private function validateAndSetBool(&$output, $value) {
    if (is_bool($value)) {
      $output = $value;
    } else {
      throw new KurogoConfigurationException("Invalid Bool");
    }
  }

  private function validateAndSetOptionalString(&$output, $value) {
    if (!isset($value)) {
      $output = null;
    } else if ($this->isValidString($value)) {
      $output = $value;
    } else {
      throw new KurogoConfigurationException("Invalid Optional String");
    }
  }

  private function validateAndSetSourceType(&$output, $value) {
    if ($this->isValidString($value) && in_array($value, $this->getValidSourceTypes())) {
      $output = $value;
    } else {
      throw new KurogoConfigurationException("Invalid SourceType");
    }
  }

  private function validateAndSetLabelMap(&$output, $configLabelMap) {
    if ($configLabelMap == null) {
      $output = null;
    } else if (is_array($configLabelMap)) {
      $output = array();
      foreach ($this->getLabelValidateMap() as $label => $validateAndSetFunction) {
        $output[$label] = null;
        $this->{$validateAndSetFunction}($output[$label], $configLabelMap[$label]);
      }
    } else {
      throw new KurogoConfigurationException("Invalid LabelMap");
    }
  }

  private function validateAndSetConfig($configDecoded) {    
    
    $this->configMap = array();
    foreach ($this->getConfigValidateMap() as $key => $validateAndSetFunction) {
      $this->configMap[$key] = null;
      $this->{$validateAndSetFunction}($this->configMap[$key], $configDecoded[$key]);
    }

    // RSS feeds have custom mappings dependent on feed and must have labelMap
    if ($this->configMap['sourceType'] == "RSS") {
      if ($this->configMap['labelMap'] == null) {
        throw new KurogoConfigurationException("Invalid label mappings for source {$configDecoded->title}");
      }
    }
  }

  public function __construct($configDecoded) {
    $this->validateAndSetConfig($configDecoded);
  }



}