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

    public function __construct() {
      parent::__construct();
      $this->oauth2->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
    }

    protected function provider() {
      return new GoogleOAuth2Provider();
    }

    protected function scopes() {
      return array(
       'https://www.googleapis.com/auth/userinfo.profile',
       'https://www.googleapis.com/auth/userinfo.email'
      );
    }

    protected function process() {
      $rest= new RestClient('https://www.googleapis.com/oauth2/v2/');
      $rest->setTrace(Logger::getInstance()->getCategory());
      $request= new RestRequest('userinfo');

      $rest->addHeader($this->oauth2->getAuthorization());
      $user= $rest->execute($request)->data();

      $this->out->writeLine('User: ', xp::stringOf($user));
    }
  }
?>