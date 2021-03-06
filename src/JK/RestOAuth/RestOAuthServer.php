<?php

namespace JK\RestOAuth;

use JK\RestServer\RestServer;

/**
 * RestOAuthServer extends RestServer with OAuth2 support
 *
 * @copyright © 2011 milch & zucker AG. All rights reserved.
 * @author jkohl <j.kohl@milchundzucker.de>
 * @since 2011-08-15
 *
 * On 2015-06-26 we changed the OAuth2 library to "bshaffer/oauth2-server-php" to get final OAuth2 support
 *
 * @package REST-O-Auth
 */
class RestOAuthServer extends RestServer
{
    /**
     * @var bool Disable authentication ONLY FOR TESTING PURPOSES!
     * If set to true, no authentication methods will be called. It's
     * mainly there to help debugging the REST service.
     */
    public $bypassAuthentication = false;

    /**
     * @var bool Inject optional OAuth2 header
     * Some big API use optional useful headers. By default we use
     * them, too. Set it to false if you want a more strict to the
     * specs service.
     */
    public $optionalHeaders = true;

    /**
     * @var \OAuth2\Server bshaffer/oauth2-server-php server instance
     */
    protected $oauth2_server;

    /**
     * @var mixed Storage variable to hold an IOAuth2Storage compliant object
     */
    protected $storage;

    /**
     * The REST-O-Auth server requires a IOAuth2Storage compatible storage instance. The other
     * parameters are optional. While developing your REST web service, it's a good idea to
     * set mode to 'debug'.
     *
     * @param mixed $storage array or OAuth2\Storage
     * @param string $mode
     *    In 'debug' mode you get more and prettier output. Defaults to 'production'.
     * @param string $realm
     *    Think 'title' of your web service. With OAuth you will probably never see this, so it's optional.
     */
    public function __construct($storage, $mode = 'production', $realm = 'REST-O-Auth server')
    {
        parent::__construct($mode, $realm);
        $this->storage = $storage;
    }

    /**
     * This method gets called every time a REST method is called which lacks
     * the @noAuth keyword. So this is the right place to implement other
     * authentication mechanisms like OAuth2, what we're doing here.
     *
     * Don't call that method directly, it has not any benefits in doing
     * so. It will be automatically called by the super class, if it's there.
     *
     * @access protected
     * @param bool $ask It's just here for compatibility
     * @return bool True if verified request, False otherwise
     */
    protected function doServerWideAuthorization($ask = false)
    {
        if ($this->bypassAuthentication) {
            return true;
        }

        list($obj, $method, $params, $thisParams, $keys) = $this->findUrl();
        $accepted_scope = (isset($keys['scope'])) ? $keys['scope'] : null;

        // Handle a request to a resource and authenticate the access token
        $request = \OAuth2\Request::createFromGlobals();
        $response = new \OAuth2\Response();
        if (!$this->getOAuth2Server()->verifyResourceRequest($request, $response, $accepted_scope)) {
            // Presented token wasn't valid
            $response->send();

            return false;
        } else {
            // Token is valid

            /** @var array $token_data */
            $token_data = $this->getOAuth2Server()->getAccessTokenData($request);

            // Save all info in the _SERVER environment
            $_SERVER['OAUTH2_USER_ID'] = $token_data['user_id'];
            $_SERVER['OAUTH2_CLIENT_ID'] = $token_data['client_id'];
            $_SERVER['OAUTH2_EXPIRES'] = $token_data['expires'];
            $_SERVER['OAUTH2_EXPIRES_AT'] = strftime("%d.%m.%Y %H:%M", $token_data['expires']);
            $_SERVER['OAUTH2_SCOPE'] = $token_data['scope'];
            $_SERVER['OAUTH2_ACCESS_TOKEN'] = $token_data['access_token'];

            if ($this->optionalHeaders) {
                // This is completely optional, but GitHub does so,
                // too: http://developer.github.com/v3/oauth/
                $this->header_manager->addHeader('X-OAuth-Scopes', $token_data['scope']);
                $this->header_manager->addHeader('X-Accepted-OAuth-Scopes: ', $accepted_scope);
            }

            return true;
        }
    }

    /**
     * sharedInstance of \OAuth2\Server class
     *
     * @return \OAuth2\Server
     *    OAuth2 shared instance
     */
    public function getOAuth2Server()
    {
        if (!isset($this->_oauth)) {
            // If there is no \OAuth2\Server instance available create a default one

            $this->oauth2_server = new \OAuth2\Server($this->storage);

            // Config the OAuth2 server component
            $this->oauth2_server->addGrantType(new \OAuth2\GrantType\ClientCredentials($this->storage));
            $this->oauth2_server->addGrantType(new \OAuth2\GrantType\AuthorizationCode($this->storage));
            $this->oauth2_server->addGrantType(new \OAuth2\GrantType\RefreshToken($this->storage));

            $this->oauth2_server->setConfig('access_lifetime', 2592000);

            $defaultScope = 'basic';
            $supportedScopes = [
                'basic'
            ];
            $memory = new \OAuth2\Storage\Memory([
                'default_scope' => $defaultScope,
                'supported_scopes' => $supportedScopes
            ]);
            $scopeUtil = new \OAuth2\Scope($memory);
            $this->oauth2_server->setScopeUtil($scopeUtil);
        }

        return $this->oauth2_server;
    }

    public function setOAuth2Server(\OAuth2\Server $server)
    {
        $this->oauth2_server = $server;
    }
}
