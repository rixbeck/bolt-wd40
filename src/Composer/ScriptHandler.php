<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */

namespace WD40\Composer;

use Bolt\Composer\Script\DirectorySyncer;
use Composer\Script\Event;

class ScriptHandler
{
    public static function installResources(Event $event)
    {
        $syncer = DirectorySyncer::fromEvent($event);

        $syncer->sync('files', '%root%/files');
        $syncer->sync('%vendor%/bolt/themes', '%root%/theme', false, ['base-2016', 'base-2018', 'skeleton']);
        $syncer->sync('bolt-public', '%root%/bolt-public');
        $syncer->sync('app', '%root%/app');
    }
}
