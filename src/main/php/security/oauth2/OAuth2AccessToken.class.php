<?php
  uses('util.DateUtil', 'security.oauth2.OAuth2Exception');

  class OAuth2AccessToken extends Object {
    private 
      $type         = NULL,
      $token        = NULL,
      $createdAt    = NULL,
      $expiresIn    = NULL,
      $refreshToken = NULL;

    public function __construct($map) {
      if (!isset($map['access_token'])) {
        throw new OAuth2Exception('Access token looks invalid; expected "access_token" field.');
      }

      $this->withToken($map['access_token'])
        ->withType($map['token_type'])
        ->withCreatedAt(isset($map['created']) ? new Date($map['created']) : new Date())
        ->withExpiresIn(isset($map['expires_in']) ? $map['expires_in'] : NULL)
        ->withRefreshToken(isset($map['refresh_token']) ? $map['refresh_token'] : NULL);
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
      $this->createdAt= $time;
      return $this;
    }

    public function withExpiresIn($secs) {
      if (NULL === $secs) return $this;

      if ($secs < 0) {
        throw new OAuth2Exception('Expires_in values must be a natural number, was: "'.$secs.'"');
      }

      $this->expiresIn= (int)$secs;
      return $this;
    }

    /**
     * Set refresh token
     *
     */
    public function withRefreshToken($token) {
      $this->refreshToken= $token;
      return $this;
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

    public function hasExpiry() {
      return NULL !== $this->expiresIn;
    }

    public function hasExpired() {
      if (!$this->hasExpiry()) return FALSE;
      return create(new Date())->isAfter(DateUtil::addSeconds($this->createdAt, $this->expiresIn));
    }

    public function hasRefreshToken() {
      return NULL !== $this->refreshToken;
    }

    /**
     * Retrieve refresh token
     *
     * @return  string
     */
    public function getRefreshToken() {
      return $this->refreshToken;
    }
  }