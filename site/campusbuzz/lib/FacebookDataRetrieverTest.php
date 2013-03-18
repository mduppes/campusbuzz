<?php

class FacebookDataRetrieverTest {


  public function testGetFeedFromPage() {

    return true;
  }



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

}