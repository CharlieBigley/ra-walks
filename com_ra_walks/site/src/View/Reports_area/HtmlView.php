<?php

/**
 * @version     4.0.12
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 *
 *  This can be invoked from view list_area, in which case the Ares Code will be passed as a parameter
 *  or directly from a menu.
 *  In the second case, it takes the required Area Code from the component parameters
 *
 * 15/05/21 CB created
 * // 22/06/21 correction for selection criteria for National reports
 * 21/07/21 tmpl=component
 * 24/09/22 CB allow three character for area (i.e NAT for national)
 * 23/05/23 CB converted to Joomla 4
 * 15/08/23 get default group from tool configuration, no walks config
 * 22/01/24 CB eliminate JText
 */

namespace Ramblers\Component\Ra_walks\Site\View\Reports_area;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

/**
 * Ramblers detail view
 */
class HtmlView extends BaseHtmlView {

    protected $component_params;
    protected $menu_params;
//  variables for the templates
    protected $area;
    protected $area_name;
    protected $callback;
    protected $criteria_sql;
    protected $mode;
    protected $opt;
    protected $report_type;
    protected $scope;
    protected $scope_desc;

    /**
     * Display the view
     */
    public function display($template = null) {
//      Load the component params
        $app = Factory::getApplication();
        $this->component_params = $app->getParams();
        $context = 'com_ra_walks.reports.';

        $this->scope = substr($app->input->getCmd('scope', 'F'), 0, 1);
        // Report type will be blank, unless showing statistics
        $this->report_type = substr($app->input->getWord('report_type', ''), 0, 1);

        // Callback will be blank if called from a menu
        $this->callback = $app->input->getWord('callback', '');

//      See if we have been invoked from a menu
        // If invoked from list_areas, the specific area_code will have been passed as parameter
        // However, if invoked from a menu, assume it is a Group or Area website,
        // and derive the code from the menu parameters
        $this->area = $app->input->getWord('area', '');
        if ($this->area == '') {
            $menu = $app->getMenu()->getActive();
            if (is_null($menu)) {
                echo "Menu is Null, defaulting to National walks<br>";
                $this->area = 'NAT';
            } else {
                $this->menu_params = $menu->getParams();
                $type = $this->menu_params->get('type');
                if ($type == 'national') {
                    $this->area = 'NAT';
                } else {
                    if ($type == 'home') {
                        $tools_params = $app->getParams('com_ra_tools');
                        $this->area = substr($tools_params->get('default_group'), 0, 2);
                    } else {
                        $this->area = $this->menu_params->get('area', 'NAT');
                    }
                }

                $app->setUserState($context . 'mode', 'A');
                $app->setUserState($context . 'opt', $this->area);
//               echo "Menu is active, area=$this->area<br>";
            }
        }


        if ($this->scope == 'A') {
            $this->scope_desc = 'All walks';
        } else {
            if ($this->scope == "F") {              // Future walks
                $this->scope_desc = 'Future walks';
                $this->criteria_sql .= ' (datediff(walk_date, CURRENT_DATE) >= 0) ';
                $this->criteria_sql .= 'AND (walks.state=1) ';
            } elseif ($this->scope == "H") {   // Historic
                $this->scope_desc = 'Historic walks';
                $this->criteria_sql .= ' (datediff(walk_date, CURRENT_DATE) < 0)  ';
            } elseif ($this->scope == "D") {   // Draft/ Cancelled/Archived
                $this->scope_desc = 'Draft walks';
                $this->criteria_sql .= ' NOT (walks.state=1)  ';
            }
        }
        $app->setUserState($context . 'scope', $this->scope);
        // Load the template header here to simplify the template
        $this->prepareDocument();

        // get the current invokation parameters so that after drilldown, the
        // subordinate programs can return to the same state
        $app->setUserState('com_ra_walks.callback_matrix', 'reports_area');

//      set callback in globals so TopWalkLeaders can return as appropriate
        $app->setUserState('com_ra_walks.reports.topleaders', 'reports_area');

        // N.B. name of template has been over-ridden in the construct above
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

    protected function buildTarget($row, $col) {
        // builds the parameters from the chosen option for the drilldown report
        $target = 'index.php?option=com_ra_walks&task=reports.drilldown';
        // $targer .= '&tmpl=component';
        $target .= '&scope=' . $this->scope;
        $target .= '&row=' . $row . '&col=' . $col;
        return $target;
    }

}
