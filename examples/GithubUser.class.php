<?php

  uses(
    'util.cmd.Command',
    'security.oauth2.OAuth2Client',
    'security.oauth2.GithubOAuth2Provider',
    'webservices.rest.RestClient',
    'util.log.ColoredConsoleAppender'
  );

  /**
   * Create an GitHub oauth app first, at this URL:
   * https://github.com/settings/applications
   *
   * 
   *
   * @see   http://developer.github.com/v3/oauth/#create-a-new-authorization
   */
  class GithubUser extends Command {
    private $code= NULL;
    private $token= NULL;
    private $client= NULL;
    private $outh2= NULL;

    /**
     * Constructor
     *
     */
    public function __construct() {
      $this->oauth2= new OAuth2Client(new GithubOAuth2Provider());
      $this->oauth2->setTrace(Logger::getInstance()->getCategory());
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

    #[@arg]
    public function setCode($c= NULL) {
      $this->code= $c;
    }

    #[@arg]
    public function setToken($t= NULL) {
      $this->token= $t;
    }

    #[@arg]
    public function setVerbose($v= FALSE) {
      if (FALSE !== $v) {
        Logger::getInstance()->getCategory()->addAppender(new ColoredConsoleAppender());
      }
    }

    public function run() {
      $this->out->writeLine('--> Logging in.');

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
      
      $rest= new RestClient('https://api.github.com/');
      $rest->setTrace(Logger::getInstance()->getCategory());
      $request= new RestRequest('user');
      $this->oauth2->signRest($request);
      $user= $rest->execute($request)->content();

      $json= new JsonDecoder();
      $this->out->writeLine('User: ');
      $this->out->writeLine(xp::stringOf($json->decode($user)));
    }

    private function displayAuthURL() {
      $this->out->writeLine('Go to:');
      $this->out->writeLine($this->oauth2->createAuthUrl(array('user')));
    }
  }
?>