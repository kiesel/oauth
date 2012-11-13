<?php
  /**
   * This class is part of a XP Forge project
   *
   */

  uses(
    'util.cmd.Command',
    'security.oauth2.OAuth2Client',
    'security.oauth2.GoogleOAuth2Provider',
    'webservices.rest.RestClient',
    'util.log.ColoredConsoleAppender'
  );

  /**
   * Displays user info from Google
   *
   * Go to https://code.google.com/apis/console/ to create/see client credentials.
   *
   * @see   https://developers.google.com/accounts/docs/OAuth2
   */
  class GoogleUserInfo extends Command {
    private $code= NULL;
    private $token= NULL;
    private $client= NULL;
    private $outh2= NULL;

    public function __construct() {
      $this->oauth2 = new OAuth2Client(new GoogleOAuth2Provider());
    }

    /**
     * ClientID from Google
     *
     */
    #[@arg(name= 'clientid', short= 'cid')]
    public function setClientId($c) {
      $this->oauth2->setClientId($c);
    }

    /**
     * ClientSecret from Google
     *
     */
    #[@arg(name= 'clientsecret', short='sec')]
    public function setClientSecret($s) {
      $this->oauth2->setClientSecret($s);
    }

    /**
     * DeveloperKey from Google
     *
     */
    #[@arg]
    public function setDeveloperKey($d= NULL) {
      $this->oauth2->setDeveloperKey($d);
    }

    /**
     * Code received from createURL
     *
     */
    #[@arg]
    public function setCode($c= NULL) {
      $this->code= $c;
    }

    /**
     * Token received after full oauth completed
     *
     */
    #[@arg]
    public function setToken($t= NULL) {
      $this->token= $t;
    }

    public function run() {
      $this->out->writeLine('--> Logging in.');

      // $this->oauth2->setApplicationName("Google UserInfo PHP Starter Application");
      
      // Announce this is a "desktop app", eg. no redirects possible:
      $this->oauth2->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
      

      if (NULL !== $this->code) {
        $this->processAuthCode();
        return;
      }

      if (NULL !== $this->token) {
        $this->displayUserInfo();
        return;
      }

      $this->displayAuthURL();
    }

    private function processAuthCode() {
      $token= $this->oauth2->authenticate($this->code);
      $this->out->writeLine('Token: ', $token);
    }

    private function displayUserInfo() {
      $this->oauth2->setAccessToken($this->token);
      
      $rest= new RestClient('https://www.googleapis.com/oauth2/v2/');
      $rest->setTrace(Logger::getInstance()->getCategory()->withAppender(new ColoredConsoleAppender()));
      $request= new RestRequest('userinfo');
      $this->oauth2->signRest($request);
      $user= $rest->execute($request)->content();

      $json= new JsonDecoder();
      $this->out->writeLine('User: ', xp::stringOf($user));
      $this->out->writeLine(xp::stringOf($json->decode($user)));
    }

    private function displayAuthURL() {
      $this->out->writeLine('Go to: ', $this->oauth2->createAuthUrl(array(
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/userinfo.email'
      )));
    }
  }
?>