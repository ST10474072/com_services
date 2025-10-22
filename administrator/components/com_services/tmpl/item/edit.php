<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Jbaylet\Component\Services\Administrator\View\Item\HtmlView $this */

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.tooltip');
?>

<form action="<?php echo Route::_('index.php?option=com_services&layout=edit&id=' . (int) ($this->item->id ?? 0)); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">

  <div class="row">
    <div class="col-lg-9">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0"><?php echo Text::_('COM_SERVICES_ITEM_DETAILS'); ?></h5>
        </div>
        <div class="card-body">
          <?php echo $this->form->renderField('title'); ?>
          <?php echo $this->form->renderField('alias'); ?>
          <?php echo $this->form->renderField('category_id'); ?>
          <?php echo $this->form->renderField('logo'); ?>
          <?php echo $this->form->renderField('location'); ?>
          <div class="row">
            <div class="col-md-6">
              <?php echo $this->form->renderField('lat'); ?>
            </div>
            <div class="col-md-6">
              <?php echo $this->form->renderField('lng'); ?>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label"><?php echo Text::_('COM_SERVICES_FIELD_MAP_LABEL'); ?></label>
            <div id="services-map" style="height: 320px; border-radius: 6px; overflow: hidden;"></div>
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo Text::_('COM_SERVICES_FIELD_MAP_SEARCH_LABEL'); ?></label>
            <div class="input-group">
              <input type="text" id="services-map-search" class="form-control" placeholder="<?php echo Text::_('COM_SERVICES_FIELD_MAP_SEARCH_PLACEHOLDER'); ?>">
              <button type="button" class="btn btn-primary" id="services-map-search-btn"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            </div>
            <div class="form-text"><?php echo Text::_('COM_SERVICES_FIELD_MAP_HELP'); ?></div>
          </div>

          <?php echo $this->form->renderField('short_description'); ?>
          <?php echo $this->form->renderField('long_description'); ?>
        </div>
      </div>
    </div>

    <div class="col-lg-3">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0"><?php echo Text::_('JSTATUS'); ?></h5>
        </div>
        <div class="card-body">
          <?php echo $this->form->renderField('state'); ?>
          <?php echo $this->form->renderField('is_featured'); ?>
          <?php echo $this->form->renderField('is_247'); ?>
          <?php echo $this->form->renderField('is_emergency'); ?>
        </div>
      </div>

      <div class="card mt-3">
        <div class="card-header">
          <h5 class="mb-0"><?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></h5>
        </div>
        <div class="card-body">
          <?php echo $this->form->renderField('created'); ?>
          <?php echo $this->form->renderField('created_by'); ?>
          <?php echo $this->form->renderField('modified'); ?>
          <?php echo $this->form->renderField('modified_by'); ?>
        </div>
      </div>
    </div>
  </div>

  <input type="hidden" name="task" value="" />
  <?php echo HTMLHelper::_('form.token'); ?>

  <?php
    // Load Leaflet (no API key required) and our map picker script
    echo HTMLHelper::_('stylesheet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', ['version' => 'auto', 'relative' => false]);
    echo HTMLHelper::_('script', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', ['version' => 'auto', 'relative' => false]);
    echo HTMLHelper::_('script', 'media/com_services/js/map-picker.js', ['version' => 'auto', 'relative' => true]);
  ?>
</form>
