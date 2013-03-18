<?php

class TwitterDataRetrieverTest {

  // simple sanity test for feed retrieval
  public function testGetTweetsByUser() {
    $user = "ubcnews";
    $tweets = Tester::getTester()->twitterController->getTweetsByUser($user);

    return (count($tweets)) > 0 ? true : false;
  }

  public function testGetTweetsAtGeoLocation() {
    $centerOfUBC = new GeoCoordinate(49.26, -123.24);
    $tweets = Tester::getTester()->twitterController->getTweetsAtGeoLocation($centerOfUBC, 3);

    return (count($tweets)) > 0 ? true : false;
  }

  public function testParseResultsIntoFeedItems() {
    $config = new DataSourceConfig($this->sampleTwitterConfig);
    $sampleFeed = json_decode($this->sampleTwitterFeedJson, true);
    $feedItems = Tester::getTester()->twitterController->parseResultsIntoFeedItems($sampleFeed, $config);


    if (count($feedItems) != 5) {
      print "Number feeditems returned is wrong\n";
      return false;
    }

    if ($feedItems[0]->getLabel("name") != "matthewgeorge13") {
      print "name wrong\n";
      return false;
    }

    $date = new DateTime("Fri, 15 Mar 2013 20:21:44 +0000");
    if ($feedItems[0]->getLabel("pubDate") != $date->format("Y-m-d\TH:i:s\Z")) {
      print "pubDate wrong\n";
      return false;
    }

    if ($feedItems[0]->getLabel("content") != "Johnny big rig hedricks") {
      print "content wrong\n";
      return false;
    }

    if ($feedItems[0]->getLabel("title") != "Johnny big rig hedricks") {
      print "title wrong\n";
      return false;
    }

    if ($feedItems[0]->getLabel("url") != "https://www.twitter.com/matthewgeorge13") {
      print "url wrong\n";
      return false;
    }

    if ($feedItems[0]->getLabel("imageUrl") != "http://a0.twimg.com/profile_images/3198715402/5f5d9a99530151cb91f925c5bddfe396_normal.jpeg") {
      print "imageUrl wrong\n";
      return false;
    }

    if ($feedItems[0]->getLabel("locationName") != "British Columbia CANADA EARTH!") {
      print "locationName wrong\n";
      return false;
    }

    // check geo working
    if ($feedItems[4]->getGeoCoordinate() != new GeoCoordinate(49.266116, -123.251558)) {
      print "geo wrong\n";
      return false;
    }

    // #2 test normal timeline, not geosearch
    $config = new DataSourceConfig(json_decode($this->sampleTwitterConfig2, true));
    $sampleFeed = json_decode($this->sampleTwitterFeedJson2, true);
    $feedItems = Tester::getTester()->twitterController->parseResultsIntoFeedItems($sampleFeed, $config);

    if (count($feedItems) != 4) {
      print "Number feeditems for test2 returned is wrong\n";
      return false;
    }

    if ($feedItems[0]->getLabel("name") != "ubcnews") {
      print "Name wrong for test2\n";
      return false;
    }

    // default location since no location exists
    if ($feedItems[0]->getLabel("locationName") != "UBC") {
      print "location 2 wrong\n";
      return false;
    }

    if ($feedItems[0]->getLabel("url") != "https://www.twitter.com/ubcnews") {
      print "url wrong\n";
      return false;
    }

    if ($feedItems[0]->getLabel("imageUrl") != "http://a0.twimg.com/profile_images/2709185304/c4460b6a6a2f0bc7836a043c4c6a569d_normal.jpeg") {
      print "imageUrl wrong\n";
      return false;
    }

    $categories = $feedItems[0]->getLabel("category");
    if ($categories[0] != "Learning") {
      print_r($categories);
      print "category wrong\n";
      return false;
    }

    return true;
  }

  private $sampleTwitterConfig =
    array("name" => "TwitterGeoSearch",

          "sourceUrl" => "www.twitter.com",
          "sourceImageUrl" => "https://abs.twimg.com/a/1363285036/images/resources/twitter-bird-callout.png",
          "sourceType" => "TwitterGeoSearch",
          "sourceLocation" =>"UBC",
          "officialSource" => false,
          "sourceCategory" =>"Life");

