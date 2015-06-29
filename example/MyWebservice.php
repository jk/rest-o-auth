<?php

class MyWebservice
{
    /**
     * @noAuth
     * @url GET /public
     */
    public function publicMethod()
    {
        return ['I am publicly available.'];
    }

    /**
     * @url GET /protected
     * @scope basic
     */
    public function protectedMethod()
    {
        $OAUTH2_ACCESS_TOKEN = (isset($_SERVER['OAUTH2_ACCESS_TOKEN'])) ? $_SERVER['OAUTH2_ACCESS_TOKEN'] : null;
        $OAUTH2_EXPIRES_AT = (isset($_SERVER['OAUTH2_EXPIRES_AT'])) ? $_SERVER['OAUTH2_EXPIRES_AT'] : null;

        return [
            'access_token' => $OAUTH2_ACCESS_TOKEN,
            'expires_at' => $OAUTH2_EXPIRES_AT
        ];
    }
}
