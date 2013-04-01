<?php


/**
 * Class that contains functionality for retrieving RSS data sources.
 * Parses the sources into FeedItems.
 */
class RSSDataRetriever extends URLDataRetriever
{
  protected $DEFAULT_PARSER_CLASS = 'PassthroughDataParser';
  protected $cacheGroup = 'RSS';

  /**
   * Get the RSS feed for the given url
   * @param the url of the RSS feed
   */
  public function getFeed($url) {
    $this->setBaseURL($url);
    $data = $this->getData();
    //print "RAW FEED FOR URL {$url}\n";
    //print_r($data);

    //print "\n\n\n\n\n\n\n\n";
    return $data;
  }

  public function parseResultsIntoFeedItems($rawFeed, $dataSourceConfig) {
    // feedItems is just the string of data, now parse this with simpleXML
    $parsedXML = new SimpleXMLElement($rawFeed);

    // apply namespaces used in this xml
    $namespaces = $parsedXML->getNameSpaces(true);
    foreach ($namespaces as $key => $nameSpaceUrl) {
      //print "Applying xml namespace {$key}, {$nameSpaceUrl}\n";
      $parsedXML->registerXPathNamespace($key, $nameSpaceUrl);
    }

    $labelMap =$dataSourceConfig->getLabelMap();
    if ($labelMap == null) {
      throw new KurogoConfigurationException("Label map creating new feed item is null");
    }

    $numFeedItems = count($parsedXML->channel->item);
    $feedItems = array();
    foreach($parsedXML->channel->item as $xmlItem) {
      try {
        $newFeedItem = FeedItem::createFromConfig($dataSourceConfig);
        foreach($labelMap as $schemaLabel => $xpath) {
          //print "Searching: ". $schemaLabel. " => ". $xpath. "\n";
          if (isset($xpath)) {
            $xpathResults = $xmlItem->xpath($xpath);
            if ($xpathResults == null) {
              print "Xpath returned no results: ". $xpath. "\n";
              continue;
            }
            // Get first result simpleXML element and convert it to string, and also strip html tags
            //print_r($xpathResults);
            $value = strip_tags((string) $xpathResults[0]);
            $newFeedItem->addLabel($schemaLabel, $value);
            //print "   Adding new data: ". $schemaLabel." => ".$value. "\n";
          } else {
            $newFeedItem->addLabel($schemaLabel, null);
          }
        }

        if ($dataSourceConfig->getSourceType() === "RSSEvents") {
          // further parse date
          $this->parseUBCEvent($newFeedItem);
          //print_r($newFeedItem);
        }

        $newFeedItem->addMetaData();
        $feedItems[] = $newFeedItem;
      } catch (Exception $e) {
        print "Error in parsing feed item. ". $e->getMessage()."\n";
      }
    }
    return $feedItems;
  }

  /**
   * Retrieve an array of FeedItems from a RSS data source from its config.
   * @param DataSourceConfig object that must have an rss type
   * @return array of feedItems populated from config settings.
   */
  public function retrieveSource($dataSourceConfig) {
    $rawFeed = $this->getFeed($dataSourceConfig->getSourceUrl());
    return $this->parseResultsIntoFeedItems($rawFeed, $dataSourceConfig);
  }

  /**
   * If the source were from UBC events database, they provide start/end dates in an orderly format without metadata
   * Extra parsing must be done which will modify the FeedItem content by extracting the start/end times.
   */
  private function parseUBCEvent(FeedItem &$newFeedItem) {
    $content = $newFeedItem->getLabel("content");

    // Date is in formats such as
    // Tue, December 4, 2012 9:30 AM - April 9, 2013, 10:30 AM
    // Tue, March 26, 2013 8:00 AM - 9:00 AM

    // first token is unneeded, day of week
    strtok($content, ' ');

    $startTime = $this->_extractDate();

    // next is '-', unneeded
    strtok(' ');

    $endTime = $this->_extractDate($startTime);

    $newFeedItem->addLabel("startDate", $startTime);
    $newFeedItem->addLabel("endDate", $endTime);

    // Now extract location
    $locationName = $this->_extractEventLocation();
    $newFeedItem->addLabel("locationName", $locationName);
  }

  // Internal functions from here related to ubc event rss feed parsing
  private function _extractEventLocation() {
    $locationName = strtok('.');
    if (strlen($locationName) < 3) {
      // If the length of string is too short, this is most likely an abbreviation
      $locationNameContinued = strtok('.');
      $locationName .= $locationNameContinued;
    }
    if (strlen($locationName) > 70) {
      // length is most likely too long.. something is wrong?
      throw new KurogoDataException("Location name is very long: {$locationName}");
    }
    return $locationName;
  }

  private $_daysOfWeek = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');

  private $_monthToIntMap =
    array('January' => 1,
          'February' => 2,
          'March' => 3,
          'April' => 4,
          'May' => 5,
          'June' => 6,
          'July' => 7,
          'August' => 8,
          'September' => 9,
          'October' => 10,
          'November' => 11,
          'December' => 12);

  /**
   * Assumes strtok is initialized prior to call at position where date starts
   * @return DateTime object for this parsed date
   */
  private function _extractDate($startDate = null) {
    if (isset($startDate)) {
      $dateTime = clone $startDate;
    } else {
      $dateTime = new DateTime();
      $dateTime->setTimeZone(new DateTimeZone('America/Vancouver'));
    }

    // The first token is either day of week which we don't need
    // and is also optional. The next token is then month
    $firstToken = trim(strtok(' '), ',');
    if (strpos($firstToken, ':') !== false) {
      $time = $firstToken;
      $clock = trim(strtok(' '), ',');
    } else {
      // There is a date, month, day component we need to extract
      if (in_array($firstToken, $this->_daysOfWeek)) {
        // this is a day of week, so skip and get next one which is month
        $month = trim(strtok(' '), ',');
      } else {
        $month = $firstToken;
      }

      // Convert to integer
      if (isset($this->_monthToIntMap[$month])) {
        $monthInt = $this->_monthToIntMap[$month];
      } else {
        throw new KurogoDataException("Invalid month: {$month}");
      }

      $day = trim(strtok(' '), ',');
      $year = trim(strtok(' '), ',');
      $dateTime->setDate($year, $monthInt, $day);

      $time = trim(strtok(' '), ',');
      $clock = trim(strtok(' '), ',');
    }

    // Now set time
    $timeTemp = explode(':', $time);
    if (count($timeTemp) != 2) {
      throw new KurogoDataException("Invalid hours:minutes from feed");
    }
    $timeHour = $timeTemp[0];
    $timeMin = $timeTemp[1];

    if ($clock === 'PM') {
      $timeHour += 12;
    } else if ($clock !== 'AM') {
      throw new KurogoDataException("Invalid time, no AM or PM specified from feed");
    }

    $dateTime->setTime($timeHour, $timeMin);
    return $dateTime;
  }

}