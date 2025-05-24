<?php
/**
 * @brief            steamGroups Task
 * @author           <a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright    (c) Invision Power Services, Inc.
 * @license          https://www.invisioncommunity.com/legal/standards/
 * @package          Invision Community
 * @subpackage       steam
 * @since            08 Aug 2017
 */

namespace IPS\steam\tasks;

use IPS\Application;
use IPS\Task;
use IPS\steam\Update;
use IPS\Widget;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * steamGroups Task
 */
class _steamGroups extends Task
{
    /**
     * Execute
     * If ran successfully, should return anything worth logging. Only log something
     * worth mentioning (don't log "task ran successfully"). Return NULL (actual NULL, not '' or 0) to not log (which
     * will be most cases). If an error occurs which means the task could not finish running, throw an
     * \IPS\Task\Exception - do not log an error as a normal log. Tasks should execute within the time of a normal HTTP
     * request.
     * @return    mixed    Message to log or NULL
     * @throws    \IPS\Task\Exception
     */
    public function execute()
    {
        if (!Application::appIsEnabled('steam')) {
            return null;
        }

        if (!isset($_SERVER['REQUEST_METHOD'])) {
            $_SERVER['REQUEST_METHOD'] = 'POST';
        }

        try {
            Update\Groups::i()->update();
        } catch (\Exception $e) {
            throw new Task\Exception($this, $e);
        }

        Widget::deleteCaches('steamGroupWidget', 'steam');

        return null;
    }

    /**
     * Cleanup
     * If your task takes longer than 15 minutes to run, this method
     * will be called before execute(). Use it to clean up anything which
     * may not have been done
     * @return    void
     */
    public function cleanup(): void
    {

    }
}