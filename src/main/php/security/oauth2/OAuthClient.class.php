<?php
  uses(
    'security.oauth2.OAuth',
    'security.oauth2.OAuthHttpRequest'
  );

  /**
   * OAuth client
   *
   * @see   https://dev.twitter.com/docs/api/1/post/oauth/request_token
   */
  class OAuthClient extends Object implements OAuth {
    private 
      $clientId       = NULL,
      $clientSecret   = NULL,
      $developerKey   = NULL,
      $redirectUri    = NULL;

    private
      $provider       = NULL;

    private 
      $accessToken    = NULL;

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


    public function authenticate($service) {
      $conn= new HttpConnection();
      $request= new OAuthHttpRequest($this->provider->getRequestTokenUri());
      $request->generateAuthorization($this);
    }

    private function encodeHeader(array $values) {
      $s= '';
      foreach ($values as $k => $v) {
        $s.= $k.'="'.urlencode($v).'", ';
      }

      return rtrim($s, ', ');
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