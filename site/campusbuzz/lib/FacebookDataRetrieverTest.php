<?php

/**
 * Tests for their respective functions in FacebookDataRetriever.php
 */
class FacebookDataRetrieverTest {

  /**
   * Accesses facebook
   */
  public function testGetFeedFromPage() {
    $data = Tester::getTester()->facebookController->getFeedFromPage("mduppes");
    return (count($data) > 0) ? true : false;
  }

  /**
   * Accesses facebook
   */
  public function testGetFbId() {
    $data = Tester::getTester()->facebookController->getFbId("mduppes");
    return ($data === "1446951058") ? true : false;
  }

  /**
   * Uses sample config and feed returned from facebook to test creation of FeedItems
   */
  public function testParseResultsIntoFeedItems() {
    $configJson = json_decode($this->sampleConfig, true);
    $config = new DataSourceConfig($configJson);

    $sampleFeed = json_decode($this->sampleFeed, true);
    $fbid = "16761458703";
    $feedItems = Tester::getTester()->facebookController->parseResultsIntoFeedItems($sampleFeed, $config, $fbid);


    if (count($feedItems) !== 2) {
      print "invalid count\n";
      return false;
    }

    if ($feedItems[0]->getLabel("content") != "Time to hit the ball! \n\nLink to sign-up:\nhttp://tinyurl.com/ax95kjc\n\nLink to view who has signed up:\nhttp://tinyurl.com/b842k7j\n\nDon't forget the UBC Tennis Team plays SFU on sat in a do-or-die match to qualify for nationals!\n") {
      print "invalid content\n";
      return false;
    }

    if ($feedItems[0]->getLabel("url") != "https://www.facebook.com/events/391617947611752/") {
      print "invalid url\n";
      return false;
    }

    if ($feedItems[0]->getLabel("officialSource") !== false) {
      print "invalid official source\n";
      return false;
    }

    if ($feedItems[0]->getLabel("startDate") !== "2013-03-16T02:00:00Z") {
      print "invalid startDate\n";
      return false;
    }

    if ($feedItems[0]->getLabel("endDate") !== "2013-03-16T04:00:00Z") {
      print "invalid endDate\n";
      return false;
    }

    // same as startdate for event
    if ($feedItems[0]->getLabel("pubDate") !== "2013-03-16T02:00:00Z") {
      print "invalid pubDate\n";
      return false;
    }

    if ($feedItems[1]->getLabel("content") !== "Mission Accomplished!!! UBC answered the biggest call by sweeping Calgary 11-0. Next Sat, UBC MUST beat SFU with a decisive score to qualify for Nationals.") {
      print "invalid content for 2nd item\n";
      return false;
    }

    $sampleFeed2 = json_decode($this->sampleFeed2, true);
    $feedItems = Tester::getTester()->facebookController->parseResultsIntoFeedItems($sampleFeed2, $config, $fbid);

    if (count($feedItems) !== 2) {
      print "invalid number of feeds\n";
      return false;
    }

    if ($feedItems[0]->getLabel("content") !== "Storm the Wall 2013 starts today at UBC! It will be streaming live on The Point starting at 12:15 pm www.thepoint.ubc.ca/storm2013") {
      print "invalid content for samplefeed2\n";
      return false;
    }

    if ($feedItems[0]->getLabel("url") !== "http://www.thepoint.ubc.ca/storm2013") {
      print "invalid url for samplefeed2\n";
      return false;
    }

    if ($feedItems[0]->getLabel("officialSource") !== true) {
      print "invalid official source for samplefeed2\n";
      return false;
    }

    return true;
  }

  private $sampleConfig = '
{
                "name":"UBC",
                "sourceUrl":"universityofbc",
                "sourceImageUrl":"https://fbcdn-sphotos-b-a.akamaihd.net/hphotos-ak-snc7/598662_10151167459643704_1215460439_n.jpg",
                "sourceType":"Facebook",
                "officialSource":true,
                "sourceLocation":"UBC",
                "sourceCategory":"Learning"
                }';

  private $sampleFeed = '
[
    {
      "id": "113161515407260_391617947611752",
      "from": {
        "category": "University",
        "category_list": [
          {
            "id": "108051929285833",
            "name": "College & University"
          }
        ],
        "name": "UBC Tennis Club",
        "id": "113161515407260"
      },
      "story": "UBC Tennis Club created an event.",
      "story_tags": {
        "0": [
          {
            "id": "113161515407260",
            "name": "UBC Tennis Club",
            "offset": 0,
            "length": 15,
            "type": "page"
          }
        ]
      },
      "link": "https://www.facebook.com/events/391617947611752/",
    "actions": [
                {
                  "name": "Comment",
                  "link": "https://www.facebook.com/113161515407260/posts/391617947611752"
                },
                {
                  "name": "Like",
                  "link": "https://www.facebook.com/113161515407260/posts/391617947611752"
                }
                ],
    "privacy": {
    "value": ""
  },
    "type": "link",
      "created_time": "2013-03-15T01:31:54+0000",
      "updated_time": "2013-03-15T01:31:54+0000",
      "comments": {
      "count": 0
        }
},
{
  "id": "113161515407260_497837633606311",
    "from": {
    "category": "School sports team",
      "category_list": [
                        {
                          "id": "1804",
                            "name": "School Sports Team"
                            }
                        ],
      "name": "UBC Tennis Team",
      "id": "117348195040755"
      },
    "to": {
      "data": [
               {
                 "category": "University",
                   "category_list": [
                                     {
                                       "id": "108051929285833",
                                         "name": "College & University"
                                         }
                                     ],
                   "name": "UBC Tennis Club",
                   "id": "113161515407260"
                   }
        ]
        },
      "message": "Mission Accomplished!!! UBC answered the biggest call by sweeping Calgary 11-0. Next Sat, UBC MUST beat SFU with a decisive score to qualify for Nationals.",
        "actions": [
                    {
                      "name": "Comment",
                        "link": "https://www.facebook.com/113161515407260/posts/497837633606311"
                        },
                    {
                      "name": "Like",
                        "link": "https://www.facebook.com/113161515407260/posts/497837633606311"
                        }
                    ],
        "privacy": {
        "value": ""
          },
        "type": "status",
          "created_time": "2013-03-10T21:21:32+0000",
          "updated_time": "2013-03-10T21:21:32+0000",
          "likes": {
          "data": [
                   {
                     "name": "Sina Adabi",
                       "id": "1167748262"
                       },
                   {
                     "name": "Matt Armstrong",
                       "id": "1012124984"
                       },
                   {
                     "name": "Tin J",
                       "id": "514978061"
                       },
                   {
                     "name": "Braedon Beaulieu",
                       "id": "1406660099"
                       }
                   ],
            "count": 9
            },
          "comments": {
            "count": 0
              }
}
]
';

