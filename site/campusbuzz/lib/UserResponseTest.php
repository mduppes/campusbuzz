<?php

/**
 * Sanity tests for response sent back to users
 */
class UserResponseTest {

  /**
   * Checks to see map pins can be queried for sample data
   */
  public function testMapPinResponse() {
    $feedItemSolrController = Tester::getTester()->feedItemSolrController;

    $feedItemSolrController->persist($this->sampleSolrData);

    // official
    $result = UserResponse::getGeoRadiusResponse($feedItemSolrController, 49.26646, -123.250550999999, 2000, true, 200);
    $resultObj = json_decode($result, true);
    if ($resultObj["numFound"] !== 2) {
      print "Incorrect return count\n";
      print_r($resultObj);
      return false;
    }

    // unofficial
    $result = UserResponse::getGeoRadiusResponse($feedItemSolrController, 49.26646, -123.250550999999, 2000, false, 200);
    $resultObj = json_decode($result, true);
    if ($resultObj["numFound"] !== 1) {
      print "Incorrect return count\n";
      print_r($resultObj);
      return false;
    }

    // smaller georadius for official
    $result = UserResponse::getGeoRadiusResponse($feedItemSolrController, 49.2683282,-123.2498625, 500, true, 200);
    $resultObj = json_decode($result, true);
    if ($resultObj["numFound"] !== 1) {
      print "Incorrect return count\n";
      print_r($resultObj);
      return false;
    }

    return true;
  }

  /**
   * Tests for keyword and category queries
   */
  public function testKeywordCategoryResponse() {
    $feedItemSolrController = Tester::getTester()->feedItemSolrController;

    $feedItemSolrController->persist($this->sampleSolrData);

    $categories = '"Club","Health","Leisure"';
    // official
    $result = UserResponse::getGeoRadiusResponse($feedItemSolrController, 49.26646, -123.250550999999, 2000, true, 200, null, $categories);
    $resultObj = json_decode($result, true);
    if ($resultObj["numFound"] !== 1) {
      print "Incorrect return count\n";
      print_r($resultObj);
      return false;
    }

    $categories = '"Health"';
    $result = UserResponse::getGeoRadiusResponse($feedItemSolrController, 49.26646, -123.250550999999, 2000, true, 200, null, $categories);
    $resultObj = json_decode($result, true);
    if ($resultObj["numFound"] !== 0) {
      print "Incorrect return count\n";
      print_r($resultObj);
      return false;
    }

    $categories = '"Club","Health","Leisure"';
    $keyword = "Exam";
    $result = UserResponse::getGeoRadiusResponse($feedItemSolrController, 49.26646, -123.250550999999, 2000, true, 200, $keyword, $categories);
    $resultObj = json_decode($result, true);
    if ($resultObj["numFound"] !== 1) {
      print "Incorrect return count\n";
      print_r($resultObj);
      return false;
    }

    // unofficial
    $keyword = "thunderbirds";
    $result = UserResponse::getGeoRadiusResponse($feedItemSolrController, 49.26646, -123.250550999999, 2000, false, 200, $keyword, $categories);
    $resultObj = json_decode($result, true);
    if ($resultObj["numFound"] !== 1) {
      print "Incorrect return count unofficial\n";
      print_r($resultObj);
      return false;
    }

    return true;
  }


  public function testBoundingBoxResponse() {
    // Test for a bounding box query
    $feedItemSolrController = Tester::getTester()->feedItemSolrController;

    $feedItemSolrController->persist($this->sampleSolrData);

    $neLng=-123.24986250;
    $neLat=49.26832820;
    $swLng=-123.24986250;
    $swLat=49.26832820;
    $isOfficial=true;

    $sortBy="time";
    $index = 0;
    $numResults = 20;
    $keyword = '';
    $category = "Leisure";
    $solrResult = UserResponse::getBoundingBoxResponse($feedItemSolrController, $neLng, $neLat, $swLng, $swLat, $isOfficial, $index, $numResults, $keyword, $category, $sortBy);
    $resultObj = json_decode($solrResult, true);

    if ($resultObj["numFound"] != 1 && $resultObj["docs"][0]["id"] == "c2b9bf14455be55ccabb02b7a1774e4c466cf209") {
      print "incorrect result:\n";
      print_r($resultObj);
    }
    return true;
  }

