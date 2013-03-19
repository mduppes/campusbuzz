<?php

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
          
          $radius= 1000; // in metres

          // Change this for # of results returned
          $numResultsReturned = 50;
          
          // test radius
          $getPostsSearchQuery = SearchQueryFactory::createGeoRadiusSearchQuery($neLat, $neLng, $radius);
          $getPostsSearchQuery->addFilter(new FieldQueryFilter("officialSource", $isOfficial));
          $getPostsSearchQuery->addFilter(new FieldQueryFilter("category", $category));
          $getPostsSearchQuery->setMaxItems($numResultsReturned);

          // Fields we want returned from solr
          $getPostsSearchQuery->addReturnField("title");
          $getPostsSearchQuery->addReturnField("id");
          $getPostsSearchQuery->addReturnField("author");
          $getPostsSearchQuery->addReturnField("sourceType");
          $getPostsSearchQuery->addReturnField("url");
          $getPostsSearchQuery->addReturnField("imageUrl");
          $getPostsSearchQuery->addReturnField("pubDate");
          $getPostsSearchQuery->addReturnField("startDate");
          $getPostsSearchQuery->addReturnField("endDate");
          $getPostsSearchQuery->addReturnField("content");
           
           // Get and convert solr response to php object
          $data = $feedItemSolrController->query($getPostsSearchQuery);

          if (!isset($data["response"])) {
            throw new KurogoDataException("Error, not a valid response.");
          }

          $posts = json_encode($data["response"]);
          $json= json_decode ($posts, true);
            //$posts= $this->getArg('response');
            //$posts= json_decode($this->getArg('response'));
            $postList= array();

            foreach ($json["docs"] as $postData) {
                $post= array(
                    'title'=> $postData['title'],
                    'id'=> $postData['id'],
                    // 'author'=> $postData['author'],
                    'sourceType'=> $postData['sourceType'],
                    'pubDate'=> $postData['pubDate'],
                    'url'=> $postData['url'],
                    'imageUrl'=> $postData['imageUrl'],
                    // 'content'=> $postData['content'],
                    //'url'=> $this->buildBreadcrumbURL('detail', array('id'=>$tweetData['id_str']))
                );
                $postList[] = $post;
            }

            $this->assign('postList', $postList);
             break;
     }
  }
}