<?php
  uses('peer.Header');

  /**
   * OAuth header class
   *
   */
  class OAuth2Header extends Header {

    /**
     * Constructor
     *
     * @param   <string,string> accessToken
     */
    public function __construct($accessToken) {
      parent::__construct('Authorization', $accessToken['token_type'].' '.$accessToken['access_token']);
    }
  }
?>