<?php

  uses(
    'util.Configurable',
    'security.oauth2.OAuth2Provider'
  );

  class CustomerOAuth2Provider extends Object implements OAuth2Provider, Configurable {
    private
      $oauthUrl       = NULL,
      $oauthTokenUri  = NULL,
      $oauthRevokeUri = NULL;

    public function getOauthUrl() {
      return $this->oauthUrl;
    }

    public function setOauthUrl($url) {
      $this->oauthUrl= $url;
    }

    public function getOauthTokenUri() {
      return $this->oauthTokenUri;
    }

    public function setOauthTokenUri($uri) {
      $this->oauthTokenUri= $uri;
    }

    public function getOauthRevokeUri() {
      return $this->oauthRevokeUri;
    }

    public function setOauthRevokeUri($uri) {
      $this->oauthRevokeUri= $uri;
    }

    public function configure(Properties $prop) {
      $this->setOAuthUrl($prop->readString('provider', 'url'));
      $this->setOAuthTokenUri($prop->readString('provider', 'tokenUri'));
      $this->setOauthRevokeUri($prop->readString('provider', 'revokeUri'));
    }
  }

?>