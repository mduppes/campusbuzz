<?php

class FacebookDataRetriever extends URLDataRetriever
{
  protected $DEFAULT_PARSER_CLASS = 'JSONDataParser';

  private $clientId = "347882941994347";

  // move this to file..
  private $clientSecret = "ca91999af6a102dc16168394fe826d92";
  private $accessToken = null;


  private function getOpenGraphUrl($url) {
    $this->getAccessToken();
    $this->setBaseURL($url);
    $this->addParameter('access_token', $this->accessToken);
    $data = $this->getData();
    return $data;
  }

  private function getFbId($name) {
    $url = 'https://graph.facebook.com/'. $name;
    $data = $this->getOpenGraphUrl($url);
    if (!isset($data)) {
      throw new KurogoDataException("Failed to retrieve fbid base feed");
    }
    return $data["id"];
  }

  public function getPage($id) {
    $url = 'https://graph.facebook.com/'. $id;
    return $this->getOpenGraphUrl($url);
  }

  public function getFeedFromPage($name) {
    $url = 'https://graph.facebook.com/'. $name. '/feed';
    $feed = $this->getOpenGraphUrl($url);
    return $feed["data"];
  }

  // Get access token from facebook (essentially the same as what is used in Kurogo's FacebookDataRetriever)
  private function getAccessToken() {
    if (!$this->accessToken) {
      $this->clearInternalCache();
      $this->setBaseURL('https://graph.facebook.com/oauth/access_token');
      $this->addParameter('client_id', $this->clientId);
      $this->addParameter('client_secret', $this->clientSecret);
      $this->addParameter('grant_type', 'client_credentials');

      $response = $this->getResponse()->getResponse();
      list($label, $token) = explode("=", $response);
      if ($label != "access_token" || !$token) {
	throw new KurogoDataException("Unable to retrieve facebook access token");
      }
      $this->accessToken = $token;
      $this->clearInternalCache();
    }
  }

  private function extractGeoCoordinate($jsonFeedItem) {
    if (isset($jsonFeedItem["place"]) && isset($jsonFeedItem["place"]["location"])) {
      return new GeoCoordinate($jsonFeedItem["place"]["location"]["latitude"],
                               $jsonFeedItem["place"]["location"]["longitude"]);
    } else if (isset($jsonFeedItem["location"])) {
      return new GeoCoordinate($jsonFeedItem["location"]["latitude"],
                               $jsonFeedItem["location"]["longitude"]);
    }
    return null;
  }

  private function populateFeedItem(&$newFeedItem, $jsonFeedItem, $fbid) {

    // Manually populate for now

    $url = $jsonFeedItem["link"];
    $newFeedItem->addAndValidateStringLabel("url", $url, "Invalid url for tweet");

    // check if event
    $eventUrl = "https://www.facebook.com/events/";
    if ($jsonFeedItem["type"] == "link" && !strncmp($url, $eventUrl, strlen($eventUrl)) ) {
      // This is an event
      $eventId = substr($url, strlen($eventUrl));
      $eventJsonData = $this->getFbId($eventId);
      // Need a title, for now copy content
      $newFeedItem->addAndValidateStringLabel("title", $eventJsonData["name"],
                                              "Error for event title");

      $newFeedItem->addAndValidateStringLabel("startDate", $eventJsonData["start_time"],
                                              "No start_time for event");
      $newFeedItem->addAndValidateStringLabel("endDate", $eventJsonData["end_time"],
                                              "No end_time for event");

      $newFeedItem->addAndValidateOptionalStringLabel("content", $eventJsonData["description"],
                                                      "No valid description");

      $newFeedItem->addAndValidateOptionalStringLabel("locationName", $eventJsonData["location"], "Not a valid location string for fb event");

      $venueId = $eventJsonData["venue"];
      $venueJsonData = $this->getPage($venueId);
      $coord = $this->extractGeoCoordinate($venueJsonData);
      $newFeedItem->addGeoCoordinate($coord);
    } else {
      // not an event

      $newFeedItem->addAndValidateOptionalStringLabel("imageUrl", @$jsonFeedItem["picture"], "Not a valid image url for tweet");

      $newFeedItem->addAndValidateStringLabel("content", $jsonFeedItem["message"],
                                              "No text for tweet");

      // Need a title, for now copy content
      $newFeedItem->addAndValidateStringLabel("title", $jsonFeedItem["message"],
                                              "Error duplicating text for title");

      $newFeedItem->addAndValidateOptionalStringLabel("locationName", @$jsonFeedItem["place"]["name"], "Not a valid location string for tweet");

      $geoCoord = $this->extractGeoCoordinate($jsonFeedItem);
      $newFeedItem->addGeoCoordinate($geoCoord);

    }
    $newFeedItem->addAndValidateStringLabel("pubDate", $jsonFeedItem["updated_time"],
                                            "No created_at for tweet");

    // change name if this was from a different id
    if ($jsonFeedItem["from"]["id"] != $fbid) {
      $newFeedItem->addAndValidateStringLabel("name", $jsonFeedItem["from"]["name"], "Invalid from name from fb object");
    }


  }


  private function parseResultsIntoFeedItems($feedMap, $config, $fbid) {
    if ($feedMap == null) {
      throw new KurogoDataException("Error parsing null feed json item");
    }

    $feedItems = array();
    foreach ($feedMap as $jsonFeedItem) {
      if ($jsonFeedItem["from"]["id"] != $fbid && $config->isOfficialSource()) {
        // This post is from somebody else
        // TODO: handle this
        continue;
      }

      try {
        $newFeedItem = FeedItem::createFromConfig($config);
        $this->populateFeedItem($newFeedItem, $jsonFeedItem, $fbid);
        $newFeedItem->addMetaData();
        $feedItems[] = $newFeedItem;
      } catch (Exception $e) {
        print "Failed to populate feed item". $e->getMessage(). "\n";
      }
    }
    print "\n\n============= Obtained Facebook result: \n\n";
    //print_r($feedMap);
    print "fbid\n";
    //print_r($fbid);
    print_r($feedItems);
    return $feedItems;
  }

  public function retrieveSource($config) {
    $fbid = $this->getFbId($config->getSourceUrl());
    $feedMap = $this->getFeedFromPage($config->getSourceUrl());
    return $this->parseResultsIntoFeedItems($feedMap, $config, $fbid);
  }
}