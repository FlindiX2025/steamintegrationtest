//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    exit;
}

class steam_hook_steamDefault extends _HOOK_CLASS_
{
    protected function manage()
    {
	try
	{
	        try {
	            if (\IPS\Settings::i()->steam_default_tab && !isset(\IPS\Request::i()->tab) && !\IPS\Request::i()->isAjax()) {
	                \IPS\Request::i()->tab = 'node_steam_profile';
	            }
	
	            return \call_user_func_array('parent::manage', \func_get_args());
	        } catch (\RuntimeException $e) {
	            if (method_exists(get_parent_class(), __FUNCTION__)) {
	                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
	            }
	            throw $e;
	        }
	}
	catch ( \Error | \RuntimeException $e )
	{
		if( \defined( '\IPS\DEBUG_HOOKS' ) AND \IPS\DEBUG_HOOKS )
		{
			\IPS\Log::log( $e, 'hook_exception' );
		}

		if ( method_exists( get_parent_class(), __FUNCTION__ ) )
		{
			return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
		}
		else
		{
			throw $e;
		}
	}
    }
}