  private $sampleTwitterConfig2 =
    '{
                "name":"UbysseyCulture",
                "sourceUrl":"UbysseyCulture",
                "sourceImageUrl":"http://www.gothunderbirds.ca/images/2009/10/19/leftnav_ubclogo.gif",
                "sourceType":"Twitter",
                "officialSource":true,
                "sourceLocation":"UBC",
                "sourceCategory":"Learning"}';

  private $sampleTwitterFeedJson = '
    [
        {
            "created_at": "Fri, 15 Mar 2013 20:21:44 +0000",
            "from_user": "matthewgeorge13",
            "from_user_id": 279742857,
            "from_user_id_str": "279742857",
            "from_user_name": "Central250",
            "geo": null,
            "location": "British Columbia CANADA EARTH!",
            "id": 312659904500482049,
            "id_str": "312659904500482049",
            "iso_language_code": "en",
            "metadata": {
                "result_type": "recent"
            },
            "profile_image_url": "http:\/\/a0.twimg.com\/profile_images\/3198715402\/5f5d9a99530151cb91f925c5bddfe396_normal.jpeg",
            "profile_image_url_https": "https:\/\/si0.twimg.com\/profile_images\/3198715402\/5f5d9a99530151cb91f925c5bddfe396_normal.jpeg",
            "source": "&lt;a href=&quot;http:\/\/blackberry.com\/twitter&quot;&gt;Twitter for BlackBerry\u00ae&lt;\/a&gt;",
            "text": "Johnny big rig hedricks"
        },
        {
            "created_at": "Fri, 15 Mar 2013 20:19:48 +0000",
            "from_user": "sarahcasm",
            "from_user_id": 36848279,
            "from_user_id_str": "36848279",
            "from_user_name": "Sarah",
            "geo": null,
            "location": "Greater Toronto, Canada",
            "id": 312659415448834048,
            "id_str": "312659415448834048",
            "iso_language_code": "en",
            "metadata": {
                "result_type": "recent"
            },
            "profile_image_url": "http:\/\/a0.twimg.com\/profile_images\/2976020998\/22186e4b30a515c4f70823c1831b1b8b_normal.jpeg",
            "profile_image_url_https": "https:\/\/si0.twimg.com\/profile_images\/2976020998\/22186e4b30a515c4f70823c1831b1b8b_normal.jpeg",
            "source": "&lt;a href=&quot;https:\/\/mobile.twitter.com&quot;&gt;Mobile Web (M2)&lt;\/a&gt;",
            "text": "@gwenleron too cute. Funny how different they are at home.",
            "to_user": "GwenLeron",
            "to_user_id": 90312785,
            "to_user_id_str": "90312785",
            "to_user_name": "Gwen L.",
            "in_reply_to_status_id": 312655406256250883,
            "in_reply_to_status_id_str": "312655406256250883"
        },
        {
            "created_at": "Fri, 15 Mar 2013 20:18:54 +0000",
            "from_user": "matthewgeorge13",
            "from_user_id": 279742857,
            "from_user_id_str": "279742857",
            "from_user_name": "Central250",
            "geo": null,
            "location": "British Columbia CANADA EARTH!",
            "id": 312659191997296640,
            "id_str": "312659191997296640",
            "iso_language_code": "und",
            "metadata": {
                "result_type": "recent"
            },
            "profile_image_url": "http:\/\/a0.twimg.com\/profile_images\/3198715402\/5f5d9a99530151cb91f925c5bddfe396_normal.jpeg",
            "profile_image_url_https": "https:\/\/si0.twimg.com\/profile_images\/3198715402\/5f5d9a99530151cb91f925c5bddfe396_normal.jpeg",
            "source": "&lt;a href=&quot;http:\/\/blackberry.com\/twitter&quot;&gt;Twitter for BlackBerry\u00ae&lt;\/a&gt;",
            "text": "@mmacanada",
            "to_user": "mmacanada",
            "to_user_id": 20629173,
            "to_user_id_str": "20629173",
            "to_user_name": "MMACanada.net",
            "in_reply_to_status_id": 312655891931475969,
            "in_reply_to_status_id_str": "312655891931475969"
        },
        {
            "created_at": "Fri, 15 Mar 2013 20:18:45 +0000",
            "from_user": "sarahcasm",
            "from_user_id": 36848279,
            "from_user_id_str": "36848279",
            "from_user_name": "Sarah",
            "geo": null,
            "location": "Greater Toronto, Canada",
            "id": 312659150805020673,
            "id_str": "312659150805020673",
            "iso_language_code": "en",
            "metadata": {
                "result_type": "recent"
            },
            "profile_image_url": "http:\/\/a0.twimg.com\/profile_images\/2976020998\/22186e4b30a515c4f70823c1831b1b8b_normal.jpeg",
            "profile_image_url_https": "https:\/\/si0.twimg.com\/profile_images\/2976020998\/22186e4b30a515c4f70823c1831b1b8b_normal.jpeg",
            "source": "&lt;a href=&quot;https:\/\/mobile.twitter.com&quot;&gt;Mobile Web (M2)&lt;\/a&gt;",
            "text": "@darelleats it.is.non.stop.all.day.long! I cant think straight.",
            "to_user": "darelleats",
            "to_user_id": 15599865,
            "to_user_id_str": "15599865",
            "to_user_name": "Darell",
            "in_reply_to_status_id": 312657536958484481,
            "in_reply_to_status_id_str": "312657536958484481"
        },
        {
            "created_at": "Fri, 15 Mar 2013 20:18:12 +0000",
            "from_user": "HughHeth",
            "from_user_id": 88775109,
            "from_user_id_str": "88775109",
            "from_user_name": "Heather Hughes-Adams",
            "geo": {
                "coordinates": [
                    49.266116,
                    -123.251558
                ],
                "type": "Point"
            },
            "id": 312659014091673601,
            "id_str": "312659014091673601",
            "iso_language_code": "en",
            "metadata": {
                "result_type": "recent"
            },
            "place": {
                "full_name": "Greater Vancouver",
                "id": "216dd1bcf824f9f7",
                "type": "CITY"
            },
            "profile_image_url": "http:\/\/a0.twimg.com\/profile_images\/3180960442\/bf77812b6cdfd27653cde46833b5f5f8_normal.jpeg",
            "profile_image_url_https": "https:\/\/si0.twimg.com\/profile_images\/3180960442\/bf77812b6cdfd27653cde46833b5f5f8_normal.jpeg",
            "source": "&lt;a href=&quot;http:\/\/twitter.com\/download\/android&quot;&gt;Twitter for Android&lt;\/a&gt;",
            "text": "Really wanting a glass of wine halfway though the day #collegesymptoms #endoftheweek"
        }]';

  private $sampleTwitterFeedJson2 = '
