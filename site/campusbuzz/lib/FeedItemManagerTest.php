<?php

/**
 * Integration level testing the data retrieval procedure for data sources.
 */
class FeedItemManagerTest {

  /**
   * Persists FeedItems into solr and checks to see they exist and are equal in contents
   */
  private function _solrUpdate($feedItems) {
    $solrController = Tester::getTester()->feedItemSolrController;
    $solrController->persistFeedItems($feedItems);

    $failed = 0;
    foreach ($feedItems as $feedItem) {
      $searchQuery = new SearchQuery();
      $searchQuery->addKeyword($feedItem->getLabel("id"), null, "id");
      $returnedFeedItems = $solrController->queryFeedItem($searchQuery);
      if (count($returnedFeedItems) != 1 ) {
        print "Does not return the same item:\n";
        print "FeedItem:\n";
        print_r($feedItem);
        print "Solr return:\n";
        print_r($returnedFeedItems);
        return false;
      }

      if (!$feedItem->isEqual($returnedFeedItems[0])) {
        print "NOT EQUAL: \n";
        print "pubdate: " . $feedItem->getLabel("pubDate"). " solr: ". $returnedFeedItems[0]->getLabel("pubDate")." \n";

        $failed++;
      }
    }

    return ($failed) ? false : true;
  }

  public function testRetrieveRSSSource() {
    $configJson = json_decode($this->sampleRSSEventConfig, true);
    $config = new DataSourceConfig($configJson);

    $feedItems = Tester::getTester()->rssController->retrieveSource($config);

    if (count($feedItems) == 0) {
      print "no feed items returned\n";
      return false;
    }

    return $this->_solrUpdate($feedItems);
  }

  public function testRetrieveRSSEventSource() {
    $configJson = json_decode($this->sampleRSSEventConfig, true);
    $config = new DataSourceConfig($configJson);

    $feedItems = Tester::getTester()->rssController->retrieveSource($config);

    if (count($feedItems) == 0) {
      print "no feed items returned\n";
      return false;
    }
    return $this->_solrUpdate($feedItems);
  }

  public function testRetrieveFacebookSource() {
    $configJson = json_decode($this->sampleFacebookConfig, true);
    $config = new DataSourceConfig($configJson);

    $feedItems = Tester::getTester()->facebookController->retrieveSource($config);

    if (count($feedItems) == 0) {
      print "no feed items returned\n";
      return false;
    }
    return $this->_solrUpdate($feedItems);
  }

  private $sampleFacebookConfig = '
{
                "name":"UBC",
                "sourceUrl":"universityofbc",
                "sourceImageUrl":"https://fbcdn-sphotos-b-a.akamaihd.net/hphotos-ak-snc7/598662_10151167459643704_1215460439_n.jpg",
                "sourceType":"Facebook",
                "officialSource":true,
                "sourceLocation":"UBC",
                "sourceCategory":"Learning"
                }';



  private $sampleRSSConfig = '
{
                "name":"UBC Thunderbirds News",
                "sourceUrl":"http://gothunderbirds.ca/rss.aspx",
                "sourceImageUrl":"http://www.gothunderbirds.ca/images/2009/10/19/leftnav_ubclogo.gif",
                "sourceType":"RSS",
                "officialSource":true,
                "sourceLocation":"UBC",
                "sourceCategory":"Recreation",
                "labelMap":{
                        "title":"title",
                        "name":null,
                        "content":"description",
                        "url":"guid",
                        "imageUrl":"enclosure/@url",
                        "pubDate":"pubDate",
                        "startDate": null,
                        "endDate":null,
                        "category":"category",
                        "locationName":null,
                        "locationGeo":null
                        }
                }';

  private $sampleRSSEventConfig = '{
                "name":"UBCEvents",
                "sourceUrl":"http://services.calendar.events.ubc.ca/cgi-bin/rssCache.pl?mode=rss&days=1",
                "sourceImageUrl":"http://www.hr.ubc.ca/hr-networks/files/2011/10/ubc-logo-189x300.png",
                "sourceType":"RSSEvents",
                "sourceLocation":"UBC",
                "officialSource":true,
                "sourceCategory":"News",
                "labelMap":{
                        "title":"title",
                        "name":null,
                        "content":"description",
                        "url":"link",
                        "imageUrl":null,
                        "pubDate":null,
                        "startDate":null,
                        "endDate":null,
                        "category":"category",
                        "locationName":null,
                        "locationGeo":null
                        }}';

}