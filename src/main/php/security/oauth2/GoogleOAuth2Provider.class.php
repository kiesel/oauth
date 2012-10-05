<?php

  uses('security.oauth2.OAuth2Provider');

  class GoogleOAuth2Provider extends Object implements OAuth2Provider {
    const OAUTH_URL   = 'https://accounts.google.com/o/oauth2/auth';
    const TOKEN_URI   = 'https://accounts.google.com/o/oauth2/token';
    const REVOKE_URI  = 'https://accounts.google.com/o/oauth2/revoke';

    public function getOauthUrl() {
      return self::OAUTH_URL;
    }

    public function getOauthTokenUri() {
      return self::TOKEN_URI;
    }

    public function getOauthRevokeUri() {
      return self::REVOKE_URI;
    }
  }
?>