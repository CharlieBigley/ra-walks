<?php

/**
 * @package     Ramblers.Walks
 * @subpackage  System.ramblerswalks
 *
 * @copyright   (C) 2024 Charlie Bigley
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ramblers\Plugin\System\Ramblerswalks\Extension;

//namespace Ramblers\Plugin\System\Onoffbydate\Extension;
\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Ramblers\Plugin\System\Ramblerswalks\Command\WalksloadCommand;
use Ramblers\Plugin\System\Ramblerswalks\Command\WalkslistCommand;

class Ramblerswalks extends CMSPlugin {

    protected $app;

    public function __construct(&$subject, $config = []) {
        parent::__construct($subject, $config);

        if (!$this->app->isClient('cli')) {
            return;
        }

        $this->registerCLICommands();
    }

    public static function getSubscribedEvents(): array {
        if ($this->app->isClient('cli')) {
            return [
                Joomla\Application\ApplicationEvents\ApplicationEvents::BEFORE_EXECUTE => 'registerCLICommands',
            ];
        }
    }

    public function registerCLICommands() {

        $commandObject = new WalksloadCommand;
        $this->app->addCommand($commandObject);

        $commandObject = new WalkslistCommand;
        $this->app->addCommand($commandObject);
    }

}
