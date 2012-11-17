<?php
/*
 * This class is part of the XP Framework
 *
 */

  uses(
    'unittest.TestCase',
    'peer.URL',
    'security.oauth2.OAuthHttpRequest'
  );

  class OAuthHttpRequestTest extends TestCase {
    private $fixture = NULL;

    /**
     * Setup test case
     *
     * @param   type name
     * @return  type
     * @throws  type description
     */
    public function setUp() {
      $this->fixture= newinstance('security.oauth2.OAuthHttpRequest', array(), '{
        public function signableParameters(array $params) {
          $args= func_get_args();
          return call_user_func_array(array("parent", __FUNCTION__), $args);
        }

        public function signatureBaseString(array $params) {
          $args= func_get_args();
          return call_user_func_array(array("parent", __FUNCTION__), $args);
        }

        public function oauthSignature($params, $clientId, $clientSecret) {
          $args= func_get_args();
          return call_user_func_array(array("parent", __FUNCTION__), $args);
        }

      }');
      $this->fixture->setURL(new URL('http://localhost/sign-in-with-twitter'));
    }

    /**
     * Test
     *
     */
    #[@test]
    public function signableParams() {
      $this->assertEquals(array(), $this->fixture->signableParameters(array()));
    }

    /**
     * Test
     *
     */
    #[@test]
    public function signable_params_1_arg() {
      $this->fixture->setParameter('key', 'value');
      $this->assertEquals(array('key' => 'value'), $this->fixture->signableParameters(array()));
    }

    /**
     * Test
     *
     */
    #[@test]
    public function signable_params_2_arg() {
      $this->fixture->setParameter('key', 'value');
      $this->fixture->setParameter('key2', 'v!=4L ue');
      $this->assertEquals(array('key' => 'value', 'key2' => 'v!=4L ue'), $this->fixture->signableParameters(array()));
    }

    /**
     * Test
     *
     */
    #[@test]
    public function signable_params_mixed_args() {
      $this->fixture->setParameter('key', 'value');
      $this->fixture->getURL()->setParam('key2', 'v!=4L ue');
      $this->assertEquals(array('key' => 'value', 'key2' => 'v!=4L ue'), $this->fixture->signableParameters(array()));
    }

    /**
     * Test
     *
     */
    #[@test, @expect('lang.IllegalStateException')]
    public function signable_params_detects_duplicates() {
      $this->fixture->setParameter('key', 'value');
      $this->fixture->getURL()->setParam('key', 'v!=4L ue');
      $this->fixture->signableParameters(array());
    }

    /**
     * Sets up fixture w/ twitter example data
     *
     * @param   type name
     * @return  type
     * @throws  type description
     */
    protected function setupTwitterExample() {
      $this->fixture->setMethod(HttpConstants::POST);
      $this->fixture->setURL(new URL('https://api.twitter.com/1/statuses/update.json?include_entities=true'));
      $this->fixture->setParameter('status', 'Hello Ladies + Gentlemen, a signed OAuth request!');
    }

    /**
     * Auth from twitter example
     *
     * @param   type name
     * @return  type
     * @throws  type description
     */
    protected function twitterExampleAuth() {
      return array(
        'oauth_consumer_key' => 'xvz1evFS4wEEPTGEFPHBog',
        'oauth_nonce' => 'kYjzVBB8Y0ZFabxSWbWovY3uYSQ2pTgmZeNu2VS4cg',
        'oauth_signature_method' => 'HMAC-SHA1',
        'oauth_timestamp' => '1318622958',
        'oauth_token' => '370773112-GmHxMAgYyLbNEtIKZeRNFsMKPR9EyMZeS9weJAEb',
        'oauth_version' => '1.0'
      );
    }

    /**
     * Example from https://dev.twitter.com/docs/auth/creating-signature
     *
     */
    #[@test]
    public function signatureBaseString() {
      $this->setupTwitterExample();

      $this->assertEquals(
        'POST&https%3A%2F%2Fapi.twitter.com%2F1%2Fstatuses%2Fupdate.json&include_entities%3Dtrue%26oauth_consumer_key%3Dxvz1evFS4wEEPTGEFPHBog%26oauth_nonce%3DkYjzVBB8Y0ZFabxSWbWovY3uYSQ2pTgmZeNu2VS4cg%26oauth_signature_method%3DHMAC-SHA1%26oauth_timestamp%3D1318622958%26oauth_token%3D370773112-GmHxMAgYyLbNEtIKZeRNFsMKPR9EyMZeS9weJAEb%26oauth_version%3D1.0%26status%3DHello%2520Ladies%2520%252B%2520Gentlemen%252C%2520a%2520signed%2520OAuth%2520request%2521',
        $this->fixture->signatureBaseString($this->twitterExampleAuth())
      );
    }

    /**
     * Example from https://dev.twitter.com/docs/auth/creating-signature
     *
     */
    #[@test]
    public function signature() {
      $this->setupTwitterExample();

      $this->assertEquals(
        'tnnArxj06cWHq44gCs1OSKk/jLY=',
        $this->fixture->oauthSignature(
          $this->twitterExampleAuth(),
          'kAcSOqF21Fu85e7zjz7ZN2U4ZRhfV3WpwPAoE3Z7kBw', 
          'LswwdoUaIvS8ltyTt5jkRh4J50vUPVVHtR2YPi5kE'
      ));
    }
  }
?>