[
    {
        "created_at": "Fri Mar 15 18:23:25 +0000 2013",
        "id": 312630125961302016,
        "id_str": "312630125961302016",
        "text": "Coding for a cause: #UBC computer science students stage first community hackathon this weekend http:\/\/t.co\/5OCohTFiDi",
        "source": "\u003ca href=\"http:\/\/www.hootsuite.com\" rel=\"nofollow\"\u003eHootSuite\u003c\/a\u003e",
        "truncated": false,
        "in_reply_to_status_id": null,
        "in_reply_to_status_id_str": null,
        "in_reply_to_user_id": null,
        "in_reply_to_user_id_str": null,
        "in_reply_to_screen_name": null,
        "user": {
            "id": 64540763,
            "id_str": "64540763",
            "name": "UBC Public Affairs",
            "screen_name": "ubcnews",
            "location": "Vancouver, BC",
            "url": "http:\/\/www.ubc.ca\/",
            "description": "The Twitter account for University of British Columbia news. Follow us for research news, expert advisories, events and story ideas from UBC Public Affairs. \r\n",
            "protected": false,
            "followers_count": 9716,
            "friends_count": 1992,
            "listed_count": 503,
            "created_at": "Mon Aug 10 22:49:56 +0000 2009",
            "favourites_count": 6,
            "utc_offset": -28800,
            "time_zone": "Pacific Time (US & Canada)",
            "geo_enabled": false,
            "verified": false,
            "statuses_count": 4958,
            "lang": "en",
            "contributors_enabled": false,
            "is_translator": false,
            "profile_background_color": "002859",
            "profile_background_image_url": "http:\/\/a0.twimg.com\/profile_background_images\/123841893\/twitter-background.jpg",
            "profile_background_image_url_https": "https:\/\/si0.twimg.com\/profile_background_images\/123841893\/twitter-background.jpg",
            "profile_background_tile": false,
            "profile_image_url": "http:\/\/a0.twimg.com\/profile_images\/2709185304\/c4460b6a6a2f0bc7836a043c4c6a569d_normal.jpeg",
            "profile_image_url_https": "https:\/\/si0.twimg.com\/profile_images\/2709185304\/c4460b6a6a2f0bc7836a043c4c6a569d_normal.jpeg",
            "profile_link_color": "1F98C7",
            "profile_sidebar_border_color": "C6E2EE",
            "profile_sidebar_fill_color": "DAECF4",
            "profile_text_color": "663B12",
            "profile_use_background_image": true,
            "default_profile": false,
            "default_profile_image": false,
            "following": null,
            "follow_request_sent": null,
            "notifications": null
        },
        "geo": null,
        "coordinates": null,
        "place": null,
        "contributors": null,
        "retweet_count": 0,
        "favorited": false,
        "retweeted": false,
        "possibly_sensitive": false,
        "lang": "en"
    },
    {
        "created_at": "Fri Mar 15 17:29:26 +0000 2013",
        "id": 312616541587906562,
        "id_str": "312616541587906562",
        "text": "#UBC clubfoot treatment in Bangladesh gains $4.3M federal boost http:\/\/t.co\/2fm6b9gNon",
        "source": "\u003ca href=\"http:\/\/www.hootsuite.com\" rel=\"nofollow\"\u003eHootSuite\u003c\/a\u003e",
        "truncated": false,
        "in_reply_to_status_id": null,
        "in_reply_to_status_id_str": null,
        "in_reply_to_user_id": null,
        "in_reply_to_user_id_str": null,
        "in_reply_to_screen_name": null,
        "user": {
            "id": 64540763,
            "id_str": "64540763",
            "name": "UBC Public Affairs",
            "screen_name": "ubcnews",
            "location": "Vancouver, BC",
            "url": "http:\/\/www.ubc.ca\/",
            "description": "The Twitter account for University of British Columbia news. Follow us for research news, expert advisories, events and story ideas from UBC Public Affairs. \r\n",
            "protected": false,
            "followers_count": 9716,
            "friends_count": 1992,
            "listed_count": 503,
            "created_at": "Mon Aug 10 22:49:56 +0000 2009",
            "favourites_count": 6,
            "utc_offset": -28800,
            "time_zone": "Pacific Time (US & Canada)",
            "geo_enabled": false,
            "verified": false,
            "statuses_count": 4958,
            "lang": "en",
            "contributors_enabled": false,
            "is_translator": false,
            "profile_background_color": "002859",
            "profile_background_image_url": "http:\/\/a0.twimg.com\/profile_background_images\/123841893\/twitter-background.jpg",
            "profile_background_image_url_https": "https:\/\/si0.twimg.com\/profile_background_images\/123841893\/twitter-background.jpg",
            "profile_background_tile": false,
            "profile_image_url": "http:\/\/a0.twimg.com\/profile_images\/2709185304\/c4460b6a6a2f0bc7836a043c4c6a569d_normal.jpeg",
            "profile_image_url_https": "https:\/\/si0.twimg.com\/profile_images\/2709185304\/c4460b6a6a2f0bc7836a043c4c6a569d_normal.jpeg",
            "profile_link_color": "1F98C7",
            "profile_sidebar_border_color": "C6E2EE",
            "profile_sidebar_fill_color": "DAECF4",
            "profile_text_color": "663B12",
            "profile_use_background_image": true,
            "default_profile": false,
            "default_profile_image": false,
            "following": null,
            "follow_request_sent": null,
            "notifications": null
        },
        "geo": null,
        "coordinates": null,
        "place": null,
        "contributors": null,
        "retweet_count": 1,
        "favorited": false,
        "retweeted": false,
        "possibly_sensitive": false,
        "lang": "en"
    },
    {
        "created_at": "Fri Mar 15 15:58:55 +0000 2013",
        "id": 312593765057572865,
        "id_str": "312593765057572865",
        "text": "#UBC students: Register to vote in BCs May election TODAY in the SUB. #bcpoli #bced @ElectionsBC",
        "source": "\u003ca href=\"http:\/\/www.hootsuite.com\" rel=\"nofollow\"\u003eHootSuite\u003c\/a\u003e",
        "truncated": false,
        "in_reply_to_status_id": null,
        "in_reply_to_status_id_str": null,
        "in_reply_to_user_id": null,
        "in_reply_to_user_id_str": null,
        "in_reply_to_screen_name": null,
        "user": {
            "id": 64540763,
            "id_str": "64540763",
            "name": "UBC Public Affairs",
            "screen_name": "ubcnews",
            "location": "Vancouver, BC",
            "url": "http:\/\/www.ubc.ca\/",
            "description": "The Twitter account for University of British Columbia news. Follow us for research news, expert advisories, events and story ideas from UBC Public Affairs. \r\n",
            "protected": false,
            "followers_count": 9716,
            "friends_count": 1992,
            "listed_count": 503,
            "created_at": "Mon Aug 10 22:49:56 +0000 2009",
            "favourites_count": 6,
            "utc_offset": -28800,
            "time_zone": "Pacific Time (US & Canada)",
            "geo_enabled": false,
            "verified": false,
            "statuses_count": 4958,
            "lang": "en",
            "contributors_enabled": false,
            "is_translator": false,
            "profile_background_color": "002859",
            "profile_background_image_url": "http:\/\/a0.twimg.com\/profile_background_images\/123841893\/twitter-background.jpg",
            "profile_background_image_url_https": "https:\/\/si0.twimg.com\/profile_background_images\/123841893\/twitter-background.jpg",
            "profile_background_tile": false,
            "profile_image_url": "http:\/\/a0.twimg.com\/profile_images\/2709185304\/c4460b6a6a2f0bc7836a043c4c6a569d_normal.jpeg",
            "profile_image_url_https": "https:\/\/si0.twimg.com\/profile_images\/2709185304\/c4460b6a6a2f0bc7836a043c4c6a569d_normal.jpeg",
            "profile_link_color": "1F98C7",
            "profile_sidebar_border_color": "C6E2EE",
            "profile_sidebar_fill_color": "DAECF4",
            "profile_text_color": "663B12",
            "profile_use_background_image": true,
            "default_profile": false,
            "default_profile_image": false,
            "following": null,
            "follow_request_sent": null,
            "notifications": null
        },
        "geo": null,
        "coordinates": null,
        "place": null,
        "contributors": null,
        "retweet_count": 1,
        "favorited": false,
        "retweeted": false,
        "lang": "en"
    },
    {
        "created_at": "Fri Mar 15 15:25:17 +0000 2013",
        "id": 312585298422935552,
        "id_str": "312585298422935552",
        "text": "In the news: Female #MBA students reflect on #Facebook COO @sherylsandbergs new book http:\/\/t.co\/rmGXjZfPYh @UBCSauderSchool",
        "source": "\u003ca href=\"http:\/\/www.hootsuite.com\" rel=\"nofollow\"\u003eHootSuite\u003c\/a\u003e",
        "truncated": false,
        "in_reply_to_status_id": null,
        "in_reply_to_status_id_str": null,
        "in_reply_to_user_id": null,
        "in_reply_to_user_id_str": null,
        "in_reply_to_screen_name": null,
        "user": {
            "id": 64540763,
            "id_str": "64540763",
            "name": "UBC Public Affairs",
            "screen_name": "ubcnews",
            "location": "Vancouver, BC",
            "url": "http:\/\/www.ubc.ca\/",
            "description": "The Twitter account for University of British Columbia news. Follow us for research news, expert advisories, events and story ideas from UBC Public Affairs. \r\n",
            "protected": false,
            "followers_count": 9716,
            "friends_count": 1992,
            "listed_count": 503,
            "created_at": "Mon Aug 10 22:49:56 +0000 2009",
            "favourites_count": 6,
            "utc_offset": -28800,
            "time_zone": "Pacific Time (US & Canada)",
            "geo_enabled": false,
            "verified": false,
            "statuses_count": 4958,
            "lang": "en",
            "contributors_enabled": false,
            "is_translator": false,
            "profile_background_color": "002859",
            "profile_background_image_url": "http:\/\/a0.twimg.com\/profile_background_images\/123841893\/twitter-background.jpg",
            "profile_background_image_url_https": "https:\/\/si0.twimg.com\/profile_background_images\/123841893\/twitter-background.jpg",
            "profile_background_tile": false,
            "profile_image_url": "http:\/\/a0.twimg.com\/profile_images\/2709185304\/c4460b6a6a2f0bc7836a043c4c6a569d_normal.jpeg",
            "profile_image_url_https": "https:\/\/si0.twimg.com\/profile_images\/2709185304\/c4460b6a6a2f0bc7836a043c4c6a569d_normal.jpeg",
            "profile_link_color": "1F98C7",
            "profile_sidebar_border_color": "C6E2EE",
            "profile_sidebar_fill_color": "DAECF4",
            "profile_text_color": "663B12",
            "profile_use_background_image": true,
            "default_profile": false,
            "default_profile_image": false,
            "following": null,
            "follow_request_sent": null,
            "notifications": null
        },
        "geo": null,
        "coordinates": null,
        "place": null,
        "contributors": null,
        "retweet_count": 0,
        "favorited": false,
        "retweeted": false,
        "possibly_sensitive": false,
        "lang": "en"
    }]';

}