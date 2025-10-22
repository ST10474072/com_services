<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var \Jbaylet\Component\Services\Administrator\View\Items\HtmlView $this */

$user = Factory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('script', 'com_services/admin-services.js', ['version' => 'auto', 'relative' => true]);
?>

<form action="<?php echo Route::_('index.php?option=com_services&view=items'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
                
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('COM_SERVICES_NO_ITEMS_MESSAGE'); ?>
                        <br><br>
                        <p><strong><?php echo Text::_('COM_SERVICES_DATABASE_CHECK'); ?></strong></p>
                        <p><?php echo Text::_('COM_SERVICES_DATABASE_INSTRUCTIONS'); ?></p>
                        <div class="mt-3">
                            <a href="<?php echo Route::_('index.php?option=com_services&view=dashboard'); ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i><?php echo Text::_('COM_SERVICES_BACK_TO_DASHBOARD'); ?>
                            </a>
                            <button type="button" class="btn btn-success ms-2" onclick="createTables()">
                                <i class="fas fa-database me-2"></i><?php echo Text::_('COM_SERVICES_CREATE_TABLES'); ?>
                            </button>
                        </div>
                    </div>
                <?php else : ?>
                    <!-- Enhanced Batch Operations Toolbar -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-success" onclick="batchPublish()" data-bs-toggle="tooltip" title="<?php echo Text::_('COM_SERVICES_BATCH_PUBLISH'); ?>">
                                    <i class="fas fa-check"></i> <?php echo Text::_('JPUBLISH'); ?>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="batchUnpublish()" data-bs-toggle="tooltip" title="<?php echo Text::_('COM_SERVICES_BATCH_UNPUBLISH'); ?>">
                                    <i class="fas fa-times"></i> <?php echo Text::_('JUNPUBLISH'); ?>
                                </button>
                                <button type="button" class="btn btn-outline-warning" onclick="batchFeature()" data-bs-toggle="tooltip" title="<?php echo Text::_('COM_SERVICES_BATCH_FEATURE'); ?>">
                                    <i class="fas fa-crown"></i> <?php echo Text::_('COM_SERVICES_TOOLBAR_FEATURE'); ?>
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="batchLocation()" data-bs-toggle="tooltip" title="<?php echo Text::_('COM_SERVICES_BATCH_LOCATION'); ?>">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo Text::_('COM_SERVICES_SET_LOCATION'); ?>
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="batchDelete()" data-bs-toggle="tooltip" title="<?php echo Text::_('JACTION_DELETE'); ?>">
                                    <i class="fas fa-trash"></i> <?php echo Text::_('JACTION_DELETE'); ?>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary" onclick="toggleView('card')" id="cardViewBtn">
                                    <i class="fas fa-th-large"></i> <?php echo Text::_('COM_SERVICES_CARD_VIEW'); ?>
                                </button>
                                <button type="button" class="btn btn-primary" onclick="toggleView('table')" id="tableViewBtn">
                                    <i class="fas fa-list"></i> <?php echo Text::_('COM_SERVICES_TABLE_VIEW'); ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Card View (Hidden by default) -->
                    <div id="cardView" class="row" style="display: none;">
                        <?php foreach ($this->items as $i => $item) : ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card service-card h-100" data-id="<?php echo $item->id; ?>">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div class="form-check">
                                            <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <?php if (isset($item->is_featured) && $item->is_featured) : ?>
                                                <span class="badge bg-warning text-dark me-2">
                                                    <i class="fas fa-crown"></i> <?php echo Text::_('COM_SERVICES_FEATURED'); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'items.', true); ?>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <?php if ($user->authorise('core.edit', 'com_services.' . $item->id)) : ?>
                                                <a href="<?php echo Route::_('index.php?option=com_services&task=item.edit&id=' . (int) $item->id); ?>" class="text-decoration-none">
                                                    <?php echo $this->escape($item->title); ?>
                                                </a>
                                            <?php else : ?>
                                                <?php echo $this->escape($item->title); ?>
                                            <?php endif; ?>
                                        </h5>
                                        <?php if (!empty($item->author)) : ?>
                                            <p class="card-text text-muted mb-2">
                                                <i class="fas fa-user me-1"></i><?php echo Text::_('COM_SERVICES_BY'); ?> <?php echo $this->escape($item->author); ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if (!empty($item->location)) : ?>
                                            <p class="card-text text-muted mb-2">
                                                <i class="fas fa-map-marker-alt me-1"></i><?php echo $this->escape($item->location); ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if (isset($item->rating_avg) && $item->rating_avg > 0) : ?>
                                            <div class="mb-2">
                                                <span class="text-warning">
                                                    <?php for ($star = 1; $star <= 5; $star++) : ?>
                                                        <?php if ($star <= floor($item->rating_avg)) : ?>
                                                            <i class="fas fa-star"></i>
                                                        <?php elseif ($star - 0.5 <= $item->rating_avg) : ?>
                                                            <i class="fas fa-star-half-alt"></i>
                                                        <?php else : ?>
                                                            <i class="far fa-star"></i>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </span>
                                                <span class="ms-1"><?php echo number_format($item->rating_avg, 1); ?></span>
                                                <?php if (isset($item->reviews_count) && $item->reviews_count > 0) : ?>
                                                    <small class="text-muted">(<?php echo $item->reviews_count; ?> <?php echo Text::_('COM_SERVICES_REVIEWS'); ?>)</small>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">ID: <?php echo (int) $item->id; ?></small>
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($user->authorise('core.edit', 'com_services.' . $item->id)) : ?>
                                                    <a href="<?php echo Route::_('index.php?option=com_services&task=item.edit&id=' . (int) $item->id); ?>" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-outline-info btn-sm" onclick="viewDetails(<?php echo $item->id; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Enhanced Table View -->
                    <div id="tableView">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="itemsList">
                                <thead class="table-dark">
                                    <tr>
                                        <td class="w-1 text-center">
                                            <?php echo HTMLHelper::_('grid.checkall'); ?>
                                        </td>
                                        <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                            <?php echo HTMLHelper::_('searchtools.sort', '', 'a.state', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_STATUS', 'icon-publish'); ?>
                                        </th>
                                        <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                                            <?php echo Text::_('COM_SERVICES_HEADING_IMAGE'); ?>
                                        </th>
                                        <th scope="col">
                                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_SERVICES_HEADING_TITLE', 'a.title', $listDirn, $listOrder); ?>
                                        </th>
                                        <th scope="col" class="w-15 d-none d-md-table-cell">
                                            <?php echo Text::_('JCATEGORY'); ?>
                                        </th>
                                        <th scope="col" class="w-20 d-none d-md-table-cell">
                                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_SERVICES_HEADING_LOCATION', 'a.location', $listDirn, $listOrder); ?>
                                        </th>
                                        <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                            <?php echo Text::_('COM_SERVICES_HEADING_RATING'); ?>
                                        </th>
                                        <th scope="col" class="w-5 d-none d-lg-table-cell text-center">
                                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_SERVICES_HEADING_FEATURED', 'a.is_featured', $listDirn, $listOrder); ?>
                                        </th>
                                        <th scope="col" class="w-10 d-none d-lg-table-cell text-center">
                                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                                        </th>
                                        <th scope="col" class="w-10 text-center"><?php echo Text::_('COM_SERVICES_ACTIONS'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($this->items as $i => $item) : ?>
                                        <tr class="service-row" data-id="<?php echo $item->id; ?>">
                                            <td class="text-center">
                                                <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                                            </td>
                                            <td class="text-center d-none d-md-table-cell">
                                                <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'items.', true); ?>
                                            </td>
                                            <td class="text-center d-none d-md-table-cell">
                                                <?php if (!empty($item->logo)) : ?>
                                                    <img src="<?php echo htmlspecialchars($item->logo); ?>" alt="logo" style="height:36px;width:auto;border-radius:4px;" />
                                                <?php else : ?>
                                                    <span class="text-muted"><i class="far fa-image"></i></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="has-context">
                                                <div class="d-flex align-items-center">
                                                    <?php if (isset($item->is_featured) && $item->is_featured) : ?>
                                                        <span class="badge bg-warning text-dark me-2">
                                                            <i class="fas fa-crown"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                    <div>
                                                        <?php if ($user->authorise('core.edit', 'com_services.' . $item->id)) : ?>
                                                            <a href="<?php echo Route::_('index.php?option=com_services&task=item.edit&id=' . (int) $item->id); ?>" 
                                                               class="fw-bold text-decoration-none" 
                                                               title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
                                                                <?php echo $this->escape($item->title); ?>
                                                            </a>
                                                        <?php else : ?>
                                                            <span class="fw-bold"><?php echo $this->escape($item->title); ?></span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($item->author)) : ?>
                                                            <div class="small text-muted">
                                                                <i class="fas fa-user me-1"></i><?php echo Text::_('COM_SERVICES_BY'); ?> <?php echo $this->escape($item->author); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="small d-none d-md-table-cell">
                                                <?php if (!empty($item->category_title)) : ?>
                                                    <i class="fas fa-folder text-primary me-1"></i><?php echo $this->escape($item->category_title); ?>
                                                <?php else : ?>
                                                    <span class="text-muted"><?php echo Text::_('JNONE'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="small d-none d-md-table-cell">
                                                <?php if (!empty($item->location)) : ?>
                                                    <i class="fas fa-map-marker-alt text-primary me-1"></i><?php echo $this->escape($item->location); ?>
                                                <?php else : ?>
                                                    <span class="text-muted"><?php echo Text::_('COM_SERVICES_NO_LOCATION'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="small d-none d-md-table-cell text-center">
                                                <?php if (isset($item->rating_avg) && $item->rating_avg > 0) : ?>
                                                    <div class="rating-display">
                                                        <span class="text-warning">
                                                            <?php for ($star = 1; $star <= 5; $star++) : ?>
                                                                <?php if ($star <= floor($item->rating_avg)) : ?>
                                                                    <i class="fas fa-star"></i>
                                                                <?php elseif ($star - 0.5 <= $item->rating_avg) : ?>
                                                                    <i class="fas fa-star-half-alt"></i>
                                                                <?php else : ?>
                                                                    <i class="far fa-star"></i>
                                                                <?php endif; ?>
                                                            <?php endfor; ?>
                                                        </span>
                                                        <div class="small">
                                                            <strong><?php echo number_format($item->rating_avg, 1); ?></strong>
                                                            <?php if (isset($item->reviews_count) && $item->reviews_count > 0) : ?>
                                                                <span class="text-muted">(<?php echo $item->reviews_count; ?>)</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php else : ?>
                                                    <span class="text-muted"><?php echo Text::_('COM_SERVICES_NO_RATING'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center d-none d-lg-table-cell">
                                                <?php if (isset($item->is_featured) && $item->is_featured) : ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-star"></i> <?php echo Text::_('COM_SERVICES_FEATURED'); ?>
                                                    </span>
                                                <?php else : ?>
                                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="toggleFeature(<?php echo $item->id; ?>)" data-bs-toggle="tooltip" title="<?php echo Text::_('COM_SERVICES_MAKE_FEATURED'); ?>">
                                                        <i class="far fa-star"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                            <td class="d-none d-lg-table-cell text-center">
                                                <span class="badge bg-secondary"><?php echo (int) $item->id; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <?php if ($user->authorise('core.edit', 'com_services.' . $item->id)) : ?>
                                                        <a href="<?php echo Route::_('index.php?option=com_services&task=item.edit&id=' . (int) $item->id); ?>" 
                                                           class="btn btn-outline-primary" 
                                                           data-bs-toggle="tooltip" 
                                                           title="<?php echo Text::_('JACTION_EDIT'); ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-outline-info" onclick="viewDetails(<?php echo $item->id; ?>)" data-bs-toggle="tooltip" title="<?php echo Text::_('COM_SERVICES_VIEW_DETAILS'); ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" onclick="deleteItem(<?php echo $item->id; ?>)" data-bs-toggle="tooltip" title="<?php echo Text::_('JACTION_DELETE'); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php // load the pagination. ?>
                    <?php echo $this->pagination->getListFooter(); ?>
                <?php endif; ?>

                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>">
                <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>

<script>
function createTables() {
    if (confirm('<?php echo Text::_('COM_SERVICES_CREATE_TABLES_CONFIRM'); ?>')) {
        // This would trigger the SQL installation
        window.location.href = '<?php echo Route::_('index.php?option=com_installer&view=install'); ?>';
    }
}
</script>

<style>
.alert {
    border-radius: 8px;
    padding: 1.5rem;
}

.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.badge {
    font-size: 0.75rem;
}

.text-warning .fas, .text-warning .far {
    color: #ffc107 !important;
}
</style>