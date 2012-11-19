<?php

  uses(
    'AbstractOAuthCommand',
    'security.oauth2.OAuthClient',
    'security.oauth2.TwitterOAuthProvider'
  );

  /**
   * Create an GitHub oauth app first, at this URL:
   * https://github.com/settings/applications
   *
   * @see   http://developer.github.com/v3/oauth/#create-a-new-authorization
   */
  class TwitterUser extends AbstractOAuthCommand {


    /**
     * Constructor
     *
     */
    public function __construct() {
      $this->oauth2= new OAuthClient($this->provider());
      $this->oauth2->setRedirectUri('http://localhost.local/');
      $this->oauth2->setTrace(Logger::getInstance()->getCategory());
    }

    protected function provider() {
      return new TwitterOAuthProvider();
    }

    protected function scopes() {
      return array('user');
    }

    protected function process() {
      $rest= new RestClient('https://api.twitter.com');
      $rest->setTrace(Logger::getInstance()->getCategory());
      $request= new RestRequest('user');

      $auth= $this->oauth2->getAuthorization();
      $request->addHeader($auth->getName(), $auth->getValue());
      $user= $rest->execute($request)->data();

      $this->out->writeLine('User: ', xp::stringOf($user));
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
      $this->out->writeLine($this->oauth2->authenticate(array()));
    }

  }
?>