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

//use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
        ->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');
//$objHelper = new ToolsHelper;
?>

<form
    action="<?php echo Route::_('index.php?option=com_ra_walks&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" enctype="multipart/form-data" name="adminForm" id="walk-form" class="form-validate form-horizontal">


    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'walk')); ?>
<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'walk', Text::_('COM_RA_WALKS_TAB_WALK', true)); ?>
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <fieldset class="adminform">
                <legend><?php echo Text::_('COM_RA_WALKS_FIELDSET_WALK'); ?></legend>
                <?php echo $this->form->renderField('walk_id'); ?>
                <?php echo $this->form->renderField('walk_date'); ?>
                <?php echo $this->form->renderField('group_code'); ?>
                <?php echo $this->form->renderField('title'); ?>
                <?php echo $this->form->renderField('description'); ?>
                <?php echo $this->form->renderField('contact_display_name'); ?>
                <?php echo $this->form->renderField('distance_miles'); ?>
                <?php echo $this->form->renderField('start_time'); ?>
                <?php echo $this->form->renderField('distance_km'); ?>

<?php echo $this->form->renderField('difficulty'); ?>


            </fieldset>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'walk', Text::_('Start details', true)); ?>
    <?php echo $this->form->renderField('start_gridref'); ?>
    <?php echo $this->form->renderField('start_details'); ?>
    <?php echo $this->form->renderField('start_postcode'); ?>
    <?php echo $this->form->renderField('start_longitude'); ?>
    <?php echo $this->form->renderField('start_latitude'); ?>

<?php echo HTMLHelper::_('uitab.endTab'); ?>
    <input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
    <input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
    <input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
    <input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
    <input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
    <?php echo $this->form->renderField('created_by'); ?>
<?php echo $this->form->renderField('modified_by'); ?>


<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value=""/>
<?php echo HTMLHelper::_('form.token'); ?>

</form>
