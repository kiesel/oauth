<?php
  uses('peer.Header');

  class OAuth2Header extends Header {
    private
      $accessToken  = NULL;

    public function __construct($accessToken) {
      parent::__construct('Authorization', $accessToken['token_type'].' '.$accessToken['access_token']);
    }
  }
?>