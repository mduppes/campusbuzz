<?php

class CategorizerTest {

  private $categorizer;

  private $testBuzzCategories =
    array( "Sports" => array("soccer", "Thunderbirds"));

  private $testOfficialCategories =
    array( "News" => array("news", "media"),
           "Leisure" => array("soccer", "Thunderbirds"));

  private function _init() {
    $this->categorizer = new Categorizer($this->testOfficialCategories, $this->testBuzzCategories, Tester::getTester()->feedItemSolrController);
  }

  public function testCategorizerOfficial() {
    $this->_init();
    $startTime = new DateTime();
    $startTime->setTimezone(new DateTimeZone("UTC"));

    $config = new DataSourceConfig(json_decode($this->sampleRSSConfigJson, true));
    $feedItems = Tester::getTester()->rssController->parseResultsIntoFeedItems($this->sampleRSSFeed, $config);

    Tester::getTester()->feedItemSolrController->persistFeedItems($feedItems);

    $this->categorizer->categorizeFeedItemsSince($startTime);


    $query = new SearchQuery();
    $query->addCategory("Leisure");
    $query->addReturnField("id");
    $results = Tester::getTester()->feedItemSolrController->query($query);

    $ids = array();
    foreach ($results["response"]["docs"] as $doc) {
      $ids[$doc["id"]] = true;
    }

    $categoryIds =
      array("4baa1cec6327ea202343c1e66830fdbad1b16fe3",
            "40096c77d70a831cc92066ad54deab2e0823dc4f",
            "79ea6eb26dc97b682671574628620c617d2706a4",
            "f0d71dedce54d2b1e84f132be410674d999a4bf8",
            "214df98b9b814caa02cc0857abc87d627b09e9b4",
            "765bb625386ba59bd0ad13a19106b97984d43a67",
            "c66853bbd9862a82285787339b69a9c9f579da63");

    foreach ($categoryIds as $id) {
      if (!in_array($id, $ids)) {
        print "ID: {$id} is not in category leisure\n";
        return false;
      }
    }
    return true;

  }

  public function testCategorizerUnofficial() {
    $this->_init();
    $startTime = new DateTime();
    $startTime->setTimezone(new DateTimeZone("UTC"));

    // Test for unofficial now by changing the config
    $config = new DataSourceConfig( json_decode($this->sampleRSSConfigJson2, true));
    $feedItems = Tester::getTester()->rssController->parseResultsIntoFeedItems($this->sampleRSSFeed, $config);
    Tester::getTester()->feedItemSolrController->persistFeedItems($feedItems);

    $this->categorizer->categorizeFeedItemsSince($startTime);


    $query = new SearchQuery();
    $query->addCategory("Sports");
    $query->addReturnField("id");
    $results = Tester::getTester()->feedItemSolrController->query($query);

    $ids = array();
    foreach ($results["response"]["docs"] as $doc) {
      $ids[$doc["id"]] = true;
    }

    $categoryIds =
      array("4baa1cec6327ea202343c1e66830fdbad1b16fe3",
            "40096c77d70a831cc92066ad54deab2e0823dc4f",
            "79ea6eb26dc97b682671574628620c617d2706a4",
            "f0d71dedce54d2b1e84f132be410674d999a4bf8",
            "214df98b9b814caa02cc0857abc87d627b09e9b4",
            "765bb625386ba59bd0ad13a19106b97984d43a67",
            "c66853bbd9862a82285787339b69a9c9f579da63");

    foreach ($categoryIds as $id) {
      if (!in_array($id, $ids)) {
        print "ID: {$id} is not in category Sports\n";
        return false;
      }
    }

    return true;
  }

  private $sampleRSSConfigJson = '{
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

  private $sampleRSSConfigJson2 = '{
                "name":"UBC Thunderbirds News",
                "sourceUrl":"http://gothunderbirds.ca/rss.aspx",
                "sourceImageUrl":"http://www.gothunderbirds.ca/images/2009/10/19/leftnav_ubclogo.gif",
                "sourceType":"RSS",
                "officialSource":false,
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


