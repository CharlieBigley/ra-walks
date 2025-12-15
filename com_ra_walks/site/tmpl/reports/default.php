<?php

/**
 * @version     1.0.8
 * @package     com_ra_walks(Ramblers Walks)
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 10/12/22 CB created from com ramblers
 * 14/12/22 CB remove report on schema
  * 23/04/25 CB walksByDate
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

$app = JFactory::getApplication();
$user = JFactory::getUser();

$objHelper = new ToolsHelper;
// set callback in globals so reports can return as appropriate
JFactory::getApplication()->setUserState('com_ra_walks.callback', 'reports');
echo "<h2>Reports</h2>";

//endif;
//$mode = $this->escape($this->state->get('list.ordering'));
//$listDirn = $this->escape($this->state->get('list.direction'));

$objTable = new ToolsTable();
$objTable->width = 30;

$objTable->add_column("Report", "R");
$objTable->add_column("Action", "L");
$objTable->generate_header();

$objTable->add_item("<b>Summaries</b>");
$objTable->add_item("");
//$objTable->generate_line("", 1);
$objTable->generate_line("");

// if (ComponentHelper::isEnabled('com_ra_walks', true);


$objTable->add_item("Total walks by Group");
$objTable->add_item($objHelper->buildLink("index.php?option=com_ra_walks&task=reports.countWalks", "Go", False, "link-button button-p0555"));
$objTable->generate_line();

$objTable->add_item("Walks by Date");
$objTable->add_item($objHelper->buildLink("index.php?option=com_ra_walks&task=reports.walksByDate", "Go", False, "link-button button-p0555"));
$objTable->generate_line();

$objTable->add_item("Groups without walks");
$objTable->add_item($objHelper->buildLink("index.php?option=com_ra_walks&task=reports.groupsNoWalks", "Go", False, "link-button button-p0555"));
$objTable->generate_line();

$objTable->add_item("Download CSV of walk leaders");
$objTable->add_item($objHelper->buildLink("index.php?option=com_ra_walks&task=reports.walkLeaders", "Go", False, "link-button button-p0555"));
$objTable->generate_line();

$objTable->add_item("Logfile");
$objTable->add_item($objHelper->buildLink("index.php?option=com_ra_walks&task=reports.showLogfile&offset=1", "Go", False, "link-button button-p0555"));
$objTable->generate_line();

// Reports for Walks Follow
if (ComponentHelper::isEnabled('com_ra_wf', true)) {
    $objTable->add_item("Feedback by Date");
    $objTable->add_item($objHelper->buildLink("index.php?option=com_ra_walks&task=reports.countFeedback", "Go", False, "link-button button-p0555"));
    $objTable->generate_line();

    $objTable->add_item("Followers by date");
    $objTable->add_item($objHelper->buildLink("index.php?option=com_ra_walks&task=reports.showFollowersByDate", "Go", False, "link-button button-p0555"));
    $objTable->generate_line();

    $objTable->add_item("Recent emails");
    $objTable->add_item($objHelper->buildLink("index.php?option=com_ra_walks&task=reports.recentEmails", "Go", False, "link-button button-p0555"));
    $objTable->generate_line();

    if ($user->id == 0) {
        echo "<b>More reports are available if you log in</b><br>";
    } else {
        $objTable->add_item("Registered walk leaders");
        $objTable->add_item($objHelper->buildLink("index.php?option=com_ra_walks&task=reports.showRegisteredLeaders", "Go", False, "link-button button-p0555"));
        $objTable->generate_line();

        $objTable->add_item("Walks and Followers");
        $objTable->add_item($objHelper->buildLink("index.php?option=com_ra_walks&task=reports.walksFollowers", "Go", False, "link-button button-p0555"));
        $objTable->generate_line();

        $objTable->add_item("Groups and Followers");
        $objTable->add_item($objHelper->buildLink("index.php?option=com_ra_walks&task=reports.showUserGroups", "Go", False, "link-button button-p0555"));
        $objTable->generate_line();
    }
}


//$objTable->add_item("Walks updated");
//$objTable->add_item($objHelper->buildLink("index.php?option=com_ra_walks&task=reports.walksAudit&offset=1", "Go", False, "link-button button-p0555"));
//$objTable->generate_line();

if ($objHelper->isSuperuser()) {
    $objTable->add_item("Recent feedback");
    $objTable->add_item($objHelper->buildLink("index.php?option=com_ra_walks&task=reports.recentFeedback", "Go", False, "link-button button-p0555"));
    $objTable->generate_line();

    $objTable->add_item("Users");
    $objTable->add_item($objHelper->buildLink("index.php?option=com_ra_walks&task=reports.showUsers", "Go", False, "link-button button-p0555"));
    $objTable->generate_line();
}

$objTable->generate_table();
echo "<p>&nbsp;</p>";
$groups = $app->getUserState("com_ra_walks.groups", 'Not set');
echo 'Value from State=' . $groups;
?>

<p class="text-highlight">TODO</p>
<ol>
    <li class="text-highlight">Week-by-week count of walks</li>
    <li class="text-highlight">Group by location</li>
</ol>
<?php

echo '<form action="';
echo JRoute::_('index.php?option=com_ra_walks&view=reports');
echo '" method="post" name="reportsForm" id="reportsForm">';
echo '<input type="hidden" name="mode" value="<?php echo $mode; ?>" />';
//echo '<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn;

echo '</form>';

echo "<!-- End of code from com_ra_walks/views/reports/tmpl/reports_template.php -->" . PHP_EOL;
?>



