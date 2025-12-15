<?php

/**
 * @version     4.0.0
 * @package     com_ra_walks (Ramblers Walks Follow)
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 04/12/2 CB created from com ramblers
 * 08/12/22 CB use Ra_wfWalk
 * 24/04/25 CB showWalksByDate
 */

namespace Ramblers\Component\Ra_walks\Site\Controller;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

/**
 * Ramblers list controller
 */
class ReportsController extends FormController {

    public $dbDatabase;
    public $dbPrefix;
    public $dbPassword;
    public $dbServer;
    public $dbUser;
    public $objMysqli;
    public $message;
    protected $criteria_sql;
    protected $db;
    protected $objApp;
    protected $objHelper;
    protected $query;
    protected $scope;

    public function __construct(array $config = array(), \Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null) {
        parent::__construct($config, $factory);
        $this->db = Factory::getDbo();
        $this->objHelper = new ToolsHelper;
        $this->objApp = Factory::getApplication();
        // Import CSS
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('ramblers', 'com_ra_tools/ramblers.css');
    }

    function countFeedback() {
        echo "<h2>Feedback by Date of walk</h2>";

        $objTable = new ToolsTable();

        $objTable->add_header("Walk Date,Title,Leader,Count");
//        $sql = "SELECT date_format(f.date_created,'%a %e-%m-%y') AS 'Date', COUNT(f.id) as 'Num' ";
//        $sql = "SELECT date_format(w.walk_date,'%a %e-%m-%y') AS 'Date', COUNT(f.id) as 'Num' ";
        $sql = "SELECT date_format(w.walk_date,'%a') AS 'weekday', ";
        $sql .= "DAYOFMONTH(w.walk_date) AS 'Day', ";
        $sql .= "MONTH(w.walk_date) as 'Month', ";
        $sql .= "YEAR(w.walk_date) as 'Year', ";
        $sql .= 'w.walk_date, w.title, w.contact_display_name, ';
        $sql .= "COUNT(f.id) as 'Num' ";
        $sql .= 'FROM #__ra_walks_feedback AS f ';
        $sql .= 'INNER JOIN #__ra_walks AS w ON w.id = f.walk_id ';
        $sql .= "GROUP BY date_format(w.walk_date, '%a'), DAYOFMONTH(w.walk_date), MONTH(w.walk_date), YEAR(w.walk_date) ";
        $sql .= 'ORDER BY walk_date DESC ';
        $sql .= 'LIMIT 20';
//        echo "$sql<br>";
        $target = "index.php?option=com_ra_walks&task=reports.showFeedback&walk_date=";
        $rows = $this->objHelper->getRows($sql);
        foreach ($rows as $row) {
            $date_parameter = $row->Year . '-' . $this->prependZero($row->Month) . '-' . $this->prependZero($row->Day);
            $date_display = $row->Day . '/' . $row->Month . '/' . $row->Year;
            $objTable->add_item($row->weekday . ' ' . $date_display);
            $objTable->add_item($row->title);
            $objTable->add_item($row->contact_display_name);
            $objTable->add_item($this->objHelper->buildLink($target . $date_parameter, $row->Num));
//            $objTable->add_item($target);
            $objTable->generate_line();
        }
        $objTable->generate_table();
//        echo $objTable->num_rows . ' Walks<br>';
        $back = "index.php?option=com_ra_walks&view=reports";
        echo $this->objHelper->backButton($back);
    }

