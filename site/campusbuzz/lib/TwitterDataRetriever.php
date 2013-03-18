<?php

class TwitterDataRetriever extends URLDataRetriever
{
  protected $DEFAULT_PARSER_CLASS = 'JSONDataParser';


  private function extractGeoCoordinate($jsonFeedItem) {
    $geo = null;
    // geo is apparently deprecated
    if (isset($jsonFeedItem["geo"])) {
      $geo = $jsonFeedItem["geo"];
    }
    // other alternative to obtaining geocoords
    if (isset($jsonFeedItem["coordinates"])) {
      $geo = $jsonFeedItem["coordinates"];
    }

    if ($geo != null && isset($geo["coordinates"]) && isset($geo["type"])) {
      // Check it is a point:
      if ($geo["type"] == "Point") {
        if (is_float($geo["coordinates"][0]) && is_float($geo["coordinates"][1])) {
          return new GeoCoordinate($geo["coordinates"][0], $geo["coordinates"][1]);
        }
      }
      // There is also polygon but ignore for now
    }
    // Something wrong with this geocoordinate
    return null;

  }

  private function populateFeedItem(&$newFeedItem, $jsonFeedItem) {

    // Manually populate for now
    $newFeedItem->addAndValidateStringLabel("pubDate", $jsonFeedItem["created_at"],
                                            "No created_at for tweet");

    $newFeedItem->addAndValidateStringLabel("content", $jsonFeedItem["text"],
                                            "No text for tweet");


    // Need a title, for now copy content
    $newFeedItem->addAndValidateStringLabel("title", $jsonFeedItem["text"],
                                            "Error duplicating text for title");

    $geoCoord = $this->extractGeoCoordinate($jsonFeedItem);
    $newFeedItem->addGeoCoordinate($geoCoord);

    $newFeedItem->addAndValidateOptionalStringLabel("locationName", @$jsonFeedItem["location"], "Not a valid location string for tweet");

    $userJsonItem = @$jsonFeedItem["user"];

    // If user item exists, get information from there
    if (isset($userJsonItem)) {
      $url = "https://www.twitter.com/". $userJsonItem["screen_name"];
      $newFeedItem->addAndValidateStringLabel("url", $url, "Invalid url for tweet");
      $newFeedItem->addAndValidateOptionalStringLabel("imageUrl", $userJsonItem["profile_image_url"], "Not a valid image url for tweet");
      $newFeedItem->addAndValidateStringLabel("name", $userJsonItem["screen_name"], "Invalid name from tweet");
    } else if (isset($jsonFeedItem["from_user"])) {
      // If no user field exists, see if from_user is there
      // source URL is the twitter user that the message originated from
      $url = "https://www.twitter.com/". $jsonFeedItem["from_user"];
      $newFeedItem->addAndValidateStringLabel("url", $url, "Invalid url for tweet");
      $newFeedItem->addAndValidateOptionalStringLabel("imageUrl", $jsonFeedItem["profile_image_url"], "Not a valid image url for tweet");
      $newFeedItem->addAndValidateStringLabel("name", $jsonFeedItem["from_user"], "Invalid name from tweet");
    }
  }

  public function parseResultsIntoFeedItems($feedMap, $config) {
    if ($feedMap == null) {
      throw new KurogoDataException("Error parsing null feed json item");
    }

    $feedItems = array();
    foreach ($feedMap as $jsonFeedItem) {
      try {
        $newFeedItem = FeedItem::createFromConfig($config);
        $this->populateFeedItem($newFeedItem, $jsonFeedItem);
        $newFeedItem->addMetaData();
        $feedItems[] = $newFeedItem;
      } catch (Exception $e) {
        print "Failed to populate feed item: ". $e->getMessage(). "\n";
      }
    }
    return $feedItems;
  }

  // Retrieve twitter feed from user or geolocation search depending on config
  public function retrieveSource($config) {
    if ($config->getSourceType() == "Twitter") {
      $feedMap = $this->getTweetsByUser($config->getSourceUrl());
    } else {
      // Config is geo location search for twitter at ubc
      $centerOfUBC = new GeoCoordinate(49.26, -123.24);
      $feedMap = $this->getTweetsAtGeoLocation($centerOfUBC, 3);
    }
    //print_r($feedMap);
    $feedItems = $this->parseResultsIntoFeedItems($feedMap, $config);
    return $feedItems;
  }

  public function getTweetsAtGeoLocation($coordinate, $radius) {
    $this->setBaseURL('http://search.twitter.com/search.json');
    $this->addParameter('geocode', $coordinate->latitude. ','.
			$coordinate->longitude. ','.
			$radius. 'km');
    $data = $this->getData();
    if (isset($data) && isset($data["results"])) {
      return $data["results"];
    } else {
      return null;
    }
  }

  public function getTweetsByUser($user) {
    $this->setBaseURL('http://api.twitter.com/1/statuses/user_timeline.json');
    $this->addParameter('screen_name', $user);
    return $this->getData();
  }

  public function getTweetById($id) {
    $this->setBaseURL('http://api.twitter.com/1/statuses/show.json');
    $this->addParameter('id', $id);
    return $this->getData();
  }

}