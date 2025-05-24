<?php

namespace IPS\steam\Login;

use IPS\Login\Handler;
use IPS\Member;
use IPS\Http\Url;
use IPS\Login\Exception as LoginException;
use IPS\steam\Profile;
use IPS\steam\Update;
use IPS\Settings;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Steam extends Handler
{
    public static function enabled(): bool
    {
        return (bool) Settings::i()->steam_api_key;
    }

    public function steamLoginUrl(): string
    {
        $returnTo = (string) Url::internal('app=core&module=system&controller=steamlogin', 'front');
        $params = [
            'openid.ns'         => 'http://specs.openid.net/auth/2.0',
            'openid.mode'       => 'checkid_setup',
            'openid.return_to'  => $returnTo,
            'openid.realm'      => Url::baseUrl(),
            'openid.identity'   => 'http://specs.openid.net/auth/2.0/identifier_select',
            'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
        ];
        return 'https://steamcommunity.com/openid/login?' . http_build_query($params);
    }

    public function authenticateSteam()
    {
        // Check for OpenID response
        if (!isset($_GET['openid_mode']) || $_GET['openid_mode'] !== 'id_res') {
            throw new LoginException('Invalid OpenID response');
        }

        // Validate OpenID with Steam (simplified, use a library for production!)
        $params = $_GET;
        $params['openid.mode'] = 'check_authentication';
        $data = http_build_query($params);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => $data,
            ]
        ]);

        $result = file_get_contents('https://steamcommunity.com/openid/login', false, $context);

        // Check if authentication is valid
        if (strpos($result, 'is_valid:true') === false) {
            throw new LoginException('Steam authentication failed');
        }

        // Extract SteamID from openid.claimed_id
        if (!preg_match('#https://steamcommunity.com/openid/id/([0-9]+)#', $_GET['openid_claimed_id'], $matches)) {
            throw new LoginException('No SteamID found');
        }

        $steamId = $matches[1];

        // Find or create the member
        $member = Member::load($steamId, 'steamid');
        if (!$member->member_id) {
            // New member
            $member = Member::create([
                'name'    => 'steam_' . $steamId,
                'email'   => $steamId . '@steamid.fake',
                'steamid' => $steamId,
                'members_pass_hash' => '',
                'members_pass_salt' => '',
            ]);
        } else {
            // Update SteamID field if needed
            $member->steamid = $steamId;
            $member->save();
        }

        // Save or update Steam profile (fetch info from Steam API)
        $profile = Profile::load($member->member_id, 'st_member_id');
        if (!$profile->member_id) {
            $profile->member_id = $member->member_id;
            $profile->steamid   = $steamId;
            $profile->save();
        }
        Update::i()->updateFullProfile($profile->member_id);

        return $member;
    }

    /**
     * Show the login form (add Steam button)
     */
    public function loginForm( $url, $error = NULL, $username = NULL )
    {
        $steamUrl = $this->steamLoginUrl();
        return "<a href='{$steamUrl}'><img src='https://steamcommunity-a.akamaihd.net/public/images/signinthroughsteam/sits_01.png' alt='Sign in through Steam'></a>";
    }
}