<?php
/**
 * @version    1.0.0
 * @package    com_ra_walks
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2023 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ramblers\Component\Ra_walks\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;

/**
 * Walk controller class.
 *
 * @since  4.0.0
 */
class WalkController extends FormController
{
	protected $view_list = 'walks';
}
