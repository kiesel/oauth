<?php
  /**
   * This class is part of a XP Forge project
   *
   */

  uses(
    'AbstractOAuthCommand',
    'security.oauth2.GoogleOAuth2Provider'
  );

  /**
   * Displays user info from Google
   *
   * Go to https://code.google.com/apis/console/ to create/see client credentials.
   *
   * @see   https://developers.google.com/accounts/docs/OAuth2
   */
  class GoogleUserInfo extends AbstractOAuthCommand {

    protected function provider() {
      return new GoogleOAuth2Provider();
    }

    protected function process() {
      $rest= new RestClient('https://www.googleapis.com/oauth2/v2/');
      $rest->setTrace(Logger::getInstance()->getCategory()->withAppender(new ColoredConsoleAppender()));
      $request= new RestRequest('userinfo');
      $this->oauth2->signRest($request);
      $user= $rest->execute($request)->content();

      $json= new JsonDecoder();
      $this->out->writeLine('User: ', xp::stringOf($user));
      $this->out->writeLine(xp::stringOf($json->decode($user)));
    }
  }
?>