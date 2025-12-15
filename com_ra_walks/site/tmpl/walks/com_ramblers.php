<?php

/*
 * @package Ramblers
 * @author Charlie Bigley
 * @copyright (C) 2018 Keith Grimes
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * 12/05/22 CB Created from group_list
 * 30/09/22 CB Disallow Follo if not logged in
 * 19/08/23 CB copied from com_ramblers
 * */
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

//JFormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');
// set callback in globals so walk_detail can return as appropriate
Factory::getApplication()->setUserState('com_ra_wf.callback', 'walk_list');

$sortColumn = $this->escape($this->state->get('list.ordering'));
$sortDirn = $this->escape($this->state->get('list.direction'));
/*
  $days = JFormHelper::loadFieldType('Dow', false);
  $dayOptions = $days->getOptions();

  $locale = JFormHelper::loadFieldType('Locale', false);
  $localeOptions = $locale->getOptions();

  $scope = JFormHelper::loadFieldType('Scope', false);
  $scopeOptions = $scope->getOptions();

  $range = JFormHelper::loadFieldType('Range', false);
  $rangeOptions = $range->getOptions();
 */
//

$objHelper = new ToolsHelper;
// Find the maximum allowed on a walk
$params = ComponentHelper::getParams('com_ra_wf');
$max_walkers = $params->get("max_walkers");

$user_id = Factory::getUser()->id;
$target_email = "index.php?option=com_ra_wf&view=chat&layout=edit&id=";
$target_follow = "index.php?option=com_ra_wf&task=walk.follow&id=";
$target_info = "index.php?option=com_ra_wf&view=walkdetail&id=";
$target_unfollow = "index.php?option=com_ra_wf&task=walk.unFollow&id=";
$target_update = "index.php?option=com_ra_wf&view=walk&id=";
$target_walksfinder = "https://www.ramblers.org.uk/go-walking/find-a-walk-or-route/walk-detail.aspx?walkID=";
echo "<h2>$this->title</h2>";
JHtml::_('behavior.tooltip');
echo '<form method="post" name="adminForm" id="adminForm">' . PHP_EOL;

//$objHelper = new RamblersHelper;
echo '<div id="filter-bar" class="btn-toolbar">' . PHP_EOL;

echo '<div class="filter-search btn-group pull-left">' . PHP_EOL;
echo '  <label for="filter_search"';
echo '  class="element-invisible">';
echo JText::_('JSEARCH_FILTER');
echo '  </label>' . PHP_EOL;

echo '<input type="text" ';
echo '  name="filter_search" ';
echo '  id="filter_search" ';
echo '  placeholder="' . JText::_('JSEARCH_FILTER') . '..." ';
// filter.search is set in the model::populateState, but gives an error
//echo '  value="' . $this->escape($this->state->get('filter.search')) . '"';
echo '  value="' . $this->escape($this->filterSearch) . '"';
echo '  value="' . '' . '"';
echo '  title="' . JText::_('JSEARCH_FILTER') . '" />';
echo ' </div>' . PHP_EOL;

echo '<button class="btn hasTooltip"';
echo '  type="submit"';
echo '  title="' . JText::_('JSEARCH_FILTER_SUBMIT') . '">' . JText::_('JSEARCH_FILTER');
echo '</button>';

echo '<button class="btn hasTooltip"';
echo '  type="button"';
echo ' title="' . JText::_('JSEARCH_FILTER_CLEAR') . '">' . JText::_('JSEARCH_FILTER_CLEAR');
echo '</button>' . PHP_EOL;

echo '<select name="filter_scope" class="inputbox" onchange="this.form.submit()">';
echo '<option value=""> - Select Scope - </option>';
echo JHtml::_('select.options', $scopeOptions, 'value', 'text', $this->state->get('filter.scope'));
echo '</select>' . PHP_EOL;

echo '<select name="filter_locale" class="inputbox" onchange="this.form.submit()">';
echo '<option value=""> - Select Locale - </option>';
echo JHtml::_('select.options', $localeOptions, 'value', 'text', $this->state->get('filter.locale'));
echo '</select>' . PHP_EOL;

echo '<select name="filter_day" class="inputbox" onchange="this.form.submit()">';
echo '<option value=""> - Any day of the week - </option>';
echo JHtml::_('select.options', $dayOptions, 'value', 'text', $this->state->get('filter.day'));
echo '</select>' . PHP_EOL;

echo '<select name="filter_range" class="inputbox" onchange="this.form.submit()">';
echo '<option value=""> - Select Range - </option>';
echo JHtml::_('select.options', $rangeOptions, 'value', 'text', $this->state->get('filter.range'));
echo '</select>' . PHP_EOL;

echo '</div>' . PHP_EOL;

echo '</div>' . PHP_EOL;

echo '<div class="table-responsive">';
//echo '<table class="table table-striped">';   // this stops an individual line being coloured
echo '<table class="table">';
// Start actual table of contents
echo '<thead>';

echo '<tr>';

echo '<th class="left">';
echo JHtml::_('grid.sort', 'Walk Date', 'a.walk_date', $sortDirn, $sortColumn);
echo '</th>';

echo '<th class="left">';
echo JHtml::_('grid.sort', 'Title', 'a.title', $sortDirn, $sortColumn);
echo '</th>';

echo '<th class="left">';
echo JHtml::_('grid.sort', 'Group', 'a.group_code', $sortDirn, $sortColumn);
echo '</th>';

