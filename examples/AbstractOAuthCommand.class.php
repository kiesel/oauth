<?php

  uses(
    'util.cmd.Command',
    'security.oauth2.OAuth2Client',
    'webservices.rest.RestClient',
    'util.log.ColoredConsoleAppender'
  );

  /**
   * Abstract command
   *
   */
  abstract class AbstractOAuthCommand extends Command {
    protected $code= NULL;
    protected $token= NULL;
    protected $outh2= NULL;

    /**
     * Constructor
     *
     */
    public function __construct() {
      $this->oauth2= new OAuth2Client($this->provider());
      $this->oauth2->setTrace(Logger::getInstance()->getCategory());
    }

    /**
     * Retrieve provider
     *
     * @return  security.oauth2.OAuth2Provider
     */
    protected abstract function provider();

    /**
     * ClientID
     *
     */
    #[@arg(name= 'clientid', short= 'cid')]
    public function setClientId($c) {
      $this->oauth2->setClientId($c);
    }

    /**
     * ClientSecret
     *
     */
    #[@arg(name= 'clientsecret', short='sec')]
    public function setClientSecret($s) {
      $this->oauth2->setClientSecret($s);
    }

    /**
     * DeveloperKey 
     *
     */
    #[@arg]
    public function setDeveloperKey($d= NULL) {
      $this->oauth2->setDeveloperKey($d);
    }

    /**
     * Code retrieve from server after initial grant
     *
     * @param   string c default NULL
     */
    #[@arg]
    public function setCode($c= NULL) {
      $this->code= $c;
    }

    /**
     * Token from server after code has been used
     *
     * @param   string t default NULL
     */
    #[@arg]
    public function setToken($t= NULL) {
      $this->token= $t;
    }

    /**
     * Enable verbose
     *
     * @param   string v default FALSE
     */
    #[@arg]
    public function setVerbose($v= FALSE) {
      if (FALSE !== $v) {
        Logger::getInstance()->getCategory()->addAppender(new ColoredConsoleAppender());
      }
    }

    public function run() {
      if (NULL !== $this->code) {
        $this->out->writeLine('Acquiring accessToken...');
        $this->processAuthCode();
        return;
      }

      if (NULL !== $this->token) {
        $this->out->writeLine('Performing authorized action...');
        $this->oauth2->setAccessToken($this->token);
        $this->process();
        return;
      }

      $this->out->writeLine('Redirecting ...');
      $this->displayAuthURL();
    }

    /**
     * Consume code
     *
     */
    private function processAuthCode() {
      $token= $this->oauth2->authenticate($this->code);
      $this->out->writeLine('Token:');
      $this->out->writeLine($token);
    }

    private function displayAuthURL() {
      $this->out->writeLine('Go to:');
      $this->out->writeLine($this->oauth2->createAuthUrl(array('user')));
    }

    /**
     * Client-specific code
     *
     */
    protected abstract function process();
  }
?>