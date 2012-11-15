<?php
/*
 * This class is part of the XP Framework
 *
 */

  uses('peer.http.HttpRequest');

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
      $parameters['oauth_signature']= $this->calculateSignature($parameters);

      $this->addHeader('Authorization', $this->encodeHeader($parameters));
    }

    /**
     * Description
     *
     * @return  string
     */
    private function calculateSignature(var $params) {
      $elements= array(
        strtoupper($this->getMethod()),
        $this->getURL()->getURL(),
        $this->signableParameters($params)
      );

      $ret= '';
      foreach ($elements as $element) {
        $ret.= '&'.rawurlencode($element);
      }

      return ltrim($ret, '&');
    }

    /**
     * Description
     *
     * @param   type name
     * @return  type
     * @throws  type desc
     */
    private function signableParameters(var $params) {
      // TODO
    }
  }
?>