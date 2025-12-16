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
 * 30/06/21 CB showTopLeaders
 * 05/07/21 Don't show Miles/Group
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

echo "<!-- Code from com_ra_walks/views/reports_area/tmpl/reports_group.php -->" . PHP_EOL;
echo "Start of code from " . __FILE__ . '<br>';
$toolsHelper = new ToolsHelper;

$title = $this->component_params->get('page_title', '');

$group_name = $toolsHelper->getValue("SELECT name FROM #__ra_groups where code='" . $this->code . "' ");
echo "<h2>" . 'Group=' . $this->code . ' ' . $group_name . "</h2>";
?>
<script type="text/javascript">
    function changeScope(target) {
        window.location = target + "&scope=" + document.getElementById("selectScope").value
        return true;
    }
</script>
<?php

$target = "index.php?option=com_ra_walks&view=reports&&gcode=" . $this->code;
ToolsHelper::selectScope($this->scope, $target);
echo '<br>';
$sql = "SELECT COUNT(id) FROM #__ra_walks ";
$sql .= "WHERE group_code ='" . $this->code . "' ";

if ($this->scope == "F") {              // Future walks
    $sql .= " AND (datediff(walk_date, CURRENT_DATE) >= 0) ";
    $sql .= "AND state =1 ";
    $scope_desc = 'Active walks';
} elseif ($this->scope == "H") {   // Historic
    $sql .= " AND (datediff(walk_date, CURRENT_DATE) < 0)  ";
    $scope_desc = 'Historic walks';
} elseif ($this->scope == "D") {   // Historic
    $sql .= " AND (state=0)  ";
    $scope_desc = 'Draft walks';
} else {
    $scope_desc = 'All walks';
}
//echo $sql . '<br>';

echo '<p>Total number of walks in scope=<b>' . $toolsHelper->getValue($sql) . '</b><i>';
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
echo '<p>For example, if you select Group / DOW the Groups will be listed vertically
    and the Days of the week horizontally. In each cell will be shown the number
    of walks meeting those joint criteria. Clicking on the cell will display details of the actual walks.</p>' . PHP_EOL;

$objTable = new Table;
$objTable->add_column('Row', 'L');
$objTable->add_column('Difficulty', 'C');
$objTable->add_column('DOW', 'C');
$objTable->add_column('Grade', 'C');
$objTable->add_column('Pace', 'C');
$objTable->generate_header();

/*
  $objTable->add_item('Group');
  $target = $this->buildTarget('G', 'Dif');
  $objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
  $target = $this->buildTarget('G', 'W');
  $objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
  $target = $this->buildTarget('G', 'L');
  $objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
  $target = $this->buildTarget('G', 'P');
  $objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
  $objTable->generate_line('');
 */
$objTable->add_item('Miles');
$target = $this->buildTarget('M', 'Dif');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget('M', 'W');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget('M', 'L');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget('M', 'P');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$objTable->generate_line('');

$objTable->add_item('Pace');
$target = $this->buildTarget('P', 'Dif');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget('P', 'W');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget('P', 'L');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$objTable->add_item('' . " ");
$objTable->generate_line('');

$objTable->add_item('Grade');
$target = $this->buildTarget('L', 'Dif');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$target = $this->buildTarget('L', 'W');
$objTable->add_item($toolsHelper->imageButton('I', $target, False, "link-button button-p5565") . " ");
$objTable->add_item('' . " ");
$objTable->add_item('' . " ");
$objTable->generate_line('');

$objTable->generate_table();
echo '<h4>Other analyses</h4>';
$target = $this->buildTarget('Y', 'YM');
echo 'Year/Month ' . $toolsHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';

if ($this->scope == "A") {              // Future walks
    $target = $this->buildTarget('G', 'S');
    echo 'Group/Status ' . $toolsHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';
}
//$target = $this->buildTarget('M', 'G');
//echo 'Miles/Group ' . $toolsHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';

$target = "index.php?option=com_ra_walks&task=reports.showTopLeaders&mode=G&opt=" . $this->group_code . '&scope=' . $this->scope;
echo 'Top walk leaders ' . $toolsHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';

$target = "index.php?option=com_ra_walks&task=reports.showSummary&group_code=" . $this->group_code . '&scope=' . $this->scope;
echo 'Monthly summary ' . $toolsHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';

$target = "index.php?option=com_ra_walks&task=reports.showFeed&group_code=" . $this->group_code;
echo 'Feed update summary ' . $toolsHelper->imageButton('I', $target, false, "link-button button-p5565") . '<br>';

// This is usually invoked from view=raports_area, but it can also be invoked from
// showTopGroups. In the case, callback will have been set up
if (!$this->callback == '') {
    $target = $this->callback;
} else {
    $target = "index.php?option=com_ra_walks&view=group_list";
}
echo $toolsHelper->backButton($target);

echo '<p class="text-highlight">';
echo '"DOW" = Day of week';
echo ', ' . '"Grade" = Local grade' . PHP_EOL;
echo ', ' . '"Miles" - Distances are always rounded';
echo '</p>';
?>



