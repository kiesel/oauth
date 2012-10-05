<?php

  interface OAuth2Provider {
    public function getOauthUrl();
    public function getOauthTokenUri();
    public function getOauthRevokeUri();
  }

?>