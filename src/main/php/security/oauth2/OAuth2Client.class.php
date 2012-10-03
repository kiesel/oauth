<?php

  uses(
    'security.oauth2.OAuth',
    'peer.http.HttpConnection',
    'peer.http.HttpRequest',
    'peer.http.HttpConstants',
    'webservices.json.JsonDecoder',
    'util.Date',
    'security.oauth2.OAuth2Exception'
  );

  class OAuth2Client extends Object implements OAuth {
    private
      $clientId       = NULL,
      $clientSecret   = NULL,
      $developerKey   = NULL,
      $state          = NULL,
      $redirectUri    = NULL,
      $approvalPrompt = 'force';

    private
      $oauthUrl       = NULL,
      $oauthTokenUri  = NULL,
      $oauthRevokeUri = NULL;

    private
      $provider       = NULL;

    private
      $accessToken    = NULL;

    /**
     * Create auth url; this is the URL that must be called by the user
     * to be authenticated to create a oauth code.
     *
     * That code must later be fed to authenticate().
     *
     * @param   string[] scope
     * @return  string
     */
    public function createAuthUrl(array $scope) {
      $params= array(
        'response_type=code',
        'redirect_uri='.urlencode($this->redirectUri),
        'client_id='.urlencode($this->clientId),
        'scope='.urlencode(implode(' ', $scope)),
        'access_type='.urlencode($this->accessType),
        'approval_prompt='.urlencode($this->approvalPrompt)
      );

      if (isset($this->state)) {
        $params[]= 'state='.urlencode($this->state);
      }

      return $this->getOauthUrl().'?'.implode('&', $params);
    }

    private function doRequest($url, $params) {
      $request= new HttpRequest(new URL($url));
      $request->setMethod(HttpConstants::POST);
      $request->setParameters(array_merge(array(
        'client_id'     => $this->clientId,
        'client_secret' => $this->clientSecret
      ), $params));

      $conn= new HttpConnection($url);
      return $conn->send($request);
    }

    /**
     * Authenticate
     *
     */
    public function authenticate($code) {

      // The user has potentially granted the authentication request
      // on the provider's auth page.
      // Now the code must be verified directly with the provider
      $response= $this->doRequest($this->getOauthTokenUri(), array(
        'code'          => $code,
        'grant_type'    => 'authorization_code',
        'redirect_uri'  => $this->redirectUri
      ));

      if (HttpConstants::STATUS_OK !== $response->getStatusCode()) {
        throw new OAuth2Exception('Could not fetch OAuth2 token, response code was: '.$response->getStatusCode());
      }

      $this->setAccessToken($response->readData());
      $this->setAccessTokenCreatedTime(new Date());

      return $this->getAccessToken();
    }

    public function getOauthUrl() {
      return $this->oauthUrl;
    }

    public function setOauthUrl($url) {
      $this->oauthUrl= $url;
    }

    public function getOauthTokenUri() {
      return $this->oauthTokenUri;
    }

    public function setOauthTokenUri($uri) {
      $this->oauthTokenUri= $uri;
    }

    public function getOauthRevokeUri() {
      return $this->oauthRevokeUri;
    }

    public function setOauthRevokeUri($uri) {
      $this->oauthRevokeUri= $uri;
    }

    public function getRedirectUri() {
      return $this->redirectUri;
    }

    public function setRedirectUri($uri) {
      $this->redirectUri= $uri;
    }

    public function setAccessToken($data) {
      $decoder= new JsonDecoder();
      $struct= $decoder->decode($data);

      if (!isset($struct['access_token'])) {
        throw new OAuth2Exception('Access token looks invalid; expected "access_token" field.');
      }

      if (!isset($struct['expires_in'])) {
        throw new OAuth2Exception('Access token looks invalid; expected "expires_in" field.');
      }

      $this->accessToken= $struct;
    }

    public function getAccessToken() {
      $decoder= new JsonDecoder();
      return $decoder->encode($this->accessToken);
    }

    private function setAccessTokenCreatedTime(Date $time) {
      if (!is_array($this->accessToken)) {
        throw new IllegalStateException('Cannot set creation time of access token.');
      }

      $this->accessToken['created']= $time->getTime();
    }

    public function setClientId($id) {
      $this->clientId= $id;
    }

    public function getClientId() {
      return $this->clientId;
    }

    public function setClientSecret($secret) {
      $this->clientSecret= $secret;
    }

    public function setDeveloperKey($key) {
      $this->developerKey= $key;
    }

    public function getDeveloperKey() {
      return $this->developerKey;
    }

    /**
     * Sign a given HttpRequest with this oauth's token's
     * signature
     *
     * @param   peer.http.HttpRequest request
     * @throws  security.oauth2.OAuth2Exception
     */
    public function sign(HttpRequest $request) {

      // Check we actually can sign this
      if (!$this->accessToken) {
        throw new IllegalStateException('Cannot sign HttpRequest w/o possessing an accessToken.');
      }

      // Add developerKey prior to signing request
      if ($this->developerKey) {
        $request->setParameter('key', $this->developerKey);
      }

      // TODO: Check whether token has already expired, in that case: refresh it

      $request->setHeader('Authorization', 'Bearer '.$this->accessToken['access_token']);
    }

    public function signRest(RestRequest $request) {
      // Check we actually can sign this
      if (!$this->accessToken) {
        throw new IllegalStateException('Cannot sign HttpRequest w/o possessing an accessToken.');
      }

      // Add developerKey prior to signing request
      if ($this->developerKey) {
        // $request->setParameter('key', $this->developerKey);
      }

      // TODO: Check whether token has already expired, in that case: refresh it

      $request->addHeader('Authorization', 'Bearer '.$this->accessToken['access_token']);

    }

    /**
     * Refresh access token
     *
     */
    public function refreshToken($refreshToken) {
      $response= $this->doRequest($this->getOauthTokenUri(), array(
        'refresh_token' => $refreshToken,
        'grant_type'    => 'refresh_token'
      ));

      if (HttpConstants::STATUS_OK !== $response->getStatusCode()) {
        throw new OAuth2Exception('Could not refresh accessToken, response code was: '.$response->getStatusCode());
      }

      $this->setAccessToken($response->readData());
      $this->setAccessTokenCreatedTime(new Date());
      return $this->getAccessToken();
    }

    /**
     * Revoke previously granted accessToken
     *
     */
    public function revokeToken() {
      $response= $this->doRequest($this->getOauthRevokeUri(), array(
        'token' => $this->accessToken['access_token']
      ));

      if (HttpConstants::STATUS_OK !== $response->getStatusCode()) {
        throw new OAuth2Exception('Could not revoke token, response code was: '.$response->getStatusCode());
      }

      return TRUE;
    }
  }
?>