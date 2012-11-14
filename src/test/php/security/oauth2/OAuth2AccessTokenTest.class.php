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
      $this->assertEquals('foo', $token->getToken());
    }

    /**
     * Test
     *
     */
    #[@test]
    public function token_type_stored() {
      $token= new OAuth2AccessToken(array('access_token' => 'foo', 'token_type' => 'bearer'));
      $this->assertEquals('bearer', $token->getType());
    }

    /**
     * Test
     *
     */
    #[@test]
    public function created_time_set() {
      $token= new OAuth2AccessToken(array('access_token' => 'foo', 'token_type' => 'bearer'));
      $this->assertInstanceOf(XPClass::forName('util.Date'), $token->getCreatedAt());
      $this->assertTrue(5 < $token->getCreatedAt()->getTime(), Date::now()->getTime());
    }

    /**
     * Test
     *
     */
    #[@test]
    public function created_time_from_map() {
      $token= new OAuth2AccessToken(array('access_token' => 'foo', 'token_type' => 'bearer', 'created_at' => 0));
      $this->assertInstanceOf(XPClass::forName('util.Date'), $token->getCreatedAt());
      $this->assertEquals(new Date(0), $token->getCreatedAt());
    }

    /**
     * Test
     *
     */
    #[@test]
    public function not_yet_expired() {
      $token= new OAuth2AccessToken(array('access_token' => 'foo', 'token_type' => 'bearer', 'created_at' => time()-10, 'expires_in' => 3600));
      $this->assertFalse($token->hasExpired());
    }

    /**
     * Test
     *
     */
    #[@test]
    public function now_has_expired() {
      $token= new OAuth2AccessToken(array('access_token' => 'foo', 'token_type' => 'bearer', 'created_at' => time()-3601, 'expires_in' => 3600));
      $this->assertTrue($token->hasExpired());
    }

    /**
     * Test
     *
     */
    #[@test]
    public function no_implicit_expiry() {
      $token= new OAuth2AccessToken(array('access_token' => 'foo', 'token_type' => 'bearer', 'created_at' => time()-10));
      $this->assertFalse($token->hasExpiry());
    }
  }
?>