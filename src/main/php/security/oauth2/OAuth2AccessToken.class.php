<?php

  class OAuth2AccessToken extends Object {
    private 
      $type       = NULL,
      $token      = NULL,
      $createdAt  = NULL;

    public function __construct($map) {
      $this->withToken($map['access_token'])
        ->withType($map['token_type'])
        ->withCreatedAt(isset($map['created_at']) ? $map['created_at'] : new Date());
    }

    public function withType($type) {
      if ($type !== 'bearer') {
        throw new IllegalArgumentException('Unsupported token type: '.$type);
      }

      $this->type= $type;
      return $this;
    }

    public function withToken($token) {
      if (!strlen($token)) {
        throw new IllegalArgumentException('Access token must not be zero-length.');
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