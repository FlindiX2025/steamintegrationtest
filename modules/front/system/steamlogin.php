<?php

namespace IPS\steam\modules\front\system;

use IPS\Dispatcher\Controller;
use IPS\steam\Login\Steam;
use IPS\Output;
use IPS\Session;
use IPS\Member;
use IPS\Dispatcher;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Handle Steam Login Callback
 */
class _steamlogin extends Controller
{
    public function execute()
    {
        Session::i()->csrfCheck();
        parent::execute();
    }

    public function manage()
    {
        try {
            $handler = new Steam();
            $member = $handler->authenticateSteam();
            // Log the user in
            \IPS\Login::internal($member);
            Output::i()->redirect(\IPS\Http\Url::internal('app=core&module=system&controller=login', 'front'), 'steam_login_success');
        } catch (\Exception $e) {
            Output::i()->error($e->getMessage(), '1STEAMLOGIN/1', 403, '');
        }
    }
}