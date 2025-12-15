<?php

/**
 * @version     0.1.2
 * @package     com_ra_walks(Ramblers Walks)
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 04/05/21 CB Created
 * 18/05/21 CB Add extra reports, back button
 * 19/06/21 CB allow change of scope
  // 21/06/21 Allow national reports
  // 22/06/21 correction for selection criteria
  // 02/09/21 show Feed Summary
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

$objHelper = new ToolsHelper;
// set callback in globals so reports can return as appropriate
Factory::getApplication()->setUserState('com_ra_tools.callback', 'reports_area');
//die('template');
//$title = $this->component_params->get('page_title', '');
//die($title);
?>
<script type="text/javascript">
    function changeScope(target) {
        window.location = target + "&scope=" + document.getElementById("selectScope").value
        return true;
    }
</script>
<?php

$sql = "Select COUNT(id) from #__ra_walks AS walks ";
if ($this->area == 'NAT') {
    echo "<h2>National walks</h2>";
    if (!$this->criteria_sql == '') {
        $sql .= 'WHERE ' . $this->criteria_sql;
    }
} else {
    $area_name = $objHelper->getValue("SELECT name FROM #__ra_areas where code='" . $this->area . "' ");
    echo "<h2>" . 'Area=' . $this->area . ' ' . $area_name . "</h2>";
    $sql .= "WHERE group_code LIKE '" . $this->area . "%' ";
    if (!$this->criteria_sql == '') {
        $sql .= 'AND ' . $this->criteria_sql;
    }
}

$target = "index.php?option=com_ra_walks&view=reports_area&area=" . $this->area;
$objHelper->selectScope($this->scope, $target);

//echo "$sql<br>";
echo '<p>Total number of walks in scope=<b>' . number_format($objHelper->getValue($sql)) . '</b><i>';
if ($this->scope == "F") {              // Future walks
    echo ' (this includes all walks on or after today, that have not been cancelled in WM)';
} elseif ($this->scope == "A") {
    echo ' (this includes all walks that have been loaded from WM, including past walks and cancelled walks)';
} elseif ($this->scope == "H") {
    echo ' (this includes all walks that have been loaded from WM, not cancelled, and a date before today)';
}
echo '</i></p>' . PHP_EOL;
echo '<p>You can analyse these in a number of ways - each report will be shown as a matrix. T'
 . 'here will be one row for each of the first selected criteria'
 . ', and one column for each occurrence of the second.</p>' . PHP_EOL;
echo '<p>For example, if you select Group / Dow the Groups will be listed vertically
    and the Days of the week horizontally. In each cell will be shown the number
    of walks meeting those joint criteria. Clicking on the cell will display details of the actual walks.</p>' . PHP_EOL;

$objTable = new ToolsTable;
$objTable->add_column('Row', 'L');
$objTable->add_column('Difficulty', 'C');
$objTable->add_column('DOW', 'C');
$objTable->add_column('Grade', 'C');
$objTable->add_column('Pace', 'C');
$objTable->generate_header();

if ($this->area == 'NAT') {
    $objTable->add_item('Area');
    $opt = 'A';
} else {
    $objTable->add_item('Group');
    $opt = 'G';
}
$target = $this->buildTarget($opt, 'Dif');
$objTable->add_item($objHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget($opt, 'W');
$objTable->add_item($objHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget($opt, 'L');
$objTable->add_item($objHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget($opt, 'P');
$objTable->add_item($objHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$objTable->generate_line('');

$objTable->add_item('Miles');
$target = $this->buildTarget('MR', 'Dif');
$objTable->add_item($objHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget('MR', 'W');
$objTable->add_item($objHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget('MR', 'L');
$objTable->add_item($objHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget('MR', 'P');
$objTable->add_item($objHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$objTable->generate_line('');

$objTable->add_item('Pace');
$target = $this->buildTarget('P', 'Dif');
$objTable->add_item($objHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget('P', 'W');
$objTable->add_item($objHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget('P', 'L');
$objTable->add_item($objHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$objTable->add_item('' . " ");
$objTable->generate_line('');

$objTable->add_item('Grade');
$target = $this->buildTarget('L', 'Dif');
$objTable->add_item($objHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget('L', 'W');
$objTable->add_item($objHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$objTable->add_item('' . " ");
$objTable->add_item('' . " ");
$objTable->generate_line('');

$objTable->generate_table();

echo '<h4>Other analyses</h4>';

$self = 'index.php?option=com_ra_walks&view=reports_area&area=' . $this->area . '&scope=' . $this->scope;
$callback = ToolsHelper::convert_to_ASCII($self);

$target = $this->buildTarget('Y', 'YM') . '&callback=' . $callback;
echo 'Year/Month ' . $objHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';

if ($this->scope == "A") {
    if ($this->area == 'NAT') {
        $target = $this->buildTarget('A', 'S') . '&callback=' . $callback;
        echo 'Area/Status ' . $objHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';
    } else {
        $target = $this->buildTarget('G', 'S') . '&callback=' . $callback;
        echo 'Group/Status ' . $objHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';
    }
}
if ($this->area == 'NAT') {
    $target = 'index.php?option=com_ra_walks&limit=20&mode=M&scope=' . $this->scope . '&task=reports.';
//    $target = 'index.php?option=com_ra_walks&task=reports.showTopLeaders&limit=20&mode=M&scope=' . $this->scope;
    echo 'Top walk leaders ' . $objHelper->imageButton('I', $target . 'showTopLeaders', false, "link-button button-p5565") . '<br>';

    echo 'Top Areas for walks ' . $objHelper->imageButton('I', $target . 'showTopAreas', false, "link-button button-p5565") . '<br>';

    echo 'Top Groups for walks ' . $objHelper->imageButton('I', $target . 'showTopGroups', false, "link-button button-p5565") . '<br>';

//    echo 'Guest walks' . $objHelper->imageButton('I', $target . 'guestWalks', false, "link-button button-p5565") . '<br>';
//    echo 'Feed summary' . $objHelper->imageButton('I', $target . 'showFeedSummary', false, "link-button button-p5565") . '<br>';
} else {
    $target = $this->buildTarget('MR', 'G') . '&callback=' . $callback;
    echo 'Miles/Group ' . $objHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';

    $target = 'index.php?option=com_ra_walks&task=reports.countWalks&mode=A&code=' . $this->area . '&scope=' . $this->scope;
    $target .= '&callback=reports_area';
    echo 'Total number of walks by Group ' . $objHelper->imageButton('I', $target, false, 'link-button button-p5565') . '<br>';

    $target = 'index.php?option=com_ra_walks&task=reports.showTopLeaders&mode=A&opt=' . $this->area . '&scope=' . $this->scope;
    $target .= '&callback=reports_area';
    echo 'Top walk leaders ' . $objHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';

//    $target = "index.php?option=com_ra_walks&task=reports.showFeedSummaryArea&area_code=" . $this->area . '&scope=' . $this->scope;
//    echo 'Feed summary' . $objHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';
}

//$target = "index.php?option=com_ra_walks&view=reports_matrix&report_type=S&mode=A&opt=" . $this->area . '&scope=' . $this->scope;
//echo 'Analyse Walk length ' . $objHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';
// $callback contains ? and =, so we must convert to ASCII
$callback = ToolsHelper::convert_to_ASCII("index.php?option=com_ra_walks&view=reports_area&area=" . $this->area . '&scope=' . $this->scope);
$target = "index.php?option=com_ra_walks&task=reports.groupsNoWalks&area=" . $this->area . '&callback=' . $callback;
echo 'Groups without future walks ' . $objHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';

// If not invoked from the menu, allow retrun to list of Areas
if ($this->callback != '') {
    $target = "index.php?option=com_ra_walks&view=area_list";
    echo $objHelper->backButton($target);
}
echo '<p class="text-highlight">';
echo '"DOW" = Day of week';
echo ', ' . '"Grade" = Local grade' . PHP_EOL;
echo ', ' . '"Miles" - Distances are always rounded';
echo '</p>';
?>

