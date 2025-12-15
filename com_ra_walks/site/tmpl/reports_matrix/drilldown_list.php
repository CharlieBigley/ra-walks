<?php

/**
 * @version     1.1.0
 * @package     com_ra_walks
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * Usually this is invoked from view reports_matrix (template reports_matrix.php), and therefore that is the target of the "Back" button.

  However, it can also be invoked from other reports, for example reports_statistics.php.
 * In such cases, the parameters being passed will include "callback",
 * so $this->callback will contain the appropriate URL.
 * 13/05/21 created
 * 10/06/21 Different code if invoked from showTopDistance
 * 21/06/21 Show national walks
 * 24/07/21 show Meet/Start times
 * 14/03/22 CB qualify title by walks table
 * 21/08/23 CB use db->replacePrefix
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

$objHelper = new ToolsHelper;
$objApp = JFactory::getApplication();
echo "<h2>Walks Drilldown detail" . "</h2>";
echo "<h4>Scope=" . $this->scope_desc . ', ' . $this->criteria;
if ($this->row != '') {
    echo ', ' . $this->row_type . "=";
//    if (($this->row == 'M') AND ($this->col == '')) {
    echo $this->row_value;
//    } else {
//        echo $this->row_value;
//    }
}
if (!$this->col == '') {
    echo ', ' . $this->col_type . "=" . $this->col_value;
}
echo '</h4>';
$db = JFactory::getDbo();

$query = $db->getQuery(true);
$query->select("DATE_FORMAT(walk_date,'%a %e-%m-%y') AS " . $db->quoteName('Date'));
$query->select($db->quoteName('meeting_time'));
$query->select($db->quoteName('start_time'));
$query->select($db->quoteName('walks.title'));
$query->select($db->quoteName('group_code'));
$query->select($db->quoteName('difficulty'));
$query->select($db->quoteName('grade_local'));
$query->select($db->quoteName('distance_miles'));
$query->select($db->quoteName('pace'));
$query->select($db->quoteName('contact_display_name'));
$query->select($db->quoteName('walks.state'));
$query->select($db->quoteName('walks.id'));
$query->from($db->quoteName('#__ra_walks', 'walks'));     // Second parameter generates AS clause
if (($this->mode == 'A') OR ($this->mode == 'G') OR ($this->col == 'G')) {
    $query->innerJoin($db->quoteName('#__ra_groups', 'groups') . ' ON ' . $db->quoteName('walks.group_code') . '=' . $db->quoteName('groups.code'));
    if ($this->mode == 'A') {
        $query->select($db->quoteName('areas.name'));
        $query->innerJoin($db->quoteName('#__ra_areas', 'areas') . ' ON ' . $db->quoteName('groups.area_id') . '=' . $db->quoteName('areas.id'));
    }
}
if ($this->criteria_sql != '') {
    $query->where($this->criteria_sql);
}
if ($this->row != '') {
    if (($this->row == 'M') AND ($this->col == '')) {
        $query->where("ROUND(distance_miles)='" . (int) $this->row_value . "'");
    } else {
        $query->where($this->row_field . "='" . $this->row_value . "'");
    }
}
if ($this->col != '') {
    $query->where($this->col_field . "='" . $this->col_value . "'");
}
$query->order('walk_date');
//if (JDEBUG) {
//    echo 'sql = ' . (string) $query;
//}
//$query->setLimit('10');
try {
    $db->setQuery($query);
    $rows = $db->loadObjectList();
    $target_info = "index.php?option=com_ra_walks&view=walk&id=";
    $objTable = new ToolsTable;
    $objTable->add_header("Date,Meet/Start,Title,Group,Diff,Grade,Miles,Pace,Leader");
    foreach ($rows as $row) {

        $objTable->add_item($row->Date);
        if ($row->meeting_time == '') {
            $details = '--/';
        } else {
            $details = $row->meeting_time . '/';
        }
        if ($row->start_time == '') {
            $details .= '--';
        } else {
            $details .= $row->start_time;
        }
        $objTable->add_item($details);
        $objTable->add_item($objHelper->buildLink($target_info . $row->id, $row->title, True));
//        $objTable->add_item($row->title);
        $objTable->add_item($row->group_code);
        $objTable->add_item($row->difficulty);
        $objTable->add_item($row->grade_local);
        $objTable->add_item($row->distance_miles);
        $objTable->add_item($row->pace);
        $objTable->add_item($row->contact_display_name);
        if ($row->state == 0) {   // walk is draft
            $line_colour = "#ffffaa";
        } elseif ($row->state == 2) {   // walk is cancelled
            $line_colour = "#ff00ff";
        } else {
            $line_colour = "";
        }
        $objTable->generate_line($line_colour);
    }
    $objTable->generate_table();
    echo 'Count=' . ($objTable->num_rows - 1) . '<br>';
    if ($objTable->num_rows == 1) {
        JFactory::getApplication()->enqueueMessage($db->replacePrefix($query));
    }
} catch (Exception $e) {
    $code = $e->getCode();
    JFactory::getApplication()->enqueueMessage($code . ' ' . $e->getMessage(), 'error');
    JFactory::getApplication()->enqueueMessage($db->replacePrefix($query));
}
if (!$this->callback == '') {
    $back = 'index.php?option=com_ra_walks&view=' . $this->callback;
} else {
    // All the required parameters will have been saved in the user state
    $back = 'index.php?option=com_ra_walks&view=reports_matrix';
}

echo $objHelper->backButton($back);
?>