echo '<th class="left">';
echo JHtml::_('grid.sort', 'Grade(N)', 'a.difficulty', $sortDirn, $sortColumn);
echo '</th>';

echo '<th class="left">';
echo JHtml::_('grid.sort', 'Grade(L)', 'a.grade_local', $sortDirn, $sortColumn);
echo '</th>';

echo '<th class="left">';
echo JHtml::_('grid.sort', 'Mi', 'a.distance_miles', $sortDirn, $sortColumn);
echo '</th>';

echo '<th class="left">';
echo JHtml::_('grid.sort', 'Pace', 'a.pace', $sortDirn, $sortColumn);
echo '</th>';

echo '<th class="left">';
echo JHtml::_('grid.sort', 'Leader', 'a.contact_display_name', $sortDirn, $sortColumn);
echo '</th>';

echo '<th class="left">';
echo 'Followers';
echo '</th>';

echo '<th class="left">';
echo 'Actions';
echo '</th>';

echo '</tr>';
echo '</thead>' . PHP_EOL;

$row_count = 0;
foreach ($this->items as $i => $item) {
    $row_count++;
    $line_colour = "";

    if ($item->max_walkers == 0) {
//      Leader has not set a limit, take from component default
        $current_max_walkers = $max_walkers;
    } else {
        $current_max_walkers = $item->max_walkers;
    }
    // Find the number of Followers to this walk
    $sql = "SELECT count(id) FROM  #__ra_walks_follow WHERE walk_id=" . $item->id;
    $count_followers = $objHelper->getValue($sql);
    $target_followers = 'index.php?option=com_ra_wf&task=walk.showFollowers&id=' . $item->id;

//  Decide which buttons are needed in the last column
    $actions = $objHelper->imageButton("I", $target_info . $item->id); // . "-" . $user_id);
    if (($item->leader_user_id > 0) and ($item->leader_user_id == $this->user_id)) {
        $line_colour = "#b0e0e6";
        // Add button with icon-cog to allow update
        $actions .= " " . $objHelper->imageButton("M", $target_update . $item->id);
        if ($count_followers > 0) {
            $email_mode = "L";
            $actions .= $objHelper->imageButton('E2', $target_email . $item->id . "&mode=" . $email_mode);
        }
    } else {
        if (($item->days_to_go > 0) and ($user_id > 0)) {         // walk is in the future
            if ($objHelper->isFollower($item->id, $user_id)) {
                $actions .= $objHelper->imageButton("X", $target_unfollow . $item->id);
            } else {
                $count_followers = $objHelper->getValue('SELECT COUNT(id) FROM #__ra_walks_follow WHERE id=' . $item->id);
                if (($max_walkers > 0) and ($count_followers >= $current_max_walkers)) {
                    $actions .= $objHelper->buildLink($target_info . $item->id, "Full", False, "link-button button-p0186");
                } else {
                    if ($item->state > 0) {
                        // this button invokes function follow in RamblersControllerWalk
                        $actions .= $objHelper->buildLink($target_follow . $item->id, "Follow", False, "link-button button-p0186");
                    }
                }
            }
        }
    }

    if (($line_colour == "") AND ($row_count % 2 == 0)) {
        $line_colour = '#f9f9f9';
    }
    if ($line_colour == "") {
        echo "<tr>";
    } else {
        echo '<tr style="background-color:' . $line_colour . '">';
    }
    $details = $item->day . ' ' . $item->walk_date . '<br>';
    if ($item->meeting_time == '') {
        //                        $details .= '--/';
    } else {
        $details .= 'M: ' . $item->meeting_time . ' / ';
    }
    if ($item->start_time == '') {
        $details .= '--';
    } else {
        $details .= 'S:' . $item->start_time;
    }
    echo '<td>' . $details . '</td>';  // $target_info . $item->id
    echo '<td>' . $objHelper->buildLink($target_info . $item->id, $item->title) . '</td>';
//    echo '<td>' . $item->title . '</td>';
    echo '<td>' . $item->group_code . '</td>';
    echo '<td>' . $item->difficulty . '</td>';
    echo '<td>' . $item->grade_local . '</td>';
    echo '<td>' . $item->distance_miles . '</td>';
    echo '<td>' . $item->pace . '</td>';
    echo '<td>' . $item->contact_display_name . '</td>';        // Open to other groups?
    // Find the number of Followers to this walk
    echo '<td>' . PHP_EOL;
    $walk_id = $item->walk_id;
    if ($count_followers == 0) {
        $details = $count_followers;
    } else {
        if (($item->leader_user_id == $this->user_id) or ($objHelper->isSuperuser())) {
            $details = $objHelper->buildLink($target_followers, $count_followers) . "/";
        } else {
            $details = $count_followers . "/";
        }
        $details .= $current_max_walkers;
    }
    echo $details;
    echo '</td>';
    echo '<td>' . $actions . '</td>' . PHP_EOL;

    echo "</tr>" . PHP_EOL;
}
echo '</tbody>';
echo '</table>';
echo '<div class="pagination center">';
echo $this->pagination->getListFooter();
echo '</div>';

echo '</div>';
echo '<input type="hidden" name="task" value="delete" />';
echo JHtml::_('form.token');
echo '<input type="hidden" name="filter_order" value="' . $this->sortColumn . '"/>';
echo '<input type="hidden" name="filter_order_Dir" value="' . $this->sortDirection . '"/>';
echo '<input type="hidden" name="boxchecked" value="0" />';
echo '</form>';
