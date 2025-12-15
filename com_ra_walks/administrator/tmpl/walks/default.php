<?php
/**
 * @version    1.0.0
 * @package    com_ra_walks
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2023 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 06/07/23 delete ordering
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->registerAndUseStyle('ramblers', 'com_ra_tools/ramblers.css');

$user = Factory::getApplication()->getIdentity();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canOrder = $user->authorise('core.edit.state', 'com_ra_walks');
$objHelper = new ToolsHelper;
?>

<form action="<?php echo Route::_('index.php?option=com_ra_walks&view=walks'); ?>" method="post"
      name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

                <div class="clearfix"></div>
                <table class="table table-striped" id="walkList">
                    <thead>
                        <tr>
                            <th class="w-1 text-center">
                                <input type="checkbox" autocomplete="off" class="form-check-input" name="checkall-toggle" value=""
                                       title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
                            </th>
                            <th  scope="col" class="w-1 text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                            </th>

                            <th class='left'>
                                <?php echo HTMLHelper::_('searchtools.sort', 'walk_id', 'a.walk_id', $listDirn, $listOrder); ?>
                            </th>
                            <th class='left'>
                                <?php echo HTMLHelper::_('searchtools.sort', 'Date', 'a.walk_date', $listDirn, $listOrder); ?>
                            </th>
                            <th class='left'>
                                <?php echo HTMLHelper::_('searchtools.sort', 'group_code', 'a.group_code', $listDirn, $listOrder); ?>
                            </th>
                            <th class='left'>
                                <?php echo HTMLHelper::_('searchtools.sort', 'Title', 'a.title', $listDirn, $listOrder); ?>
                            </th>
                            <th class='left'>
                                <?php echo HTMLHelper::_('searchtools.sort', 'Leader', 'a.contact_display_name', $listDirn, $listOrder); ?>
                            </th>
                            <th class='left'>
                                <?php echo HTMLHelper::_('searchtools.sort', 'Mi', 'a.distance_miles', $listDirn, $listOrder); ?>
                            </th>
                            <th class='left'>
                                <?php echo HTMLHelper::_('searchtools.sort', 'Start', 'a.start_time', $listDirn, $listOrder); ?>
                            </th>
                            <th class='left'>
                                <?php echo HTMLHelper::_('searchtools.sort', 'start_details', 'a.start_details', $listDirn, $listOrder); ?>
                            </th>
                            <th class='left'>
                                <?php echo HTMLHelper::_('searchtools.sort', 'Diff', 'a.difficulty', $listDirn, $listOrder); ?>
                            </th>

                            <th scope="col" class="w-3 d-none d-lg-table-cell" >

                                <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>					</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
                                <?php echo $this->pagination->getListFooter(); ?>
                            </td>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php
                        foreach ($this->items as $i => $item) {
                            $canCreate = $user->authorise('core.create', 'com_ra_walks');
                            $canEdit = $user->authorise('core.edit', 'com_ra_walks');
                            $canCheckin = $user->authorise('core.manage', 'com_ra_walks');
                            $canChange = $user->authorise('core.edit.state', 'com_ra_walks');
                            echo '<tr class="row' . $i / 2 . '">';
                            echo '<td class="text-center">' . HTMLHelper::_('grid.id', $i, $item->id) . '</td>' . PHP_EOL;
                            echo '<td class="text-center">' . HTMLHelper::_('jgrid.published', $item->state, $i, 'walks.', $canChange, 'cb') . '</td>' . PHP_EOL;
                            echo '<td>' . $item->walk_id . '</td>' . PHP_EOL;
                            echo '<td>' . $item->walk_date . '</td>' . PHP_EOL;
                            echo '<td>' . $item->group_code . '</td>' . PHP_EOL;
                            if ($canEdit) {
                                $target = 'administrator/index.php?option=com_ra_walks&view=walk&layout=edit&id=' . $item->id;
                                echo '<td>' . $objHelper->buildLink($target, $item->title) . '</td>' . PHP_EOL;
                            } else {
                                echo '<td>' . $item->title . '</td>' . PHP_EOL;
                            }
                            echo '<td>' . $item->contact_display_name . '</td>' . PHP_EOL;
                            echo '<td>' . $item->distance_miles . '</td>' . PHP_EOL;
                            echo '<td>' . $item->start_time . '</td>' . PHP_EOL;
                            echo '<td>' . $item->start_details . '</td>' . PHP_EOL;
                            echo '<td>' . $item->difficulty . '</td>' . PHP_EOL;
                            echo '<td class="d-none d-lg-table-cell">' . $item->id . '</td>' . PHP_EOL;
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>

                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="boxchecked" value="0"/>
                <input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>"/>
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>