  private $sampleRSSFeed = '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:s="http://sidearmsports.com/schemas/stories/1.0/"><channel><title>University of British Columbia</title><link>http://www.gothunderbirds.ca</link><description>Latest news items for University of British Columbia</description><atom:link href="http://www.gothunderbirds.ca/rss.aspx" rel="self" type="application/rss+xml" /><language>en-us</language><ttl>60</ttl><item><title>T-Birds split doubleheader with Red Hawks</title><link>http://www.gothunderbirds.ca/news/2013/3/30/BASE_0330130836.aspx</link><guid>http://www.gothunderbirds.ca/news/2013/3/30/BASE_0330130836.aspx</guid><category>Baseball</category><description><![CDATA[<img src="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&image_path=/images/2013/3/18//WEB-BB-2013-0317-CON-062.jpg" /><br /><br />The Simpson University Red Hawks picked up their first conference win of the season 9-7 over UBC in game one of their doubleheader on Saturday, but the Birds responded with a 10-3 win in the nightcap to earn the split in Redding, California.]]></description><enclosure url="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&amp;image_path=/images/2013/3/18//WEB-BB-2013-0317-CON-062.jpg" length="57500" type="image/jpg" /><pubDate>Sat, 30 Mar 2013 17:34:00 GMT</pubDate><s:story_id>2760</s:story_id></item><item><title>Softball seniors honoured after UBC splits with No. 10 Corban</title><link>http://www.gothunderbirds.ca/news/2013/3/30/SOFT_0330131806.aspx</link><guid>http://www.gothunderbirds.ca/news/2013/3/30/SOFT_0330131806.aspx</guid><category>Softball</category><description><![CDATA[<img src="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&image_path=/images/2013/3/25//WEB-_RXL2084.jpg" /><br /><br />The UBC Thunderbirds softball team concluded a weeklong home stand with a split against the No. 10 Corban Warriors on Friday at North Delta Park.]]></description><enclosure url="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&amp;image_path=/images/2013/3/25//WEB-_RXL2084.jpg" length="36862" type="image/jpg" /><pubDate>Fri, 29 Mar 2013 21:23:00 GMT</pubDate><s:story_id>2759</s:story_id></item><item><title>UBC Open performances propel T-Birds to the NAIA championships</title><link>http://www.gothunderbirds.ca/news/2013/3/29/TRACK_0329132533.aspx</link><guid>http://www.gothunderbirds.ca/news/2013/3/29/TRACK_0329132533.aspx</guid><category>Track and Field</category><description><![CDATA[<img src="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&image_path=/images/2013/3/29//WEB-TRK-2013-03229-10.jpg" /><br /><br />With strong performances at the UBC Open, numerous Thunderbirds hit the NAIA A qualifying marks for the national championships in May.]]></description><enclosure url="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&amp;image_path=/images/2013/3/29//WEB-TRK-2013-03229-10.jpg" length="46383" type="image/jpg" /><pubDate>Fri, 29 Mar 2013 20:21:00 GMT</pubDate><s:story_id>2758</s:story_id></item><item><title>UBC baseball travels to winless Simpson</title><link>http://www.gothunderbirds.ca/news/2013/3/29/BASE_0329131804.aspx</link><guid>http://www.gothunderbirds.ca/news/2013/3/29/BASE_0329131804.aspx</guid><category>Baseball</category><description><![CDATA[<img src="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&image_path=/images/2013/3/18//WEB-BB-2013-0317-CON-038.jpg" /><br /><br />The UBC Thunderbirds have won eight of their last nine games, and they have a great opportunity to keep rolling on the road this week against the struggling Simpson University Red Hawks in Redding, California.]]></description><enclosure url="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&amp;image_path=/images/2013/3/18//WEB-BB-2013-0317-CON-038.jpg" length="35869" type="image/jpg" /><pubDate>Fri, 29 Mar 2013 11:33:00 GMT</pubDate><s:story_id>2757</s:story_id></item><item><title>UBC softball records one of its biggest wins ever in split with Corban</title><link>http://www.gothunderbirds.ca/news/2013/3/29/SOFT_0329130046.aspx</link><guid>http://www.gothunderbirds.ca/news/2013/3/29/SOFT_0329130046.aspx</guid><category>Softball</category><description><![CDATA[<img src="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&image_path=/images/2011/5/3//Day2_050111.jpg" /><br /><br />The UBC Thunderbirds softball team (22-17) earned a signature victory on Thursday in a doubleheader split with the No. 10 Corban (Ore.) Warriors (26-6) at North Delta Community Park.]]></description><enclosure url="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&amp;image_path=/images/2011/5/3//Day2_050111.jpg" length="28269" type="image/jpg" /><pubDate>Thu, 28 Mar 2013 22:32:00 GMT</pubDate><s:story_id>2756</s:story_id></item><item><title>Three Thunderbirds To Represent UBC At CIS East-West Bowl</title><link>http://www.gothunderbirds.ca/news/2013/3/27/FB_0327133225.aspx</link><guid>http://www.gothunderbirds.ca/news/2013/3/27/FB_0327133225.aspx</guid><category>Football</category><description><![CDATA[<img src="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&image_path=/images/2013/3/3//2012.10.13_032.jpg" /><br /><br />Vancouver, BC - The UBC Thunderbirds will be well represented at that 2013 CIS East-West Bowl. The annual showcase of the top draft eligible players in university football will be held in the second week of May.]]></description><enclosure url="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&amp;image_path=/images/2013/3/3//2012.10.13_032.jpg" length="54451" type="image/jpg" /><pubDate>Thu, 28 Mar 2013 11:00:00 GMT</pubDate><s:story_id>2754</s:story_id></item><item><title>Kareem Ba Impresses At 2013 CFL Combine</title><link>http://www.gothunderbirds.ca/news/2013/3/27/FB_0327131405.aspx</link><guid>http://www.gothunderbirds.ca/news/2013/3/27/FB_0327131405.aspx</guid><category>Football</category><description><![CDATA[<img src="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&image_path=/images/2013/3/27/Web_Kareem_Ba_Combine.jpg" /><br /><br />Toronto, ON - UBC Thunderbirds defensive lineman Kareem Ba turned a few heads this past weekend at the CFL Combine in Toronto, Ontario. The annual event tests potential draft picks on their speed, strength, and agility.&nbsp;]]></description><enclosure url="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&amp;image_path=/images/2013/3/27/Web_Kareem_Ba_Combine.jpg" length="37014" type="image/jpg" /><pubDate>Thu, 28 Mar 2013 08:00:00 GMT</pubDate><s:story_id>2753</s:story_id></item><item><title>UBC sweeps No. 24 Concordia</title><link>http://www.gothunderbirds.ca/news/2013/3/28/SOFT_0328134954.aspx</link><guid>http://www.gothunderbirds.ca/news/2013/3/28/SOFT_0328134954.aspx</guid><category>Softball</category><description><![CDATA[<img src="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&image_path=/images/2013/3/26//WEB-_RXL2437.jpg" /><br /><br />The UBC Thunderbirds softball team gave up just two runs in a doubleheader sweep of the NAIA No. 24 Concordia (Ore.) Cavaliers on Wednesday at North Delta Community Park.]]></description><enclosure url="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&amp;image_path=/images/2013/3/26//WEB-_RXL2437.jpg" length="67976" type="image/jpg" /><pubDate>Wed, 27 Mar 2013 22:43:00 GMT</pubDate><s:story_id>2755</s:story_id></item><item><title>Schedule finalized for UBC Open</title><link>http://www.gothunderbirds.ca/news/2013/3/27/TRACK_0327131347.aspx</link><guid>http://www.gothunderbirds.ca/news/2013/3/27/TRACK_0327131347.aspx</guid><category>Track and Field</category><description><![CDATA[<img src="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&image_path=/images/2012/8/8/2010_04_25_UBC_Track_112.jpg" /><br /><br />Here is the final schedule for the UBC Open, which will take place on Friday, March 29 at the Rashpal Dhillon Track and Field Oval on campus.]]></description><enclosure url="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&amp;image_path=/images/2012/8/8/2010_04_25_UBC_Track_112.jpg" length="34454" type="image/jpg" /><pubDate>Wed, 27 Mar 2013 20:03:00 GMT</pubDate><s:story_id>2752</s:story_id></item><item><title>UBC alumni and Olympians to compete at the UBC Open on Friday</title><link>http://www.gothunderbirds.ca/news/2013/3/27/TRACK_0327133237.aspx</link><guid>http://www.gothunderbirds.ca/news/2013/3/27/TRACK_0327133237.aspx</guid><category>Track and Field</category><description><![CDATA[<img src="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&image_path=/images/2012/7/26//Mason-2012-trials.jpg" /><br /><br />Former UBC standout high jumper and 2012 Olympian Michael Mason will begin his journey towards the 2013 IAAF World Athletics Championships this Friday, March 29 at the 2013 UBC Open.]]></description><enclosure url="http://www.gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&amp;image_path=/images/2012/7/26//Mason-2012-trials.jpg" length="52422" type="image/jpg" /><pubDate>Wed, 27 Mar 2013 11:24:00 GMT</pubDate><s:story_id>2751</s:story_id></item></channel></rss>';
}