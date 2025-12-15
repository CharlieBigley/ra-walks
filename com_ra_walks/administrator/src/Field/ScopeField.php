<?php

/**
 * Custom Field class for filtering Walks
 *
 * @package		com_ramblers
 * @since		1.3.3
 */
defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldScope extends JFormFieldList {

    protected $type = 'Scope';

    /**
     * Method to get the field options.
     *
     * @return	array	The field option objects.
     */
    public function getOptions() {
        $options = array(
            'future' => 'Future walks',
            'past' => 'Past walks',
            'all' => 'All walks',
        );

        return $options;
    }

}
