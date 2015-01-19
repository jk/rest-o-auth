<?php

namespace JK\RestOAuth;

use JK\OAuth2\IOAuth2Storage;
use JK\OAuth2\OAuth2;
use JK\OAuth2\OAuth2ServerException;
use JK\RestServer\RestServer;

/**
 * RestOAuthServer extends RestServer with OAuth2 support
 *
 * @copyright © 2011 milch & zucker AG. All rights reserved.
 * @author jkohl <j.kohl@milchundzucker.de>
 * @since 2011-08-15
 * 
 * @package REST-O-Auth
 */
class RestOAuthServer extends RestServer {
	/**
	 * Storage variable to hold an IOAuth2Storage compliant object
	 * @var IOAuth2Storage
	 */
	protected $storage;
	
	/**
	 * If set to true, no authentication methods will be called. It's
	 * mainly there to help debugging the REST service.
	 * @var bool
	 */
	public $bypassAuthentication = false;
	
	/**
	 * Some big API use optional useful headers. By default we use
	 * them, too. Set it to false if you want a more strict to the
	 * specs service.
	 * @var bool
	 */
	public $optionalHeaders = true;
	
	/**
	 * The REST-O-Auth server requires a IOAuth2Storage compatable storage instance. The other
	 * parameters are optional. While developing your REST web service, it's a good idea to
	 * set mode to 'debug'.
	 *
	 * @param IOAuth2Storage $storage
	 * 	It's required to specify a storage container for tokens and client registrations
	 * @param string $mode
	 * 	In 'debug' mode you get more and prettier output. Defaults to 'production'.
	 * @param string $realm
	 * 	Think 'title' of your web service. With OAuth you will probably never see this, so it's optional.
	 */
	public function __construct(IOAuth2Storage $storage, $mode = 'production', $realm = 'REST-O-Auth server') {
		parent::__construct($mode, $realm);
		$this->storage = $storage;
	}
	
	/**
	 * sharedInstance of OAuth2 class
	 *
	 * @access protected
	 * @return OAuth2
	 * 	OAuth2 shared instance
	 */
	protected function oauth() {
		if (!isset($this->_oauth)) {
			$this->_oauth = new OAuth2($this->storage);
		}
		return $this->_oauth;
	}
	
	/**
	 * This method gets called every time a REST method is called which lacks
	 * the @noAuth keyword. So this is the right place to implement other
	 * authentication mechanisms like OAuth2, what we're doing here.
	 * 
	 * Don't call that method directly, it hasn't any benefits in doing
	 * so. It will be automatically called by the super class, if it's there.
	 *
	 * @access protected
	 * @param string $ask
	 * 	It's just here for compatibility
	 * @return bool
	 * 	True if verified request, False otherwise
	 */
	protected function doServerWideAuthorization($ask = false)
	{
		if ($this->bypassAuthentication) {
			return true;
		}
		try {
			// Check for bearer tokens (Header, GET params, etc.)
			$token = $this->oauth()->getBearerToken();
			
			// Verify token (and optionally the scope)
			list($obj, $method, $params, $thisParams, $keys) = $this->findUrl();
			$info = $this->oauth()->verifyAccessToken($token, $keys['scope']);
			
			// Save all info in the _SERVER enviroment
			$_SERVER['OAUTH2_USER_ID'] = $info['user_id'];
			$_SERVER['OAUTH2_CLIENT_ID'] = $info['client_id'];
			$_SERVER['OAUTH2_EXPIRES'] = $info['expires'];
			$_SERVER['OAUTH2_EXPIRES_AT'] = strftime("%d.%m.%Y %H:%M", $info['expires']);
			$_SERVER['OAUTH2_SCOPE'] = $info['scope'];
			
			if ($this->optionalHeaders) {
				// This is completly optional, but Github does so, 
				// too: http://developer.github.com/v3/oauth/
				header('X-OAuth-Scopes: '.$info['scope']);
				header('X-Accepted-OAuth-Scopes: '.$keys['scope']);
			}
			return true;
		} catch (OAuth2ServerException $oauthError) {
			/**
			 * For now don't send any additional headers here. The oauth2-php lib will
			 * cover that for you in sendHttpResponse().
			 */
		 	$oauthError->sendHttpResponse();
			return false;
		}
	}
}
