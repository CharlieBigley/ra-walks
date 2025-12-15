<?php

/**
 * @version     4.0.12
 * @package     com_ra_walks(Ramblers Walks)
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 *
 * 07/08/23 CB Created for V4
 * 22/01/24 CB eliminate JText
 */

namespace Ramblers\Component\Ra_walks\Site\View\Reports_matrix;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
//use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

class HtmlView extends BaseHtmlView {

    protected $state;
    protected $item;
    protected $component_params;
// fields used by all templates
    protected $callback;
    protected $limit;
    protected $mode;
    protected $opt;
    protected $scope;
    protected $scope_desc;
    protected $row;
    protected $row_value;
    protected $col;
    protected $col_value;
    protected $criteria;
    protected $criteria_sql;
    protected $sort;

    /**
     * Display the view
     */
    public function display($tpl = null) {

        $objHelper = new ToolsHelper;
        $app = Factory::getApplication();
        //        $app->input->set('tmpl', 'component');
//
        $this->component_params = $app->getParams();
        // get the input parameters
        $this->row = substr($app->input->getCmd('row', ''), 0, 2);
        $this->row_value = ToolsHelper::convert_from_ASCII($app->input->getCmd('row_value', ''));
        $this->col = $app->input->getCmd('col', '');
        $this->col_value = ToolsHelper::convert_from_ASCII($app->input->getCmd('col_value', ''));
        $this->report_type = $app->input->getCmd('report_type', '');
        $this->limit = $app->input->getCmd('limit', '20');
        $this->sort = $app->input->getCmd('sort', 'M');

        // Ensure that when drilldown programs have finished, returning here will restore the
        // original set of parameters
        $context = 'com_ra_walks.reports.';
        $this->mode = $app->getUserState($context . 'mode');
        $this->opt = $app->getUserState($context . 'opt');
        $this->scope = $app->getUserState($context . 'scope');
        $this->row = $app->getUserState($context . 'row');
        $this->col = $app->getUserState($context . 'col');
        echo "report_type: $this->report_type<br>";
        echo "mode: $this->mode<br>";
        echo "opt: $this->opt<br>";
        echo "scope: $this->scope<br>";
        echo "row: $this->row<br>";
        echo "col: $this->col<br>";

        $this->criteria_sql = '';
        switch ($this->mode) {
            case ($this->mode == "A");
                if ($this->opt == 'NAT') {
                    $this->criteria = 'National walks';
                } else {
                    if ($this->row != "A") {
                        $this->criteria_sql = "SUBSTRING(walks.group_code,1,2)='" . $this->opt . "' ";
                        $this->criteria = "Area=" . $objHelper->getValue("SELECT name FROM #__ra_areas where code='" . $this->opt . "' ");
                    }
                }
                break;
            //            case ($this->mode == 'A2');
            //                // we are finding walks for a given Area
            //                $this->criteria = 'Group';
            //                break;
            case ($this->mode == "Dif");
                $this->criteria_sql = "walks.difficulty='" . $this->opt . "' ";
                $this->criteria = "Difficulty=$this->opt";
                break;
            case ($this->mode == "G");
                $this->criteria_sql = "walks.group_code='" . $this->opt . "' ";
                $this->criteria = "Group=" . $objHelper->getValue("SELECT name FROM #__ra_groups where code='" . $this->opt . "' ");
                break;
            case ($this->mode == "L");
                $this->criteria_sql = "walks.local_grade='" . $this->opt . "' ";
                $this->criteria = "Local grade=$this->opt";
                break;
            case ($this->mode == "M");
                $this->criteria_sql = "walks.distance_miles='" . $this->opt . "' ";
                $this->criteria = "Miles=$this->opt";
                break;
            case ($this->mode == "MR");
                $this->criteria_sql = "ROUND(walks.distance_miles)='" . $this->opt . "' ";
                $this->criteria = "Miles (rounded)=$this->opt";
                break;
            case ($this->mode == "P");
                $this->criteria_sql = "walks.pace='" . $this->opt . "' ";
                $this->criteria = "Pace=$this->opt";
                break;
            case ($this->mode == "W");
                $this->criteria_sql = "dayname(walk_date)='" . $this->opt . "' ";
                $this->criteria = "Weekday=$this->opt";
                break;
            case ($this->mode == "WL");
                $opt = ToolsHelper::convert_from_ASCII($this->opt);
                $this->criteria_sql = "contact_display_name='" . $opt . "' ";
                $this->criteria = "Leader=$opt";
                break;
            default;
                Factory::getApplication()->enqueueMessage('mode ' . $this->mode . ' unknown', 'message');
                $error = 1;
        }
        // set up sql to select the ROWS of the matrix =================================
        switch ($this->row) {
            case ($this->row == 'A');
                $this->row_type = 'Area';
                $this->row_field = '`areas`.name';
                break;
            case ($this->row == 'G');
                $this->row_type = 'Group';
                $this->row_field = '`groups`.name';
                break;
            //    case ($row == "D");   // Not initially used
            //        $row_type = "Date";
            //        break;
            case ($this->row == "Dif");
                $this->row_type = "Difficulty";
                $this->row_field = 'difficulty';
                $this->row_group_by = "GROUP BY difficulty ORDER BY difficulty";
                break;
            case ($this->row == "L");
                $this->row_type = "Local grade";
                $this->row_field = "grade_local";
                break;
            case ($this->row == "M");
                $this->row_type = "Miles";
                $this->row_field = "distance_miles";
                break;
            case ($this->row == "MR");
                $this->row_type = "Miles (rounded)";
                $this->row_field = "ROUND(distance_miles)";
                break;
            case ($this->row == "P");
                $this->row_type = "Pace";
                $this->row_field = "pace";
                break;
            case ($this->row == "W");
                $this->row_type = "Weekday";
                $this->row_field = "DAYNAME(walk_date)";
                break;
            case ($this->row == "WL");
                $this->row_type = "Walk leader";
                $this->row_field = "contact_display_name)";
                break;
            case ($this->row == "Y");
                $this->row_type = "Year";
                $this->row_field = "YEAR(walk_date)";
            //            default;
            // Factory::getApplication()->enqueueMessage('row' . $this->row . ' unknown', 'message');
            //                $error = 1;
        }

        // Field $col represents the column of the matrix ==============================
        switch ($this->col) {
            case ($this->col == 'C');
                $this->col_type = 'GroupCode';
                $this->col_field = '`walks`.group_code';
                break;
            case ($this->col == 'G');
                $this->col_type = 'Group';
                $this->col_field = '`groups`.name';
                break;
            case ($this->col == "Dif");
                $this->col_type = "Difficulty";
                $this->col_field = "difficulty";
                break;
            case ($this->col == "L");
                $this->col_type = "Local grade";
                $this->col_field = "grade_local";
                break;
            case ($this->col == "M");
                $this->col_type = "Miles";
                $this->col_field = "distance_miles";
                break;
            case ($this->col == "MR");
                $this->col_type = "Miles (rounded)";
                $this->col_field = "ROUND(distance_miles)";
                break;
            case ($this->col == "P");
                $this->col_type = "Pace";
                $this->col_field = "pace";
                break;
            case ($this->col == "S");
                $this->col_type = "Status";
                $this->col_field = 'state';
                break;
            case ($this->col == "W");
                $this->col_type = "Weekday";
                $this->col_field = "DAYOFWEEK(walk_date)";
                break;
            case ($this->col == "YM");
                $this->col_type = "Month";
                $this->col_field = "MONTH(walk_date)";
                break;
            //            default;
            // Factory::getApplication()->enqueueMessage('col' . $this->col . ' unknown', 'message');
            //                $error = 1;
        }

        if ($this->scope == "A") {
            $this->scope_desc = 'All walks';
        } else {
            if (!$this->criteria_sql == '') {
                $this->criteria_sql .= ' AND ';
            }
            if ($this->scope == "F") {              // Future walks
                $this->scope_desc = 'Future walks';
                $this->criteria_sql .= ' (datediff(walk_date, CURRENT_DATE) >= 0) ';
                $this->criteria_sql .= 'AND (walks.state=1) ';
            } elseif ($this->scope == "H") {   // Historic
                $this->scope_desc = 'Historic walks';
                $this->criteria_sql .= ' (datediff(walk_date, CURRENT_DATE) < 0) ';
            } elseif ($this->scope == "D") {   // Draft/ Cancelled/Archived
                $this->scope_desc = 'Draft walks';
                $this->criteria_sql .= ' NOT (walks.state=1) ';
            }
        }

        $this->prepareDocument();

        if ($this->report_type == 'C') {
            parent::SetLayout('tmpl:drilldown_csv');
            $callback_key = 'com_ra_walks.callback_csv';
        } elseif ($this->report_type == 'L') {
            parent::SetLayout('tmpl:drilldown_list');
            $callback_key = 'com_ra_walks.callback_list';
        } else {
            parent::SetLayout('tmpl:drilldown_matrix');
            $callback_key = 'com_ra_walks.callback_matrix';
//        } elseif ($this->report_type == 'S') {
//            parent::SetLayout('tmpl:drilldown_stats');           parent::SetLayout('tmpl:drilldown_matrix');
        }

        // set the current invokation parameters so that after drilldown, the
        // subordinate programs can return to the same state
        $current_uri = Uri::getInstance()->toString();
//        echo 'MatrixView: callback_key=' . $callback_key . ' ' . $current_uri . '<br>';
//        echo 'MatrixView: criteria:' . $this->criteria . ', sql=' . $this->criteria_sql . '<br>';
        Factory::getApplication()->setUserState($callback_key, $current_uri);

//        echo "callback=$this->callback<br>";
//        die('view');
        parent::display($template);
    }

    /**
     * Prepares the document
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function prepareDocument() {
        // Import CSS
        $wa = $this->document->getWebAssetManager();
        $wa->registerStyle('ramblers', 'com_ra_tools/ramblers.css');
        $wa->useStyle('ramblers');

        $app = Factory::getApplication();
        $menus = $app->getMenu();

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->component_params->def('page_heading', $this->component_params->get('page_title', $menu->title));
        } else {
            $this->component_params->def('page_heading', 'Ramblers Walks');
        }

        $title = $this->component_params->get('page_title', '');

        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

        if ($this->component_params->get('menu-meta_description')) {
            $this->document->setDescription($this->component_params->get('menu-meta_description'));
        }

        if ($this->component_params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->component_params->get('menu-meta_keywords'));
        }

        if ($this->component_params->get('robots')) {
            $this->document->setMetadata('robots', $this->component_params->get('robots'));
        }
    }

}