  private $sampleFeed2 = '
[{
      "id": "16761458703_599948990032777",
      "from": {
        "category": "University",
        "category_list": [
          {
            "id": "108051929285833",
            "name": "College & University"
          }
        ],
        "name": "The University of British Columbia (UBC)",
        "id": "16761458703"
      },
      "message": "Storm the Wall 2013 starts today at UBC! It will be streaming live on The Point starting at 12:15 pm www.thepoint.ubc.ca/storm2013",
      "picture": "https://fbexternal-a.akamaihd.net/safe_image.php?d=AQAkd2uSqfNpVLtY&w=154&h=154&url=http%3A%2F%2Fwww.thepoint.ubc.ca%2Ffiles%2F2013%2F03%2Funtitled-shoot-1331-150x150.jpg",
      "link": "http://www.thepoint.ubc.ca/storm2013",
      "name": "Storm the Wall 2013 | The Point",
      "caption": "www.thepoint.ubc.ca",
      "icon": "https://fbstatic-a.akamaihd.net/rsrc.php/v2/yD/r/aS8ecmYRys0.gif",
      "actions": [
        {
          "name": "Comment",
          "link": "https://www.facebook.com/16761458703/posts/599948990032777"
        },
        {
          "name": "Like",
          "link": "https://www.facebook.com/16761458703/posts/599948990032777"
        }
      ],
      "privacy": {
        "value": ""
      },
      "type": "link",
      "status_type": "shared_story",
      "created_time": "2013-03-25T17:51:54+0000",
      "updated_time": "2013-03-25T17:51:54+0000",
      "likes": {
        "data": [
          {
            "name": "Michael Ngai",
            "id": "1345598684"
          },
          {
            "name": "KK Wong",
            "id": "618172537"
          },
          {
            "name": "Kiyoko Takahashi",
            "id": "100005295882286"
          },
          {
            "category": "Club",
            "category_list": [
              {
                "id": "108051929285833",
                "name": "College & University"
              },
              {
                "id": "215343825145859",
                "name": "Social Club"
              }
            ],
            "name": "Vancouvers Got Talent - AMS",
            "id": "438111642933855"
          }
        ],
        "count": 25
      },
      "comments": {
        "count": 0
      }
    },
    {
      "id": "16761458703_425612984195604",
      "from": {
        "name": "Sara Eftekhar",
        "id": "512734431"
      },
      "to": {
        "data": [
          {
            "category": "University",
            "category_list": [
              {
                "id": "108051929285833",
                "name": "College & University"
              }
            ],
            "name": "The University of British Columbia (UBC)",
            "id": "16761458703"
          }
        ]
      },
      "message": "Hi UBC!\nI just wanted to let you know that I have been nominated for the top 25 immigrants of Canada Award and out of over 600 applicants, I have been listed as the top 75 and now in order to win the top 25, I need votes from anyone with an email address and I was wondering if you can share it on thefacebook and twitter page and support a UBC student........ I am the only UBC student on the list \nThank you so much for your time! I appreciate all of your support!\nThis is the website: http://canadianimmigrant.ca/canadas-top-25-immigrants/vote",
    "picture": "https://fbexternal-a.akamaihd.net/safe_image.php?d=AQCnxKbrXVECKGmO&w=154&h=154&url=http%3A%2F%2Fcanadianimmigrant.ca%2Fwordpress%2Fwp-content%2Fuploads%2Ftop25%2Fthumb%2F33.jpg",
      "link": "http://canadianimmigrant.ca/canadas-top-25-immigrants/vote",
      "name": "Vote | Canadian Immigrant",
      "caption": "canadianimmigrant.ca",
      "description": "Remember, you can vote for up to 3 finalists and voting ends on May 13, 2013. So tell your friends, family and colleagues to vote for your favourites for the 2013 RBC Top 25 Canadian Immigrant Awards",
      "icon": "https://fbstatic-a.akamaihd.net/rsrc.php/v2/yD/r/aS8ecmYRys0.gif",
      "actions": [
                  {
                    "name": "Comment",
                    "link": "https://www.facebook.com/16761458703/posts/425612984195604"
                  },
                  {
                    "name": "Like",
                    "link": "https://www.facebook.com/16761458703/posts/425612984195604"
                  }
                  ],
      "privacy": {
      "value": ""
    },
    "type": "link",
      "created_time": "2013-03-25T09:57:50+0000",
      "updated_time": "2013-03-25T09:57:50+0000",
      "comments": {
      "count": 0
        }
}]';

}