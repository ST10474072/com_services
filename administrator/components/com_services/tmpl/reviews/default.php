<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

/** @var \Jbaylet\Component\Services\Administrator\View\Reviews\HtmlView $this */

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('script', 'com_services/admin-services.js', ['version' => 'auto', 'relative' => true]);
?>

<form action="<?php echo Route::_('index.php?option=com_services&view=reviews'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                
                <?php if ($this->filterForm) : ?>
                    <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
                <?php else : ?>
                    <!-- Simple search without filter form -->
                    <div class="js-stools" role="search">
                        <div class="js-stools-container-bar">
                            <div class="btn-toolbar" role="toolbar">
                                <div class="btn-group me-2">
                                    <div class="input-group">
                                        <input type="text" name="filter_search" class="form-control" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="document.forms.adminForm.filter_search.value=''; document.forms.adminForm.submit();">
                                        <?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('COM_SERVICES_NO_BUSINESSES_FOUND'); ?>
                        <br><br>
                        <p><strong><?php echo Text::_('COM_SERVICES_DATABASE_CHECK'); ?></strong></p>
                        <p><?php echo Text::_('COM_SERVICES_DATABASE_INSTRUCTIONS_REVIEWS'); ?></p>
                        <div class="mt-3">
                            <a href="<?php echo Route::_('index.php?option=com_services&view=dashboard'); ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i><?php echo Text::_('COM_SERVICES_BACK_TO_DASHBOARD'); ?>
                            </a>
                        </div>
                    </div>
                <?php else : ?>
                    <!-- Reviews Management Header -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <h3><i class="fas fa-star me-2"></i><?php echo Text::_('COM_SERVICES_BUSINESS_REVIEWS'); ?></h3>
                            <p class="text-muted"><?php echo Text::_('COM_SERVICES_BUSINESS_REVIEWS_DESC'); ?></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-primary" onclick="toggleBusinessView()" id="businessViewBtn">
                                    <i class="fas fa-th-large"></i> <?php echo Text::_('COM_SERVICES_CARD_VIEW'); ?>
                                </button>
                                <button type="button" class="btn btn-primary" onclick="toggleBusinessView()" id="businessTableBtn">
                                    <i class="fas fa-list"></i> <?php echo Text::_('COM_SERVICES_TABLE_VIEW'); ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Business Cards View -->
                    <div id="businessCardView" class="row" style="display: none;">
                        <?php foreach ($this->items as $i => $business) : ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card business-review-card h-100" data-service-id="<?php echo (int) $business->service_id; ?>">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div class="form-check">
                                            <?php echo HTMLHelper::_('grid.id', $i, $business->service_id); ?>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <?php if ($business->pending_reviews > 0) : ?>
                                                <span class="badge bg-warning text-dark me-2">
                                                    <i class="fas fa-clock"></i> <?php echo (int) $business->pending_reviews; ?> <?php echo Text::_('COM_SERVICES_PENDING'); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <?php if (!empty($business->business_logo)) : ?>
                                                <img src="<?php echo $this->escape($business->business_logo); ?>" alt="Logo" class="business-logo me-3">
                                            <?php else : ?>
                                                <div class="business-logo-placeholder me-3">
                                                    <i class="fas fa-store"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex-grow-1">
                                                <h5 class="card-title mb-1">
                                                    <?php echo $this->escape($business->service_title); ?>
                                                </h5>
                                                <p class="text-muted mb-1">
                                                    <strong><?php echo $this->escape($business->business_name ?: 'Unknown Business'); ?></strong>
                                                    <small>(ID: <?php echo (int) $business->business_user_id; ?>)</small>
                                                </p>
                                                <?php if (!empty($business->business_location)) : ?>
                                                    <small class="text-muted">
                                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo $this->escape($business->business_location); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="review-statistics">
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <div class="stat-item">
                                                        <div class="stat-number text-primary"><?php echo (int) $business->total_reviews; ?></div>
                                                        <div class="stat-label"><?php echo Text::_('COM_SERVICES_REVIEWS'); ?></div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="stat-item">
                                                        <?php if ($business->average_rating > 0) : ?>
                                                            <div class="stat-number text-warning"><?php echo number_format($business->average_rating, 1); ?></div>
                                                            <div class="stat-label">
                                                                <span class="text-warning">
                                                                    <?php for ($star = 1; $star <= 5; $star++) : ?>
                                                                        <?php if ($star <= round($business->average_rating)) : ?>
                                                                            <i class="fas fa-star"></i>
                                                                        <?php else : ?>
                                                                            <i class="far fa-star"></i>
                                                                        <?php endif; ?>
                                                                    <?php endfor; ?>
                                                                </span>
                                                            </div>
                                                        <?php else : ?>
                                                            <div class="stat-number text-muted">-</div>
                                                            <div class="stat-label"><?php echo Text::_('COM_SERVICES_NO_RATING'); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="stat-item">
                                                        <?php if ($business->pending_reviews > 0) : ?>
                                                            <div class="stat-number text-warning"><?php echo (int) $business->pending_reviews; ?></div>
                                                            <div class="stat-label"><?php echo Text::_('COM_SERVICES_PENDING'); ?></div>
                                                        <?php else : ?>
                                                            <div class="stat-number text-success">✓</div>
                                                            <div class="stat-label"><?php echo Text::_('COM_SERVICES_UP_TO_DATE'); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <?php if (!empty($business->latest_review_date)) : ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i><?php echo Text::_('COM_SERVICES_LAST_REVIEW'); ?>: <?php echo HTMLHelper::_('date', $business->latest_review_date, 'M d, Y'); ?>
                                                </small>
                                            <?php else : ?>
                                                <small class="text-muted"><?php echo Text::_('COM_SERVICES_NO_REVIEWS_YET'); ?></small>
                                            <?php endif; ?>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" onclick="viewBusinessReviews(<?php echo (int) $business->service_id; ?>)" title="<?php echo Text::_('COM_SERVICES_VIEW_ALL_REVIEWS'); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Business Table View -->
                    <div id="businessTableView">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col" class="w-1 text-center">
                                            <?php echo HTMLHelper::_('grid.checkall'); ?>
                                        </th>
                                        <th scope="col" class="w-2 text-center"><?php echo Text::_('COM_SERVICES_BUSINESS_LOGO'); ?></th>
                                        <th scope="col"><?php echo Text::_('COM_SERVICES_BUSINESS_INFO'); ?></th>
                                        <th scope="col" class="text-center"><?php echo Text::_('COM_SERVICES_REVIEWS_STATS'); ?></th>
                                        <th scope="col" class="text-center"><?php echo Text::_('COM_SERVICES_RATING'); ?></th>
                                        <th scope="col" class="text-center"><?php echo Text::_('COM_SERVICES_PENDING_REVIEWS'); ?></th>
                                        <th scope="col" class="text-center"><?php echo Text::_('COM_SERVICES_LAST_REVIEW'); ?></th>
                                        <th scope="col" class="text-center"><?php echo Text::_('COM_SERVICES_ACTIONS'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($this->items as $i => $business) : ?>
                                        <tr class="business-row" data-service-id="<?php echo (int) $business->service_id; ?>">
                                            <td class="text-center">
                                                <?php echo HTMLHelper::_('grid.id', $i, $business->service_id); ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($business->business_logo)) : ?>
                                                    <img src="<?php echo $this->escape($business->business_logo); ?>" alt="Logo" style="height: 40px; width: auto; border-radius: 4px;">
                                                <?php else : ?>
                                                    <div class="logo-placeholder">
                                                        <i class="fas fa-store text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong class="text-primary"><?php echo $this->escape($business->service_title); ?></strong>
                                                    <br>
                                                    <span class="text-muted"><?php echo $this->escape($business->business_name ?: 'Unknown Business'); ?></span>
                                                    <small class="text-muted">(ID: <?php echo (int) $business->business_user_id; ?>)</small>
                                                    <?php if (!empty($business->business_location)) : ?>
                                                        <br><small class="text-muted">
                                                            <i class="fas fa-map-marker-alt me-1"></i><?php echo $this->escape($business->business_location); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary"><?php echo (int) $business->total_reviews; ?> <?php echo Text::_('COM_SERVICES_TOTAL'); ?></span>
                                                <?php if ($business->approved_reviews > 0) : ?>
                                                    <br><span class="badge bg-success mt-1"><?php echo (int) $business->approved_reviews; ?> <?php echo Text::_('COM_SERVICES_APPROVED'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($business->average_rating > 0) : ?>
                                                    <div class="rating-display">
                                                        <span class="text-warning">
                                                            <?php for ($star = 1; $star <= 5; $star++) : ?>
                                                                <?php if ($star <= round($business->average_rating)) : ?>
                                                                    <i class="fas fa-star"></i>
                                                                <?php else : ?>
                                                                    <i class="far fa-star"></i>
                                                                <?php endif; ?>
                                                            <?php endfor; ?>
                                                        </span>
                                                        <br><small><strong><?php echo number_format($business->average_rating, 1); ?></strong>/5</small>
                                                    </div>
                                                <?php else : ?>
                                                    <span class="text-muted"><?php echo Text::_('COM_SERVICES_NO_RATING'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($business->pending_reviews > 0) : ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-clock me-1"></i><?php echo (int) $business->pending_reviews; ?>
                                                    </span>
                                                <?php else : ?>
                                                    <span class="text-success"><i class="fas fa-check"></i></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($business->latest_review_date)) : ?>
                                                    <small><?php echo HTMLHelper::_('date', $business->latest_review_date, Text::_('DATE_FORMAT_LC4')); ?></small>
                                                <?php else : ?>
                                                    <small class="text-muted"><?php echo Text::_('COM_SERVICES_NEVER'); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" onclick="viewBusinessReviews(<?php echo (int) $business->service_id; ?>)" title="<?php echo Text::_('COM_SERVICES_VIEW_ALL_REVIEWS'); ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php echo $this->pagination->getListFooter(); ?>
                <?php endif; ?>

                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>

<style>
.business-review-card {
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
}

.business-review-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    border-color: #007bff;
}

