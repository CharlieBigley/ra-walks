<?php

/**
 * @version     1.1.0
 * @package     com_ra_walks
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 13/05/21 created
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$toolsHelper = new RamblersHelper;
$objApp = JFactory::getApplication();

echo "<h2>Extract CSV</h2>";
echo "<h4>Scope=$this->scope_desc, $this->criteria" . "</h4>";
$db = JFactory::getDbo();

$db = JFactory::getDbo();
$query = $db->getQuery(true);
$query->select('YEAR(walk_date) as yyyy');
$query->select('MONTH(walk_date) as mm');
$query->select('count(*) AS record_count');
$query->group('YEAR(walk_date),MONTH(walk_date)');
$query->from($db->quoteName('#__ra_walks', 'walks'));     // Second parameter generates AS clause
//if (($this->mode == 'A') OR ($this->mode == 'G')) {
//    $query->innerJoin($db->quoteName('#__ra_groups', 'groups') . ' ON ' . $db->quoteName('walks.group_code') . '=' . $db->quoteName('groups.code'));
//}

$query->where($this->criteria_sql);
//$query->setLimit('10');
$target = 'index.php?option=com_ra_walks&view=reports_matrix&report_type=L&mode=';
$target .= $this->mode . '&opt=' . $this->opt . '&scope=' . $this->scope;
try {
    $db->setQuery($query);
//    echo($query->__toString()) . '<br>';
    $rows = $db->loadObjectList();
    $objTable = new Table;
    $objTable->add_column('Year', "L");
    $objTable->add_column('Month', "L");
    $objTable->add_column('Count', "L");
    $objTable->generate_header();
    $total = 0;
    foreach ($rows as $row) {
        //   echo $row->YYYYWW . ' ' . $row->b . '<br>';
        $objTable->add_item($row->yyyy);
        $objTable->add_item($row->mm);
        $link = $target . '&row=Y&row_value=' . RamblersHelper::convert_to_ASCII($row->yyyy);
        $link .= '&col=YM&col_value=' . RamblersHelper::convert_to_ASCII($row->mm);
        //            echo $link . '<br>';
        $objTable->add_item($row->record_count . $toolsHelper->imageButton('DD', $link, false, "link-button button-p5565"));
//      $objTable->add_item($row->b);
        $total = $total + $row->record_count;
        $objTable->generate_line();
    }

    $objTable->add_item('Total');
    $objTable->add_item('');
    $objTable->add_item($total);
    $objTable->generate_line();
    $objTable->generate_table();
} catch (Exception $e) {
    $code = $e->getCode();
    JFactory::getApplication()->enqueueMessage($code . ' ' . $e->getMessage(), 'error');
    JFactory::getApplication()->enqueueMessage('sql=' . (string) $query, 'message');
}

$target = "index.php?option=com_ra_walks&view=";
if ($this->mode == 'A') {
    $target .= 'reports_area&area=';
} else {
    $target .= 'reports_group&group_code=';
}
$target .= $this->opt;
echo $toolsHelper->backButton($target);

