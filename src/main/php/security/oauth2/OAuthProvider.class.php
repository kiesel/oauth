<?php

  interface OAuthProvider {
    public function getOAuthRequestTokenUri();
    public function getOAuthTokenUri();
    public function getOAuthRevokeUri();
  }

?>