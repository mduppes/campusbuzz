<?php

class FacebookDataRetriever extends URLDataRetriever
{
  protected $DEFAULT_PARSER_CLASS = 'JSONDataParser';
  protected $cacheGroup = 'Facebook';

  // Facebook Graph API access related
  private $clientId;
  private $clientSecret;
  private $accessToken = null;

  protected function init($args) {
    parent::init($args);

    if (isset($args["FB_ID"])) {
      $this->clientId = $args["FB_ID"];
    } else {
      throw new KurogoConfigurationException("No FB_ID exists");
    }

    if (isset($args["FB_SECRET"])) {
      $this->clientSecret = $args["FB_SECRET"];
    } else {
      throw new KurogoConfigurationException("No FB_SECRET exists");
    }
  }

  private function getOpenGraphUrl($url) {
    $this->getAccessToken();
    $this->setBaseURL($url);
    $this->addParameter('access_token', $this->accessToken);
    $data = $this->getData();
    return $data;
  }

  // internal function exposed for testing..
  public function getFbId($name) {
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
    $url = @$jsonFeedItem["link"];

    // check if event
    $eventUrl = "https://www.facebook.com/events/";
    if ($jsonFeedItem["type"] == "link" && isset($url) && !strncmp($url, $eventUrl, strlen($eventUrl)) ) {
      // This is an event
      $newFeedItem->addAndValidateStringLabel("url", $url, "Invalid url for fb post");

      $eventId = trim(substr($url, strlen($eventUrl)), '/');

      $eventJsonData = $this->getPage($eventId);

      // Need a title, for now copy content
      $newFeedItem->addAndValidateStringLabel("title", $eventJsonData["name"],
                                              "Error for event title");

      $newFeedItem->addAndValidateStringLabel("startDate", $eventJsonData["start_time"],
                                              "No start_time for event");
      $newFeedItem->addAndValidateStringLabel("endDate", $eventJsonData["end_time"],
                                              "No end_time for event");

      $newFeedItem->addAndValidateStringLabel("pubDate", $eventJsonData["start_time"],
                                              "No starttime / pubdate for fb");

      $newFeedItem->addAndValidateOptionalStringLabel("content", $eventJsonData["description"],
                                                      "No valid description");

      $newFeedItem->addAndValidateOptionalStringLabel("locationName", $eventJsonData["location"], "Not a valid location string for fb event");



      $venueId = $eventJsonData["venue"]["id"];
      $venueJsonData = $this->getPage($venueId);
      $coord = $this->extractGeoCoordinate($venueJsonData);
      if (isset($coord)) {
        $newFeedItem->addGeoCoordinate($coord);
      }

    } else {
      // not an event

      $message = isset($jsonFeedItem["message"]) ? $jsonFeedItem["message"] : null;

      if (!isset($message)) {
        if (isset($jsonFeedItem["story"])) {
          $message = $jsonFeedItem["story"];
        } else {
          // This is probably a photo with no message, ignore
          throw new KurogoDataException("Message field is null, ignoring this fb: ". $jsonFeedItem["type"]);
        }
      }

      $newFeedItem->addAndValidateStringLabel("content", $message,
                                              "No message for fb");

      // Need a title, for now copy content
      $newFeedItem->addAndValidateStringLabel("title", $message,
                                              "Error duplicating text for title");


      if (!isset($url) && isset($jsonFeedItem["id"])) {
          $url = "https://www.facebook.com/". $jsonFeedItem["id"];
      }
      $newFeedItem->addAndValidateStringLabel("url", $url, "Invalid url for fb post");

      $newFeedItem->addAndValidateOptionalStringLabel("imageUrl", @$jsonFeedItem["picture"], "Not a valid image url for fb");

      $newFeedItem->addAndValidateStringLabel("pubDate", $jsonFeedItem["updated_time"],
                                              "No update_time (pubDate) for fb item");


      $newFeedItem->addAndValidateOptionalStringLabel("locationName", @$jsonFeedItem["place"]["name"], "Not a valid location string for fb");

      $geoCoord = $this->extractGeoCoordinate($jsonFeedItem);
      if (isset($geoCoord)) {
        $newFeedItem->addGeoCoordinate($geoCoord);
      }

    }

    // change name if this was from a different id
    if ($jsonFeedItem["from"]["id"] != $fbid) {
      $newFeedItem->addAndValidateStringLabel("name", $jsonFeedItem["from"]["name"], "Invalid from name from fb object");

      // This post is from somebody else, check if it is an official source
      $isOfficialSource = isset($this->_officialSourceFbIdMap[$jsonFeedItem["from"]["id"]]) ? true : false;
      $newFeedItem->addLabel("officialSource", $isOfficialSource);
    }
  }

  public function parseResultsIntoFeedItems($feedMap, $config, $fbid) {
    if ($feedMap == null) {
      throw new KurogoDataException("Error parsing null feed json item");
    }

    $feedItems = array();
    foreach ($feedMap as $jsonFeedItem) {
      try {
        $newFeedItem = FeedItem::createFromConfig($config);
        $this->populateFeedItem($newFeedItem, $jsonFeedItem, $fbid);

        $newFeedItem->addMetaData();
        $feedItems[] = $newFeedItem;
      } catch (Exception $e) {
        print "Failed to populate feed item: ". $e->getMessage(). "\n";
        print_r($jsonFeedItem);
        print "\n";
      }
    }
    //print "\n\n============= Obtained Facebook result: \n\n";
    //print_r($feedMap);
    //print "fbid\n";
    //print_r($fbid);
    //print_r($feedItems);
    return $feedItems;
  }

