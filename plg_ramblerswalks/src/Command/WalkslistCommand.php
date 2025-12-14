<?php

/**
 * @package     Ramblers.Console
 * @subpackage  Ramblerswalks
 *
 * @copyright   Copyright (C) 2005 - 2021 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 16/12/24 CB created from WalksloadCommand
 * 17/12/24 CB return 1 from doExecute
 * 21/12/24 CB show totals by state
 * 23/12/24 CB use this->objHelper
 * 06/01/25 CB send result as email
 * 07/01/25 CB add dependencies
 */

namespace Ramblers\Plugin\System\Ramblerswalks\Command;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Console\Command\AbstractCommand;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class WalkslistCommand extends AbstractCommand {

    /**
     * The default command name
     *
     * @var    string
     *
     * @since  4.0.0
     */
    protected static $defaultName = 'ramblerswalks:walkslist';

    /**
     * @var InputInterface
     * @since version
     */
    private $cliInput;

    /**
     * SymfonyStyle Object
     * @var SymfonyStyle
     * @since 4.0.0
     */
    private $ioStyle;
    private $objHelper;

    /**
     * Instantiate the command.
     *
     * @since   4.0.0
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Configures the IO
     *
     * @param   InputInterface   $input   Console Input
     * @param   OutputInterface  $output  Console Output
     *
     * @return void
     *
     * @since 4.0.0
     *
     */
    private function configureIO(InputInterface $input, OutputInterface $output) {
        $this->cliInput = $input;
        $this->ioStyle = new SymfonyStyle($input, $output);
    }

    /**
     * Initialise the command.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function configure(): void {

        $help = "<info>%command.name%</info> List summary data about walks
            \nUsage: <info>php %command.full_name%
            \nNo parameters are available</info>";

        $this->setDescription('Called by cron to list summary of walks.');
        $this->setHelp($help);
        $this->objHelper = new ToolsHelper;
    }

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @since   4.0.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int {
        $this->configureIO($input, $output);

        $sql = 'SELECT COUNT(id) FROM #__ra_walks';
        $this->ioStyle->comment("Total number of walks is " . number_format($this->objHelper->getValue($sql)));
        $body = "Total number of walks is " . number_format($this->objHelper->getValue($sql)) . '<br>';

        $sql = 'SELECT COUNT(id) FROM #__ra_walks WHERE state=1';
        $this->ioStyle->comment("Total number of published walks is " . number_format($this->objHelper->getValue($sql)));
        $body .= "Total number of published walks is " . number_format($this->objHelper->getValue($sql)) . '<br>';

        $sql = 'SELECT COUNT(id) FROM #__ra_walks WHERE state=0';
        $this->ioStyle->comment("Total number of unpublished walks is " . number_format($this->objHelper->getValue($sql)));
        $body .= "Total number of unpublished walks is " . number_format($this->objHelper->getValue($sql)) . '<br>';

        $sql = 'SELECT MIN(walk_date) FROM #__ra_walks';
        $this->ioStyle->comment("Earliest walk is for " . $this->objHelper->getValue($sql));
        $body .= "Earliest walk is for " . $this->objHelper->getValue($sql) . '<br>';

        $sql = 'SELECT MAX(walk_date) FROM #__ra_walks';
        $this->ioStyle->comment("Latest walk is for " . $this->objHelper->getValue($sql));
        $body .= "Latest walk is for " . $this->objHelper->getValue($sql) . '<br>';

        $this->ioStyle->comment($body);
        /*
          $params = ComponentHelper::getParams('com_hy_bookings');
          //       print_r($params);
          $reply_to = $params['notification_email'];
          $to = array($params['book_sec_email'], $params['treasurer_email']);
          //        print_r($to);
          $to = array($params['book_sec_email'], 'charlie@bigley.me.uk');
         */
        $to = 'hyperbigley@gmail.com';
        $reply_to = 'hyperbigley@gmail.com';
        $title = 'Statistics about walks';
        $toolsHelper = new ToolsHelper;

        return $toolsHelper->sendEmail($to, $reply_to, $title, $body);

        return 1;
    }

}
