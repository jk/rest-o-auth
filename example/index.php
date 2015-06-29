<?php
require_once(__DIR__.'/server.php');
require_once(__DIR__.'/MyWebservice.php');

$rest_o_auth_server->addClass('\MyWebservice', 'my_webservice');

$rest_o_auth_server->handle();
