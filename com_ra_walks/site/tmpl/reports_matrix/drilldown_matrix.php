<?php

/**
 * @version     0.1.2
 * @package     com_ra_walks
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * This is a generic program to display a report matrix about Walks held in the local database.
 *
 * Many combinations of row / column can be specified, but in each case the appropriate size
 * is calculated dynamically.

 * The dataset to be analysed will usually have been selected in advance, and is specified by the two parameters Mode and Opt,  for example "area=xx" or "weekday=sunday".

 * The "scope" of the report is used to specify whether to report on All walks, Future walks etc. At the time of writing Draft walks are not expected  but support is already provide by analysis by "state".
 *
 * Two queries are built, one to populate the column headings and the other for the individual rows of the table.
 * 05/05/21 CB Created ``
 * 02/06/21 extra code for WEEKDAY to show days in proper order, not alphabetically
 * 19/06/21 select scope
 * 21/06/21 Show national walks
 * 18/07/21 Don't use drilldown icon
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

$objHelper = new ToolsHelper;

$title = $this->component_params->get('page_title', '');
echo "<h2>Matrix for  $this->row_type / $this->col_type</h2>";
?>
<script type="text/javascript">
    function changeScope(target) {
        window.location = target + "&scope=" + document.getElementById("selectScope").value
        return true;
    }
</script>
<?php

echo '<h4>' . $this->criteria . ', ';
$target = 'index.php?option=com_ra_walks&task=reports.drilldown';

ToolsHelper::selectScope($this->scope, $target);
echo '</h4>';
$debug = 0; // set to 1 or 2 as required to debug

$col_sql = "SELECT " . $this->col_field . ' AS `col_type`, count(*) AS `record_count` ';
if ($this->col == 'W') {
    $col_sql .= ', DAYNAME(walk_date) as day_literal ';
}
$col_sql .= "from #__ra_walks as walks  ";
if (($this->mode == 'G') OR ($this->col == 'G') OR ($this->col == 'A')) {
    $col_sql .= "INNER JOIN #__ra_groups AS `groups` ON walks.group_code = `groups`.code ";
}
$col_group_by = "GROUP BY " . $this->col_field;
if ($this->col == 'W') {
    $col_group_by .= ", WEEKDAY(walk_date) ";
}
$col_group_by .= " ORDER BY " . $this->col_field;

$row_sql = 'SELECT ' . $this->row_field . ' AS  `row_type`, ' . $this->col_field . ' AS  `col_type`, count(*) AS `record_count` ';
$row_sql .= "from #__ra_walks as walks  ";
if (($this->mode == 'G') OR ($this->row == 'G') OR ($this->row == 'A') OR ($this->col == 'G')) {
    $row_sql .= "INNER JOIN #__ra_groups AS `groups` ON walks.group_code = `groups`.code ";
    if ($this->row == 'A') {
        $row_sql .= "INNER JOIN #__ra_areas AS `areas` ON groups.area_id = `areas`.id ";
    }
}
$row_group_by = "GROUP BY " . $this->row_field . ", " . $this->col_field . " ";
$row_group_by .= "ORDER BY " . $this->row_field;

if (!$this->criteria_sql == '') {
    $row_sql .= 'WHERE ' . $this->criteria_sql;
    $col_sql .= 'WHERE ' . $this->criteria_sql;
}
$row_sql .= $row_group_by;
$col_sql .= $col_group_by;
if ($debug) {
    echo 'THIS->COL=' . $this->col . '<br>';
    echo 'Where ' . $this->criteria_sql . ', Group by=' . $col_group_by . '<br>';
    echo $col_sql . '<br>';
    echo 'THIS->ROW=' . $this->row . '<br>';
    echo 'Where ' . $this->criteria_sql . ', Group by=' . $row_group_by . '<br>';
    echo $row_sql . '<br>';
}
$target = 'index.php?option=com_ra_walks&task=reports.drilldownList';
//$target .= $this->mode . '&opt=' . $this->opt . '&scope=' . $this->scope;
$objTable = new ToolsTable;
$objTable->add_column($this->row_type, "L");
$col_count = 1;    // first header description will go in Column 1
// Find details for the bottom row of the matrix, saving the record-counts and values for later use
$db = Factory::getDbo();
$query = $db->getQuery(true);
// run to query to find the column headings, and the total for each column
try {
    $db->setQuery($col_sql);
    $columns = $db->loadObjectList();
    if ($debug) {
        echo 'sql=' . $query->__toString() . '<br>';
    }

    $col_header[] = $this->row_type;
    $col_total[] = 'Total';
    foreach ($columns as $column) {
        if ($debug > 1) {
            echo $col_count . '=' . $column->col_type . ', ' . $column->record_count . '<br>';
        }
        $col_count++;
        $col_total[] = $column->record_count;
        $col_header[] = $column->col_type;
        if ($column->col_type == '') {
            $objTable->add_column('(blank)', "L");
        } else {
            // Special code to show the name, rather than a number
            if ($this->col == 'W') {
                $objTable->add_column($column->day_literal, "L");
            } else {
                $objTable->add_column($column->col_type, "L");
            }
        }
    }
    $col_count++;
    $objTable->add_column('Total', 'C');
    if ($debug > 1) {
        echo "count=$col_count Col 1=" . $col_header[1] . "<br>";
    }
    if ($col_count == 3) {
        JFactory::getApplication()->enqueueMessage('All selected records have the same value for ' . $this->col_type, 'message');
    }
    $objTable->generate_header();
} catch (Exception $e) {
    $code = $e->getCode();
    JFactory::getApplication()->enqueueMessage($code . ' ' . $e->getMessage(), 'error');
    JFactory::getApplication()->enqueueMessage('Col ' . $col_sql);
}
/*
 *
 * Now get the main data for the matrix
 *
 */
