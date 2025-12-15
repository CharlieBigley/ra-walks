<?php

/**
 * @version     1.1.1
 * @package     com_ra_walks
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 02/06/23 CB JoomlaUsersByGroup - LEFT JOIN
 * 18/07/23 CB delete unused reports
 * 20/08/23 CB Show Admin'Site in menu report
 * 14/12/24 CB showLogfile added from backup
 * 15/12/25 CB walks by month
 */

namespace Ramblers\Component\Ra_walks\Administrator\Controller;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHtml;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

class ReportsController extends FormController {

    protected $criteria_sql;
    protected $back;
    protected $db;
    protected $objApp;
    protected $toolsHelper;
    protected $prefix;
    protected $query;
    protected $scope;

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDbo();
        $this->toolsHelper = new ToolsHelper;
        $this->objApp = Factory::getApplication();
        $this->prefix = 'Reports: ';
        $this->back = 'administrator/index.php?option=com_ra_walks&view=reports';
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('ramblers', 'com_ra_tools/ramblers.css');
    }

    //put your code here
    public function countUsers() {
        ToolBarHelper::title($this->prefix . 'User count by Group');
        $sql = "SELECT a.ra_group_code AS 'GroupCode', g.name, count(u.id) AS 'Number', ";
        $sql .= "MIN(w.walk_date) AS 'Earliest', ";
        $sql .= "MAX(w.walk_date) as 'Latest' ";
        $sql .= "from #__ra_profiles AS a ";
        $sql .= 'INNER JOIN #__users AS u ON u.id = a.id ';
        $sql .= 'LEFT JOIN #__ra_groups AS g ON g.code = a.ra_group_code ';
        $sql .= 'LEFT JOIN #__ra_walks AS w ON w.leader_user_id = a.id ';
        $sql .= 'GROUP BY a.ra_group_code ';
        $sql .= 'ORDER BY a.ra_group_code ';
        $rows = $this->toolsHelper->getRows($sql);
        //      Show link that allows page to be printed
        $target = 'index.php?option=com_ra_wf&task=reports.countUsers';
        echo $this->toolsHelper->showPrint($target) . '<br>' . PHP_EOL;
        $objTable = new ToolsTable;
        $objTable->add_header("Code,Group,Count,Earliest walk,Latest walk");
        $target = 'administrator/index.php?option=com_ra_wf&task=reports.showUsersForGroup&group=';
        foreach ($rows as $row) {
            if ($row->GroupCode == '') {
                $objTable->add_item('');
            } else {
                $objTable->add_item($this->toolsHelper->buildLink($target . $row->GroupCode, $row->GroupCode));
            }
            $objTable->add_item($row->name);
            $objTable->add_item($row->Number);
            $objTable->add_item($row->Earliest);
            $objTable->add_item($row->Latest);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo $this->toolsHelper->backButton('administrator/index.php?option=com_ra_wf&view=reports');
//        echo "<p>";
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
            $this->query->where("groups.code='" . $opt . "'");
        } else {
            if ($opt == 'NAT') {

            } else {
                $this->query->where("SUBSTR(groups.code,1,2)='" . $opt . "'");
            }
        }
    }

    public function showFeed() {

        $group_code = $this->objApp->input->getCmd('group_code', 'NS03');
        $this->scope = $this->objApp->input->getCmd('scope', '');
        $csv = substr($this->objApp->input->getCmd('csv', ''), 0, 1);
        ToolBarHelper::title($this->prefix . 'Feed update for ' . $this->toolsHelper->lookupGroup($group_code));
        $objTable = new ToolsTable();

        $objTable->add_header("Date,Message");
        $sql = "SELECT date_amended, field_value ";
        $sql .= "FROM #__ra_groups_audit AS audit ";
        $sql .= "INNER JOIN #__ra_groups `groups` ON `groups`.id = audit.object_id ";
        $sql .= "WHERE `groups`.code='" . $group_code . "' ";
        $sql .= 'ORDER BY date_amended DESC ';
        //        echo $sql;
        $rows = $this->toolsHelper->getRows($sql);
        foreach ($rows as $row) {
            $objTable->add_item($row->date_amended);
            $objTable->add_item($row->field_value);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        $back = "administrator/index.php?option=com_ra_wf&view=reports_group&group_code=" . $group_code . '&scope=' . $this->scope;
        echo $this->toolsHelper->backButton($back);
//        if ($csv == '') {
//            $target = "administrator/index.php?option=com_ra_wf&task=reports.showFeed&csv=feed&group_code=" . $group_code . '&scope=' . $this->scope;
//            echo $this->toolsHelper->buildLink($target, "Extract as CSV", False, "link-button button-p0159");
//        }
    }

    public function showFeedSummary() {
        $this->scope = $this->objApp->input->getCmd('scope', '');
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
        $rows = $this->toolsHelper->getRows($sql);
        foreach ($rows as $row) {
            $objTable->add_item($row->log_date);
            $objTable->add_item($row->message);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        $back = "administrator/index.php?option=com_ra_tools&view=reports_area&area=NAT&scope=" . $this->scope;
        echo $this->toolsHelper->backButton($back);
        if ($csv == '') {
            $target = "administrator/index.php?option=com_ra_wf&task=reports.showFeedSummary&csv=feedSummary";
            echo $this->toolsHelper->buildLink($target, "Extract as CSV", False, "link-button button-p0159");
        }
    }

    public function showFeedSummaryArea() {
        $area = $this->objApp->input->getCmd('area_code', 'NS');
        $this->scope = $this->objApp->input->getCmd('scope', '');
        $current_group = '';
        $groups_count = 0;
        $groups_found = 0;
        $area_code = 'NS';
        echo "<h2>Feed update for " . $this->toolsHelper->lookupArea($area) . "</h2>";
        $sql = "SELECT code from #__ra_groups where code LIKE '" . $area . "%' ORDER BY code";
        $objTable = new ToolsTable();
        $objTable->add_header("Group,Date,Message");

        $groups = $this->toolsHelper->getRows($sql);
        $groups_count = $this->toolsHelper->rows;
        foreach ($groups as $group) {
            $sql = "SELECT `groups`.code, date_amended, field_value ";
            $sql .= "FROM #__ra_groups_audit AS audit ";
            $sql .= "INNER JOIN #__ra_groups `groups` ON `groups`.id = audit.object_id ";
            $sql .= "WHERE `groups`.code='" . $group->code . "' ";
            $sql .= 'ORDER BY date_amended DESC LIMIT 7';
//            echo $sql . '<br>';
            $rows = $this->toolsHelper->getRows($sql);
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
        $back = "administrator/index.php?option=com_ra_tools&view=reports_area&area=" . $area . '&scope=' . $this->scope;
        echo $this->toolsHelper->backButton($back);
    }

    function showFollowers() {
        ToolBarHelper::title($this->prefix . 'Walk Followers');
        $sql = "SELECT p.id, ra_display_name, ra_group_code, COUNT(w.id) as 'Num', ";
        $sql .= "MIN(walk_date) AS 'Earliest', ";
        $sql .= "MAX(walk_date) as 'Latest' ";

        $sql .= "FROM #__ra_profiles as p ";
        $sql .= "INNER JOIN #__ra_walks_follow as f on f.user_id = p.id ";
        $sql .= "INNER JOIN #__ra_walks as w on w.id = f.walk_id ";
        $sql .= "GROUP BY p.id, ra_display_name, ra_group_code ";
        $sql .= "ORDER BY COUNT(w.id) DESC ";
        $rows = $this->toolsHelper->getRows($sql);
//        $target = 'index.php?option=com_ra_wf&task=reports.showFollowings&user=';
        $objTable = new ToolsTable();
//        $objTable->set_csv(True);
        $objTable->add_header('Name,Groups, Number of walks, Earliest, Latest');
        foreach ($rows as $row) {
            $objTable->add_item($row->ra_display_name);
            $objTable->add_item($row->ra_group_code);
            $objTable->add_item($row->Num);
//            $link = $this->toolsHelper->buildLink($target . $row->id), $row->Num);
//            $objTable->add_item($link);
            $objTable->add_item($row->Earliest);
            $objTable->add_item($row->Latest);

            $objTable->generate_line();
        }
        $objTable->generate_table();

        $back = "administrator/index.php?option=com_ra_wf&view=reports";
        echo $this->toolsHelper->backButton($back);
    }

    public function showFollowersByDate() {
        $sql = "SELECT date_format(walk_date, '%a %e-%m-%y') AS Date, count(walks.id) AS 'Count' ";
        $sql .= "FROM #__ra_walks_follow as walk_follow ";
        $sql .= "LEFT JOIN #__ra_walks as walks on walks.id = walk_follow.walk_id ";
        $sql .= "LEFT JOIN __ra_profiles as profiles on profiles.id = walk_follow.user_id ";
        $sql .= "WHERE (datediff(walk_date, CURRENT_DATE) >= 0) ";
        $sql .= "GROUP BY date_format(walk_date, '%a %e-%m-%y'), walk_date ";
        $sql .= "ORDER BY walk_date";
//        echo $sql . '<br>';
        echo "<h2>Reporting</h2>";
        echo "<h4>Followers by date</h4>";
        $this->toolsHelper->showSql($sql);
        $back = "administrator/index.php?option=com_ra_wf&view=reports";
        echo $this->toolsHelper->backButton($back);
    }

    public function showLeaders() {
        $this->scope = $this->objApp->input->getCmd('scope', 'F');
        $self = 'index.php?option=com_ra_wf&task=reports.showLeaders';
        $callback = $self . '&scope = ' . $this->scope;
        ?>
        <script type = "text/javascript">
            function changeScope(target) {
                window.location = target + "&scope=" + document.getElementById("selectScope").value;
                return true;
            }
        </script>
        <?php

        $this->toolsHelper->showMenu("WalksFollowers");

        $sql = "SELECT  date_format(walk_date,'%a %e-%m-%y') AS Date,";
        $sql .= "walks.title as 'Title', ";
        $sql .= "walks.contact_display_name as 'Leader', ";
        $sql .= "walks.organising_group as 'Group', ";
        $sql .= "walks.walk_id as WalkId, ";
        $sql .= "walks.id as 'Internal',";
//        $sql .= "walks.leader_user_id, ";
        $sql .= "profile.user_id as Ref ";
        $sql .= "FROM #__ra_walks as walks  ";
//        $sql .= "LEFT JOIN __ra_profiles as profile on profile.user_e = walks.contact_display_name ";
        $sql .= 'LEFT JOIN __ra_profiles as profile ON walks.leader_user_id = profile.user_id ';
        $sql .= "WHERE (walks.leader_user_id > 0) ";

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
        $target = 'index.php?option=com_ra_wf&task=reports.showLeaders';
        Helper::selectScope($this->scope, $target);
        echo '<br>';

        $this->toolsHelper->showSql($sql);
        $back = "administrator/index.php?option=com_ra_wf&view=reports";
        echo $this->toolsHelper->backButton($back);
    }

    public function showLogfile() {

        $offset = $this->objApp->input->getCmd('offset', '0');
        $next_offset = $offset - 1;
        $previous_offset = $offset + 1;

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
        ToolBarHelper::title($this->prefix . 'Logfile records for ' . date_format($target, "D d M"));

        $sql = "SELECT date_format(log_date, '%a %e-%m-%y') as Date, ";
        $sql .= "date_format(log_date, '%H:%i:%s.%u') as Time, ";
        $sql .= "record_type, ";
        $sql .= "ref, ";
        $sql .= "message ";
        $sql .= "FROM #__ra_logfile ";
        $sql .= "WHERE log_date >='" . date_format($target, "Y/m/d H:i:s") . "' ";
        $sql .= "AND log_date <'" . date_format($target, "Y/m/d 23:59:59") . "' ";
        $sql .= "ORDER BY log_date DESC, record_type ";
        if ($this->toolsHelper->showSql($sql)) {
            echo "<h5>End of logfile records for " . date_format($target, "D d M") . "</h5>";
        } else {
            echo 'Error: ' . $this->toolsHelper->error . '<br>';
        }

        echo $this->toolsHelper->buildButton("administrator/index.php?option=com_ra_tools&task=reports.showLogfile&offset=" . $previous_offset, "Previous day", False, 'grey');
        if ($next_offset >= 0) {
            echo $this->toolsHelper->buildButton("administrator/index.php?option=com_ra_tools&task=reports.showLogfile&offset=" . $next_offset, "Next day", False, 'teal');
        }
        $target = "administrator/index.php?option=com_ra_tools&view=reports";
        echo $this->toolsHelper->backButton($target);
    }

    function showUsers() {
        ToolBarHelper::title($this->prefix . 'Users');
        $sql = "Select profile.id, profile.ra_display_name as 'Display name', ";
        $sql .= "users.email as 'email', ";
        $sql .= "group_code as 'Groups', ";
        $sql .= "privacy_level as 'Privacy level', ";
        $sql .= "acknowledge_follow as 'Acknowledge Follow', ";
//        $sql .= "contact_email as 'Email', ";
        $sql .= "contactviaemail as 'Contact by email', ";
        $sql .= "mobile as 'Mobile' ";
        $sql .= "FROM #__ra_profiles as profile ";
        $sql .= "LEFT JOIN #__users as users on profile.id = users.id ";
        $sql .= "WHERE group_code>''";
        $sql .= "order by profile.id";
        $this->toolsHelper->showSql($sql);
        $back = "administrator/index.php?option=com_ra_wf&view=reports";
        echo $this->toolsHelper->backButton($back);
    }

    function showUsersByRange() {
        ToolBarHelper::title($this->prefix . 'Users by chosen range');
        $sql = "SELECT ";
        $sql .= "min_miles as 'Min', ";
        $sql .= "max_miles as 'Max', ";
        $sql .= "group_code as 'Groups', ";
        $sql .= "u.name as 'Login name', ";
        $sql .= "profile.ra_display_name as 'Display name' ";
        $sql .= "FROM #__ra_profiles as profile ";
        $sql .= "LEFT JOIN #__users as u on profile.id = u.id ";
        $sql .= "WHERE ra_group_code>''";
        $sql .= "AND (min_miles >0 OR max_miles >0)";
        $sql .= "ORDER BY min_miles, max_miles";
        $this->toolsHelper->showSql($sql);
        $back = "administrator/index.php?option=com_ra_wf&view=reports";
        echo $this->toolsHelper->backButton($back);
    }

    public function showUsersForGroup() {
        $group = $this->objApp->input->getCmd('group', '');
        ToolBarHelper::title('Ramblers Reports');
        $sql = "SELECT u.name AS 'Name', u.email ";
        $sql .= "from #__ra_profiles AS a ";
        $sql .= 'INNER JOIN #__users AS u ON u.id = a.id ';
        $sql .= 'WHERE a.ra_group_code=' . $this->db->quote($group);
//        echo $sql;
        $rows = $this->toolsHelper->getRows($sql);
        //      Show link that allows page to be printed
        $target = 'index.php?option=com_ra_wf&task=reports.showUsersForGroup&group=' . $group;
        echo '<h4>Users for Group ' . $group . '</h4>';
        echo $this->toolsHelper->showPrint($target) . '<br>' . PHP_EOL;
        $objTable = new ToolsTable;
        $objTable->add_header("Name,Email");
        foreach ($rows as $row) {
            $objTable->add_item($row->Name);
            $objTable->add_item($row->email);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo $this->toolsHelper->backButton('administrator/index.php?option=com_ra_wf&task=reports.countUsers');
//        echo "<p>";
    }

    public function showWalksByMonth() {  //index.php?option=com_ra_walks&task=reports.showWalksByMonth
        $field = 'walk_date';
        $table = ' #__ra_walks';
        $criteria = '';
//        $criteria = 'HAVING group_code="NS03" ';
        $title = 'Walks by month';
        $link = 'administrator/index.php?option=com_ra_walks&task=reports.showWalksForMonth';
        //      $back = $this->back;
        $back = 'administrator/index.php?option=com_ra_walks&view=reports';
        echo $this->breadcrumbs;
        $this->toolsHelper->showMonthMatrix($field, $table, $criteria, $title, $link, $back);
    }

    public function showWalksForDay() {
        $date = $this->objApp->input->getCmd('date', '');
        if ($date == '') {
            ToolBarHelper::title('date is blank' . $date);
            echo $this->toolsHelper->backButton($this->back);
        }
        ToolBarHelper::title('Walks for ' . $date);

        $sql = "SELECT group_code, date_format(walk_date, '%a %e-%m-%y') as Date, ";
        $sql .= "title,walk_leader, ";
        //       $sql .= "ref, ";
        $sql .= "distance_miles ";
        $sql .= "FROM #__ra_walks ";
        $sql .= "WHERE walk_date >='" . $date . " 00:00:00' ";
        $sql .= "AND walk_date <'" . $date . " 23:59:59' ";
        $sql .= "ORDER BY group_code, walk_date  ";
//       echo $sql;
        if ($this->toolsHelper->showSql($sql)) {
            echo "<h5>End of walks for " . $date . "</h5>";
        } else {
            echo 'Error: ' . $this->toolsHelper->error . '<br>';
        }

        $back = 'administrator/index.php?option=com_ra_walks&task=reports.showWalksForMonth';
        $yyyy = substr($date, 0, 4);
        $mm = substr($date, 5, 2);
        $back .= '&year=' . $yyyy . '&month=' . $mm;
        echo $this->toolsHelper->backButton($back);
    }

    public function showWalksForMonth() {
        //       echo $this->breadcrumbs . $this->breadcrumbsExtra('Walks by month', 'showWalksByMonth') . '<br>';
        $yyyy = $this->objApp->input->getInt('year', '2025');
        $mm = $this->objApp->input->getInt('month', '5');
        $field = 'walk_date';
        $table = ' #__ra_walks';
        $criteria = '';
        $title = 'Walks records for month ' . $mm . '/' . $yyyy;
        $link = 'administrator/index.php?option=com_ra_walks&task=reports.showWalksForDay';
        $back = 'administrator/index.php?option=com_ra_walks&task=reports.showWalksByMonth';
        $this->toolsHelper->showDayMatrix($field, $table, $yyyy, $mm, $criteria, $title, $link, $back);
    }

    function walksAudit() {

        $offset = jRequest::getString("offset");
        $next_offset = $offset - 1;
        $previous_offset = $offset + 1;

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
        ToolBarHelper::title($this->prefix . 'Walks updated ' . date_format($target, "D d M"));
        $sql = "SELECT date_format(date_amended, '%d/%m/%y') as 'Date', ";
        $sql .= "time_format(date_amended, '%H:%i') as 'Time', ";
        $sql .= "walks.walk_id as 'Walk', ";
        $sql .= "date_format(walks.walk_date, '%d/%m/%y') as 'WalkDate', ";
        $sql .= "walks.group_code as 'Group', ";
        $sql .= "walks.title as 'Title', ";
        $sql .= "walks.contact_display_name as 'Leader', ";
        $sql .= "field_name as 'Field', record_type as 'Action', ";
        $sql .= "field_value as 'Change' ";
        $sql .= "from #__ra_walks_audit as walks_audit ";
        $sql .= "INNER JOIN #__ra_walks as walks on walks.id = walks_audit.object_id ";
        $sql .= "WHERE date_amended >='" . date_format($target, "Y/m/d H:i:s") . "' ";
        $sql .= "AND date_amended <'" . date_format($target, "Y/m/d 23:59:59") . "' ";
        $sql .= "ORDER BY date_amended DESC, record_type ";
//        $sql .= "LIMIT 10";
        if ($this->toolsHelper->showSql($sql)) {

        } else {
            echo "Error: " . $this->toolsHelper->error;
        }
//echo "<h5>End of audit records for " . date_format($target, "D d M") . "</h5>";
        echo $this->toolsHelper->buildButton("administrator/index.php?option=com_ra_wf&task=reports.walksAudit&offset=$previous_offset", "Previous day", False, 'grey') . " ";
        if ($next_offset >= 0) {
            echo $this->toolsHelper->buildButton("administrator/index.php?option=com_ra_wf&task=reports.walksAudit&offset=$next_offset", "Next day", False, 'teal');
        }
        $back = "administrator/index.php?option=com_ra_wf&view=reports";
        echo $this->toolsHelper->backButton($back);
    }

}
