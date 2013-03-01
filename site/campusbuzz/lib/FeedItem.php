<?php


// Represents a basic feed item such as an item in an RSS feed, or a post in Facebook, or a tweet in Twitter.
class FeedItem
{
  protected $dataMap = array();
  protected $geoCoord;
  protected $config;

  public function __construct($sourceConfig) {
    $this->config = $sourceConfig;
  }

  public function getLabel($key) {
    return (isset($this->dataMap[$key])) ? $this->dataMap[$key] : null;
  }
  
  public function addLabel($key, $value) {      
    $this->dataMap[$key] = $value;    
  }

  public function addGeoCoordinate($coordinate) {
    $this->geoCoord = $coordinate;
  }

  public function addAndValidateStringLabel($key, $value, $errorMessage) {
    if (is_string($value)) {
      $this->dataMap[$key] = $value;
    } else {
      throw new KurogoDataException($errorMessage);
    }
  }

  public function addAndValidateOptionalStringLabel($key, $value, $errorMessage) {
    if ($value == null) {
      $this->dataMap[$key] = null;
    } else {
      $this->addAndValidateStringLabel($key, $value, $errorMessage);
    }
  }

  private function formatDate(&$date) {
    if (isset($date)) {
      $dateTime = new DateTime($date);
      $dateTime->setTimezone(new DateTimeZone("UTC"));
      // Solr date format, mandatory Z at the end for UTC
      $date = $dateTime->format('Y-m-d\TH:i:s\Z'); 
    }
  }

  

  // Obtains the json necessary to perform a solr update from an already populated FeedItem
  public function getSolrUpdateJson() {
    // create new map every time. May be better to memoize.
    $feedMap = $this->dataMap;

    // Add other metadata
    $feedMap["officialSource"] = $this->config->isOfficialSource();
    $feedMap["sourceType"] = $this->config->getSourceType();

    // Use the hash of the url to distinguish between feed items (the unique key in the db)
    if ($feedMap["url"] != null) {
      $feedMap["id"] = sha1($feedMap["url"]);
    } else {
      throw new KurogoDataException("Url for feed item is null");
    }

    // Set to default image url if this feed item did not contain an image
    if ($feedMap["imageUrl"] == null) {
      $sourceImageUrlDefault = $this->config->getSourceImageUrl();
      if ($sourceImageUrlDefault != null) {
        $feedMap["imageUrl"] = $sourceImageUrlDefault;
      }
    }

    // Add source category from config if it exists
    $sourceCategory = $this->config->getSourceCategory();
    if (isset($sourceCategory) && isset($feedMap["category"])) {
      switch (gettype($feedMap["category"])) {
      case "string":
        $feedMap["category"] = array($feedMap["category"], $sourceCategory);
        break;
      case "array":
        $feedMap["category"][] = $sourceCategory;
        break;
      case "NULL":
        $feedMap["category"] = array($sourceCategory);
        break;
      default:
        throw new KurogoDataException("Error in retrieved category type");
      }
    }

    // Fix date to be compatible with solr
    $this->formatDate($feedMap["pubDate"]);
    $this->formatDate($feedMap["startDate"]);
    $this->formatDate($feedMap["endDate"]);

    // Just use pubDate as startDate if pubDate doesn't exist
    if ($feedMap["pubDate"] == null && $feedMap["startDate"] != null) {
      $feedMap["pubDate"] = $feedMap["startDate"];
    }
    
    //TODO: Source location validation and map to GPS coord
    if ($feedMap["locationGeo"] == null) {

    }

    //Simply mark testing data as testing
    $feedMap["testing"] = (Tester::isTesting()) ? true : false;
    
    return json_encode($feedMap);
  }

  private function addCategory($category) {
  }

}