  /**
   * Retrieve FeedItems by querying the URL's from a given data source config.
   * @param data source config to retrieve.
   * @return array of FeedItems obtained from this data source
   */
  public function retrieveSource(DataSourceConfig $config) {
    $fbid = $this->getFbId($config->getSourceUrl());
    $feedMap = $this->getFeedFromPage($config->getSourceUrl());
    return $this->parseResultsIntoFeedItems($feedMap, $config, $fbid);
  }

  // Gathered FBID's of official UBC source pages
  private $_officialSourceFbIdMap =
    array(
          "16761458703" => "The University of British Columbia",
          "187745150182" => "UBC Alumni Association",
          "43456649150" => "UBC Film Production Alumni Association",
          "8593703425" => "UBC Bookstore",
          "119392889939" => "UBC Campus and Community Planning",
          "185527296331" => "UBC Career Services",
          "211023718628" => "UBC Centre for Student Involvement",
          "135004786640540" => "UBC Centre for Teaching, Learning and Technology",
          "152158141486572" => "UBC First Nations House of Learning",
          "114025336267" => "UBC Go Global",
          "138288639530229" => "UBC Learning Exchange",
          "133596536665975" => "UBC's news in the Okanagan",
          "62250146183" => "UBC Press",
          "206397061958" => "UBC Prospective Undergraduates",
          "48978787993" => "UBC Alma Mater Society",
          "64895937030" => "UBC Commerce Undergraduate Society",
          "138083118623" => "UBC International Business Club",
          "216459251726609" => "UBC Residence Hall Association",
          "128843469516" => "Science Undergraduate Society of UBC",
          "173864423352" => "UBC Student Leadership Conference (SLC)",
          "123083611078355" => "UBC Players Club",
          "99164676792" => "The Ubyssey",
          "191367330892004" => "UBC Faculty of Applied Science (Engineering)",
          "143543645534" => "UBC Faculty of Arts",
          "145943338785891" => "UBC Faculty of Forestry",
          "283683945029178" => "UBC Faculty of Graduate Studies",
          "349844239398" => "UBC Faculty of Land and Food Systems",
          "180644975280905" => "UBC Faculty of Law",
          "77207808489" => "UBC Faculty of Science",
          "151693221552338" => "UBC Sauder School of Business BCom Program",
          "138463566188010" => "UBC Sauder School of Business MBA Program",
          "122723497779164" => "UBC Master of Management - Early Career Masters Program",
          "152000511496023" => "Sauder School of Business at the University of British Columbia",
          "199645413412070" => "Arts One Program at UBC",
          "22151909544" => "Biochemistry at UBC",
          "257755474262509" => "UBC Department of Chemistry",
          "32625077959" => "UBC CFIS",
          "105481622830895" => "UBC CPD (The UBC Division of Continuing Professional Development, Faculty of Medicine)",
          "118530886992" => "UBC English Language Institute",
          "43456649150" => "UBC Film Production Program",
          "218095258223843" => "UBC Department of Geography",
          "150521538304337" => "UBC Graduate School of Journalism",
          "12269080757" => "UBC Integrated Science Program",
          "138288639530229" => "UBC Learning Exchange",
          "92780592171" => "UBC Opera",
          "471799796202940" => "UBC Philosophy Department",
          "301557643201292" => "UBC Teacher Education",
          "84835972965" => "Theatre at UBC",
          "320891444656637" => "UBC Arts Co-op Program",
          "137857806262448" => "UBC Arts Co-op Students' Association",
          "217735101629082" => "School of Population and Public Health",
          "319171334760379" => "UBC Human Early Learning Partnership",
          "116247148388385" => "UBC School of Music",
          "221355054597138" => "UBC Liu Institute for Global Issues",
          "79718832777" => "UBC Aquatic Centre",
          "77837463267" => "UBC Botanical Garden",
          "148903255121402" => "Beaty Biodiversity Museum",
          "33605145925" => "Chan Centre for the Performing Arts",
          "141914082503899" => "Go Thunderbirds",
          "16609125085" => "Irving K. Barber Learning Centre",
          "94560725354" => "Morris and Helen Belkin Art Gallery",
          "261008030084" => "Museum of Anthropology",
          "201724309862026" => "UBC Department of Occupational Science &amp; Occupational Therapy",
          "124560474228521" => "UBC REC",
          "21575979616" => "UBC Okanagan Athletics",
          "112198338520" => "UBC Wellness Centre",
          "116247148388385" => "UBC School of Music",
          "142718259085294" => "UBC Birdcoop Fitness Centre",
          "235699997790" => "UBC Library",
          "165768816766471" => "Canaccord Learning Commons",
          "128846127152088" => "Chapman Learning Commons",
          "10434725898" => "David Lam Library",
          "16609125085" => "Irving K. Barber Learning Centre",
          "6582698414" => "Law Library",
          "26266194795" => "Asian Library",
          "230872143627109" => "Xwi7xwa Library",
          "120500581320676" => "Asia Pacific Memo",
          "125372226396" => "Healthy Minds at UBC"
          );

}