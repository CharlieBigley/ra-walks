<?php

/**
 * @version     1.1.2
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 *
 *  This can be invoked from view groupd, in which case the Groups Code will be passed as a parameter
 *  or directly from a menu.
 *  In the second case, it takes the required Group Code from the component parameters
 *
 * 23/05/21 CB created from reports_area
 * 04/07/21 CB add callback
 * 23/05/23 CB converted to Joomla 4
 * 22/01/24 CB eliminate JText
 * 10/01/26 CB deleted code for Pace and Grade local (GWEM only)
 */

namespace Ramblers\Component\Ra_walks\Site\View\Reports_group;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

/**
 * Ramblers detail view
 */
class HtmlView extends BaseHtmlView {

    protected $app;
    protected $component_params;
//  variables for the template
    protected $back;
    protected $group_code;
    protected $mode;
    protected $scope;

    /**
     * Display the view
     */
    public function display($template = Null) {
        // If invoked from list_groups, the specific group_code will have been passed as parameter
        // However, if invoked fram a menu, derive the code from the component parameters
        //
        // Load the component params
        $this->app = Factory::getApplication();

        $menu = $this->app->getMenu()->getActive();
        if (is_null($menu)) {
            echo "Menu is Null, defaulting to NS03<br>";
            $group = 'NS03';
        } else {
            echo 'menu_id ' . $this->app->input->getInt('Itemid', '') . '<br>';
        }

        $this->component_params = $this->app->getParams();
// get the input parameters - truncate to avoid injection attacks
        $this->scope = substr($this->app->input->getCmd('scope', 'F'), 0, 1);

        $context = 'com_ra_walks.reports.';
        // Invoked by will be blank if called from a menu or from group_list
        $invoked_by = $this->app->input->getWord('invoked_by', '');
//        die('invoked_by(' . $invoked_by . ')');
        if ($invoked_by == '') {
            $this->group_code = $this->getGroupCode();
            $this->app->setUserState($context . 'group', $this->group_code);
            $this->back = '';
            $this->app->setUserState($context . 'callback.group', '');
        } elseif ($invoked_by == 'group_list') {
            // Group code will have been passed as input
            $this->group_code = $this->app->input->getCmd('group_code', 'NS03');
            $this->app->setUserState($context . 'group', $this->group_code);
            $this->back = 'index.php?option=com_ra_walks&view=group_list';
            $this->app->setUserState($context . 'callback.group', 'group_list');
        } else {
            // We are returning from a sub-ordinate program
            // Retrieve parameters from the user state
            $this->group_code = $this->app->getUserState($context . 'group');
            echo "found $this->group_code<br>";
            $back = $this->app->getUserState($context . 'callback.group');
            if ($back == '') {
                $this->back = '';
            } else {
                $this->back = 'index.php?option=com_ra_walks&view=' . $back;
            }
        }
        if (JDEBUG) {
            echo "View: reports_group<br>";
            echo "invoked_by: $invoked_by<br>";
            echo "group: $this->group_code<br>";
            echo "scope: $this->scope<br>";
        }
        $this->app->setUserState($context . 'group', $this->group_code);
        $this->app->setUserState($context . 'scope', $this->scope);
        // Load the template header here to simplify the template
        $this->prepareDocument();

        // NOTE: name of the template has been specified in the construct above
        parent::display();
    }

    private function getGroupCode() {
        $menu = $this->app->getMenu()->getActive();
        if (is_null($menu)) {
            echo "Menu is Null, defaulting to NS03<br>";
            $group = 'NS03';
        } else {
            $this->menu_params = $menu->getParams();
            $type = $this->menu_params->get('type');
            //           var_dump($this->menu_params);
            //           echo '<br>';
            if ($type == 'home') {
                $tools_params = $this->app->getParams('com_ra_tools');
                var_dump($tools_params);
                $group = substr($tools_params->get('default_group'), 0, 4);
            } else {
                $group = $this->menu_params->get('group', 'NS03');
            }
        }
        //       die($group);
        return $group;
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

        $menus = $this->app->getMenu();

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
            $title = $this->app->get('sitename');
        } elseif ($this->app->get('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $this->app->get('sitename'), $title);
        } elseif ($this->app->get('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $this->app->get('sitename'));
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
        $target = 'index.php?option=com_ra_walks&view=reports_matrix';
        $target .= '&invoked_by=reports_group';
        $target .= '&mode=G&opt=' . $this->group_code;
        $target .= '&scope=' . $this->scope;
        $target .= '&row=' . $row . '&col=' . $col;
        return $target;
    }

}
