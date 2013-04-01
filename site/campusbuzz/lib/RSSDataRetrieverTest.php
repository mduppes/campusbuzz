<?php

/**
 * Test functions for RSSDataRetriever.php
 */
class RSSDataRetrieverTest {

  /**
   * Uses network to access an existing rss feed
   */
  public function testGetFeed() {
    $data = Tester::getTester()->rssController->getFeed("http://gothunderbirds.ca/rss.aspx");
    return ($data != null) ? true : false;
  }

  /**
   * Sanity test against sample feed from UBC events
   */
  public function testParseResultsIntoFeedItems() {
    $configJson = json_decode($this->sampleRSSEventConfig, true);
    $config = new DataSourceConfig($configJson);

    $feedItems = Tester::getTester()->rssController->parseResultsIntoFeedItems($this->sampleRSSEventFeed, $config);

    if (count($feedItems) != 4) {
      print "invalid number of feedItems\n";
      return false;
    }

    if ($feedItems[0]->getLabel("title") !== "INVOKING VENUS: Feathers and Fashion") {
      print "invalid title\n";
      return false;
    }

    if ($feedItems[0]->getLabel("name") !== "UBCEvents") {
      print "invalid name\n";
      return false;
    }

    if ($feedItems[0]->getLabel("content") !== "Thu, February 7, 2013 10:00 AM - Sun, May 5, 2013, 5:00 PM UBC Point Grey Campus. Included with admission or membership. An exhibition of photo-based images by Catherine Stewart and accessories from the clothing collections of Claus Jahnke and Ivan Sayers. Using bird specimens from the Beaty Biodiversity Museum, Vancouver-based Stewart explores the role colour, patterning and adornment play in courtship and attraction. Through the juxtaposition of images of bird plumage with images of vintage fabrics and actual feathered fashion accessories, the parallels in human and bird behaviour become apparent. The lush and sensuous images magnify details in avian plumage and vintage fabrics, revealing a multitude of rich and varied hues that combine to create the colours, textures and patterns observed when viewing birds and humans at their finest.") {
      print "invalid content\n";
      return false;
    }

    if ($feedItems[0]->getLabel("imageUrl") !== "http://www.hr.ubc.ca/hr-networks/files/2011/10/ubc-logo-189x300.png") {
      print "invalid content\n";
      return false;
    }

    // pubdate = startdate, and daylight savings
    if ($feedItems[0]->getLabel("pubDate") !== "2013-02-07T18:00:00Z") {
      print "invalid content\n";
      return false;
    }

    if ($feedItems[0]->getLabel("startDate") !== "2013-02-07T18:00:00Z") {
      print "invalid content\n";
      return false;
    }

    if ($feedItems[0]->getLabel("endDate") !== "2013-05-06T00:00:00Z") {
      print "invalid content\n";
      return false;
    }

    if ($feedItems[0]->getLabel("locationName") !== "UBC Point Grey Campus") {
      print "invalid content\n";
      return false;
    }

    if ($feedItems[2]->getLabel("id") !== "0ad84133a05c87002a625dc2d6c82902b54305fa") {
      print_r( $feedItems[2]);
      print "invalid id\n";
      return false;
    }

    // pubdate = startdate, and daylight savings
    if ($feedItems[2]->getLabel("pubDate") !== "2013-03-06T20:00:00Z") {
      print "invalid content\n";
      return false;
    }

    if ($feedItems[2]->getLabel("startDate") !== "2013-03-06T20:00:00Z") {
      print "invalid content\n";
      return false;
    }

    if ($feedItems[2]->getLabel("endDate") !== "2013-03-30T19:00:00Z") {
      print "invalid content\n";
      return false;
    }

    return true;

  }

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


