<?php
//include 'chromephp-master/ChromePhp.php';
class BuzzWebModule extends WebModule
{
  protected $id='buzz';
  protected function initializeForPage() {

    $this->addExternalJavascript('https://maps.googleapis.com/maps/api/js?key=AIzaSyC0U2xGsOkbSbKMppsuJPUp3Tbud_U1GgY&sensor=true');
    $this->addExternalJavascript('http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markermanager/src/markermanager.js');
    $this->addExternalJavascript('//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js');
    $this->addInternalJavascript("/modules/buzz/javascript/markerclusterer.js");
    $this->addInternalJavascript("/modules/buzz/javascript/markerclusterer.js");
    $this->addInternalJavascript("/modules/buzz/javascript/mapEvents.js");
    // $this->addExternalJavascript("http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.js");
    // $this->addExternalCSS("http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.css");

    $this->controller = DataRetriever::factory('TwitterDataRetriever', array());

    $feedItemSolrController = DataRetriever::factory('FeedItemSolrDataRetriever', array());

     switch ($this->page)
     {
        case 'index':
            break;
        case 'detail':
          $category= $this->getArg('category');
          $neLng= $this->getArg('neLng');
          $neLat= $this->getArg('neLat');
          $swLng= $this->getArg('swLng');
          $swLat= $this->getArg('swLat');
          $isOfficial=$this->getArg('isOfficial',0);
          $keyword= $this-> getArg('keyword',0);
          $sortBy= $this-> getArg ('sortBy',0);
          $index= $this-> getArg('index');

          // Change this for # of results returned
          if ($index==0){
            //first time loading
            $numResultsReturned = 10;
          }else{
            $numResultsReturned = $index;
          }

          $posts = UserResponse::getBoundingBoxResponse($feedItemSolrController, $neLng, $neLat, $swLng, $swLat, $isOfficial, $index, $numResultsReturned, $keyword, $category, $sortBy);

          $json= json_decode ($posts, true);
          //ChromePhp::log('JSON: '.$posts);
            //$posts= $this->getArg('response');
            //$posts= json_decode($this->getArg('response'));
            $postList= array();

            foreach ($json["docs"] as $postData) {
              //timezone conversion
              $tz = new DateTimeZone('America/Vancouver');
              $date = new DateTime($postData['pubDate']);
              $date->setTimeZone($tz);

              $startDateString="";
              $endDateString="";
              if (isset($postData['startDate'])){
                $startDate = new DateTime($postData['startDate']);
                $startDate->setTimeZone($tz);
                $startDateString= $startDate->format('D M j H:i');
              }
              if (isset($postData['startDate'])){
                $endDate = new DateTime($postData['endDate']);
                $endDate->setTimeZone($tz);
                $endDateString= $endDate->format('D M j H:i');
              }
              $content="";
              $title= $postData['title'];
              if (isset($postData['content'])){
                $content= trim($postData['content'],"\t\n\r\0");
                $content=substr($content, 0, 100)."...";
              }

              if (strlen($title)>150)
                  $title=substr($title, 0, 150)."...";
              

                $post= array(
                    'title'=> $title,
                    'content'=> $content,
                    'id'=> $postData['id'],
                    'name'=> $postData['name'],
                    'sourceType'=> $postData['sourceType'],
                    'pubDate'=> $date->format('D M j Y g:i:s A'),
                    'url'=> $postData['url'],
                    'imageUrl'=> $postData['imageUrl'],
                    'locationName'=> $postData['locationName'],
                    'startDate'=> $startDateString,
                    'endDate'=> $endDateString
                );
                $postList[] = $post;
            }

            $this->assign('postList', $postList);

            $newIndex= $numResultsReturned;
            //create search params array
            $params= array(
              "category" => $category,
              "neLng" => $neLng,
              "neLat"=> $neLat,
              "swLng"=> $swLng,
              "swLat"=> $swLat,
              "isOfficial"=> $isOfficial,
              "keyword"=> $keyword,
              "sort"=> $sortBy

            );
            $this->assign ('params', json_encode($params));
            $this->assign ('index', $newIndex);

             break;
     }
  }
}