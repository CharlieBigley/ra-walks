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

class JFormFieldLocale extends JFormFieldList {

    protected $type = 'Locale';

    /**
     * Method to get the field options.
     *
     * @return	array	The field option objects.
     */
    public function getOptions() {
        $options = array(
//            'home' => 'My home group',
            'local' => 'My local groups',
            'area' => 'My home area',
            'home' => 'My home group',
        );

        return $options;
    }

}
