<?php
  uses(
    'util.log.Traceable',
    'security.oauth2.OAuth',
    'security.oauth2.OAuthHttpRequest',
    'security.oauth2.OAuthException'
  );

  /**
   * OAuth client
   *
   * @see   https://dev.twitter.com/docs/api/1/post/oauth/request_token
   */
  class OAuthClient extends Object implements OAuth, Traceable {
    private 
      $clientId       = NULL,
      $clientSecret   = NULL,
      $developerKey   = NULL,
      $redirectUri    = NULL;

    private
      $provider       = NULL;

    private 
      $accessToken    = NULL;

    private
      $cat            = NULL;

    /**
     * Constructor
     *
     * @param   security.oauth2.OAuthProvider provider
     */
    public function __construct(OAuthProvider $provider) {
      $this->setProvider($provider);
      $this->setRedirectUri('oob');
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
     * @param   security.oauth2.OAuthProvider provider
     */
    public function setProvider(OAuthProvider $provider) {
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
     * Either URI to redirect after auth, or "oob" for out-of-band pin mode
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
     * Retrieve client secret
     *
     * @return  string
     */
    public function getClientSecret() {
      return $this->clientSecret;
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


    public function authenticate($scope) {
      $conn= new HttpConnection($this->provider->getOAuthRequestTokenUri());
      $request= new OAuthHttpRequest(new URL($this->provider->getOAuthRequestTokenUri()));
      $request->setMethod(HttpConstants::POST);
      
      if (sizeof($scope)) {
        $request->setParameter('scope', implode(' ', $scope));
      }

      $request->generateAuthorization($this);

      $this->cat && $this->cat->debug('>>>', $request->getRequestString());
      $response= $conn->send($request);

      $data= $this->recv($response);
      if (HttpConstants::STATUS_OK !== $response->getStatusCode()) {
        throw new OAuthException('Could not authenticate, expected status 200, got "'.$response->getStatusCode().'"');
      }


    }

    /**
     * Read response
     *
     * @param   peer.http.HttpResponse response
     * @return  string
     */
    private function recv(HttpResponse $response) {
      $this->cat && $this->cat->debug('<<<', $response);
      $buf= '';

      while ($data= $response->readData()) {
        $buf.= $data;
      }

      $this->cat && $this->cat->debug('<<<', $buf);
      return $buf;
    }

    public function sign(HttpRequest $request) {

    }

    public function createAuthUrl(array $scope) {

    }

    public function getAccessToken() {

    }

    public function setAccessToken(OAuth2AccessToken $accessToken) {

    }
    public function refreshToken() {

    }
    public function revokeToken() {

    }
    public function getAuthorization() {

    }

  }
?>