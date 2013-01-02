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
     * @param   security.oauth2.OAuth2AccessToken
     */
    public function __construct(OAuth2AccessToken $token) {
      parent::__construct('Authorization', $token->getType().' '.$token->getToken());
    }
  }
?>