    function countWalks() {
        $code = $this->objApp->input->getCmd('code', 'NAT');
        $callback = $this->objApp->input->getCmd('callback', 'view=walks');
        echo "<h2>Total walks by Group";
        if (strlen($code) == 2) {
            echo ' for ' . $this->objHelper->lookupArea($code);
        }
        echo "</h2>";
        $objTable = new ToolsTable();

        $objTable->add_header("Group, Count");
        $sql = "SELECT  group_code, COUNT(id) as 'Num' ";
        $sql .= 'FROM #__ra_walks ';
        if (strlen($code) == 2) {
            $sql .= 'WHERE (group_code like "' . $code . '%") ';
        }
        $sql .= 'GROUP BY group_code ';
        $sql .= 'ORDER BY group_code ';
//        echo $sql;
        $rows = $this->objHelper->getRows($sql);
        $total_walks = 0;
        foreach ($rows as $row) {
            $objTable->add_item($row->group_code);
            $objTable->add_item($row->Num);
            $total_walks += $row->Num;
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo number_format($total_walks) . ' Walks<br>';
        // Depending on from where it was invoked, control will be passed back either to reports or to reports_area

        $back = 'index.php?option=com_ra_walks&view=';
        if ($callback == '') {
            $back .= 'walks';
        } else {
            $back .= $callback . '&area=' . $code;
        }
        echo $this->objHelper->backButton($back);
    }

    public function countWalksByDate() {
        echo '<h2>Walk count by Month</h2>';

        $objTable = new ToolsTable();
        $objTable->add_header("Year, Month, Count");
        $sql = "SELECT YEAR(walk_date) AS yy, MONTH(walk_date) AS mm ";
        $sql .= 'FROM #__ra_walks ';
        $sql .= 'GROUP BY YEAR(walk_date), MONTH(walk_date) ';
        $sql .= 'ORDER BY YEAR(walk_date), MONTH(walk_date) ';
        $months = $this->objHelper->getRows($sql);
        foreach ($months as $month) {
            $objTable->add_item($month->yy);
            $objTable->add_item($month->mm);
            $sql = "SELECT COUNT(contact_display_name) AS num ";
            $sql .= 'FROM #__ra_walks ';
            $sql .= 'WHERE YEAR(walk_date)= "' . $month->yy . '" ';
            $sql .= 'AND MONTH(walk_date)= "' . $month->mm . '" ';
            $sql .= 'GROUP BY contact_display_name ';
//        $sql .= 'ORDER BY YEAR(walk_date), MONTH(walk_date) ';
            $objTable->add_item($sql);
            //           $total_walks += $row->Num;
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo $this->objHelper->backButton('index.php?option=com_ra_walks&view=reports');
    }

    public function debug() { // http://localhost/index.php?option=com_ra_walks&task=reports.debug
        $app = Factory::getApplication();
        $context = 'com_ra_walks.reports.';
        $this->mode = $app->getUserState($context . 'mode');
        $this->opt = $app->getUserState($context . 'opt');
        $this->scope = $app->getUserState($context . 'scope');
        $this->row = $app->getUserState($context . 'row');
        $this->col = $app->getUserState($context . 'col');
        echo 'mode:  ' . $app->getUserState($context . 'mode') . '<br>';
        echo 'opt: ' . $app->getUserState($context . 'mode') . '<br>';
        echo 'scope:  ' . $app->getUserState($context . 'scope') . '<br>';

        echo 'row: ' . $app->getUserState($context . 'row') . '<br>';
        echo 'row_value: ' . $app->getUserState($context . 'row_value') . '<br>';
        echo 'col:  ' . $app->getUserState($context . 'col') . '<br>';
        echo 'col_value: ' . $app->getUserState($context . 'col_value') . '<br>';
    }

    public function drilldown() {
        // Used as in inteface to the View Reports_matrix
        // Set up the parameters in UserState so it can repeat
        $app = Factory::getApplication();
        // get the input parameters

        $row = substr($app->input->getCmd('row', ''), 0, 2);
        $row_value = ToolsHelper::convert_from_ASCII($app->input->getCmd('row_value', ''));
        $col = $app->input->getCmd('col', '');
        $col_value = ToolsHelper::convert_from_ASCII($app->input->getCmd('col_value', ''));
        $report_type = $app->input->getCmd('report_type', '');
        $limit = $app->input->getCmd('limit', '20');
        $sort = $app->input->getCmd('sort', 'M');

        $context = 'com_ra_walks.reports.';
        $app->setUserState($context . 'row', $row);
//        $app->setUserState($context . 'row_value', $row_value);
        $app->setUserState($context . 'col', $col);
//        $app->setUserState($context . 'col_value', $col_value);
//        $app->setUserState($context . 'report_type', $report_type);
        $app->setUserState($context . 'limit', $limit);
        $app->setUserState($context . 'sort', $sort);
        $target = 'index.php?option=com_ra_walks&view=reports_matrix';
        if ($report_type !== '') {
            $target .= '&report_type=' . $report_type;
        }
        $this->setRedirect($target, false);
        $this->redirect();
    }

    public function drilldownList() {
        // Used as in inteface to the View Reports_matrix, layout = L
        // Set up the parameters in UserState so it can repeat
        $app = Factory::getApplication();
        // get the input parameters

        $row = substr($app->input->getCmd('row', ''), 0, 2);
        $row_value = ToolsHelper::convert_from_ASCII($app->input->getCmd('row_value', ''));
        $col = $app->input->getCmd('col', '');
        $col_value = ToolsHelper::convert_from_ASCII($app->input->getCmd('col_value', ''));
        $report_type = $app->input->getCmd('report_type', '');
        $limit = $app->input->getCmd('limit', '20');
        $sort = $app->input->getCmd('sort', 'M');

        $context = 'com_ra_walks.reports.';
        $app->setUserState($context . 'row', $row);
        $app->setUserState($context . 'row_value', $row_value);
        $app->setUserState($context . 'col', $col);
        $app->setUserState($context . 'col_value', $col_value);
        $app->setUserState($context . 'report_type', $report_type);
        $app->setUserState($context . 'limit', $limit);
        $app->setUserState($context . 'sort', $sort);
        $target = 'index.php?option=com_ra_walks&view=reports_matrix&report_type=L';

        $this->setRedirect($target, false);
        $this->redirect();
    }

    public function test() {
        $params = ComponentHelper::getParams('com_ra_mailman');
        echo 'header ' . $params->get('colour_header') . '<br>';
        echo 'body ' . $params->get('colour_body') . '<br>';
        echo 'footer ' . $params->get('colour_footer') . '<br>';
    }

    public function drilldownLocation() {
// Should show graphically on a map
        $objApp = Factory::getApplication();
        $mode = $objApp->input->getCmd('mode', '');
        $opt = $objApp->input->getCmd('opt', 0);
        $date_mode = $objApp->input->getCmd('date_mode', '');

        if ($date_mode == "F") {
            $filter = "Future walks";
            $where = " WHERE (datediff(walk_date, CURRENT_DATE) >= 0) AND ";
        } else {
            $filter = "Dates = ALL";
            $where = "WHERE ";
        }

        echo $filter;
        echo ", Mode = " . $mode;

        echo "<br>";
        $back = "index.php?option = com_ra_walks&task = reports.drilldown&date_mode = " . $date_mode . "&mode = GR4";
        switch ($mode) {
// Summary walks
            case ($mode == "GR6");
                $title = "Walk Start, 2 character gridref = ";
                $back = "index.php?option = com_ra_walks&task = reports.drilldown&date_mode = " . $date_mode . "&mode = GR2";
                $target_drilldown = "index.php?option = com_ra_walks&task = reports.drilldownLocation&date_mode = " . $date_mode . "&mode = GR8&opt = ";
                $target_display = "index.php?option = com_ra_walks&task = reports.drilldownLocation&date_mode = " . $date_mode . "&mode = GR10&opt = ";

                $sql = "SELECT SUBSTRING(start_gridref, 1, 6) as 'GR5', ";
                $sql .= "COUNT(*) ";
                $sql .= "FROM `#__ra_walks` AS `walks` ";
                $sql .= $where . " SUBSTRING(start_gridref, 1, 2) = '" . $opt . "'";
                $sql .= "GROUP BY SUBSTRING(start_gridref, 1, 6)";
//                echo $sql;
                break;
            case ($mode == "GR8");
                $title = "Walk Start, 6 character gridref = ";
                $back = "index.php?option = com_ra_walks&task = reports.drilldownLocation&date_mode = " . $date_mode . "&mode = GR6&opt = " . substr($opt, 0, 2);
                $target_drilldown = "index.php?option = com_ra_walks&task = reports.drilldownLocation&date_mode = " . $date_mode . "&mode = GR10";
                $target_display = "index.php?option = com_ra_walks&task = reports.drilldownLocation&date_mode = " . $date_mode . "&mode = GR10&opt = ";

                $sql = "SELECT SUBSTRING(start_gridref, 1, 10) as 'GR8', ";
                $sql .= "COUNT(*) ";
                $sql .= "FROM `#__ra_walks` AS `walks` ";
                $sql .= $where . " SUBSTRING(start_gridref, 1, 6) = '" . $opt . "' ";
                $sql .= "GROUP BY SUBSTRING(start_gridref, 1, 10)";

                break;
            case ($mode == "GR10");
//                echo $where . "<br>" . strlen($opt);
                if (strlen($opt) == 6) {
                    $where = "SUBSTRING(start_gridref, 1, 6) = '" . $opt . "' ";
                    $back = "index.php?option = com_ra_walks&task = reports.drilldownLocation&date_mode = " . $date_mode . "&mode = GR6&opt = " . substr($opt, 0, 2);
//                    echo $back;
                } else {
                    $where = "SUBSTRING(start_gridref, 1, 8) = '" . $opt . " ";
                    $back = "index.php?option=com_ra_walks&task=reports.drilldownLocation&date_mode=" . $date_mode . "&mode=GR8&opt=" . substr($opt, 0, 4);
                }
                $title = "Walks, Start Gridref=";
                break;
            default;
                $title = "Walks Follow";
        }
        $title .= $opt;

        $rs = "";
        $message = "";

        echo "<h2>Location Drilldown</h2>";
        echo "<h4>$title</h4>";
//        echo $sql . "<br>" . " group=" . $group;
        if ($mode == "GR10") {
            $objShow = new ShowWalks;
            $objShow->set_mode($date_mode);
            $objShow->set_state("P");
            $objShow->set_criteria($where);
            if ($this->objHelper->showQuery($sql)) {
                echo "Error: " . $this->objHelper->message;
            }
            echo $this->objHelper->backButton($back);
        } else {
// $group == 1, Aggregating by chosen database field
            $num_records = $this->objHelper->openRs($rs, $sql, $message);
            if ($num_records == 0) {
//            $rows = $this->objHelper->getRows($sql);
//            if ($this->objHelper->rows == 0) {
                echo "Program: message returned = $message";
            } else {
                $num_records = $this->objHelper->openRs($rs, $sql, $message);
                $total = 0;
                $objTable = new ToolsTable;
                $objTable->width = 40;
                $objTable->add_column("", "L");
                $objTable->add_column("Count", "C");
                $objTable->add_column("Action", "L");
                $objTable->generate_header();
//                foreach($rows as $row) {
                while ($row = mysqli_fetch_array($rs, MYSQLI_BOTH)) {
                    $objTable->add_item($row[0]);
                    $objTable->add_item($row[1]);
                    $total = $total + $row[1];
                    if ($row[0] == "") {
                        $objTable->add_item("");
                    } else {
                        if ($row[1] > 10) {
                            $link = $this->objHelper->buildLink($target_drilldown . $row[0], "Drilldown", False, "link-button button-p0159");
                        } else {
                            $link = $this->objHelper->buildLink($target_display . $row[0], "Display", False, "link-button button-p0159");
                        }
//$link = $this->objHelper->imageButton("I", $target . $row[0]);
                        $objTable->add_item($link);
                    }
                    $objTable->generate_line();
                }
                $objTable->generate_table();
            }

            if (isset($total)) {
                echo "Total " . $total;
            }
            echo $this->objHelper->backButton($back);
        }
    }

    public function guestWalks() {
        if ($callback == '') {
            $target = "index.php?option=com_ra_walks&view=reports_area&area=NAT";
        } else {
            $target = $this->objHelper::convert_from_ASCII($callback);
        }
        echo $this->objHelper->backButton($target);
    }

    public function extractAreas() {
// can onle be invoked directly: index.php?option=com_ra_walks&task=reports.extractAreas
        $sql = "SELECT nations.name AS nation, areas.name AS 'Area', areas.code, areas.website
FROM `#__ra_areas` AS areas
LEFT JOIN `#__ra_nations` AS nations ON nations.id = areas.nation_id
ORDER BY nations.name, areas.name";
        echo "<h2>Ramblers Areas</h2>";
        $objTable = new ToolsTable();
        $objTable->set_csv('areas');
        $objTable->add_header("Nation,Area,Code,Website");
        $rows = $this->objHelper->getRows($sql);
        foreach ($rows as $row) {
            $objTable->add_item($row->nation);
            $objTable->add_item($row->Area);
            $objTable->add_item($row->code);
            $objTable->add_item($row->website);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        $back = "index.php?option=com_ra_walks&view=reports";
        echo $this->objHelper->backButton($back);
    }

    public function groupsNoWalks() {
        $area = $this->objApp->input->getCmd('area', '');
        $callback = $this->objApp->input->getCmd('callback', '');

        echo "<h4>Groups without future walks in WM";
        $this->scope = 'F';  // Only interested in Future walks

        $this->query = $this->db->getQuery(true);
        $this->query->select('g.code AS group_code');
        $this->query->select('g.name AS group_name');
        $this->query->select('a.name AS area_name');
        $this->query->having('COUNT(w.id) =0');
        $this->query->from($this->db->quoteName('#__ra_walks', 'w'));     // Second parameter generates AS clause
        $this->query->rightJoin($this->db->quoteName('#__ra_groups', 'g') . ' ON ' . $this->db->quoteName('w.group_code') . '=' . $this->db->quoteName('g.code'));
        $this->query->innerJoin($this->db->quoteName('#__ra_areas', 'a') . ' ON ' . $this->db->quoteName('g.area_id') . '=' . $this->db->quoteName('a.id'));
        $this->query->group('g.name, g.code, a.name');
        $this->query->order('a.name,g.name');
        if ($area == 'NAT') {

        } else {
            if (!$area == '') {
                $this->query->WHERE("SUBSTRING(g.code,1,2)='" . substr($area, 0, 2) . "'");
                $area_name = $this->objHelper->getValue("SELECT name FROM #__ra_areas WHERE code='" . substr($area, 0, 2) . "' ");
                echo ", Area=" . $area_name;
            }
        }
        echo '</h4>';
        try {
            $this->db->setQuery($this->query);
            $rows = $this->db->loadObjectList();
            $objTable = new ToolsTable;
            $objTable->add_column('Area', "L");
            $objTable->add_column('Group', "L");
            $objTable->add_column('Code', "L");
            $objTable->generate_header();
            $total = 0;
            foreach ($rows as $row) {
                $objTable->add_item($row->area_name);
                $objTable->add_item($row->group_name);
                $objTable->add_item($row->group_code);
                $objTable->generate_line();
            }
            $objTable->generate_table();
        } catch (Exception $e) {
            $code = $e->getCode();
            Factory::getApplication()->enqueueMessage($code . ' ' . $e->getMessage(), 'error');
            Factory::getApplication()->enqueueMessage('sql=' . (string) $this->query, 'message');
        }
        echo $objTable->num_rows - 1 . ' Groups ';

        // This can be called from Report/ Report_group or reports_area
        // return as appropriate
        if ($callback == '') {
            $target = "index.php?option=com_ra_walks&view=reports";
        } else {
            $target = $this->objHelper::convert_from_ASCII($callback);
        }
        echo $this->objHelper->backButton($target);
    }

    private function prependZero($value) {
// always returns a two-character string
        if ((int) $value < 10) {
            return '0' . $value;
        } else {
            return $value;
        }
    }

    function recentEmails() {
        // Overall index of emails, most recent first
        $objHelper = new ToolsHelper;
        if (Factory::getUser()->id == 0) {
            echo 'Insufficient access';
            return;
        }
        echo "<h2>Most recent emails</h2>";
        // Find the most recent emails
        $sql = 'SELECT e.date_sent, e.record_type, t.description, ';
        $sql .= 'w.id, w.walk_date, w.title, e.user_id, p.preferred_name ';
        $sql .= 'FROM #__ra_wf_emails AS e ';
        $sql .= 'INNER JOIN #__ra_walks AS w on w.id = e.walk_id ';
        $sql .= 'left JOIN #__ra_wf_emailtypes as t ON t.Record_type = e.record_type ';
        $sql .= 'LEFT JOIN #__ra_profiles AS p on p.id = e.user_id ';
        $sql .= 'ORDER BY e.date_sent DESC, w.id DESC ';
        $sql .= 'LIMIT 50';

        $objHelper->showQuery($sql);
        echo $objHelper->generateButton("index.php?option=com_ra_walks&view=reports", "Back");
        return;
    }

    function recentFeedback() {
// Overall index of blogs, most recent first
        $objHelper = new ToolsHelper;
        if (!$objHelper->isSuperuser()) {
            echo 'Insufficient access';
            return;
        }
        echo "<h2>Most recent feedback</h2>";
        // Find the most recent feedback
        $sql = 'SELECT DATE(wf.date_created) AS BlogUpdated, w.id, w.walk_date, w.title ';
        $sql .= 'FROM #__ra_walks_feedback AS wf ';
        $sql .= 'INNER JOIN #__ra_walks AS w on w.id = wf.walk_id ';
        $sql .= 'ORDER BY DATE(wf.date_created) DESC, w.id DESC ';
        $sql .= 'LIMIT 50';

        $objHelper->showQuery($sql);
        echo $objHelper->generateButton("index.php?option=com_ra_walks&view=reports", "Back");
        return;
    }

    private function setScopeCriteria() {
        switch ($this->scope) {
            case ($this->scope == 'D');
                $this->query->where('state<>1');
                break;
            case ($this->scope == 'F');
                $this->query->where('state=1');
                $this->query->where('datediff(walk_date, CURRENT_DATE) >= 0');
                break;
            case ($this->scope == 'H');
                $this->query->where('state=1');
                $this->query->where('datediff(walk_date, CURRENT_DATE) < 0');
        }
    }

    private function setSelectionCriteria($mode, $opt) {
        if ($mode == 'G') {
            $this->query->where("w.group_code='" . $opt . "'");
        } else {
            if ($opt == 'NAT') {

            } else {
                $this->query->where("SUBSTR(w.group_code,1,2)='" . $opt . "'");
            }
        }
    }

    public function sharedWalks() {
        $back = "index.php?option=com_ra_walks&task=reports.showUserGroups";
        echo $this->objHelper->backButton($back);
    }

    public function showAreaWalks() {
// Invoked from views/area to show future walks for given Group
// 11/05/22 DOES NOT WORK - sql error
        $this->scope = $this->objApp->input->getCmd('scope', 'F');
        $code = $this->objApp->input->getCmd('code', '');

        $objShow = new ShowWalks;
        $objShow->set_mode("F");     // Future walks
        $objShow->set_state("P");    // Published walks
        $where = " group_code like '" . $code . "%' ";
        $objShow->set_criteria($where);
        echo "<h2>Future walks for Area $code</h2>";
        $objShow->showTable();
        $back = "index.php?option=com_ra_walks&view=areas";
        echo $this->objHelper->backButton($back);
    }

    function showAudit() {
        $table = $this->objApp->input->getCmd('table', '');
        $object_id = (int) $this->objApp->input->getCmd('id', '0');
        $callback = $this->objApp->input->getCmd('callback', 'view=walk_list');
//        echo "callback=$callback<br>";
        if ($table == 'ra_walks') {
            $name = 'Walks';
            $objWalk = new Ra_wfWalk;
            $objWalk->id = $object_id;
            if (!$objWalk->getData()) {
                echo $objWalk->message;
                die('Walk ' . $object_id . ' not found');
            }
            $name = $objWalk->getTitle();
        } else {
            $name = $table;
        }
        echo '<h2>Audit of changes made to ' . $name . '</h2>';
        $target = str_replace('EQ', '=', $callback);
        $target = str_replace('--', '&', $target);
//        Factory::getApplication()->enqueueMessage($callback, 'message');
        $dbTable = "#__" . $table . "_audit";
        if ($object_id > 0) {
            $SQL = "SELECT date_format(date_amended,'%d/%m/%y') as 'Date', ";
            $SQL .= "time_format(date_amended,'%H:%i') as 'Time', ";
            $SQL .= "field_name as 'Field', " . $dbTable . ".record_type as 'Action', ";
            $SQL .= "field_value as 'Change' ";
            $SQL .= 'from ' . $dbTable . ' ';
            $SQL .= 'WHERE object_id=' . $object_id . ' ORDER BY date_amended DESC';
//            echo "<br>ShowAudit: $SQL";
            $this->objHelper->showQuery($SQL);
        }
        $back = "index.php?option=com_ra_walks&" . $target;
        echo $this->objHelper->backButton($back);
    }

    public function showFeed() {
        $group_code = $this->objApp->input->getCmd('group_code', 'NS03');
        $this->scope = $this->objApp->input->getCmd('scope', 'F');
        $csv = substr($this->objApp->input->getCmd('csv', ''), 0, 1);
        echo "<h2>Feed update for " . $this->objHelper->lookupGroup($group_code) . "</h2>";

        $objTable = new ToolsTable();
        $objTable->set_csv($csv);

        $objTable->add_header("Date,Message");
        $sql = "SELECT date_amended, field_value ";
        $sql .= "FROM #__ra_groups_audit AS audit ";
        $sql .= "INNER JOIN #__ra_groups `groups` ON `groups`.id = audit.object_id ";
        $sql .= "WHERE `groups`.code='" . $group_code . "' ";
        $sql .= 'ORDER BY date_amended DESC ';
//        echo $sql;
        $rows = $this->objHelper->getRows($sql);
        foreach ($rows as $row) {
            $objTable->add_item($row->date_amended);
            $objTable->add_item($row->field_value);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        $back = "index.php?option=com_ra_walks&view=reports_group&group_code=" . $group_code . '&scope=' . $this->scope;
        echo $this->objHelper->backButton($back);
        if ($csv == '') {
            $target = "index.php?option=com_ra_walks&task=reports.showFeed&csv=feed&group_code=" . $group_code . '&scope=' . $this->scope;
            echo $this->objHelper->buildLink($target, "Extract as CSV", False, "link-button button-p0159");
        }
    }

    function showFeedback() {
        // invoked internally from countFeedback
        $walk_date = $this->objApp->input->getCmd('walk_date', 'NS03');
        echo '<h2>Feedback for walks on ' . date('D d M y', strtotime($walk_date)) . '</h2>';
        $objTable = new ToolsTable();

        $objTable->add_header("Walk Date, Leader, Walk title,Feedback Date,Feedback");
        $sql = "SELECT date_format(f.date_created,'%a %e-%m-%y') AS 'Date', ";
        $sql .= "date_format(w.walk_date,'%a %e-%m-%y') AS 'WalkDate', ";
        $sql .= "w.contact_display_name, w.title, f.comment ";
        $sql .= 'FROM #__ra_walks_feedback AS f ';
        $sql .= 'INNER JOIN #__ra_walks AS w ON w.id = f.walk_id ';
        $sql .= "WHERE w.walk_date ='" . $walk_date . "' ";
        $sql .= 'ORDER BY w.walk_date DESC, f.date_created DESC ';
        $sql .= 'LIMIT 20';
//        echo "$sql<br>";
        $rows = $this->objHelper->getRows($sql);
        foreach ($rows as $row) {
            $objTable->add_item($row->WalkDate);
            $objTable->add_item($row->contact_display_name);
            $objTable->add_item($row->title);
            $objTable->add_item($row->Date);
            $objTable->add_item($row->comment);
            $objTable->generate_line();
        }
        $objTable->generate_table();
//        echo $objTable->num_rows . ' Walks<br>';
        $back = "index.php?option=com_ra_walks&view=reports";
        echo $this->objHelper->backButton($back);
    }

    public function showFeedSummary() {
        $this->scope = $this->objApp->input->getCmd('scope', 'F');
        $csv = substr($this->objApp->input->getCmd('csv', ''), 0, 1);
        echo "<h2>Feed Summary</h2>";
        $objTable = new ToolsTable();
        $objTable->set_csv($csv);

        $objTable->add_header("Date,Message");
        $sql = "SELECT log_date, message ";
        $sql .= "FROM #__ra_logfile ";
        $sql .= "WHERE record_type='B9' AND ref=2 ";
        $sql .= 'ORDER BY log_date DESC ';
        $sql .= "Limit 28";
//        echo $sql;
        $rows = $this->objHelper->getRows($sql);
        foreach ($rows as $row) {
            $objTable->add_item($row->log_date);
            $objTable->add_item($row->message);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        $back = "index.php?option=com_ra_walks&view=reports_area&area=NAT&scope=" . $this->scope;
        echo $this->objHelper->backButton($back);
        if ($csv == '') {
            $target = "index.php?option=com_ra_walks&task=reports.showFeedSummary&csv=feedSummary";
            echo $this->objHelper->buildLink($target, "Extract as CSV", False, "link-button button-p0159");
        }
    }

    public function showFeedSummaryArea() {
        $area = $this->objApp->input->getCmd('area_code', 'NS');
        $this->scope = $this->objApp->input->getCmd('scope', '');
        $current_group = '';
        $groups_count = 0;
        $groups_found = 0;
        $area_code = 'NS';
        echo "<h2>Feed update for " . $this->objHelper->lookupArea($area) . "</h2>";
        $sql = "SELECT code from #__ra_groups WHERE code LIKE '" . $area . "%' ORDER BY code";
        $objTable = new ToolsTable();
        $objTable->add_header("Group,Date,Message");

        $groups = $this->objHelper->getRows($sql);
        $groups_count = $this->objHelper->rows;
        foreach ($groups as $group) {
            $sql = "SELECT `groups`.code, date_amended, field_value ";
            $sql .= "FROM #__ra_groups_audit AS audit ";
            $sql .= "INNER JOIN #__ra_groups `groups` ON `groups`.id = audit.object_id ";
            $sql .= "WHERE `groups`.code='" . $group->code . "' ";
            $sql .= 'ORDER BY date_amended DESC LIMIT 7';
//            echo $sql . '<br>';
            $rows = $this->objHelper->getRows($sql);
            foreach ($rows as $row) {
                if ($current_group == $row->code) {

                } else {
                    $groups_found++;
                    $current_group = $row->code;
                }
                $objTable->add_item($group->code);
                $objTable->add_item($row->date_amended);
                $objTable->add_item($row->field_value);
                $objTable->generate_line();
            }
        }

        $objTable->generate_table();
        echo $groups_found . " groups out of " . $groups_count;
        $back = "index.php?option=com_ra_walks&view=reports_area&area=" . $area . '&scope=' . $this->scope;
        echo $this->objHelper->backButton($back);
    }

    public function showFollowersByDate() {
        $sql = "SELECT date_format(walk_date, '%a %e-%m-%y') AS Date, count(w.id) AS 'Count' ";
        $sql .= "FROM #__ra_walks_follow as walk_follow ";
        $sql .= "LEFT JOIN #__ra_walks AS w ON w.id = walk_follow.walk_id ";
        $sql .= "LEFT JOIN #__ra_profiles as profiles ON profiles.id = walk_follow.user_id ";
        $sql .= "WHERE (datediff(walk_date, CURRENT_DATE) >= 0) ";
        $sql .= "GROUP BY date_format(walk_date, '%a %e-%m-%y'), walk_date ";
        $sql .= "ORDER BY walk_date";
//        echo $sql . '<br>';
        echo "<h2>Reporting</h2>";
        echo "<h4>Future Followers by date</h4>";
        $this->objHelper->showQuery($sql);
        $target = "index.php?option=com_ra_walks&view=reports";
        echo $this->objHelper->backButton($target);
    }

    public function showGroupFollowers() {
        $group_token = $this->objApp->input->getCmd('group');
        $group_code = $this->objHelper::convert_from_ASCII($group_token);
        if (strlen($group_code) == 4) {
            $group_name = $this->objHelper->lookupGroup($group_code);
        } else {
            $group_name = $group_code;
        }
        echo '<h2>Followers for ' . $group_name . '</h2>';
        $sql = "SELECT profile.preferred_name as 'member', ";
        $sql .= "profile.privacy_level ";
        $sql .= "FROM #__ra_profiles as profile ";
        $sql .= "WHERE group_code='" . $this->objHelper::convert_from_ASCII($group_token) . "' ";
        $sql .= "ORDER BY profile.preferred_name";
//        echo $sql;
        $rows = $this->objHelper->getRows($sql);
        $objTable = new ToolsTable();
        $objTable->add_header('Member');
        foreach ($rows as $row) {
            if ($row->privacy_level == 3) {
//                if ($this->objHelper->isSuperuser()) {
//                    $objTable->add_item($row->member . ' ' . $row->privacy_level);
//                } else {
                $objTable->add_item("anonymous");
            } else {
                $objTable->add_item($row->member);
            }

            $objTable->generate_line();
        }
        $objTable->generate_table();
        $back = "index.php?option=com_ra_walks&task=reports.showUserGroups";
        echo $this->objHelper->backButton($back);
    }

    public function showGroupWalks() {
// Invoked from views/area to show future walks for given Group
        $code = $this->objApp->input->getCmd('code', '');
        $objShow = new ShowWalks;
        $objShow->set_mode("F");    // Future walks
        $objShow->set_state("P");    // Published walks
        $where = " group_code='" . $code . "' ";
        $objShow->set_criteria($where);
        echo "<h2>Future walks for Group $code</h2>";
        try {
            $objShow->showTable();
            $objTable->generate_table();
        } catch (Exception $e) {
            $code = $e->getCode();
            Factory::getApplication()->enqueueMessage($code . ' ' . $e->getMessage(), 'error');
            Factory::getApplication()->enqueueMessage('sql = ' . (string) $this->query, 'message');
        }
        $back = "index.php?option=com_ra_walks&view=area&area=" . $code;
        echo $this->objHelper->backButton($back);
    }

    public function showJointLeaders() {
        $mode = $this->objApp->input->getCmd('mode', 'A');
        $opt = $this->objApp->input->getCmd('opt', 'NAT');
        $this->scope = $this->objApp->input->getCmd('scope', 'A');

        $this->query = $this->db->getQuery(true);
        $this->query->select('group_code, contact_display_name, MIN(walk_date) AS "Min", MAX(walk_date) AS "Max"');
        $this->query->from($this->db->quoteName('#__ra_walks', 'walks'));     // Second parameter generates AS clause
        $this->query->innerJoin($this->db->quoteName('#__ra_groups', 'groups') . ' ON ' . $this->db->quoteName('w.group_code') . '=' . $this->db->quoteName('groups.code'));
        $this->query->where("((INSTR(contact_display_name, '&')>0) OR (INSTR(contact_display_name, ' and ')>0))");
        $this->query->group('contact_display_name,group_code');
        $this->query->order('contact_display_name,group_code');
        $this->setScopeCriteria();
//        $this->setSelectionCriteria($mode, $opt);
        $this->db->setQuery($this->query);
//        echo (string) $this->query . '<br>';
        echo "<h2>Reporting</h2>";
        echo "<h4>Walk Leaders</h4>";
        $objTable = new ToolsTable();
        $objTable->set_csv('JointLeaders');

        $objTable->add_header("Group,Leaders,Earliest walk,Latest walk");
        $rows = $this->db->loadObjectList();
        foreach ($rows as $row) {
            $objTable->add_item($row->group_code);
            $objTable->add_item($row->contact_display_name);
            $objTable->add_item($row->Min);
            $objTable->add_item($row->Max);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        $target = "index.php?option=com_ra_walks&task=reports.showTopLeaders&opt=" . $opt . '&mode=' . $mode . '&scope=' . $this->scope;
        echo $this->objHelper->backButton($target);
    }

    public function showLeaders() {
        $mode = $this->objApp->input->getCmd('mode', 'A');
        $opt = $this->objApp->input->getCmd('opt', 'NAT');
        $this->scope = $this->objApp->input->getCmd('scope', 'A');

        $this->query = $this->db->getQuery(true);
        $this->query->select('group_code, contact_display_name, MIN(walk_date) AS "Min", MAX(walk_date) AS "Max"');
        $this->query->from($this->db->quoteName('#__ra_walks', 'walks'));     // Second parameter generates AS clause
        $this->query->innerJoin($this->db->quoteName('#__ra_groups', 'groups') . ' ON ' . $this->db->quoteName('w.group_code') . '=' . $this->db->quoteName('groups.code'));
        $this->query->group('contact_display_name,group_code');
        $this->query->order('contact_display_name,group_code');
        $this->setScopeCriteria();
//        $this->setSelectionCriteria($mode, $opt);
        $this->db->setQuery($this->query);
//        echo (string) $this->query . '<br>';
        echo "<h2>Reporting</h2>";
        echo "<h4>Walk Leaders</h4>";
        $objTable = new ToolsTable();
        $objTable->set_csv('Leaders');

        $objTable->add_header("Group,Leader,Earliest walk,Latest walk");
        $rows = $this->db->loadObjectList();
        foreach ($rows as $row) {
            $objTable->add_item($row->group_code);
            $objTable->add_item($row->contact_display_name);
            $objTable->add_item($row->Min);
            $objTable->add_item($row->Max);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        $target = "index.php?option=com_ra_walks&task=reports.showTopLeaders&opt=" . $opt . '&mode=' . $mode . '&scope=' . $this->scope;
        echo $this->objHelper->backButton($target);
    }

    function showLeaderFeedback() {
        $user = Factory::getUser();
        $user_id = 979;
        echo '<h2>Feedback for walks led by ' . $user->name . '</h2>';

        $objTable = new ToolsTable();

        $objTable->add_header("Walk Date, Walk title,Feedback Date,Feedback");
        $sql = "SELECT date_format(f.date_created,'%a %e-%m-%y') AS 'Date', ";
        $sql .= "date_format(w.walk_date,'%a %e-%m-%y') AS 'WalkDate', ";
        $sql .= "w.contact_display_name, w.title, f.comment ";
        $sql .= 'FROM #__ra_walks_feedback AS f ';
        $sql .= 'INNER JOIN #__ra_walks AS w ON w.id = f.walk_id ';
        $sql .= "WHERE w.leader_user_id ='" . $user->id . "' ";
        $sql .= 'ORDER BY w.walk_date DESC, f.date_created DESC ';
        $sql .= 'LIMIT 20';
//        echo "$sql<br>";
        $rows = $this->objHelper->getRows($sql);
        foreach ($rows as $row) {
            $objTable->add_item($row->WalkDate);
            $objTable->add_item($row->title);
            $objTable->add_item($row->Date);
            $objTable->add_item($row->comment);
            $objTable->generate_line();
        }
        $objTable->generate_table();
//        echo $objTable->num_rows . ' Walks<br>';
        $back = "index.php?option=com_ra_walks&view=walk_list";
        echo $this->objHelper->backButton($back);
    }

    public function showLogfile() {
        $offset = $this->objApp->input->getCmd('offset', '');
        $next_offset = $offset - 1;
        $previous_offset = $offset + 1;
        $rs = "";

        $date_difference = (int) $offset;
        $today = date_create(date("Y-m-d 00:00:00"));
        if ($date_difference === 0) {
            $target = $today;
        } else {
            if ($date_difference > 0) { // positive number
                $target = date_add($today, date_interval_create_from_date_string("-" . $date_difference . " days"));
            } else {
                $target = date_add($today, date_interval_create_from_date_string($date_difference . " days"));
            }
        }
        echo "<h4>Logfile records for " . date_format($target, "D d M") . "</h4>";
        $sql = "SELECT date_format(log_date, '%a %e-%m-%y') as Date, ";
        $sql .= "date_format(log_date, '%H:%i:%s.%u') as Time, ";
        $sql .= "record_type, ";
        $sql .= "ref, ";
        $sql .= "message ";
        $sql .= "FROM #__ra_logfile ";
        $sql .= "WHERE log_date >='" . date_format($target, "Y/m/d H:i:s") . "' ";
        $sql .= "AND log_date <'" . date_format($target, "Y/m/d 23:59:59") . "' ";
        $sql .= "ORDER BY log_date DESC, record_type DESC";
        if ($this->objHelper->showQuery($sql)) {
            echo "<h5>End of logfile records for " . date_format($target, "D d M") . "</h5>";
        } else {
            echo 'Error: ' . $this->objHelper->error . '<br>';
        }

        echo $this->objHelper->buildLink("index.php?option=com_ra_walks&task=reports.showLogfile&offset=" . $previous_offset, "Previous day", False, "link-button button-p5565");
        if ($next_offset >= 0) {
            echo $this->objHelper->buildLink("index.php?option=com_ra_walks&task=reports.showLogfile&offset=" . $next_offset, "Next day", False, "link-button button-p7474");
        }
        $target = "index.php?option=com_ra_walks&view=reports";
        echo $this->objHelper->backButton($target);
    }

    public function showRegisteredLeaders() {
        $this->scope = $this->objApp->input->getCmd('scope', 'F');
        $self = 'index.php?option=com_ra_walks&task=reports.showRegisteredLeaders';
        $callback = $self . '&scope = ' . $this->scope;
        ?>
        <script type = "text/javascript">
            function changeScope(target) {
                window.location = target + "&scope=" + document.getElementById("selectScope").value;
                return true;
            }
        </script>
        <?php

        $sql = "SELECT  date_format(walk_date,'%a %e-%m-%y') AS Date,";
        $sql .= "w.title as 'Walk Title', ";
        $sql .= "w.contact_display_name as 'Leader', ";
        $sql .= "w.group_code as 'Group', ";
        $sql .= "w.walk_id as WalkId, ";
        $sql .= "w.id as 'Internal',";
//        $sql .= "w.leader_user_id, ";
        $sql .= "profile.id as Ref ";
        $sql .= "FROM #__ra_walks AS w  ";
//        $sql .= "LEFT JOIN #__ra_profiles as profile ON profile.user_e = w.contact_display_name ";
        $sql .= 'LEFT JOIN #__ra_profiles as profile ON w.leader_user_id = profile.id ';
        $sql .= "WHERE (w.leader_user_id > 0) ";

        switch ($this->scope) {
            case ($this->scope == 'D');
                $sql .= "AND state=0 ";
                break;
            case ($this->scope == 'F');
                $sql .= "AND (datediff(walk_date, CURRENT_DATE) >= 0) ";
                break;
            case ($this->scope == 'H');
                $sql .= 'AND datediff(walk_date, CURRENT_DATE) < 0 ';
        }

        $sql .= "order by walk_date";
        echo "<h2>Reporting</h2>";
        echo "<h4>Walk Leaders who are registered</h4>";
        $target = 'index.php?option=com_ra_walks&task=reports.showRegisteredLeaders';
        ToolsHelper::selectScope($this->scope, $target);
        echo '<br>';

        $this->objHelper->showQuery($sql);
        $target = "index.php?option=com_ra_walks&view=reports";
        echo $this->objHelper->backButton($target);
    }

    public function showSummary() {
        $csv = substr($this->objApp->input->getCmd('csv', ''), 0, 1);
        $group_code = $this->objApp->input->getCmd('group_code', 'NS03');
        $scope = $this->objApp->input->getCmd('scope', 'F');
        echo "<h2>Walks history for " . $this->objHelper->lookupGroup($group_code) . "</h2>";
        $objTable = new ToolsTable();
        if ($csv === 'Y') {
            $objTable->set_csv('Summary');
        }
        $objTable->add_header("Month, Total walks,Joint walks,Guest walks,Total leaders,Total miles, Min miles,Max miles,Avg miles");
        $sql = "SELECT ym,num_walks,joint_walks,guest_walks, ";
        $sql .= "num_leaders,total_miles,min_miles,max_miles,avg_miles ";
        $sql .= "FROM #__ra_snapshot ";
        $sql .= "WHERE group_code='" . $group_code . "' ";
        $sql .= 'ORDER BY ym ';
//        echo $sql;
        $rows = $this->objHelper->getRows($sql);
        $total_miles = 0;
        $total_walks = 0;
        foreach ($rows as $row) {
            $total_miles += $row->total_miles;
            $total_walks += $row->num_walks;

            $objTable->add_item($row->ym);
            if ($row->num_walks == 0) {
                $objTable->add_item('');
            } else {
//                $target = 'index.php?option=com_ra_walks&view=reports_matrix&mode=G&row=M&col=W&opt=' . $group_code;
//                $objTable->add_item($this->objHelper->buildLink($target, $row->num_walks));
                $objTable->add_item($row->num_walks);
            }
            $objTable->add_item(number_format($row->joint_walks));
            $objTable->add_item($row->guest_walks);
            $objTable->add_item($row->num_leaders);
            $objTable->add_item($row->total_miles);
            $objTable->add_item($row->min_miles);
            $objTable->add_item($row->max_miles);
            $objTable->add_item($row->avg_miles);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo 'Total walks: ' . $total_walks . ', Total miles: ' . $total_miles . '<br>';

        $back = "index.php?option=com_ra_walks&view=reports_group&group_code=" . $group_code . '&scope=' . $scope;
        echo $this->objHelper->backButton($back);
        if (!$csv == 'Y') {
            $target = "index.php?option=com_ra_walks&task=reports.showSummary&csv=Y&group_code=" . $group_code;
            echo $this->objHelper->buildLink($target, "Extract as CSV", False, "link-button button-p0159");
        }
    }

    function showTopAreas() {
        $this->scope = $this->objApp->input->getCmd('scope', '');
        $limit = $this->objApp->input->getCmd('limit', '20');
        $sort = $this->objApp->input->getCmd('sort', 'M');
        $csv = substr($this->objApp->input->getCmd('csv', ''), 0, 1);
        $self = 'index.php?option=com_ra_walks&task=reports.showTopAreas';
        $callback = $self . '&limit=' . $limit . '&sort=' . $sort . '&scope=' . $this->scope;
        ?>

        <script type="text/javascript">
            function changeLimit(target) {
                window.location = target + "&limit=" + document.getElementById("selectLimit").value
                return true;
            }
        </script>
        <script type="text/javascript">
            function changeScope(target) {
                window.location = target + "&scope=" + document.getElementById("selectScope").value
                return true;
            }
        </script>
        <script type="text/javascript">
            function changeSort(target) {
                window.location = target + "&sort=" + document.getElementById("selectSort").value
                return true;
            }
        </script>

        <?php

        echo "<h2>Top $limit Areas for walks</h2>";
        $target = "/index.php?option=com_ra_walks&task=reports.showTopAreas&limit=" . $limit . '&scope=' . $this->scope;
        $options[] = 'Sort by average distance';
        $options_value[] = 'A';
        $options[] = 'Sort by number of Leaders';
        $options_value[] = 'L';
        $options[] = 'Sort by number of walks';
        $options_value[] = 'N';
        $options[] = 'Sort by total miles';
        $options_value[] = 'M';

        echo 'Sort sequence<a NAME=top>:</a> <select id = selectSort name = sort onChange = "changeSort(' . chr(39) . $target . chr(39) . ')">';
        for ($i = 0; $i < 4; $i++) {
            echo '<option value = ' . $options_value[$i];
            if ($options_value[$i] == $sort) {              // Future walks
                echo ' selected';
            }
            echo '>' . $options[$i];
            echo '</option>';
        }
        echo '</select> ';

        $target = '/index.php?option=com_ra_walks&task=reports.showTopAreas&scope=' . $this->scope . '&limit=' . $limit;
        ToolsHelper::selectLimit($limit, $target);

        $target = 'index.php?option=com_ra_walks&task=reports.showTopAreas&limit=' . $limit . '&sort=' . $sort;
        ToolsHelper::selectScope($this->scope, $target);
        echo '<br>';
        $this->query = $this->db->getQuery(true);
        $this->query->select('areas.code');
        $this->query->select('areas.name AS area_name');
        $this->query->select('COUNT(*) AS NumWalks');
        $this->query->select('MIN(distance_miles) AS Min');
        $this->query->select('MAX(distance_miles) AS Max');
        $this->query->select('AVG(distance_miles) AS Avg');
        $this->query->select('SUM(distance_miles) AS Sum');
        $this->query->select('COUNT(DISTINCT contact_display_name) AS NumLeaders');
        $this->query->from($this->db->quoteName('#__ra_walks', 'w'));     // Second parameter generates AS clause
        $this->query->innerJoin($this->db->quoteName('#__ra_areas', 'areas') . ' ON SUBSTRING(w.group_code,1,2)=' . $this->db->quoteName('areas.code'));
        $this->query->group('areas.name');
        if ($sort == 'A') {
            $this->query->order('AVG(distance_miles) DESC');
        } elseif ($sort == 'M') {
            $this->query->order('SUM(distance_miles) DESC');
        } elseif ($sort == 'L') {
            $this->query->order('COUNT(DISTINCT contact_display_name) DESC');
        } else {
            $this->query->order('COUNT(*) DESC');
        }
        $this->setScopeCriteria();
        $this->query->setLimit($limit);
        try {
            $this->db->setQuery($this->query);
            $rows = $this->db->loadObjectList();
            $objTable = new ToolsTable;
            $headings = 'Area,Total miles,Min,Max,Average,Count walks,Count Leaders,Walks per leader';
            $objTable->add_header($headings);
            $objTable->set_csv($csv);
            $total = 0;
            $drilldown_walks = 'index.php?option=com_ra_walks&view=reports_matrix&report_type=L';
            $drilldown_walks .= '&scope=' . $this->scope . '&mode=A&opt=';
            foreach ($rows as $row) {
                $objTable->add_item($row->area_name);
                $objTable->add_item(number_format($row->Sum));
//                $target = $drilldown_walks . $row->code;
                $target = $drilldown_walks . $row->code . '&row=M&row_value=' . ToolsHelper::convert_to_ASCII($row->Min);
                $target .= '&callback=' . ToolsHelper::convert_to_ASCII($callback);
                $objTable->add_item($this->objHelper->buildLink($target, $row->Min));

                $target = $drilldown_walks . $row->code . '&row=M&row_value=' . ToolsHelper::convert_to_ASCII($row->Max);
                $target .= '&callback=' . ToolsHelper::convert_to_ASCII($callback);
                $objTable->add_item($this->objHelper->buildLink($target, $row->Max));

                $average = (round($row->Avg * 10)) / 10;
                $objTable->add_item($average);

                $target = $drilldown_walks . $row->code;
                $target .= '&callback=' . ToolsHelper::convert_to_ASCII($callback);
                $objTable->add_item($this->objHelper->buildLink($target, $row->NumWalks));

                $objTable->add_item($row->NumLeaders);
                $objTable->add_item((round(($row->NumWalks * 10) / $row->NumLeaders)) / 10);
                $objTable->generate_line();
            }
            $objTable->generate_table();
        } catch (Exception $e) {
            $code = $e->getCode();
            Factory::getApplication()->enqueueMessage($code . ' ' . $e->getMessage(), 'error');
            Factory::getApplication()->enqueueMessage('sql=' . (string) $this->query, 'message');
        }
        $self .= '&scope=' . $this->scope . '&sort=' . $sort . '&limit=' . $limit;
        if ($csv == '') {
            $target = $self . "&csv=TopAreas";
            echo $this->objHelper->buildLink($target, "Extract as CSV", false, "link-button button-p0159");
        }
        $target = 'index.php?option=com_ra_walks&view=reports_area&area=NAT&scope=' . $this->scope;
        echo $this->objHelper->backButton($target);
        //echo '<a href="#top">Top</a>';
        echo $this->objHelper->anchor();
    }

    function showTopGroups() {
        $this->scope = $this->objApp->input->getCmd('scope', '');
        $limit = $this->objApp->input->getCmd('limit', '20');
        $sort = $this->objApp->input->getCmd('sort', 'M');
        $self = 'index.php?option=com_ra_walks&task=reports.showTopGroups';
        $callback = $self . '&limit=' . $limit . '&sort=' . $sort . '&scope=' . $this->scope;
        ?>
        <script type="text/javascript">
            function changeLimit(target) {
                window.location = target + "&limit=" + document.getElementById("selectLimit").value;
                return true;
            }
        </script>
        <script type="text/javascript">
            function changeScope(target) {
                window.location = target + "&scope=" + document.getElementById("selectScope").value;
                return true;
            }
        </script>
        <script type="text/javascript">
            function changeSort(target) {
                window.location = target + "&sort=" + document.getElementById("selectSort").value;
                return true;
            }
        </script>
        <?php

        echo "<h2>Top $limit Groups for walks</h2>";
        $options[] = 'Sort by average distance';
        $options_value[] = 'A';
        $options[] = 'Sort by Number of Leaders';
        $options_value[] = 'L';
        $options[] = 'Sort by number of walks';
        $options_value[] = 'N';
        $options[] = 'Sort by total miles';
        $options_value[] = 'M';

        $target = "index.php?option=com_ra_walks&task=reports.showTopGroups&scope=" . $this->scope . '&limit=' . $limit;
        echo 'Sort sequence: <select id=selectSort name=sort onChange="changeSort(' . chr(39) . $target . chr(39) . ')">';
        for ($i = 0; $i < 4; $i++) {
            echo '<option value=' . $options_value[$i];
            if ($options_value[$i] == $sort) {              // Future walks
                echo ' selected';
            }
            echo '>' . $options[$i];
            echo '</option>';
        }
        echo '</select> ';
        $target = $self . '&scope=' . $this->scope . '&limit=' . $limit;
        ToolsHelper::selectLimit($limit, $target);

        $target = $self . '&limit=' . $limit . '&sort=' . $sort;
        ToolsHelper::selectScope($this->scope, $target);
        echo '<br>';
        $this->query = $this->db->getQuery(true);
        $this->query->select('groups.code');
        $this->query->select('groups.name AS group_name');
        $this->query->select('COUNT(*) AS NumWalks');
        $this->query->select('MIN(distance_miles) AS Min');
        $this->query->select('MAX(distance_miles) AS Max');
        $this->query->select('AVG(distance_miles) AS Avg');
        $this->query->select('SUM(distance_miles) AS Sum');
        $this->query->from($this->db->quoteName('#__ra_walks', 'w'));     // Second parameter generates AS clause
        $this->query->innerJoin($this->db->quoteName('#__ra_groups', 'groups') . ' ON ' . $this->db->quoteName('w.group_code') . '=' . $this->db->quoteName('groups.code'));
        $this->query->group('code,groups.name');
        $this->setScopeCriteria();
        if ($sort == 'A') {
            $this->query->order('AVG(distance_miles) DESC');
        } elseif ($sort == 'M') {
            $this->query->order('SUM(distance_miles) DESC');
        } elseif ($sort == 'L') {
            $this->query->order('COUNT(DISTINCT contact_display_name) DESC');
        } else {
            $this->query->order('COUNT(*) DESC');
        }
        $this->query->setLimit($limit);
        try {
            $this->db->setQuery($this->query);
            $rows = $this->db->loadObjectList();
            $objTable = new ToolsTable;
            $headings = 'Group,Count,Min,Max,Average,Total miles';
            $objTable->add_header($headings);
            $drilldown_walks = 'index.php?option=com_ra_walks&view=reports_matrix&report_type=L';
            $drilldown_walks .= '&scope=' . $this->scope . '&mode=G&opt=';
            foreach ($rows as $row) {
                $objTable->add_item($row->group_name);
                $target = $drilldown_walks . $row->code;
                $target .= '&callback=' . ToolsHelper::convert_to_ASCII($callback);
                $objTable->add_item($this->objHelper->buildLink($target, $row->NumWalks));
                $target = $drilldown_walks . $row->code . '&row=M&row_value=' . ToolsHelper::convert_to_ASCII(round($row->Min));
                $target .= '&callback=' . ToolsHelper::convert_to_ASCII($callback);
                $objTable->add_item($this->objHelper->buildLink($target, $row->Min));
                $target = $drilldown_walks . $row->code . '&row=M&row_value=' . ToolsHelper::convert_to_ASCII(round($row->Max));
                $target .= '&callback=' . ToolsHelper::convert_to_ASCII($callback);
                $objTable->add_item($this->objHelper->buildLink($target, $row->Max));
                $average = (round($row->Avg * 10)) / 10;
                $objTable->add_item($average);
                $objTable->add_item(number_format($row->Sum)); //
                $objTable->generate_line();
            }
            $objTable->generate_table();
        } catch (Exception $e) {
            $code = $e->getCode();
            Factory::getApplication()->enqueueMessage($code . ' ' . $e->getMessage(), 'error');
            Factory::getApplication()->enqueueMessage('sql=' . (string) $this->query, 'message');
        }
//$target = "index.php?option=com_ra_walks&view=reports";
        $target = "index.php?option=com_ra_walks&view=reports_area&area=NAT&scope=" . $this->scope;
        echo $this->objHelper->backButton($target);
    }

    function showTopLeaders() {
// Can show Top Leaders: Nationally, for Area or for a Group
// Mode = (M)iles, (N)umber or (L)eaders

        $this->scope = $this->objApp->input->getWord('scope', 'F');
        $limit = $this->objApp->input->getInt('limit', '20');
        $mode = $this->objApp->input->getWord('mode', 'A');
        $opt = $this->objApp->input->getWord('opt', 'NAT');
        $sort = $this->objApp->input->getWord('sort', 'M');
        $scallback = $this->objApp->input->getWord('callback', '');
//        $self = 'index.php?option=com_ra_walks&task=reports.showTopLeaders';

        $current_uri = Uri::getInstance()->toString();
        echo $current_uri . '<br>';
//      set callback in globals so $drilldown_walks can return as appropriate
        Factory::getApplication()->setUserState('com_ra_walks.callback_matrix', $current_uri);
        ?>

        <script type="text/javascript">
            function changeLimit(target) {
                window.location = target + "&limit=" + document.getElementById("selectLimit").value;
                return true;
            }
        </script>
        <script type="text/javascript">
            function changeScope(target) {
                window.location = target + "&scope=" + document.getElementById("selectScope").value;
                return true;
            }
        </script>
        <script type="text/javascript">
            function changeSort(target) {
                window.location = target + "&sort=" + document.getElementById("selectSort").value;
                return true;
            }
        </script>
        <?php

//      find total number of walks
        $this->query = $this->db->getQuery(true);
        $this->query->select('COUNT(w.id) AS NumWalks');
        $this->query->from($this->db->quoteName('#__ra_walks', 'w'));     // Second parameter generates AS clause
//        $this->query->innerJoin($this->db->quoteName('#__ra_groups', 'groups') . ' ON ' . $this->db->quoteName('w.group_code') . '=' . $this->db->quoteName('groups.code'));
        $this->setScopeCriteria();
        $this->setSelectionCriteria($mode, $opt);
//        echo (string) $this->query . '<br>';
        $this->db->setQuery($this->query);
        $total = $this->db->loadResult();
        echo 'Total number of walks: <b>' . number_format($total) . '</b><br>';

        // find total number of leaders
        $this->query = $this->db->getQuery(true);
        $this->query->select('COUNT(DISTINCT contact_display_name)');
        $this->query->from($this->db->quoteName('#__ra_walks', 'w'));     // Second parameter generates AS clause
//        $this->query->innerJoin($this->db->quoteName('#__ra_groups', 'groups') . ' ON ' . $this->db->quoteName('w.organising_group') . '=' . $this->db->quoteName('groups.code'));
        $this->setScopeCriteria();
        $this->setSelectionCriteria($mode, $opt);
        $this->db->setQuery($this->query);
        $count = $this->db->loadResult();
        echo 'Total number of Leaders: ';
        if ($this->objHelper->isSuperuser()) {
            $target = 'index.php?option=com_ra_walks&task=reports.showLeaders&mode=' . $mode . '&opt=' . $opt . '&scope=' . $this->scope;
            echo $this->objHelper->buildLink($target, number_format($count));
        } else {
            echo number_format($count);
        }
        if ($count > 0) {
            echo ', Walks per leader: ' . (round(($total * 10) / $count)) / 10 . '<br>';
        }

        // Find walks with two leaders
        $this->query = $this->db->getQuery(true);
        $this->query->select('COUNT(w.id) AS NumWalks');
        $this->query->from($this->db->quoteName('#__ra_walks', 'w'));     // Second parameter generates AS clause
//        $this->query->innerJoin($this->db->quoteName('#__ra_groups', 'groups') . ' ON ' . $this->db->quoteName('w.organising_group') . '=' . $this->db->quoteName('groups.code'));
        $this->setScopeCriteria();
        $this->setSelectionCriteria($mode, $opt);
        $this->query->where("((INSTR(contact_display_name, '&')>0) OR (INSTR(contact_display_name, ' and ')>0))");
        $this->db->setQuery($this->query);
        $count = $this->db->loadResult();
        echo 'Two Leaders: ';
        if ($this->objHelper->isSuperuser()) {
            $target = 'index.php?option=com_ra_walks&task=reports.showJointLeaders&mode=' . $mode . '&opt=' . $opt . '&scope=' . $this->scope;
            echo $this->objHelper->buildLink($target, number_format($count));
        } else {
            echo number_format($count);
        }

        $this->query = $this->db->getQuery(true);
        $this->query->select('COUNT(w.id) AS NumWalks');
        $this->query->from($this->db->quoteName('#__ra_walks', 'w'));     // Second parameter generates AS clause
//        $this->query->innerJoin($this->db->quoteName('#__ra_groups', 'groups') . ' ON ' . $this->db->quoteName('groups.code') . '=' . $this->db->quoteName('w.organising_group'));
        $this->setScopeCriteria();
        $this->setSelectionCriteria($mode, $opt);
        $this->query->where("(INSTR(contact_display_name, ' ')=0)");
        $this->db->setQuery($this->query);
        //       echo $this->query;
        $count = $this->db->loadResult();
        if ($count > 0) {
            echo ', Single name: ' . number_format($count) . ', ' . round($count * 100 / $total) . '%<br>';
        }
        echo "<h2>Top $limit Walk leaders, ";
        $back = "index.php?option=com_ra_walks&view=";
        if ($mode == 'G') {
            echo 'Group=' . $opt . ' ' . $this->objHelper->lookupGroup($opt);
            $back .= "reports_group&group_code=$opt";
        } else {
            if ($opt == 'NAT') {
                echo 'National';
                $back .= "reports_area&area=NAT";
            } else {
                echo 'Area=' . $opt . ' ' . $this->objHelper->lookupArea($opt);
                $back .= "reports_area&area=$opt";
            }
        }
        $back .= "&scope=" . $this->scope;
        echo '</h2>';
        $options[] = 'Sort by average distance';
        $options_value[] = 'A';
        $options[] = 'Sort by number of walks';
        $options_value[] = 'N';
        $options[] = 'Sort by total miles';
        $options_value[] = 'M';
        $target = "/index.php?option=com_ra_walks&task=reports.showTopLeaders";
        $target .= "&mode=$mode&opt=$opt";
        $target .= "&limit=$limit&scope=" . $this->scope;
        echo 'Sort sequence: <select id=selectSort name=sort onChange="changeSort(' . chr(39) . $target . chr(39) . ')">';
        for ($i = 0; $i < 3; $i++) {
            echo '<option value=' . $options_value[$i];
            if ($options_value[$i] == $sort) {
                echo ' selected';
            }
            echo '>' . $options[$i];
            echo '</option>';
        }
        echo '</select> ';
        $target = "/index.php?option=com_ra_walks&task=reports.showTopLeaders";
        $target .= "&mode=$mode&opt=$opt";
        $target .= "&sort=$sort&scope=" . $this->scope;
        ToolsHelper::selectLimit($limit, $target);

        $target = "/index.php?option=com_ra_walks&task=reports.showTopLeaders";
        $target .= "&mode=$mode&opt=$opt";
        $target .= "&sort=$sort&limit$limit";
        ToolsHelper::selectScope($this->scope, $target);
//        echo '<br>';

        $this->query = $this->db->getQuery(true);
        $this->query->select('w.group_code, contact_display_name AS leader');
        $this->query->select('SUM(distance_miles) AS Sum');
        $this->query->select('MIN(distance_miles) AS Min');
        $this->query->select('MAX(distance_miles) AS Max');
        $this->query->select('AVG(distance_miles) AS Avg');
        $this->query->select('COUNT(*) AS NumWalks');

        $this->query->from($this->db->quoteName('#__ra_walks', 'w'));     // Second parameter generates AS clause
//        $this->query->innerJoin($this->db->quoteName('#__ra_groups', 'groups') . ' ON ' . $this->db->quoteName('w.organising_group') . '=' . $this->db->quoteName('groups.code'));
        $this->setScopeCriteria();
        $this->setSelectionCriteria($mode, $opt);
        $this->query->where("contact_display_name>''");
        $this->query->group('group_code,contact_display_name');
        if ($sort == 'A') {
            $this->query->order('AVG(distance_miles) DESC, contact_display_name');
        } elseif ($sort == 'M') {
            $this->query->order('SUM(distance_miles) DESC, contact_display_name');
        } else {
            $this->query->order('COUNT(*) DESC, SUM(distance_miles) DESC, contact_display_name');
        }
        if (JDEBUG) {
            Factory::getApplication()->enqueueMessage((string) $this->query, 'message');
        }
        $this->query->setLimit($limit);
        try {
            $this->db->setQuery($this->query);
            $rows = $this->db->loadObjectList();
            $objTable = new ToolsTable;
            $headings = 'Group,Leader,Total miles,Min,Max,Average,Count walks';
            $objTable->add_header($headings);
            $drilldown_walks = 'index.php?option=com_ra_walks&view=reports_matrix&report_type=L';
            $drilldown_walks .= '&scope=' . $this->scope . '&mode=WL&opt=';
            foreach ($rows as $row) {
                $objTable->add_item($row->group_code);
                $objTable->add_item($row->leader);
                $objTable->add_item($row->Sum);
                $objTable->add_item($row->Min);
                $objTable->add_item($row->Max);
                $average = (round($row->Avg * 10)) / 10;
                $objTable->add_item($average);

                $target = $drilldown_walks . ToolsHelper::convert_to_ASCII($row->leader);
                $target .= '&col=C&col_value=' . ToolsHelper::convert_to_ASCII($row->groups_code);
//                $target .= '&callback=' . ToolsHelper::convert_to_ASCII($callback);
                $objTable->add_item($this->objHelper->buildLink($target, $row->NumWalks));
                // http://localhost/index.php?option=com_ra_walks&view=reports_matrix&report_type=L&scope=A&mode=WL&opt=076105122032068046&col=C&
                // col_value=078083048054&callback=105110100101120046112104112063111112116105111110061099111109095114097095119097108107115038116097115107061114101112111114116115046115104111119084111112076101097100101114115038108105109105116061050048038115111114116061077038115099111112101061065038109111100101061065038111112116061078083

                $objTable->generate_line();
            }
            $objTable->generate_table();
        } catch (Exception $e) {
            $code = $e->getCode();
            Factory::getApplication()->enqueueMessage($code . ' ' . $e->getMessage(), 'error');
            Factory::getApplication()->enqueueMessage('sql=' . (string) $this->query, 'message');
        }
        $callback = $this->objApp->GetUserState('com_ra_walks.reports.topleaders');
        if ($callback == 'reports_group') {
            $back = 'index.php?option=com_ra_walks&view=' . $callback . '&group_code=' . $opt;
        } else {
            $back = 'index.php?option=com_ra_walks&view=' . $callback . '&area=' . $opt;
        }
        echo $this->objHelper->backButton($back);
    }

    function showUserGroups() {
        $sql = "Select home_group as 'Group', COUNT(id) as 'Number' ";
        $sql .= "from #__ra_profiles ";
        $sql .= "WHERE home_group > '' ";
        $sql .= "Group by home_group ";
        $sql .= "Order by home_group ";
        echo "<h2>Groups and Users</h2>";
        $rows = $this->objHelper->getRows($sql);
        $target = 'index.php?option=com_ra_walks&task=reports.showGroupFollowers&group=';
        $objTable = new ToolsTable();

        echo "<table class='$this->tableClass'>";
        echo RHtml::addTableHeader(array("Group", "Count members"));
        foreach ($rows as $row) {
            $link = $this->objHelper->buildLink($target . $this->objHelper::convert_to_ASCII($row->Group), $row->Number);
            echo RHtml::addTableRow(array($row->Group, $link));
        }
        echo "</table>" . PHP_EOL;
// showGroupFollowers
        $target = "index.php?option=com_ra_walks&view=reports";
        echo $this->objHelper->backButton($target);
    }

    function showUsers() {

        $sql = "Select profile.id, profile.preferred_name as 'Display name', ";
        $sql .= "users.email as 'email', ";
        $sql .= "groups_to_follow as 'Groups', ";
        $sql .= "privacy_level as 'Privacy level', ";
        $sql .= "acknowledge_follow as 'Acknowledge Follow', ";
//        $sql .= "contact_email as 'Email', ";
        $sql .= "contactviaemail as 'Contact by email', ";
        $sql .= "mobile as 'Mobile' ";
        $sql .= "FROM #__ra_profiles as profile ";
        $sql .= "LEFT JOIN #__users as users ON profile.id = users.id ";
        $sql .= "WHERE home_group>''";
        $sql .= "order by profile.id";
        echo "<h2>Reporting</h2>";
        echo "<h4>Users</h4>";
        $this->objHelper->showQuery($sql);
        $target = "index.php?option=com_ra_walks&view=reports";
        echo $this->objHelper->backButton($target);
    }

    function walksAudit() {
        $offset = jRequest::getString("offset");
        $next_offset = $offset - 1;
        $previous_offset = $offset + 1;
        $rs = "";

        $date_difference = (int) $offset;
        $today = date_create(date("Y-m-d 00:00:00"));
        if ($date_difference === 0) {
            $target = $today;
        } else {
            if ($date_difference > 0) { // positive number
                $target = date_add($today, date_interval_create_from_date_string("-" . $date_difference . " days"));
            } else {
                $target = date_add($today, date_interval_create_from_date_string($date_difference . " days"));
            }
        }
        echo "<h4>Walks updated " . date_format($target, "D d M") . "</h4>";
        $sql = "SELECT date_format(date_amended, '%d/%m/%y') as 'Date', ";
        $sql .= "time_format(date_amended, '%H:%i') as 'Time', ";
        $sql .= "w.walk_id as 'Walk', ";
        $sql .= "date_format(w.walk_date, '%d/%m/%y') as 'WalkDate', ";
        $sql .= "w.group_code as 'Group', ";
        $sql .= "w.title as 'Title', ";
        $sql .= "w.contact_display_name as 'Leader', ";
        $sql .= "field_name as 'Field', record_type as 'Action', ";
        $sql .= "field_value as 'Change' ";
        $sql .= "from #__ra_walks_audit as walks_audit ";
        $sql .= "INNER JOIN #__ra_walks AS w ON w.id = walks_audit.object_id ";
        $sql .= "WHERE date_amended >='" . date_format($target, "Y/m/d H:i:s") . "' ";
        $sql .= "AND date_amended <'" . date_format($target, "Y/m/d 23:59:59") . "' ";
        $sql .= "ORDER BY date_amended DESC, record_type ";
//        $sql .= "LIMIT 10";
        if ($this->objHelper->showQuery($sql)) {

        } else {
            echo "Error: " . $this->objHelper->error;
        }
//echo "<h5>End of audit records for " . date_format($target, "D d M") . "</h5>";
        echo $this->objHelper->buildLink("index.php?option=com_ra_walks&task=reports.walksAudit&offset=$previous_offset", "Previous day", False, "link-button button-p5565") . " ";
        if ($next_offset >= 0) {
            echo $this->objHelper->buildLink("index.php?option=com_ra_walks&task=reports.walksAudit&offset=$next_offset", "Next day", False, "link-button button-p7474");
        }
        $target = "index.php?option=com_ra_walks&view=reports";
        echo $this->objHelper->backButton($target);
    }

    public function walksByDate() {
        $helper = new ToolsHelper;
        $field = 'walk_date';
        $table = ' #__ra_walks';
        $criteria = '';
        $title = 'Walks by month';
        $link = '';

        $back = "index.php?option=com_ra_walks&view=reports";
        $helper->showDateMatrix($field, $table, $criteria, $title, $link, $back);
    }

    function walkLeaders() {
        $this->objHelper = new ToolsHelper;

        $sql = "Select group_code, contact_display_name, contact_tel1, ";
        $sql .= "contact_tel2, contact_email ";
        $sql .= "from #__ra_walks ";
        $sql .= "WHERE group_code = 'NS03' ";
        $sql .= "GROUP BY group_code, contact_display_name, contact_tel2, contact_tel1, contact_email ";
        $sql .= "ORDER BY group_code, contact_display_name ";
//        $sql .= "LIMIT 100";
        echo "<h4>Walk Leaders</h4>";
        $rows = $this->objHelper->getRows($sql);
//        $target = 'index.php?option=com_ra_walks&task=reports.showGroupFollowers&group=';
        $objTable = new ToolsTable();
//        $objTable->set_csv(True);
        $objTable->add_header('Group,Name, Tel1, Tel2, email');
        foreach ($rows as $row) {
            $objTable->add_item($row->group_code);
            $objTable->add_item($row->contact_display_name);
            $objTable->add_item($row->contact_tel1);
            $objTable->add_item($row->contact_tel2);
            $objTable->add_item($row->contact_email);
//            $link = $this->objHelper->buildLink($target . $this->objHelper::convert_to_ASCII($row->Group), $row->Number);
//            $objTable->add_item($link);
            $objTable->generate_line();
        }
        $objTable->generate_table();
// showGroupFollowers
        $target = "index.php?option=com_ra_walks&view=reports";
        echo $this->objHelper->backButton($target);
    }

    function walksFollowers() {
        $this->scope = $this->objApp->input->getCmd('scope', 'F');
        $self = 'index.php?option=com_ra_walks&task=reports.walksFollowers';
        $callback = $self . '&scope=' . $this->scope;
        ?>
        <script type = "text/javascript">
            function changeScope(target) {
                window.location = target + "&scope=" + document.getElementById("selectScope").value;
                return true;
            }
        </script>
        <?php

        $sql = "SELECT w.id, wf.walk_id, date_format(walk_date, '%a %e-%m-%y') as Date, ";
        $sql .= "w.group_code as 'Group', ";
        $sql .= "w.contact_display_name as 'Leader', ";
        $sql .= "w.leader_user_id as 'Leader_id', ";
        $sql .= "w.title as 'Walk Title', ";
        $sql .= " ";
        $sql .= "profiles.preferred_name as 'Follower', ";
//        $sql .= "users.email as 'email', ";
        $sql .= "wf.id ";
        $sql .= "from #__ra_walks_follow AS wf  ";
        $sql .= "LEFT JOIN #__ra_walks AS w ON w.id = wf.walk_id ";
        $sql .= "LEFT JOIN #__ra_profiles as profiles ON profiles.id = wf.user_id ";
//        $sql .= "WHERE walk_follow.walk_id=$walk_id ";

        switch ($this->scope) {
            case ($this->scope == 'D');
                $sql .= "WHERE state=0 ";
                break;
            case ($this->scope == 'F');
                $sql .= "WHERE (datediff(walk_date, CURRENT_DATE) >= 0) ";
                break;
            case ($this->scope == 'H');
                $sql .= 'WHERE datediff(walk_date, CURRENT_DATE) < 0 ';
        }


        $sql .= "order by w.walk_date, w.title, profiles.preferred_name";
        echo "<h2>Reporting</h2>";
        echo "<h4>Walks and Followers</h4>";
        $target = 'index.php?option=com_ra_walks&task=reports.walksFollowers';
        ToolsHelper::selectScope($this->scope, $target);
        echo '<br>';
//        echo $sql;
        $this->objHelper->showQuery($sql);
        $target = "index.php?option=com_ra_walks&view=reports";
        echo $this->objHelper->backButton($target);
    }

    /*
     * DEPRECATED functions
     */

    private function openRs(&$rs, $sql, &$message) {
        $config = Factory::getConfig();    // GIVES ERROR when run from native programs
        $this->dbServer = $config->get('host');
        $this->dbDatabase = $config->get('db');
        $this->dbPrefix = $config->get('dbprefix');
        $this->dbUser = $config->get('user');
        $this->dbPassword = $config->get('password');

//        echo "Server=" . $this->dbServer . ", Database " . $this->dbDatabase . ", user $this->dbUser, Pass=$this->dbPassword<br>";
        if ($this->objMysqli = mysqli_connect($this->dbServer, $this->dbUser, $this->dbPassword)) {
            $this->message = "Connected OK to host $this->dbServer for user $this->dbUser";
        } else {
            echo "could not connect";
            $this->message = "Connection failed to the host $this->dbServer for user $this->dbUser";
            $this->message .= $this->objMysqli->error;
            fwrite(STDERR, $this->message . "/n");
        }
        if (mysqli_select_db($this->objMysqli, $this->dbDatabase)) {
            $this->message .= ", Connect OK to Database " . $this->dbDatabase;
//        echo "DatabaseAccess::Construct " . $this->message;
        } else {
            $this->message = "Could not connect to Database " . $this->dbDatabase . " for user $this->dbUser<br>" . $PHP_SERVER["PHP_SELF"];
            echo $this->message;
            fwrite(STDERR, $this->message . "\n");
        }


//    $this->objMysqli = mysqli_connect($this->dbServer, $this->dbUser, $this->dbPassword);
        if ($this->objMysqli == NULL) {
            $message = "DatabaseAccess:objMysqli is NULL";
            return 0;
        }
//        echo "Prefix: " . $this->dbPrefix . "<br>";
        $sql = str_replace("#__", $this->dbPrefix, $sql);
//        echo "After replace, sql=$sql<br>";
        if ($rs = mysqli_query($this->objMysqli, $sql)) {
            $message = mysqli_num_rows($rs) . " Records found";
//            return 1;
            return mysqli_num_rows($rs);
        } else {
// should log this error TODO
            $message = $this->objMysqli->error;
            return 0;
        }
    }

}
