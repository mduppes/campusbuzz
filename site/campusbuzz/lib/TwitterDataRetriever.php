<?php

/**
 * Class that holds methods in charge of retrieving Twitter data.
 * Can query Twitter's API for a given user's feed, or do a general geolocation search around a certain geocoordinate.
 */
class TwitterDataRetriever extends URLDataRetriever
{
  protected $DEFAULT_PARSER_CLASS = 'JSONDataParser';
  protected $cacheGroup = 'Twitter';

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


  /**
   * Given a parsed json php item, populate a newFeedItem and validate
   */
  private function populateFeedItem(&$newFeedItem, $jsonFeedItem, $userId) {
    // Manually populate for now
    $newFeedItem->addAndValidateStringLabel("pubDate", $jsonFeedItem["created_at"],
                                            "No created_at for tweet");

    $newFeedItem->addAndValidateStringLabel("content", $jsonFeedItem["text"],
                                            "No text for tweet");


    // Need a title, for now copy content
    $newFeedItem->addAndValidateStringLabel("title", $jsonFeedItem["text"],
                                            "Error duplicating text for title");

    $geoCoord = $this->extractGeoCoordinate($jsonFeedItem);
    if (isset($geoCoord)) {
      $newFeedItem->addGeoCoordinate($geoCoord);
    }

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

      // This is a tweet from a different user
      if ($jsonFeedItem["from_user"] !== $userId) {
        // Look through posts to see if any were official sources
        $isOfficialSource = (isset($this->_officialSourceTwitterUserMap[$jsonFeedItem["from_user"]])) ? true : false;
        $newFeedItem->addLabel("officialSource", $isOfficialSource);
      }
    }
  }

  /**
   * Internal function for parseing obtained feed into FeedItems. Exposed for testing (TODO: properly abstract away)
   */
  public function parseResultsIntoFeedItems($feedMap, $config) {
    if ($feedMap == null) {
      throw new KurogoDataException("Error parsing null feed json item");
    }

    $feedItems = array();
    foreach ($feedMap as $jsonFeedItem) {
      try {
        $newFeedItem = FeedItem::createFromConfig($config);
        $this->populateFeedItem($newFeedItem, $jsonFeedItem, $config->getSourceUrl());
        $newFeedItem->addMetaData();
        $feedItems[] = $newFeedItem;
      } catch (Exception $e) {
        print "Failed to populate feed item: ". $e->getMessage(). "\n";
      }
    }
    return $feedItems;
  }

  /**
   * Retrieve twitter feed from user or geolocation search depending on config
   * @param data source config for this twitter source
   */
  public function retrieveSource($config) {
    $isGeoSearch = ($config->getSourceType() == "Twitter") ? false : true;

    if ($isGeoSearch) {
      // Config is geo location search for twitter at ubc
      $centerOfUBC = new GeoCoordinate(49.26, -123.24);
      $feedMap = $this->getTweetsAtGeoLocation($centerOfUBC, 3);
    } else {
      $feedMap = $this->getTweetsByUser($config->getSourceUrl());
    }

    //print_r($feedMap);
    $feedItems = $this->parseResultsIntoFeedItems($feedMap, $config);

    return $feedItems;
  }

  /**
   * Retrieve tweets from the region given by coordinate and radius.
   * @param GeoCoordinate for the center of the query.
   * @param the radius in km around the center.
   */
  public function getTweetsAtGeoLocation(GeoCoordinate $coordinate, $radius) {
    $this->setBaseURL('http://search.twitter.com/search.json');
    $this->addParameter('geocode', $coordinate->latitude. ','.
			$coordinate->longitude. ','.
			$radius. 'km');
    $data = $this->getData();
    print_r($data);
    if (isset($data) && isset($data["results"])) {
      return $data["results"];
    } else {
      return null;
    }
  }

  /**
   * Obtain tweets from a given user.
   * @param Twitter username
   */
  public function getTweetsByUser($user) {
    $this->setBaseURL('http://api.twitter.com/1/statuses/user_timeline.json');
    $this->addParameter('screen_name', $user);
    return $this->getData();
  }

  /**
   * Obtain a single tweet specified by id
   * @param id of the tweet
   */
  public function getTweetById($id) {
    $this->setBaseURL('http://api.twitter.com/1/statuses/show.json');
    $this->addParameter('id', $id);
    return $this->getData();
  }

  // Gathered twitter id's of official UBC sources
  private $_officialSourceTwitterUserMap =
    array(
          "ubcalumni" => "UBC Alumni Affairs",
          "ubcaplaceofmind" => "A Place of Mind",
          "UBCBoG" => "UBC Board of Governors",
          "OSenSecretariat" => "UBC Okanagan Senate Secretariat",
          "ubc_candcp" => "Campus + Community Planning",
          "UBC_CareerServ" => "UBC Career Services",
          "UBC_CSI" => "UBC Centre for Student Involvement",
          "UBC_CTLT" => "UBC Centre for Teaching, Learning and Technology",
          "UBCLearnSpaces" => "UBC Classroom Services",
          "UBCLonghouse" => "UBC First Nations House of Learning",
          "UBCGoGlobal" => "UBC Go Global",
          "ubcHR" => "Human Resources",
          "itubc" => "UBC IT",
          "meditubc" => "MedIT",
          "UBCMediaGroup" => "UBC Media Group",
          "ubcnews" => "UBC News, by Public Affairs",
          "ubconews" => "Okanagan campus news, by Alumni and University Relations",
          "ubcpress" => "UBC Press",
          "ubcproperties" => "UBC Properties Trust",
          "ubc_rms" => "Risk Management",
          "UBCSecurity" => "UBC Security",
          "UBCGlobalLounge" => "Simon K. Y. Lee Global Lounge and Resource Centre",
          "terryubc" => "UBC Terry Project",
          "techtransfer" => "University Industry Liaison Office",
          "YouBC" => "YouBC (Prospective Students)",
          "lfslc" => "LFS Learning Centre (Land and Food Systems)",
          "ArtsISIT_UBC" => "Arts Instructional Support &amp; Information Technology (Arts ISIT)",
          "ubcunitedway" => "UBC United Way Campaign",
          "UBCOCTL" => "UBC Okanagan Centre for Teaching and Learning",
          "UBCCommEngage" => "Community Engagement",
          "AdmnFinanceUBCO" => "UBC Okanagan AVP Administration and Finance",
          "ChanCentre" => "Chan Centre for the Performing Arts",
          "AMS_UBC" => "AMS Student Society",
          "amsvendors" => "AMS Vendors",
          "minischool" => "AMS Minischool",
          "ubcAUS" => "UBC Arts Undergraduate Society",
          "cusonline" => "UBC Commerce Undergraduate Society",
          "UBCFinanceClub" => "UBC Finance Club",
          "UBCGoldenKey" => "UBC Golden Key",
          "UBCimprov" => "UBCimprov",
          "ibclub" => "UBC International Business Club",
          "irsa_ubc" => "International Relations Student Association of UBC-V",
          "UBCJazzCafe" => "UBC Jazz Cafe Club",
          "UBCMA" => "UBC Marketing Association",
          "UBCMHAC" => "UBC Mental Health Awareness Club",
          "ubc_psa" => "UBC Pakistan Students' Association",
          "UBCPSSA" => "UBC Political Science Student Association",
          "ubcprelaw" => "UBC Pre-Law Society",
          "UBCRHA" => "UBC Residence Hall Association",
          "susubc" => "UBC Science Undergraduate Society",
          "TeamUp4KidsUBC" => "Team Up 4 Kids UBC",
          "UBC_UAEM" => "Universities Allied for Essential Medicines",
          "ubcplayersclub" => "UBC Players Club",
          "ubccanucks" => "UBC Canucks Community",
          "Passionproj" => "Passion Project",
          "CiTRnews" => "CiTR News",
          "AMSConfidential" => "AMS Confidential",
          "ubc11eleven" => "Eleven' Eleven",
          "foxtrotubc" => "Foxtrot UBC",
          "ubcinsiders" => "UBC Insiders",
          "ubcspectator" => "The UBC Spectator",
          "ubyssey" => "The Ubyssey - UBC's official student newspaper",
          "UbysseyNews" => "Ubyssey News - Campus News",
          "UbysseyCulture" => "Ubyssey Culture - Campus Culture",
          "asiapacificmemo" => "Asia Pacific Memo - Scholarly knowledge about contemporary Asia",
          "ubcrec" => "The Point - UBC REC's Online Magazine",
          "ubcappscience" => "UBC Faculty of Applied Science",
          "ubc_arts" => "UBC Faculty of Arts",
          "ubcdentistry" => "UBC Faculty of Dentistry",
          "ubcengineering" => "UBC Engineering",
          "ubcforestry" => "UBC Faculty of Forestry",
          "UBCLaw" => "UBC Faculty of Law",
          "ubclfs" => "UBC Faculty of Land and Food Systems",
          "UBCFOM" => "UBC Faculty of Management",
          "UBCmedicine" => "UBC Faculty of Medicine",
          "ubcpharmacy" => "UBC Faculty of Pharmaceutical Sciences",
          "ubcscience" => "UBC Faculty of Science",
          "ubcsauderschool" => "Sauder School of Business",
          "UBCGradSchool" => "Faculty of Graduate Studies",
          "UBC_Languages" => "UBC Languages",
          "ubc_film_prod" => "Film Production at UBC",
          "UBCCPD" => "UBC CPD (Continuing Professional Development for health-care professionals)",
          "UBCMBA" => "UBC MBA",
          "UBCECM" => "UBC Masters of Management - Early Career Masters Program",
          "TheatreUBC" => "Theatre at UBC",
          "UBCartscoop" => "UBC Arts Co-op Program",
          "UBCCHCM" => "UBC Centre for Health Care Management",
          "UBCCPD" => "UBC Continuing Professional Development, a Division of the Faculty of Medicine",
          "ehealthstrategy" => "UBC eHealth Strategy Office",
          "UBCELI" => "UBC English Language Institute",
          "UBCAnth" => "UBC Department of Anthropology",
          "UBCChemistry" => "UBC Department of Chemistry",
          "ubc_cs" => "UBC Department of Computers Science",
          "edstubc" => "UBC Department of Educational Studies",
          "edstStudents" => "UBC Department of Educational Studies Students",
          "LLEDatUBC" => "UBC Department of Language and Literacy Education",
          "ubcosot" => "UBC Department of Occupational Science &amp; Occupational Therapy",
          "UBCPhilosophy" => "UBC Department of Philosophy",
          "UBCStatistics" => "UBC Department of Statistics",
          "ubcengineering" => "UBC Engineering",
          "ubclas" => "UBC Latin American Studies",
          "UBCMET" => "UBC Master of Educational Technology",
          "UBC_Music" => "UBC School of Music",
          "ubcteachered" => "UBC Teacher Education",
          "ubcspph" => "UBC School of Population and Public Health",
          "HELP_UBC" => "UBC Human Early Learning Partnership",
          "UBCLaw" => "UBC Faculty of Law",
          "UBCMDUP" => "UBC Faculty of Medicine Undergraduate Program",
          "SauderISIS" => "ISIS Research Centre, Sauder School of Business",
          "ISIS_Climate" => "Climate Intelligence Program, Sauder School of Business",
          "ubcpathology" => "UBC FOM Dept of Pathology and Laboratory Medicine",
          "maappsatubc" => "UBC, Institute of Asian Research, Master of Arts Asia Pacific Policy Studies",
          "liuinstituteubc" => "UBC Liu Institute for Global Issues",
          "UBCAsianStudies" => "UBC Asian Studies",
          "UBCscarp" => "UBC School of Community And Regional Planning (SCARP)",
          "ubcgarden" => "UBC Botanical Garden",
          "UBCBookstore" => "UBC Bookstore",
          "ubctours" => "UBC Campus Tours",
          "ChanCentre" => "Chan Centre",
          "UBC_Music" => "UBC School of Music",
          "CiTRradio" => "CiTR",
          "ubcfarm" => "UBC Farm",
          "UBCSprouts" => "UBC Sprouts",
          "MOA_UBC" => "UBC Museum of Anthropology",
          "ubcocampuslife" => "UBC Okanagan Campus Life",
          "UBCevents" => "UBC Online Events Calendar",
          "ubcpress" => "UBC Press",
          "ubcrec" => "UBC Rec",
          "theatreubc" => "Theatre at UBC",
          "ubctbirds" => "UBC Thunderbirds",
          "PlaceVanier" => "Place Vanier Residence",
          "TotemPark" => "Totem Park Residence",
          "UBCFairview" => "Fairview Crescent",
          "UBCAC" => "UBC Aquatic Centre",
          "UBCTennisCentre" => "UBC Tennis Centre",
          "UBCBirdcoop" => "UBC Birdcoop Fitness Centre",
          "ubclibrary" => "UBC Library",
          "UBCArchives" => "UBC Archives",
          "bmblib" => "UBC Biomedical Branch Library",
          "CanLearnCommons" => "Canaccord Learning Commons, Sauder School of Business",
          "UBCLearn" => "Chapman Learning Commons, Irving K. Barber Learning Centre",
          "ubceres" => "UBC Library eResources: Service Bulletins",
          "ubclawlib" => "UBC Law Library",
          "UBCLearn" => "UBC Learning Commons",
          "circle_ubc" => "cIRcle UBC Library's Information Repository",
          "MOA_AHHLA" => "Audrey &amp; Harry Hawthorn Library &amp; Archives at the UBC Museum of Anthropology",
          "SustainUBC" => "UBC Sustainability",
          "HealthyUBC" => "Healthy Minds at UBC, UBC Wellness Centre, and Health Promotion Programs",
          "ubc_le" => "UBC Learning Exchange",
          "ubccsl" => "UBC Community Learning Initiative",
          "ubcentrepreneur" => "entrepreneurship@UBC",
          "CelebrateLearn" => "UBC Celebrate Learning Week",
          "UBC_CLASS" => "Conference for Learning and Academic Student Success (CLASS)",
          "ubc_risingstars" => "Rising Stars of Research",
          "UBCSLC" => "UBC Student Leadership Conference (SLC)",
          "ubc_murc" => "UBC Multidisciplinary Undergraduate Research Conference (MURC)");

}