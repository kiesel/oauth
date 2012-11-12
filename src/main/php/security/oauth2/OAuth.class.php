<?php

  uses('peer.http.HttpRequest');

  interface OAuth {
    public function authenticate($service);
    public function sign(HttpRequest $request);
    public function createAuthUrl(array $scope);

    public function getAccessToken();
    public function setAccessToken($accessToken);
    public function setDeveloperKey($developerKey);
    public function refreshToken();
    public function revokeToken();
  }