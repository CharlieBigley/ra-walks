<?php
/**
 * @version     1.1.1
 * @package     com_ra_walks
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 01/12/22 CB created from com ramblers
 * 07/12/22 CB analyse Joomla users by their allocated security group
 * 12/12/22 CB showPaths
 * 19/12/22 CB add WF reports from site reports
 * 06/02/23 CB mailman report
 * 23/06/23 CB remove mailman reports again
 * 14/12/24 CB showLogfile
 * 15/12/25 CB walks by month
 */
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

$toolsHelper = new ToolsHelper;
$objTable = new ToolsTable();
ToolBarHelper::title('Walks reports');

// Import CSS
$this->wa = $this->document->getWebAssetManager();
$this->wa->registerAndUseStyle('ramblers', 'com_ra_tools/ramblers.css');

//echo __file__ . '<br>';
//var_dump($this->params);
//echo '<br>';
?>

<form action="<?php echo Route::_('index.php?option=com_ra_walks&view=reports'); ?>" method="post" name="reportsForm" id="reportsForm">
    <div id="j-main-container" class="span10">
        <div class="clearfix"> </div>
        <?php
        //$mode = $this->escape($this->state->get('list.ordering'));
        //$listDirn = $this->escape($this->state->get('list.direction'));
        $objTable->width = 30;
        $objTable->add_header('Report,Action', 'grey');

        $objTable->add_item("Show Walks by Month");
        $objTable->add_item($toolsHelper->buildButton("administrator/index.php?option=com_ra_walks&task=reports.showWalksByMonth", "Go", False, 'red'));
        $objTable->add_item("");
        $objTable->generate_line();

        $objTable->add_item("Show Logfile");
        $objTable->add_item($toolsHelper->buildButton("administrator/index.php?option=com_ra_walks&task=reports.showLogfile", "Go", False, 'red'));
        $objTable->add_item("");
        $objTable->generate_line();

        if (ComponentHelper::isEnabled('com_ra_wf', true)) {
            $objTable->add_item("Count Users");
            $objTable->add_item($toolsHelper->buildButton("administrator/index.php?option=com_ra_wf&task=reports.countUsers", "Go", False, 'red'));
            $objTable->add_item("");
            $objTable->generate_line();

            $objTable->add_item("Show Users");
            $objTable->add_item($toolsHelper->buildButton("administrator/index.php?option=com_ra_wf&task=reports.showUsers", "Go", False, 'red'));
            $objTable->add_item("");
            $objTable->generate_line();

            $objTable->add_item("Show Followers");
            $objTable->add_item($toolsHelper->buildButton("administrator/index.php?option=com_ra_wf&task=reports.showFollowers", "Go", False, 'red'));
            $objTable->generate_line();
        }

        $objTable->generate_table();
        $target = 'administrator/index.php?option=com_ra_tools&view=dashboard';
        echo $toolsHelper->backButton($target);
        ?>
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</div>
</form>
