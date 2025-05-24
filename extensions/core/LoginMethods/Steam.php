<?php

namespace IPS\steam\extensions\core\LoginMethods;

use IPS\Login\Handler;
use IPS\steam\Login\Steam;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Register the Steam login handler with the IPS login system
 */
class _Steam extends Handler
{
    public function loginForm()
    {
        $steam = new Steam();
        return $steam->loginForm();
    }
}