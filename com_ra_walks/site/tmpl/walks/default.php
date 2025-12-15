<?php
/**
 * @version    1.0.0
 * @package    com_ra_walks
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2023 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Session\Session;
//use \Joomla\CMS\User\UserFactoryInterface;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$user = Factory::getApplication()->getIdentity();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canCreate = $user->authorise('core.create', 'com_ra_walks') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'walkform.xml');
$canEdit = $user->authorise('core.edit', 'com_ra_walks') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'walkform.xml');
$canCheckin = $user->authorise('core.manage', 'com_ra_walks');
$canChange = $user->authorise('core.edit.state', 'com_ra_walks');
$canDelete = $user->authorise('core.delete', 'com_ra_walks');

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->registerAndUseStyle('ramblers', 'com_ra_tools/ramblers.css');
$target_walk = 'index.php?option=com_ra_walks&view=walk&callback=walks&id=';
$objHelper = new ToolsHelper;
?>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">
          <?php
          if (!empty($this->filterForm)) {
              echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
          }
          ?>
    <div class="table-responsive">
        <table class="table table-striped" id="walkList">
            <thead>
                <tr>
                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'Walk Id', 'a.walk_id', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'Walk date', 'a.walk_date', $listDirn, $listOrder); ?>
                    </th>
                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'Title', 'a.title', $listDirn, $listOrder); ?>
                    </th>
                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'Group', 'a.group_code', $listDirn, $listOrder); ?>
                    </th>
                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'Difficulty', 'a.difficulty', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'Miles', 'a.distance_miles', $listDirn, $listOrder); ?>
                    </th>
                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'Leader', 'a.contact_display_name', $listDirn, $listOrder); ?>
                    </th>
                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'Start details', 'a.start_details', $listDirn, $listOrder); ?>
                    </th>
                    <?php echo '<th></th>'; ?>
                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'ID', 'a.id', $listDirn, $listOrder); ?>
                    </th>

                    <th >
                        <?php echo HTMLHelper::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
                    </th>


                    <?php if ($canEdit || $canDelete): ?>
                        <th class="center">
                            <?php echo Text::_('Actions'); ?>
                        </th>
                    <?php endif; ?>

                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
                        <div class="pagination">
                            <?php echo $this->pagination->getPagesLinks(); ?>
                        </div>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <?php foreach ($this->items as $i => $item) : ?>
                    <?php $canEdit = $user->authorise('core.edit', 'com_ra_walks'); ?>
                    <?php if (!$canEdit && $user->authorise('core.edit.own', 'com_ra_walks')): ?>
                        <?php $canEdit = Factory::getApplication()->getIdentity()->id == $item->created_by; ?>
                    <?php endif; ?>

                    <tr class="row<?php echo $i % 2; ?>">



                        <?php
                        echo '<td>' . $item->walk_id . '</td>';
                        echo '<td>';
                        echo $item->walk_date . '' . $item->start_date . '<br>' . $item->start_time . '</td>';
                        echo '<td>' . $objHelper->buildLink($target_walk . $item->id, $item->title) . '</td>';
                        echo '<td>' . $item->group_code . '</td>';
                        echo '<td>' . $item->difficulty . '</td>';
                        echo '<td>' . $item->distance_miles . '</td>';
                        echo '<td>' . $item->contact_display_name . '</td>';
                        echo '<td>' . $item->start_details . '</td>';
                        ?>


                        <?php if ($canEdit || $canDelete): ?>
                            <td class="center">
                                <?php $canCheckin = Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_ra_walks.' . $item->id) || $item->checked_out == Factory::getApplication()->getIdentity()->id; ?>

                                <?php if ($canEdit && $item->checked_out == 0): ?>
                                    <a href="<?php echo Route::_('index.php?option=com_ra_walks&task=walk.edit&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button"><i class="icon-edit" ></i></a>
                                <?php endif; ?>
                                <?php if ($canDelete): ?>
                                    <a href="<?php echo Route::_('index.php?option=com_ra_walks&task=walkform.remove&id=' . $item->id, false, 2); ?>" class="btn btn-mini delete-button" type="button"><i class="icon-trash" ></i></a>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <td>
                            <?php $canCheckin = Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_ra_walks.' . $item->id) || $item->checked_out == Factory::getApplication()->getIdentity()->id; ?>
                            <?php if ($canCheckin && $item->checked_out > 0) : ?>
                                <a href="<?php echo Route::_('index.php?option=com_ra_walks&task=walk.checkin&id=' . $item->id . '&' . Session::getFormToken() . '=1'); ?>">
                                    <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'walk.', false); ?></a>
                            <?php endif; ?>
                            <a href="<?php echo Route::_('index.php?option=com_ra_walks&view=walk&id=' . (int) $item->id); ?>">
                                <?php echo $this->escape($item->id); ?></a>
                        </td>
                        <td>
                            <?php $class = ($canChange) ? 'active' : 'disabled'; ?>
                            <a class="btn btn-micro <?php echo $class; ?>" href="<?php echo ($canChange) ? JRoute::_('index.php?option=com_ra_walks&task=walk.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2), false, 2) : '#'; ?>">
                                <?php if ($item->state == 1): ?>
                                    <i class="icon-publish"></i>
                                <?php else: ?>
                                    <i class="icon-unpublish"></i>
                                <?php endif; ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($canCreate) : ?>
        <a href="<?php echo Route::_('index.php?option=com_ra_walks&task=walkform.edit&id=0', false, 0); ?>"
           class="btn btn-success btn-small"><i
                class="icon-plus"></i>
            <?php echo Text::_('Add walk'); ?></a>
        <?php endif; ?>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value=""/>
    <input type="hidden" name="filter_order_Dir" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php
echo number_format($this->pagination->total) . ' Records';
if ($canDelete) {
    $wa->addInlineScript("
			jQuery(document).ready(function () {
				jQuery('.delete-button').click(deleteItem);
			});

			function deleteItem() {

				if (!confirm(\"" . Text::_('COM_RA_WALKS_DELETE_MESSAGE') . "\")) {
					return false;
				}
			}
		", [], [], ["jquery"]);
}
?>
