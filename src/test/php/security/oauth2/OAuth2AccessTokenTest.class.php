<?php
  uses('unittest.TestCase', 'security.oauth2.OAuth2AccessToken');

  class OAuth2AccessTokenTest extends TestCase {

    /**
     * Test
     *
     */
    #[@test]
    public function create_requires_wellformed_map() {
      new OAuth2AccessToken(array('access_token' => 'foo', 'token_type' => 'bearer'));
    }

    /**
     * Test
     *
     */
    #[@test, @expect('security.oauth2.OAuth2Exception')]
    public function create_checks_token_type() {
      new OAuth2AccessToken(array('access_token' => 'foo', 'token_type' => 'basic'));
    }

    /**
     * Test
     *
     */
    #[@test]
    public function token_stored() {
      $token= new OAuth2AccessToken(array('access_token' => 'foo', 'token_type' => 'bearer'));
      $this->assertEquals('foo', $token->getAccessToken());
    }
  }
?>