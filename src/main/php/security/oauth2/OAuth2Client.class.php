<?php

  uses(
    'security.oauth2.OAuth',
    'security.oauth2.OAuth2Provider',
    'security.oauth2.OAuth2Exception',
    'peer.http.HttpConnection',
    'peer.http.HttpRequest',
    'peer.http.HttpConstants',
    'webservices.json.JsonDecoder',
    'util.Date'
  );

  /**
   * OAuth2 client implementation
   *
   */
  class OAuth2Client extends Object implements OAuth {
    private
      $clientId       = NULL,
      $clientSecret   = NULL,
      $developerKey   = NULL,
      $state          = NULL,
      $redirectUri    = NULL,
      $approvalPrompt = 'force';

    private
      $provider       = NULL;

    private
      $accessToken    = NULL;

    /**
     * Constructor
     *
     * @param   security.oauth2.OAuth2Provider provider
     */
    public function __construct(OAuth2Provider $provider) {
      $this->setProvider($provider);
    }

    /**
     * Set OAuth2Provider
     *
     * @param   security.oauth2.OAuth2Provider provider
     */
    public function setProvider(OAuth2Provider $provider) {
      $this->provider= $provider;
    }

    /**
     * Create auth url; this is the URL that must be called by the user
     * to be authenticated to create a oauth code.
     *
     * That code must later be fed to authenticate().
     *
     * The given scopes are the permissions to request from the resource
     * server and are usecase-specific.
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

      return $this->provider->getOauthUrl().'?'.implode('&', $params);
    }

    /**
     * Helper method to perform HTTP request
     *
     * @param   string url
     * @param   array<string,string> params
     * @return  peer.http.HttpResponse
     */
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
     * @param   string code
     * @return  string token
     * @throws  security.oauth2.OAuth2Exception when authentication failed
     */
    public function authenticate($code) {

      // The user has potentially granted the authentication request
      // on the provider's auth page.
      // Now the code must be verified directly with the provider
      $response= $this->doRequest($this->provider->getOauthTokenUri(), array(
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

    /**
     * Retrieve redirect uri
     *
     * @return  string
     */
    public function getRedirectUri() {
      return $this->redirectUri;
    }

    /**
     * Set redirect uri
     *
     * @param   string uri
     */
    public function setRedirectUri($uri) {
      $this->redirectUri= $uri;
    }

    /**
     * Set access token
     *
     * @param   string data
     * @throws  security.oauth2.OAuth2Exception if invalid token
     */
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

    /**
     * Retrieve access token
     *
     * @return  string
     */
    public function getAccessToken() {
      if (NULL === $this->accessToken) return NULL;

      $decoder= new JsonDecoder();
      return $decoder->encode($this->accessToken);
    }

    /**
     * Set creation timestamp
     *
     * @param   util.Date time
     */
    private function setAccessTokenCreatedTime(Date $time) {
      if (!is_array($this->accessToken)) {
        throw new IllegalStateException('Cannot set creation time of access token.');
      }

      $this->accessToken['created']= $time->getTime();
    }

    /**
     * Set client id
     *
     * @param   string id
     */
    public function setClientId($id) {
      $this->clientId= $id;
    }

    /**
     * Retrieve client id
     *
     * @return  string
     */
    public function getClientId() {
      return $this->clientId;
    }

    /**
     * Set client secret
     *
     * @param   string secret
     */
    public function setClientSecret($secret) {
      $this->clientSecret= $secret;
    }

    /**
     * Set developer key
     *
     * @param   string key
     */
    public function setDeveloperKey($key) {
      $this->developerKey= $key;
    }

    /**
     * Retrieve developer key
     *
     * @return string
     */
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

    /**
     * Sign given RestRequest
     *
     * @param   webservices.rest.RestRequest request
     */
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
      $response= $this->doRequest($this->provider->getOauthTokenUri(), array(
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
      $response= $this->doRequest($this->provider->getOauthRevokeUri(), array(
        'token' => $this->accessToken['access_token']
      ));

      if (HttpConstants::STATUS_OK !== $response->getStatusCode()) {
        throw new OAuth2Exception('Could not revoke token, response code was: '.$response->getStatusCode());
      }

      return TRUE;
    }
  }
?>