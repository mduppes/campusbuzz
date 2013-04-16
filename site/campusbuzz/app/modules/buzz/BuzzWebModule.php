<?php
include 'chromephp-master/ChromePhp.php';
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
          
          
          // bbox search
          $getPostsSearchQuery = SearchQueryFactory::createBoundingBoxSearchQuery($neLng, $neLat, $swLng, $swLat);
          $getPostsSearchQuery->addFilter(new FieldQueryFilter("officialSource", $isOfficial));
          $getPostsSearchQuery->addFilter(new FieldQueryFilter("category", $category));
          $getPostsSearchQuery->setMaxItems($numResultsReturned);
          if ($keyword != "")
            $getPostsSearchQuery->addKeyword($keyword);

          //sort by most recent/popularity
          if ($sortBy=="time"){
            $getPostsSearchQuery->addSort(new SearchSort("pubDate", false));
          }else{
            $getPostsSearchQuery->addSort(new SearchSort("queryCount", false));
          }
          

          // Fields we want returned from solr
          $getPostsSearchQuery->addReturnField("title");
          $getPostsSearchQuery->addReturnField("id");
          $getPostsSearchQuery->addReturnField("name");
          $getPostsSearchQuery->addReturnField("sourceType");
          $getPostsSearchQuery->addReturnField("url");
          $getPostsSearchQuery->addReturnField("imageUrl");
          $getPostsSearchQuery->addReturnField("pubDate");
          $getPostsSearchQuery->addReturnField("locationName");
          // $getPostsSearchQuery->addReturnField("endDate");
          $getPostsSearchQuery->addReturnField("content");
           
           // Get and convert solr response to php object
          $data = $feedItemSolrController->query($getPostsSearchQuery);

          if (!isset($data["response"])) {
            throw new KurogoDataException("Error, not a valid response.");
          }

          $posts = json_encode($data["response"]);
          $json= json_decode ($posts, true);
          ChromePhp::log('JSON: '.$posts);
            //$posts= $this->getArg('response');
            //$posts= json_decode($this->getArg('response'));
            $postList= array();

            foreach ($json["docs"] as $postData) {
              //timezone conversion
              $tz = new DateTimeZone('America/Vancouver');
              $date = new DateTime($postData['pubDate']);
              $date->setTimeZone($tz);

                $post= array(
                    'title'=> $postData['title'],
                    'id'=> $postData['id'],
                    'name'=> $postData['name'],
                    'sourceType'=> $postData['sourceType'],
                    'pubDate'=> $date->format('l F j Y g:i:s A'),
                    'url'=> $postData['url'],
                    'imageUrl'=> $postData['imageUrl'],
                    'locationName'=> $postData['locationName'],
                    //'url'=> $this->buildBreadcrumbURL('detail', array('id'=>$tweetData['id_str']))
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