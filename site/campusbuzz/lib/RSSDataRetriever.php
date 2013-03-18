<?php


class RSSDataRetriever extends URLDataRetriever
{
  protected $DEFAULT_PARSER_CLASS = 'PassthroughDataParser';
  //protected $DEFAULT_PARSER_CLASS = 'SimpleXMLDataParser';
  //protected $DEFAULT_PARSER_CLASS = 'RSSDataParserIgnoreNameSpace';

  public function retrieveSource($dataSourceConfig) {
    print "----------------- Retrieving RSS source: ". $dataSourceConfig->getSourceUrl();

    $feedItems = $this->getFeed($dataSourceConfig->getSourceUrl());
    //print_r($feedItems);

    // feedItems is just the string of data, now parse this with simpleXML
    $parsedXML = new SimpleXMLElement($feedItems);
    //print_r($parsedXML);

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
              //throw new KurogoConfigurationException("Xpath returned no results: ". $xpath);
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
        $newFeedItem->addMetaData();
        $feedItems[] = $newFeedItem;
      } catch (Exception $e) {
        print "Error in parsing feed item. ". $e->getMessage()."\n";
      }
    }
    return $feedItems;
  }

  public function getFeed($url) {
    $this->setBaseURL($url);
    $data = $this->getData();
    return $data;
  }

}