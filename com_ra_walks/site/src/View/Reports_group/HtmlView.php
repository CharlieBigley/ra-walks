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
 * 23/05/21 CB created from reports_area
 * 04/07/21 Cb add callback
 * 23/05/23 CB converted to Joomla 4
 * 22/01/24 CB eliminate JText
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

    protected $component_params;
//  variable for the template
    protected $callback;
    protected $mode;
    protected $opt;
    protected $scope;

    /*
      function __construct() {
      // override the name of the template file: instead of 'default', use 'reports_area_template'
      $config = array();
      $config['layout'] = 'reports_group';
      parent::__construct($config);
      }
     */

    /**
     * Display the view
     */
    public function display($template = Null) {
        // If invoked from list_groups, the specific group_code will have been passed as parameter
        // // However, if invoked fram a menu, assume it is a Group or Area site,
        // and derive the code from the component parameters
        //
        // Load the component params
        $app = Factory::getApplication();
        $this->component_params = $app->getParams();
// get the input parameters - truncate to avoid injection attacks
        $this->scope = substr($app->input->getCmd('scope', 'F'), 0, 1);
        $this->group_code = substr($app->input->getCmd('group_code', ''), 0, 4);

        if ($this->group_code == '') {
            $this->group_code = substr($this->component_params->get('default_group'), 0, 4);
        }
        $this->callback = ToolsHelper::convert_from_ASCII($app->input->getCmd('callback', ''));
        echo "callback $this->callback<br>";
        $context = 'com_ra_walks.reports.';
        $app->setUserState($context . 'mode', 'G');
        $app->setUserState($context . 'opt', $this->group_code);
//        $this->load_all = $this->component_params->get('load_all');
        // Load the template header here to simplify the template
        $this->prepareDocument();

//      set callback in globals so TopWalkLeaders can return as appropriate
        $app->setUserState('com_ra_walks.reports.topleaders', 'reports_group');

        // NOTE: name of the template has been specified in the construct above
        parent::display();
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
        $target = 'index.php?option=com_ra_walks&view=reports_matrix';
        $target .= '&scope=' . $this->scope;
        $target .= '&row=' . $row . '&col=' . $col;
        return $target;
    }

}
