<?php
// include our OAuth2 Server object
require_once __DIR__.'/server.php';

// Handle a request for an OAuth2.0 Access Token and send the response to the client
$rest_o_auth_server->getOAuth2Server()->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();
