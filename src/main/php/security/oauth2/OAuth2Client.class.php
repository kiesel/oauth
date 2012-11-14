<?php

  uses(
    'security.oauth2.OAuth',
    'security.oauth2.OAuth2AccessToken',
    'security.oauth2.OAuth2Provider',
    'security.oauth2.OAuth2Exception',
    'security.oauth2.OAuth2Header',
    'peer.http.HttpConnection',
    'peer.http.HttpRequest',
    'peer.http.HttpConstants',
    'webservices.json.JsonDecoder',
    'util.Date',
    'util.DateUtil',
    'util.log.Traceable'
  );

  /**
   * OAuth2 client implementation
   *
   */
  class OAuth2Client extends Object implements OAuth, Traceable {
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

    private
      $cat            = NULL;

    /**
     * Constructor
     *
     * @param   security.oauth2.OAuth2Provider provider
     */
    public function __construct(OAuth2Provider $provider) {
      $this->setProvider($provider);
    }

    /**
     * Set log category
     *
     * @param   util.log.LogCategory cat
     */
    public function setTrace($cat) {
      $this->cat= $cat;
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
      $request->setHeader('Accept', 'application/json');
      $request->setMethod(HttpConstants::POST);
      $request->setParameters(array_merge(array(
        'client_id'     => $this->clientId,
        'client_secret' => $this->clientSecret
      ), $params));

      $conn= new HttpConnection($url);

      $this->cat && $this->cat->debug($this->getClassName(), '>>> ', $request->getRequestString());
      return $conn->send($request);
    }

    /**
     * Helper method to read HTTP response
     *
     * @param   peer.http.HttpResponse response
     * @return  string
     */
    private function read(HttpResponse $response) {
      $data= '';

      while ($chunk= $response->readData()) {
        $data.= $chunk;
      }

      $this->cat && $this->cat->debug($this->getClassName(), '<<< ', $response, $data);

      return $data;
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

      $data= $this->read($response);
      if (HttpConstants::STATUS_OK !== $response->getStatusCode()) {
        throw new OAuth2Exception('Could not fetch OAuth2 token, response code was: '.$response->getStatusCode());
      }

      $this->setAccessTokenRaw($data);
      return $this->getAccessToken();
    }

    /**
     * Set access token
     *
     * @param   security.oauth2.OAuth2AccessToken
     */
    public function setAccessToken(OAuth2AccessToken $data) {
      $this->accessToken= $token;
    }

    /**
     * Set access token
     *
     * @param   string data
     * @throws  security.oauth2.OAuth2Exception if invalid token
     */
    public function setAccessTokenRaw($data) {
      $this->setAccessToken(new OAuth2AccessToken(create(new JsonDecoder())->decode($data)));
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
     * Prepare signature
     *
     * @throws  security.oauth2.OAuth2Exception
     */
    private function checkExpiry() {

      // Check we actually can sign this
      if (!$this->accessToken instanceof OAuth2AccessToken) {
        throw new IllegalStateException('Cannot sign HttpRequest w/o possessing an accessToken.');
      }

      // Check if token has expired; in that case refresh it.
      if ($this->accessToken->hasExpired()) {
        $this->refreshToken();
      }
    }

    /**
     * Retrieve authorization header
     *
     * @return  security.oauth2.OAuth2Header
     */
    public function getAuthorization() {
      $this->checkExpiry();
      return new OAuth2Header($this->accessToken);
    }

    /**
     * Refresh access token
     *
     * @return  mixed refreshed token
     * @throws  security.oauth2.OAuth2Exception if refresh is impossible or failed
     */
    public function refreshToken() {
      if (!$this->accessToken->hasRefreshToken()) {
        throw new OAuth2Exception('Cannot refresh accessToken, as no refresh_token token is available.');
      }

      $response= $this->doRequest($this->provider->getOauthTokenUri(), array(
        'refresh_token' => $this->accessToken->getRefreshToken(),
        'grant_type'    => 'refresh_token'
      ));

      $data= $this->read($response);
      if (HttpConstants::STATUS_OK !== $response->getStatusCode()) {
        throw new OAuth2Exception('Could not refresh accessToken, response code was: '.$response->getStatusCode());
      }

      $this->setAccessTokenRaw($data);
      return $this->getAccessToken();
    }

    /**
     * Revoke previously granted accessToken
     *
     */
    public function revokeToken() {
      $response= $this->doRequest($this->provider->getOauthRevokeUri(), array(
        'token' => $this->accessToken->getToken()
      ));

      if (HttpConstants::STATUS_OK !== $response->getStatusCode()) {
        throw new OAuth2Exception('Could not revoke token, response code was: '.$response->getStatusCode());
      }

      return TRUE;
    }
  }
?>