<?php


// Represents a basic feed item such as an item in an RSS feed, or a post in Facebook, or a tweet in Twitter.
class FeedItem
{
  protected $dataMap = array();
  protected $config;
  protected $createdFrom;

  protected function __construct() {}

  public static $validParams =
    array(
          "id" => array("string"),
          "title" => array("string"),
          "name" => array("string"),
          "officialSource" => array("boolean"),
          "sourceType" => array("string"),
          "url" => array("string"),
          "imageUrl" => array("string"),
          "category" => array("array"),
          "pubDate" => array("string"),
          "startDate" => array(null, "string", "DateTime"),
          "endDate" => array("string", null, "DateTime"),
          "locationName" => array("string"),
          "locationGeo" => array("string"),
          "testing" => array("boolean")
          );

  /**
   * Checks for content equality in the feedItem.
   * Does not check the config or createdFrom fields.
   * @return true if $other has the same contents as $this
   */
  public function isEqual(FeedItem $other) {
    foreach (self::$validParams as $key => $value) {
      // only check for valid parameters
      if (isset($this->dataMap[$key]) || isset($other->dataMap[$key])) {
        if ($this->dataMap[$key] !== $other->dataMap[$key]){
          print "Not Equal: {$key}\n";
          return false;
        }
      }
    }
    return true;
  }

  // Factory method to create from config file
  public static function createFromConfig(DataSourceConfig $config) {
    $feedItem = new FeedItem();
    $feedItem->config = $config;
    $feedItem->createdFrom = "config";
    return $feedItem;
  }

  /**
   * Create FeedItem from solr query results.
   * @Note when creating from solr, the feedItem is validated on initialization
   */
  public static function createFromSolr($solrResponse) {
    $feedItem = new FeedItem();
    $feedItem->createdFrom = "solr";

    $feedItem->dataMap = $solrResponse;

    if (!$feedItem->isValid()) {
      throw new KurogoDataException("Invalid feed item returned from solr");
    }
    return $feedItem;
  }

  public function isValid() {
    foreach (FeedItem::$validParams as $field => $validTypes) {
      if (isset($this->dataMap[$field])) {
        $isValidType = false;
        foreach ($validTypes as $validType) {
          if (gettype($this->dataMap[$field]) == $validType) {
            $isValidType = true;
            break;
          }
        }
        if (!$isValidType) {
          return false;
        }
      } else if (!in_array(null, $validTypes)) {
        print "no valid type found for {$field}\n";
        return false;
      }
    }
    return true;
  }

  public function getLabel($key) {
    return (isset($this->dataMap[$key])) ? $this->dataMap[$key] : null;
  }

  public function addLabel($key, $value) {
    // trim whitespace
    if (is_string($value)) {
      $value = trim($value, ' ');
    }
    $this->dataMap[$key] = $value;
  }

  public function addGeoCoordinate(GeoCoordinate $coordinate) {
    $this->dataMap["locationGeo"] = (string) $coordinate;
  }

