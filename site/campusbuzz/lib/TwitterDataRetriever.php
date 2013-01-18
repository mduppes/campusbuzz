<?php

class TwitterDataRetriever extends URLDataRetriever
{
  protected $DEFAULT_PARSER_CLASS = 'JSONDataParser';
  
  public function tweets($user) {
    $this->setBaseURL('http://api.twitter.com/1/statuses/user_timeline.json');
    $this->addParameter('screen_name', $user);
    $data = $this->getData();
    return $data;
  }


  public function getItem($id) {
    $this->setBaseURL('http://api.twitter.com/1/statuses/show.json');
    $this->addParameter('id', $id);
    $data = $this->getData();
    return $data;
  }

}