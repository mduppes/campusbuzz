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

     switch ($this->page)
     {
        case 'index':
            break;
        case 'detail':
             $user = 'ubcnews';

             //get the tweets
             $tweets = $this->controller->getTweetsByUser($user);

             //prepare the list
             $tweetList = array();
             foreach ($tweets as $tweetData) {
                 $tweet = array(
                     'title'=> $tweetData['text'],
                     'subtitle'=> $tweetData['created_at'],
                     'url'=> $this->buildBreadcrumbURL('detail', array('id'=>$tweetData['id_str']))

                 );
                 $tweetList[] = $tweet;
             }

             //assign the list to the template
             $this->assign('tweetList', $tweetList);
             break;
     }
  }
}