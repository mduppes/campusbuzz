<?php

class TwitterWebModule extends WebModule
{
  protected $id='twitter';
  protected function initializeForPage() {

    //instantiate controller
    $this->controller = DataRetriever::factory('TwitterDataRetriever', array());

    switch ($this->page)
      {
      case 'index':
	//$user = 'kurogofwk';
	$user = $this->getModuleVar('TWITTER_USER');

	//get the tweets
	$tweets = $this->controller->getTweetsByUser($user);

	//prepare the list
	$tweetList = array();
	foreach ($tweets as $tweetData) {
	  $tweet = array(
			 'title'=> $tweetData['text'],
			 'subtitle'=> $tweetData['created_at'],
			 'url' => $this->buildBreadcrumbURL('detail', array('id' => $tweetData['id_str']))
			 
			 );
	  $tweetList[] = $tweet;
	}

	//assign the list to the template
	$this->assign('tweetList', $tweetList);
	break;

      case 'detail':
	$id = $this->getArg('id');
	if ($tweet = $this->controller->getTweetById($id)) {
	  $this->assign('tweetText', $tweet['text']);
	  $this->assign('tweetPost', $tweet['created_at']);
	} else {
	  $this->redirectTo('index');

	}
      }
  }
}