$curr_row_type = '';
echo "$row_sql<br>";
try {
    $query = $db->getQuery(true);
// Execute the main query to find the rows of the table
    $db->setQuery($row_sql);
    $records = $db->loadObjectList();
} catch (Exception $e) {
    $code = $e->getCode();
    JFactory::getApplication()->enqueueMessage($code . ' ' . $e->getMessage(), 'error');
    JFactory::getApplication()->enqueueMessage('Row ' . $row_sql);
}
$rows_count = 0;
$row_total = 0;
$col_pointer = 1;
foreach ($records as $record) {
    $rows_count++;
    if ($debug > 1) {
        echo "<b>Current:</b> $curr_row_type, " . $record->row_type . ', ' . $record->col_type . ', number=' . $record->record_count . ', Rows=' . $rows_count . '<br>';
    }
    if ($rows_count == 1) {
        $curr_row_type = $record->row_type;
        $objTable->add_item($curr_row_type);
    } else {
        if (strtoupper($record->row_type) != strtoupper($curr_row_type)) {
            // We have just changed the value in the first column: unless this
            // is the first line of the table, write out the old line
            // If necessary, add blank columns until the row is complete
            while ($col_pointer < ($col_count - 1)) {
                if ($debug > 1) {
                    echo '....adding blank to col ' . $col_pointer . '<br>';
                }
                $objTable->add_item('');
                $col_pointer++;
            }
//            echo 'adding total of ' . $row_total . ' to col ' . $col_pointer . '<br>';
            $objTable->add_item(number_format($row_total));
            $objTable->generate_line();

            $curr_row_type = $record->row_type;
            $row_total = 0;
            $col_pointer = 1;
            $objTable->add_item($curr_row_type);
        }
    }
    if ($debug > 1) {
        echo "Seeking " . $record->col_type . ', current col=' . $col_pointer . '=' . $col_header[$col_pointer] . ', Rows in table=' . $objTable->num_rows . '<br>';
    }
    if ($record->row_type == $curr_row_type) {
        // If necessary, add blank columns until the col_type we have just found
        // is the same as the current column heading

        while (strtoupper($record->col_type) > strtoupper($col_header[$col_pointer])) {
            $objTable->add_item('');
            $col_pointer++;
            //           echo "..Now " . $col_pointer . '=' . $col_header[$col_pointer] . '<br>';
            if ($col_pointer > $col_count) {
                break;
            }
        }

        if ($record->record_count == 0) {
            $objTable->add_item('');
        } else {
            //        echo 'adding ' . $row->record_count . ' to col ' . $col_header[$col_pointer] . '=' . $col_pointer . ', Rows=' . $objTable->num_rows . '<br>';
            $link = $target . '&row_value=' . ToolsHelper::convert_to_ASCII($record->row_type);
            $link .= '&col_value=' . ToolsHelper::convert_to_ASCII($col_header[$col_pointer]);
            if ($debug) {
                echo $link . '<br>';
            }
            $objTable->add_item($objHelper->buildLink($link, number_format($record->record_count)));
        }
        $col_pointer++;
        $row_total += $record->record_count;
    }
}

if ($rows_count > 0) {
// Now write out the last line
    while ($col_pointer < ($col_count - 1)) {
        //    echo '....adding blank to col ' . $col_pointer . '<br>';
        $objTable->add_item('');
        $col_pointer++;
    }
    $objTable->add_item($row_total);
    $objTable->generate_line();
// If more than one row, add the total line, using values stored earlier
    if ($rows_count > 2) {
        $row_total = 0;
        for ($col_pointer = 0; ($col_pointer < ($col_count - 1)); $col_pointer++) {
            if ($col_pointer == 0) {
                $objTable->add_item('Total');       // $col_total[0]);
            } else {
                $objTable->add_item(number_format(floatval($col_total[$col_pointer])));
            }
            $row_total += (int) $col_total[$col_pointer];
        }
        $objTable->add_item(number_format($row_total));
        $objTable->generate_line();
        if ($rows_count > 6) {

        }
    }
}
$objTable->generate_table();
if ($objTable->num_rows > 6) {
    echo ($objTable->num_rows - 2) . ' different values of <i>' . $this->row_type . '</i>';
    if ($objTable->num_columns > 4) {
        echo ', ';
    }
}
if ($objTable->num_columns > 4) {
    echo ($objTable->num_columns - 2) . ' different values of <i>' . $this->col_type . '</i>';
}
echo '<br>';
$back = "index.php?option=com_ra_walks&view=";
if ($this->mode == 'A') {
    $back .= 'reports_area&area=';
} else {
    $back .= 'reports_group&group_code=';
}
$back .= $this->opt;
echo $objHelper->backButton($back);

