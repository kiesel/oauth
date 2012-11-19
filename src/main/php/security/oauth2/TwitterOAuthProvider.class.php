<?php
  uses('security.oauth2.OAuthProvider');

  class TwitterOAuthProvider extends Object implements OAuthProvider {
    public function getOAuthRequestTokenUri() {
      // return 'https://api.twitter.com/oauth/request_token';
      return 'http://oauth-sandbox.sevengoslings.net/request_token';
    }

    public function getOAuthTokenUri() {
      // return 'https://api.twitter.com/oauth/authorize';

    }

    public function getOAuthAccessTokenUri() {
      // return 'https://api.twitter.com/oauth/access_token';
    }
  }
?>