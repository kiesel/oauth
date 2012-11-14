<?php
  uses('security.oauth2.OAuth2Exception');

  class OAuth2AccessToken extends Object {
    private 
      $type       = NULL,
      $token      = NULL,
      $createdAt  = NULL,
      $expiresIn  = NULL;

    public function __construct($map) {
      if (!isset($map['access_token'])) {
        throw new OAuth2Exception('Access token looks invalid; expected "access_token" field.');
      }

      $this->withToken($map['access_token'])
        ->withType($map['token_type'])
        ->withCreatedAt(isset($map['created_at']) ? $map['created_at'] : new Date());

      // TODO: Comment in / review against RFC
      // if (!isset($struct['expires_in'])) {
      //   throw new OAuth2Exception('Access token looks invalid; expected "expires_in" field.');
      // }
    }

    public function withType($type) {
      if ($type !== 'bearer') {
        throw new OAuth2Exception('Unsupported token type: '.$type);
      }

      $this->type= $type;
      return $this;
    }

    public function withToken($token) {
      if (!strlen($token)) {
        throw new OAuth2Exception('Access token must not be zero-length.');
      }
      
      $this->token= $token;
      return $this;
    }

    public function withCreatedAt(Date $time) {
      $this->createdAt= clone $time;
    }

    public function getToken() {
      return $this->token;
    }

    public function getType() {
      return $this->type;
    }

    public function getCreatedAt() {
      return clone $this->createdAt;
    }
  }