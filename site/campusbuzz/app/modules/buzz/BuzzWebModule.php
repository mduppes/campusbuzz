<?php

class BuzzWebModule extends WebModule
{
  protected $id='buzz';
  protected function initializeForPage() {

    // $this->assign('message', 'CampussBuzz!');

    // find tweets/facebook/rss for both mode (make a loader)
     

    // switch ($this->page)
    // {
    // 	case 'index':
    // 		break;
    // 	case 'detail':
    // 		break;
    // 	case 'help':
    // 		break;
    // }

    $this->controller = DataRetriever::factory('TwitterDataRetriever', array());

     switch ($this->page)
     {
        case 'index':
            break;
        case 'detail':
             $user = 'ubcnews';

             //get the tweets
             $tweets = $this->controller->tweets($user);

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