<?php

  uses('security.oauth2.OAuth2Provider');

  class GitHubOAuth2Provider extends Object implements OAuth2Provider {
    const OAUTH_URL   = 'https://github.com/login/oauth/authorize';
    const TOKEN_URI   = 'https://github.com/login/oauth/access_token';
    const REVOKE_URI  = '';

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