<?php

  uses(
    'AbstractOAuthCommand',
    'security.oauth2.TwitterOAuth2Provider'
  );

  /**
   * Create an GitHub oauth app first, at this URL:
   * https://github.com/settings/applications
   *
   * @see   http://developer.github.com/v3/oauth/#create-a-new-authorization
   */
  class GithubUser extends AbstractOAuthCommand {

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
  }
?>