.business-logo, .business-logo-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
}

.business-logo-placeholder {
    font-size: 24px;
    color: #6c757d;
}

.stat-item {
    padding: 8px;
}

.stat-number {
    font-size: 1.2rem;
    font-weight: bold;
}

.stat-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    font-weight: 500;
}

.logo-placeholder {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 4px;
}

.rating-display {
    min-width: 120px;
}
</style>

<script>
function viewBusinessReviews(serviceId) {
    window.open('index.php?option=com_services&view=reviews&layout=business&service_id=' + serviceId, '_blank', 'width=1200,height=700,scrollbars=yes,resizable=yes');
}

function toggleBusinessView() {
    const cardView = document.getElementById('businessCardView');
    const tableView = document.getElementById('businessTableView');
    const cardBtn = document.getElementById('businessViewBtn');
    const tableBtn = document.getElementById('businessTableBtn');
    
    if (cardView.style.display === 'none') {
        // Show card view
        cardView.style.display = 'flex';
        tableView.style.display = 'none';
        cardBtn.classList.add('btn-primary');
        cardBtn.classList.remove('btn-outline-primary');
        tableBtn.classList.add('btn-outline-primary');
        tableBtn.classList.remove('btn-primary');
        localStorage.setItem('reviews-view', 'card');
    } else {
        // Show table view
        cardView.style.display = 'none';
        tableView.style.display = 'block';
        tableBtn.classList.add('btn-primary');
        tableBtn.classList.remove('btn-outline-primary');
        cardBtn.classList.add('btn-outline-primary');
        cardBtn.classList.remove('btn-primary');
        localStorage.setItem('reviews-view', 'table');
    }
}

// Initialize view on page load
document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('reviews-view') || 'table';
    if (savedView === 'card') {
        toggleBusinessView();
    }
});
</script>