  private $sampleRSSEventFeed = '
<rss version="2.0">
<channel>
<title>UBCevents Calendar</title>
<link>http://events.ubc.ca/welcome.html</link>
<description>March 28, 2013</description>
<pubDate>29 Mar 2013 03:33:00 UT</pubDate>
<language>en-US</language>
<copyright>Copyright 2008-2009, Bedework</copyright>
<managingEditor>info.events@ubc.ca</managingEditor>
<item>
<title>INVOKING VENUS: Feathers and Fashion </title>
<link>http://www.calendar.events.ubc.ca:80/cal/event/eventView.do?subid=94535&amp;calPath=%2Fpublic%2FEvents+Calendar%2FBeaty+Biodiversity+Museum&amp;guid=CAL-09d22401-3c684880-013c-69068a29-0000006bmyubc-team@interchange.ubc.ca&amp;recurrenceId=</link>
<pubDate>Thu, 07 Feb 2013 10:00 PST</pubDate>
<description>Thu, February 7, 2013 10:00 AM - Sun, May 5, 2013, 5:00 PM UBC Point Grey Campus. Included with admission or membership. An exhibition of photo-based images by Catherine Stewart and accessories from the clothing collections of Claus Jahnke and Ivan Sayers. Using bird specimens from the Beaty Biodiversity Museum, Vancouver-based Stewart explores the role colour, patterning and adornment play in courtship and attraction. Through the juxtaposition of images of bird plumage with images of vintage fabrics and actual feathered fashion accessories, the parallels in human and bird behaviour become apparent. The lush and sensuous images magnify details in avian plumage and vintage fabrics, revealing a multitude of rich and varied hues that combine to create the colours, textures and patterns observed when viewing birds and humans at their finest. </description>
<category domain="http://events.ubc.ca/">Subject - Entertainment - Culture</category>
    <category domain="http://events.ubc.ca/">Subject - Learning And Research - Arts, Humanities And Social Sciences</category>
<category domain="http://events.ubc.ca/">Subject - Lifestyle And Sport - Lifestyle</category>
<category domain="http://events.ubc.ca/">Type - Exhibit</category>
</item>
<item>
<title>Click </title>
    <link>http://www.calendar.events.ubc.ca:80/cal/event/eventView.do?subid=94782&amp;calPath=%2Fpublic%2FEvents+Calendar%2FIrving+K.+Barber+Learning+Centre+%28IKBLC%29&amp;guid=CAL-09d22401-3ca62e44-013c-a651b7d6-00000017myubc-team@interchange.ubc.ca&amp;recurrenceId=</link>
    <pubDate>Sat, 02 Mar 2013 00:00 PST</pubDate>
    <description>Sat, March 2, 2013 12:00 AM - Mon, April 1, 2013, 12:00 AM IRVING K. BARBER LEARNING CENTRE. Free. Each year, the UBC Photo Society, one of the largest student AMS clubs at UBC organizes an art exhibition featuring photo pieces of from its membership. “Click” is an exhibition hosted by the Irving K. Barber Learning Centre which features such photography. The purpose and mission of the UBC Photo Society is to develop the photographer while offering the training and facilities of UBC. The society strives to give photography enthusiasts a place to meet, talk, and share ideas about photography while offering the numerous facilities that assist photographers in taking their photography to the next level. </description>
<category domain="http://events.ubc.ca/">Subject - Entertainment - Arts</category>
<category domain="http://events.ubc.ca/">Subject - Entertainment - Social</category>
<category domain="http://events.ubc.ca/">Type - Exhibit</category>
</item>
<item>
<title>Robson Reading Series Display </title>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       <link>http://www.calendar.events.ubc.ca:80/cal/event/eventView.do?subid=94782&amp;calPath=%2Fpublic%2FEvents+Calendar%2FIrving+K.+Barber+Learning+Centre+%28IKBLC%29&amp;guid=CAL-09d22401-3d5a2b90-013d-5aa7fcfd-0000001cmyubc-team@interchange.ubc.ca&amp;recurrenceId=</link>
    <pubDate>Wed, 06 Mar 2013 00:00 PST</pubDate>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       <description>Wed, March 6, 2013 12:00 AM - Sat, March 30, 2013, 12:00 AM IRVING K. BARBER LEARNING CENTRE. Free. For over a decade the Robson Reading Series has enlivened the literary community of Vancouver. The UBC Bookstore and the Irving K. Barber Learning Centre have had the pleasure of hosting dynamic selections of authors whose poetry, fiction and creative non-fiction expand our knowledge of literature and the world through distinct approaches to style, subject and form. The display cases in the IKBLC foyer highlight just a few of the books and authors the series has had the opportunity to host during the series run. </description>
<category domain="http://events.ubc.ca/">Subject - Entertainment - Arts</category>
<category domain="http://events.ubc.ca/">Subject - Entertainment - Social</category>
<category domain="http://events.ubc.ca/">Type - Exhibit</category>
</item>
<item>
<title>New Treasures: Artifacts of Chinese-Canadian life and the Canadian Pacific Railway Company exhibition </title>
<link>http://www.calendar.events.ubc.ca:80/cal/event/eventView.do?subid=94999&amp;calPath=%2Fpublic%2FEvents+Calendar%2FUBC+Library&amp;guid=CAL-09d22401-3d92cac5-013d-942c575f-00000049myubc-team@interchange.ubc.ca&amp;recurrenceId=</link>
<pubDate>Sat, 23 Mar 2013 09:00 PDT</pubDate>
<description>Sat, March 23, 2013 9:00 AM - Sun, June 30, 2013, 2:00 PM IRVING K. BARBER LEARNING CENTRE. Free. The &lt;em&gt;New Treasures: Artifacts of Chinese-Canadian life and the Canadian Pacific Railway Company&lt;/em&gt; exhibition is a new exhibit on display in the Chung Collection Exhibition room.

&lt;em&gt;New Treasures&lt;/em&gt; is a new exhibition that features artifacts from Chinese-Canadian life such as kitchen utensils, apothecary items and more. It explores the immigration and settlement of Chinese-Canadian people in B.C.

The Chung Collection Exhibition room also has some new additions, including a model locomotive, built by a CPR engineer as a retirement project.

If you have never been to the collection, or even if you are a frequent visitor, it is time to explore the &lt;em&gt;New Treasures&lt;/em&gt; of the Chung Collection!

Location: Level One of the Irving K. Barber Learning Centre, inside Rare Books and Special Collections.

Open to the public Monday to Friday, 10 a.m. to 4 p.m. Visit the Library website for the most up-to-date &lt;a href="http://hours.library.ubc.ca/#view-rbsc"&gt;hours listing&lt;/a&gt;.

On until June 30, 2013.

&lt;strong&gt;About the Chung Collection&lt;/strong&gt;
The Wallace B. Chung and Madeline H. Chung Collection – a designated national treasure - was donated to the Library in 1999. Dr. Wallace Chung collected 25,000 items related to early B.C. history, immigration and settlement, and the Canadian Pacific Railway Company. The fascinating collection now resides in Rare Books and Special Collections in the Chung Collection Exhibition room.

Many of the materials have been digitized and are available online.

For more information visit &lt;a href="http://chung.library.ubc.ca"&gt;chung.library.ubc.ca&lt;/a&gt;. </description>
</item></channel></rss>';


}