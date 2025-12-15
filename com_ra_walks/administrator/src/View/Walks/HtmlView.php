<?php

/**
 * @version    1.0.0
 * @package    com_ra_walks
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2023 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ramblers\Component\Ra_walks\Administrator\View\Walks;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Form\Form;
use \Joomla\CMS\HTML\Helpers\Sidebar;
use \Joomla\Component\Content\Administrator\Extension\ContentComponent;
use \Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

/**
 * View class for a list of Walks.
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView {

    protected $items;
    protected $pagination;
    protected $state;

    /**
     * Display the view
     *
     * @param   string  $tpl  Template name
     *
     * @return void
     *
     * @throws Exception
     */
    public function display($tpl = null) {
        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new \Exception(implode("\n", $errors));
        }

        $this->addToolbar();

        $this->sidebar = Sidebar::render();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function addToolbar() {
        // Suppress menu side panel
        Factory::getApplication()->input->set('hidemainmenu', true);
        $state = $this->get('State');
        $canDo = ToolsHelper::getActions();

        ToolbarHelper::title(Text::_('COM_RA_WALKS_TITLE_WALKS'), "generic");

        $toolbar = Toolbar::getInstance('toolbar');

        // Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Walks';

        if (file_exists($formPath)) {
            if ($canDo->get('core.create')) {
                $toolbar->addNew('walk.add');
            }
        }

        if ($canDo->get('core.edit.state')) {
            $dropdown = $toolbar->dropdownButton('status-group')
                    ->text('JTOOLBAR_CHANGE_STATUS')
                    ->toggleSplit(false)
                    ->icon('fas fa-ellipsis-h')
                    ->buttonClass('btn btn-action')
                    ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            if (isset($this->items[0]->state)) {
                $childBar->publish('walks.publish')->listCheck(true);
                $childBar->unpublish('walks.unpublish')->listCheck(true);
                $childBar->archive('walks.archive')->listCheck(true);
            } elseif (isset($this->items[0])) {
                // If this component does not use state then show a direct delete button as we can not trash
                $toolbar->delete('walks.delete')
                        ->text('JTOOLBAR_EMPTY_TRASH')
                        ->message('JGLOBAL_CONFIRM_DELETE')
                        ->listCheck(true);
            }

            $childBar->standardButton('duplicate')
                    ->text('JTOOLBAR_DUPLICATE')
                    ->icon('fas fa-copy')
                    ->task('walks.duplicate')
                    ->listCheck(true);

            if (isset($this->items[0]->checked_out)) {
                $childBar->checkin('walks.checkin')->listCheck(true);
            }

            if (isset($this->items[0]->state)) {
                $childBar->trash('walks.trash')->listCheck(true);
            }    
        
        }


        // Show trash and delete for components that uses the state field
        if (isset($this->items[0]->state)) {

            if ($this->state->get('filter.state') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete')) {
                $toolbar->delete('walks.delete')
                        ->text('JTOOLBAR_EMPTY_TRASH')
                        ->message('JGLOBAL_CONFIRM_DELETE')
                        ->listCheck(true);
            }
        }

        $toolbar->standardButton('nrecords')
                ->icon('fa fa-info-circle')
                ->text(number_format($this->pagination->total) . ' Records')
                ->task('')
                ->onclick('return false')
                ->listCheck(false);
        ToolbarHelper::cancel('walks.cancel', 'Return to Dashboard');
        // Set sidebar action
        Sidebar::setAction('index.php?option=com_ra_walks&view=walks');
    }

    /**
     * Method to order fields
     *
     * @return void
     */
    protected function getSortFields() {
        return array(
            'a.`id`' => Text::_('JGRID_HEADING_ID'),
            'a.`state`' => Text::_('JSTATUS'),
            'a.`ordering`' => Text::_('JGRID_HEADING_ORDERING'),
            'a.`walk_id`' => Text::_('COM_RA_WALKS_WALKS_WALK_ID'),
            'a.`walk_date`' => Text::_('COM_RA_WALKS_WALKS_WALK_DATE'),
            'a.`group_code`' => Text::_('COM_RA_WALKS_WALKS_GROUP_CODE'),
            'a.`title`' => Text::_('COM_RA_WALKS_WALKS_TITLE'),
            'a.`contact_display_name`' => Text::_('COM_RA_WALKS_WALKS_CONTACT_DISPLAY_NAME'),
            'a.`distance_miles`' => Text::_('COM_RA_WALKS_WALKS_DISTANCE_MILES'),
            'a.`start_time`' => Text::_('COM_RA_WALKS_WALKS_START_TIME'),
            'a.`start_details`' => Text::_('COM_RA_WALKS_WALKS_START_DETAILS'),
            'a.`difficulty`' => Text::_('COM_RA_WALKS_WALKS_DIFFICULTY'),
        );
    }

    /**
     * Check if state is set
     *
     * @param   mixed  $state  State
     *
     * @return bool
     */
    public function getState($state) {
        return isset($this->state->{$state}) ? $this->state->{$state} : false;
    }

}
