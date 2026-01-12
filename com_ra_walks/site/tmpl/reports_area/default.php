<?php

/**
 * @version     1.1.2
 * @package     com_ra_walks(Ramblers Walks)
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 04/05/21 CB Created
 * 02/09/21 show Feed Summary
 * 22/12/25 CB new method for callback
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

$toolsHelper = new ToolsHelper;
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

$sql = "Select COUNT(id) FROM #__ra_walks AS walks ";

if ($this->area == 'NAT') {
    echo "<h2>National walks</h2>";
    if (!$this->criteria_sql == '') {
        $sql .= 'WHERE ' . $this->criteria_sql;
    }
} else {
    $area_name = $toolsHelper->getValue("SELECT name FROM #__ra_areas WHERE code='" . $this->area . "' ");
    echo "<h2>" . 'Area=' . $this->area . ' ' . $area_name . "</h2>";
    $sql .= "WHERE group_code LIKE '" . $this->area . "%' ";
    if (!$this->criteria_sql == '') {
        $sql .= 'AND ' . $this->criteria_sql;
    }
}

$target = "index.php?option=com_ra_walks&view=reports_area&area=" . $this->area;
$toolsHelper->selectScope($this->scope, $target);

//echo "$sql<br>";
echo '<p>Total number of walks in scope=<b>' . number_format($toolsHelper->getValue($sql)) . '</b><i>';
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

if ($this->area == 'NAT') {
    $location = 'Area';
    $opt = 'A';
} else {
    $location = 'Group';
    $opt = 'G';
}

$objTable = new ToolsTable;
$objTable->add_column('Row', 'L');
$objTable->add_column($location, 'C');
$objTable->add_column('Difficulty', 'C');
$objTable->add_column('DOW', 'C');
$objTable->add_column('Miles', 'C');

$objTable->generate_header();
$objTable->add_item($location);
$objTable->add_item('');
$target = $this->buildTarget($opt, 'Dif');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget($opt, 'W');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget($opt, 'MR');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");

$objTable->generate_line('');

$objTable->add_item('Difficulty');
$target = $this->buildTarget('Dif', 'G');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$objTable->add_item('' . " ");
$target = $this->buildTarget('Dif', 'W');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget('Dif', 'MR');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$objTable->generate_line('');

$objTable->add_item('DOW');
$target = $this->buildTarget('W', 'G');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget('W', 'Dif');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
//$target = $this->buildTarget('P', 'L');
//$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$objTable->add_item('' . " ");
$target = $this->buildTarget('W', 'MR');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$objTable->generate_line('');

$objTable->add_item('Miles');
//if ($this->scope == "A") {
if ($this->area == 'NAT') {
    $objTable->add_item('' . " ");
} else {
    $target = $this->buildTarget('MR', 'G');
    $objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
}
$target = $this->buildTarget('MR', 'Dif');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget('MR', 'W');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");

$objTable->generate_line('');
$objTable->generate_table();

echo '<h4>Other analyses</h4>';

$target = $this->buildTarget('A', 'YM');
echo 'Year/Month ' . $toolsHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';

$target_reports = 'index.php?option=com_ra_walks&limit=20&mode=M&invoked_by=reports_area';
$target_reports .= '&opt=' . $this->area . '&scope=' . $this->scope . '&task=reports.';

if ($this->area == 'NAT') {
//    $target_reports = 'index.php?option=com_ra_walks&task=reports.showTopLeaders&limit=20&mode=M&scope=' . $this->scope;
    echo 'Top walk leaders ' . $toolsHelper->imageButton('I', $target_reports . 'showTopLeaders', false, "link-button button-p5565") . '<br>';

    echo 'Top Areas for walks ' . $toolsHelper->imageButton('I', $target_reports . 'showTopAreas', false, "link-button button-p5565") . '<br>';

    echo 'Top Groups for walks ' . $toolsHelper->imageButton('I', $target_reports . 'showTopGroups', false, "link-button button-p5565") . '<br>';

//    echo 'Guest walks' . $toolsHelper->imageButton('I', $target_reports . 'guestWalks', false, "link-button button-p5565") . '<br>';
//    echo 'Feed summary' . $toolsHelper->imageButton('I', $target_reports . 'showFeedSummary', false, "link-button button-p5565") . '<br>';
} else {
    $target = $this->buildTarget('MR', 'G');
    echo 'Miles/Group ' . $toolsHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';

//   $target = 'index.php?option=com_ra_walks&task=reports.countWalks&mode=A&code=' . $this->area . '&scope=' . $this->scope;
//   $target .= '&invoked_by=reports_area';
    echo 'Total number of walks by Group ' . $toolsHelper->imageButton('I', $target_reports . 'countWalks', false, 'link-button button-p5565') . '<br>';

    $target = 'index.php?option=com_ra_walks&task=reports.showTopLeaders&mode=A&opt=' . $this->area . '&scope=' . $this->scope;
    $target .= '&invoked_by=reports_area';
    echo 'Top walk leaders ' . $toolsHelper->imageButton('I', $target_reports . 'showTopLeaders', false, "link-button button-p5565") . '<br>';

//    $target = "index.php?option=com_ra_walks&task=reports.showFeedSummaryArea&area_code=" . $this->area . '&scope=' . $this->scope;
//    echo 'Feed summary' . $toolsHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';
}

//$target = "index.php?option=com_ra_walks&view=reports_matrix&report_type=S&mode=A&opt=" . $this->area . '&scope=' . $this->scope;
//echo 'Analyse Walk length ' . $toolsHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';
echo 'Groups without future walks ' . $toolsHelper->imageButton('I', $target_reports . 'groupsNoWalks', false, "link-button button-p5565") . '<br>';

// If not invoked from the menu, allow return to list of Areas
if ($this->back != '') {
    echo $toolsHelper->backButton($this->back);
}
echo '<p class="text-highlight">';
echo '"DOW" = Day of week';
echo ', ' . '"Grade" = Local grade' . PHP_EOL;
echo ', ' . '"Miles" - Distances are always rounded';
echo '</p>';
?>