  public function getGeoCoordinate() {
    if (!isset($this->dataMap["locationGeo"])) {
      return null;
    } else {
      return GeoCoordinate::createFromString($this->dataMap["locationGeo"]);
    }
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

  /**
   * Converts a string or DateTime $date into a solr compatible string format
   * @params string or DateTime object that will be modified
   */
  private function formatDate(&$date) {
    if (isset($date)) {
      $startDateType = gettype($date);
      if ($startDateType === "string") {
        $dateTime = new DateTime($date);
      } else {
        $dateTime = $date;
      }

      $dateTime->setTimezone(new DateTimeZone("UTC"));
      // Solr date format, mandatory Z at the end for UTC
      $date = $dateTime->format('Y-m-d\TH:i:s\Z');
    }
  }

  /**
   * Adds additional meta information to this feed item, and formats all fields to match what solr allows.
   */
  public function addMetaData() {
    $feedMap = $this->dataMap;

    // Add other metadata if missing
    if (!isset($feedMap["officialSource"])) {
      $feedMap["officialSource"] = $this->config->isOfficialSource();
    }
    if (!isset($feedMap["sourceType"])) {
      $feedMap["sourceType"] = $this->config->getSourceType();
    }

    // Use the hash of the url and title to distinguish between feed items (the unique key in the db)
    $feedMap["id"] = sha1($feedMap["url"]. $feedMap["title"]);

    // Set to default image url if this feed item did not contain an image
    if (!isset($feedMap["imageUrl"])) {
      $sourceImageUrlDefault = $this->config->getSourceImageUrl();
      if ($sourceImageUrlDefault != null) {
        $feedMap["imageUrl"] = $sourceImageUrlDefault;
      }
    }

    // Add source category from config if it exists
    $sourceCategory = $this->config->getSourceCategory();
    if (isset($sourceCategory)) {
      $this->addCategory($sourceCategory, $feedMap);
    }

    // Fix date to be compatible with solr
    $this->formatDate($feedMap["pubDate"]);
    $this->formatDate($feedMap["startDate"]);
    $this->formatDate($feedMap["endDate"]);

    // Add default name if it doesnt exist
    if (!isset($feedMap["name"])) {
      $feedMap["name"] = $this->config->getSourceName();
    }

    // Just use pubDate as startDate if pubDate doesn't exist (events)
    if (!isset($feedMap["pubDate"]) && isset($feedMap["startDate"])) {
      $feedMap["pubDate"] = $feedMap["startDate"];
    }

    if (!isset($feedMap["locationName"])) {
      $feedMap["locationName"] = $this->config->getSourceLocation();
    }

    // Attempts to find a valid geolocation
    // First using the retrieved locationName, if it fails use configured default location
    if (!isset($feedMap["locationGeo"])) {
      $geoCoord = LocationMapper::getLocationMapper()->locationSearch($feedMap["locationName"]);
      if (isset($geoCoord)) {
        $feedMap["locationGeo"] = (string) $geoCoord;
      } else {
        print "No valid geolocation for this source!\n";
        throw new KurogoDataException("Not a valid geolocation for this source! LocationName: ". $feedMap["locationName"]);
        //$feedMap["locationName"] = $this->config->getSourceLocation();
        //$geoCoord = LocationMapper::getLocationMapper()->locationSearch($feedMap["locationName"]);
        //if (isset($geoCoord)) {
        //  $feedMap["locationGeo"] = (string) $geoCoord;
        //}
      }
    }

    //Simply mark testing data as testing
    $feedMap["testing"] = (Tester::isTesting()) ? true : false;

    //print_r($feedMap);
    $this->dataMap = $feedMap;
  }

  /**
   * Returns the json necessary to perform a solr update from an already populated FeedItem
   * @return valid json for a solr update
   */
  public function getSolrUpdateJson() {
    if (!$this->isValid()) {
      throw new KurogoDataException("Invalid feed item");
    }
    return json_encode($this->dataMap);
  }

  /**
   * Add a category to this feedItem.
   * @param new string category to add to this item
   */
  public function addCategory($category, &$modifyMap = null) {

    $feedMap = (isset($modifyMap)) ? $modifyMap : $this->dataMap;

    switch (@gettype($feedMap["category"])) {
    case "string":
      if ($feedMap["category"] !== $category) {
        $feedMap["category"] = array($feedMap["category"], $category);
      }
      break;
    case "array":
      if (!in_array($category, $feedMap["category"])) {
        $feedMap["category"][] = $category;
      }
      break;
    case "NULL":
      $feedMap["category"] = array($category);
      break;
    default:
      throw new KurogoDataException("Error in category type: ". gettype($feedMap["category"]));
    }
    if ($modifyMap == null) {
      $this->dataMap = $feedMap;
    } else {
      $modifyMap = $feedMap;
    }
  }

}



