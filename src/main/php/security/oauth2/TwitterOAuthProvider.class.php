<?php
  uses('security.oauth2.OAuthProvider');

  class TwitterOAuthProvider extends Object implements OAuthProvider {
    public function getOAuthRequestTokenUri() {
      return 'https://api.twitter.com/oauth/request_token';
    }

    public function getOAuthTokenUri() {

    }
    public function getOAuthRevokeUri() {
      
    }
  }
?>