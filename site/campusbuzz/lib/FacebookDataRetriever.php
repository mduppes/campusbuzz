<?php

class FacebookDataRetriever extends URLDataRetriever
{
  protected $DEFAULT_PARSER_CLASS = 'JSONDataParser';

  private $clientId = "347882941994347";

  // move this to file..
  private $clientSecret = "ca91999af6a102dc16168394fe826d92";
  private $accessToken = null;

  public function getFeedFromPage($id) {
    $this->getAccessToken();
    $this->setBaseURL('https://graph.facebook.com/'. $id. '/feed');
    $this->addParameter('access_token', $this->accessToken);
    $data = $this->getData();
    return $data;
  }

  // Get access token from facebook (essentially the same as what is used in Kurogo's FacebookDataRetriever)
  private function getAccessToken() {
    if (!$this->accessToken) {
      $this->clearInternalCache();
      $this->setBaseURL('https://graph.facebook.com/oauth/access_token');
      $this->addParameter('client_id', $this->clientId);
      $this->addParameter('client_secret', $this->clientSecret);
      $this->addParameter('grant_type', 'client_credentials');
      
      $response = $this->getResponse()->getResponse();
      list($label, $token) = explode("=", $response);
      if ($label != "access_token" || !$token) {
	throw new KurogoDataException("Unable to retrieve facebook access token");
      }
      $this->accessToken = $token;
      $this->clearInternalCache();
    }
  }

}