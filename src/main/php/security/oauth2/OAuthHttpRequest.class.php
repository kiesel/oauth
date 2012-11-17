<?php
/*
 * This class is part of the XP Framework
 *
 */

  uses('peer.http.HttpRequest');

  /**
   * OAuth HTTP request
   *
   * @see   https://dev.twitter.com/docs/auth/creating-signature
   */
  class OAuthHttpRequest extends HttpRequest {
    
    /**
     * Description
     *
     */
    public function generateAuthorization(OAuthClient $client) {
      $parameters= array(
        'oauth_consumer_key'      => $client->getClientId(),
        'oauth_callback'          => $client->getRedirectUri(),
        'oauth_signature_method'  => 'HMAC_SHA1',
        'oauth_timestamp'         => Date::now()->getTime(),
        'oauth_nonce'             => md5(microtime().mt_rand()),
        'oauth_version'           => '1.0',
      );
      $parameters['oauth_signature']= $this->oauthSignature($parameters, $clientId, $clientSecret);

      $this->addHeader('Authorization', $this->encodeHeader($parameters));
    }

    /**
     * Generate signature
     *
     * @param   string clientId
     * @param   string clientSecret
     * @return  string
     */
    protected function oauthSignature($params, $clientId, $clientSecret) {
      return base64_encode(hash_hmac(
        'sha1',
        $this->signatureBaseString($params),
        rawurlencode($clientId).'&'.rawurlencode($clientSecret),
        TRUE
      ));
    }

    /**
     * Description
     *
     * @return  string
     */
    protected function signatureBaseString(array $params) {
      // First part is HTTP method used
      $ret= strtoupper($this->method).'&';

      // Then, base URL - the URL w/o any parameters
      $url= clone $this->getURL();
      $url->setQuery(NULL);
      $url->setFragment(NULL);
      $ret.= rawurlencode($url->getURL()).'&';

      $leading= TRUE;
      foreach ($this->signableParameters($params) as $key => $value) {
        if (!$leading) {
          $ret.= '%26'; // rawurlencode('&');
        }

        $ret.= rawurlencode($key.'='.rawurlencode($value));
        $leading= FALSE;
      }

      return $ret;
    }

    /**
     * Description
     *
     * @param   <string,string>[] params
     * @return  <string,string>
     * @throws  lang.IllegalStateException in case duplicate param names arise
     */
    protected function signableParameters(array $params) {
      $out= array();

      // First, collect all oauth_ header values
      foreach ($params as $key => $value) {
        if ('oauth_signature' == $key) {
          throw new OAuthException('Cannot sign already signed request ("oauth_signature" already there).');
        }

        $out[$key]= $value;
      }

      foreach ($this->url->getParams() as $key => $value) {
        if (isset($out[$key])) {
          throw new IllegalStateException('Duplicate parameter "'.$key.'" not supported.');
        }
        $out[$key]= $value;
      }

      if (is_array($this->parameters)) {
        foreach ($this->parameters as $key => $value) {
          if (isset($out[$key])) {
            throw new IllegalStateException('Duplicate parameter "'.$key.'" not supported.');
          }
          $out[$key]= $value;
        }
      }

      // Sort by key
      ksort($out);
      return $out;
    }
  }
?>