  private $sampleSolrData = '
   [{
        "title": "Mon Apr 15 2013: Exams",
        "name": "UBC REC Dropin Calendar",
        "url": "http://src.rec.ubc.ca:80/day.php?getdate=20130415&cal=719d9597faa404d4b864d7560388ee17&uid=040000008200E00074C5B7101A82E0080000000060AC2CE35531CE01000000000000000010000000163F12CDB5C7CF4391D235CABD92DA1B",
    "imageUrl": "https://fbcdn-sphotos-h-a.akamaihd.net/hphotos-ak-prn1/35064_140361259315109_569713_n.jpg",
    "pubDate": "2013-04-15T11:00:00Z",
    "startDate": "2013-04-15T11:00:00Z",
    "endDate": "2013-04-16T02:00:00Z",
    "category": [
                 "Leisure",
          "Learning"
                 ],
    "locationName": "Student Recreation Centre",
    "locationGeo": "49.2683282,-123.2498625",
    "officialSource": true,
    "sourceType": "RSS",
    "id": "c2b9bf14455be55ccabb02b7a1774e4c466cf209",
    "queryCount": 12,
    "testing": true,
    "createDate": "2013-04-15T03:46:40.296Z"
    },
{
  "title": "UBC and Corban swap shutouts to split series",
    "name": "UBC Thunderbirds News",
    "content": "The UBC Thunderbirds and Corban Warriors split another well-pitched doubleheader on Sunday, with the Warriors taking game one 1-0 behind a complete game from Caleb Virtue, and the Birds getting even with a 9-0 win in game two at Thunderbird Park.",
    "url": "http://gothunderbirds.ca/news/2013/4/14/BASE_0414132012.aspx",
    "imageUrl": "http://gothunderbirds.ca/common/controls/image_handler.aspx?thumb_prefix=rp_primary&image_path=/images/2013/3/17//_WWG7261.jpg",
    "pubDate": "2013-04-14T18:17:00Z",
    "category": [
                 "Baseball",
          "Leisure"
                 ],
    "locationName": "UBC",
    "locationGeo": " 49.26487850, -123.25249630",
    "officialSource": false,
    "sourceType": "RSS",
    "id": "98bc81effe833fb7225f1cec933a31e638173b4e",
    "queryCount": 13,
    "testing": true,
    "createDate": "2013-04-15T03:46:42.128Z"
    },
{
        "title": "Antidiabetic Actions of Incretin Hormones and Dipeptidyl Peptidase 4 Inhibitors",
        "name": "UBCEvents",
        "content": "Thu, April 11, 2013 12:30 PM - 1:30 PM LIFE SCIENCES CENTRE.  LSC3\nChristopher McIntosh\nProfessor, Dept. of Cellular & Physiological Sciences, UBC\nHost: Guy Tanentzapf\n__________",
        "url": "http://www.calendar.events.ubc.ca:80/cal/event/eventView.do?subid=130650&calPath=%2Fpublic%2FEvents+Calendar%2FDepartment+of+Cellular+and+Physiological+Sciences&guid=CAL-09d22401-3ca225ae-013c-a2ac11d5-00000006myubc-team@interchange.ubc.ca&recurrenceId=",
        "imageUrl": "http://www.hr.ubc.ca/hr-networks/files/2011/10/ubc-logo-189x300.png",
        "pubDate": "2013-04-12T07:30:00Z",
        "startDate": "2013-04-12T07:30:00Z",
        "endDate": "2013-04-12T20:30:00Z",
        "category": [
          "Audience - Graduate Students",
          "Type - Seminar",
          "News",
          "Learning"
        ],
        "locationName": "LIFE SCIENCES CENTRE",
        "locationGeo": "49.2623096,-123.2462008",
        "officialSource": true,
        "sourceType": "RSSEvents",
        "id": "d1f5f583dc92395508fd186648034880b494689b",
        "queryCount": 10,
    "testing": true,
        "createDate": "2013-04-12T04:09:12.782Z"
      }]';
}