<?php

/*
 * @version    1.1.0
 * @component  com_ra_walks
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2023 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 19/02/25 CB copied from com_ra_tools
 */

namespace Ramblers\Component\Ra_walks\Site\View\Area;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView {

    protected $area;
    protected $nation;
    protected $params;

    public function display($tpl = null) {
        $app = Factory::getApplication();
        $this->area = $app->input->getCmd('code', '');

        return parent::display($tpl);
    }

}
