<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

/** @var \Jbaylet\Component\Services\Site\View\Items\HtmlView $this */

$items = $this->items;
$pagination = $this->pagination;
?>
<div class="com-services-items">
    <div class="page-header">
        <h1><?php echo Text::_('COM_SERVICES_ITEMS_TITLE'); ?></h1>
        <p class="lead"><?php echo Text::_('COM_SERVICES_ITEMS_DESC'); ?></p>
    </div>

    <!-- Advanced Search and Filter Form -->
    <div class="filter-form mb-4">
        <form action="<?php echo Route::_('index.php?option=com_services&view=items'); ?>" method="post" name="adminForm" id="adminForm">
            <!-- Primary Search Row -->
            <div class="row mb-3">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="filter_search" class="form-label"><?php echo Text::_('COM_SERVICES_FILTER_SEARCH_LABEL'); ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" name="filter_search" id="filter_search" class="form-control" 
                                   placeholder="<?php echo Text::_('COM_SERVICES_FILTER_SEARCH_PLACEHOLDER'); ?>" 
                                   value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="filter_location" class="form-label"><?php echo Text::_('COM_SERVICES_FILTER_LOCATION_LABEL'); ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                            <input type="text" name="filter_location" id="filter_location" class="form-control" 
                                   placeholder="<?php echo Text::_('COM_SERVICES_FILTER_LOCATION_PLACEHOLDER'); ?>" 
                                   value="<?php echo $this->escape($this->state->get('filter.location')); ?>" />
                        </div>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <label for="filter_radius" class="form-label"><?php echo Text::_('COM_SERVICES_FILTER_RADIUS_LABEL'); ?></label>
                        <select name="filter_radius" id="filter_radius" class="form-select">
                            <option value=""><?php echo Text::_('COM_SERVICES_FILTER_RADIUS_ANY'); ?></option>
                            <option value="5"<?php echo ($this->state->get('filter.radius') == 5) ? ' selected="selected"' : ''; ?>>5 km</option>
                            <option value="10"<?php echo ($this->state->get('filter.radius') == 10) ? ' selected="selected"' : ''; ?>>10 km</option>
                            <option value="25"<?php echo ($this->state->get('filter.radius') == 25) ? ' selected="selected"' : ''; ?>>25 km</option>
                            <option value="50"<?php echo ($this->state->get('filter.radius') == 50) ? ' selected="selected"' : ''; ?>>50 km</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Advanced Filters Row -->
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="filter_category_id" class="form-label"><?php echo Text::_('COM_SERVICES_FILTER_CATEGORY_LABEL'); ?></label>
                        <select name="filter_category_id" id="filter_category_id" class="form-select">
                            <option value=""><?php echo Text::_('COM_SERVICES_FILTER_CATEGORY_ALL'); ?></option>
                            <?php if (!empty($this->categories)) : ?>
                                <?php foreach ($this->categories as $category) : ?>
                                    <option value="<?php echo (int) $category->id; ?>"<?php echo ($this->state->get('filter.category_id') == $category->id) ? ' selected="selected"' : ''; ?>>
                                        <?php echo $this->escape($category->title); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="filter_rating" class="form-label"><?php echo Text::_('COM_SERVICES_FILTER_RATING_LABEL'); ?></label>
                        <select name="filter_rating" id="filter_rating" class="form-select">
                            <option value=""><?php echo Text::_('COM_SERVICES_FILTER_RATING_ANY'); ?></option>
                            <option value="4"<?php echo ($this->state->get('filter.rating') == 4) ? ' selected="selected"' : ''; ?>><?php echo Text::_('COM_SERVICES_FILTER_RATING_4PLUS'); ?></option>
                            <option value="3"<?php echo ($this->state->get('filter.rating') == 3) ? ' selected="selected"' : ''; ?>><?php echo Text::_('COM_SERVICES_FILTER_RATING_3PLUS'); ?></option>
                            <option value="2"<?php echo ($this->state->get('filter.rating') == 2) ? ' selected="selected"' : ''; ?>><?php echo Text::_('COM_SERVICES_FILTER_RATING_2PLUS'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="form-check-group mt-4">
                            <div class="form-check">
                                <input type="hidden" name="filter_247" value="0">
                                <input class="form-check-input" type="checkbox" name="filter_247" id="filter_247" value="1" 
                                       <?php echo ($this->state->get('filter.247') == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="filter_247">
                                    <i class="fas fa-clock me-1"></i>24/7 <?php echo Text::_('COM_SERVICES_FILTER_247'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="form-check-group mt-4">
                            <div class="form-check">
                                <input type="hidden" name="filter_emergency" value="0">
                                <input class="form-check-input" type="checkbox" name="filter_emergency" id="filter_emergency" value="1" 
                                       <?php echo ($this->state->get('filter.emergency') == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="filter_emergency">
                                    <i class="fas fa-exclamation-triangle me-1"></i><?php echo Text::_('COM_SERVICES_FILTER_EMERGENCY'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="form-check-group mt-4">
                            <div class="form-check">
                                <input type="hidden" name="filter_featured" value="0">
                                <input class="form-check-input" type="checkbox" name="filter_featured" id="filter_featured" value="1" 
                                       <?php echo ($this->state->get('filter.featured') == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="filter_featured">
                                    <i class="fas fa-star me-1"></i><?php echo Text::_('COM_SERVICES_FILTER_FEATURED'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mt-4">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-filters">
                            <i class="fas fa-eraser me-1"></i><?php echo Text::_('COM_SERVICES_CLEAR_FILTERS'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Sort Options -->
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="filter_sort" class="form-label"><?php echo Text::_('COM_SERVICES_SORT_BY'); ?></label>
                        <select name="filter_sort" id="filter_sort" class="form-select">
                            <option value="created_desc"<?php echo ($this->state->get('filter.sort') == 'created_desc') ? ' selected="selected"' : ''; ?>><?php echo Text::_('COM_SERVICES_SORT_NEWEST'); ?></option>
                            <option value="title_asc"<?php echo ($this->state->get('filter.sort') == 'title_asc') ? ' selected="selected"' : ''; ?>><?php echo Text::_('COM_SERVICES_SORT_TITLE'); ?></option>
                            <option value="rating_desc"<?php echo ($this->state->get('filter.sort') == 'rating_desc') ? ' selected="selected"' : ''; ?>><?php echo Text::_('COM_SERVICES_SORT_RATING'); ?></option>
                            <option value="reviews_desc"<?php echo ($this->state->get('filter.sort') == 'reviews_desc') ? ' selected="selected"' : ''; ?>><?php echo Text::_('COM_SERVICES_SORT_REVIEWS'); ?></option>
                            <option value="location_asc"<?php echo ($this->state->get('filter.sort') == 'location_asc') ? ' selected="selected"' : ''; ?>><?php echo Text::_('COM_SERVICES_SORT_LOCATION'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="results-info mt-4">
                        <span class="text-muted">
                            <?php if (!empty($items)) : ?>
                                <?php echo Text::sprintf('COM_SERVICES_RESULTS_SHOWING', count($items)); ?>
                                <?php if ($pagination && $pagination->total > count($items)) : ?>
                                    <?php echo Text::sprintf('COM_SERVICES_RESULTS_OF_TOTAL', $pagination->total); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="limitstart" value="" />
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>

    <?php if (!empty($items)) : ?>
        <div class="row">
            <?php foreach ($items as $item) : ?>
                <div class="col-md-4 mb-4">
                    <div class="card service-card">
                        <?php if (!empty($item->logo)) : ?>
                            <img src="<?php echo htmlspecialchars($item->logo); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item->title); ?>">
                        <?php else : ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                <span class="text-muted"><?php echo Text::_('COM_SERVICES_NO_IMAGE'); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="<?php echo Route::_('index.php?option=com_services&view=item&id=' . (int) $item->id); ?>">
                                    <?php echo htmlspecialchars($item->title); ?>
                                </a>
                            </h5>
<?php if (!empty($item->short_description)) : ?>
                                <p class="card-text"><?php echo htmlspecialchars(HTMLHelper::_('string.truncate', strip_tags($item->short_description), 140)); ?></p>
                            <?php endif; ?>
                            <ul class="list-unstyled">
                                <?php if (!empty($item->location)) : ?>
                                    <li><strong><?php echo Text::_('COM_SERVICES_FIELD_LOCATION'); ?>:</strong> <?php echo htmlspecialchars($item->location); ?></li>
                                <?php endif; ?>
                                <?php if ($item->rating_avg > 0) : ?>
                                    <li><strong><?php echo Text::_('COM_SERVICES_FIELD_RATING'); ?>:</strong> 
                                        <?php echo number_format($item->rating_avg, 1); ?>/5 
                                        (<?php echo (int) $item->reviews_count; ?> <?php echo Text::_('COM_SERVICES_REVIEWS'); ?>)
                                    </li>
                                <?php endif; ?>
                                <li class="mt-2">
                                    <?php if ($item->is_247) : ?><span class="badge bg-success me-1">24/7</span><?php endif; ?>
                                    <?php if ($item->is_emergency) : ?><span class="badge bg-danger me-1"><?php echo Text::_('COM_SERVICES_EMERGENCY'); ?></span><?php endif; ?>
                                    <?php if ($item->is_featured) : ?><span class="badge bg-primary me-1"><?php echo Text::_('COM_SERVICES_FEATURED'); ?></span><?php endif; ?>
                                </li>
                            </ul>
                            <a href="<?php echo Route::_('index.php?option=com_services&view=item&id=' . (int) $item->id); ?>" class="btn btn-primary">
                                <?php echo Text::_('COM_SERVICES_VIEW_DETAILS'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pagination->pagesTotal > 1) : ?>
            <div class="pagination-wrapper mt-4">
                <?php echo $pagination->getPagesLinks(); ?>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <div class="alert alert-info">
            <h4><?php echo Text::_('COM_SERVICES_NO_ITEMS_FOUND_TITLE'); ?></h4>
            <p><?php echo Text::_('COM_SERVICES_NO_ITEMS_FOUND_DESC'); ?></p>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Clear Filters functionality
    const clearFiltersBtn = document.getElementById('clear-filters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show loading state
            clearFiltersBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Clearing...';
            clearFiltersBtn.disabled = true;
            
            // Clear all form inputs
            document.getElementById('filter_search').value = '';
            document.getElementById('filter_location').value = '';
            document.getElementById('filter_radius').selectedIndex = 0;
            document.getElementById('filter_category_id').selectedIndex = 0;
            document.getElementById('filter_rating').selectedIndex = 0;
            document.getElementById('filter_sort').selectedIndex = 0;
            
            // Uncheck all checkboxes
            document.getElementById('filter_247').checked = false;
            document.getElementById('filter_emergency').checked = false;
            document.getElementById('filter_featured').checked = false;
            
            // Submit form to clear filters
            document.forms.adminForm.submit();
        });
    }
    
    // Auto-submit on filter changes for better UX
    const filterSelects = document.querySelectorAll('#filter_category_id, #filter_rating, #filter_sort, #filter_radius');
    filterSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            document.forms.adminForm.submit();
        });
    });
    
    // Auto-submit on checkbox changes (with small delay to prevent issues)
    const filterCheckboxes = document.querySelectorAll('#filter_247, #filter_emergency, #filter_featured');
    filterCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            // Add small delay to ensure checkbox state is properly set
            setTimeout(function() {
                document.forms.adminForm.submit();
            }, 100);
        });
    });
    
    // Handle search form submission
    const searchForm = document.forms.adminForm;
    const searchBtn = searchForm.querySelector('button[type="submit"]');
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            // Reset limitstart when searching to go to first page
            const limitstartInput = searchForm.querySelector('input[name="limitstart"]');
            if (limitstartInput) {
                limitstartInput.value = '0';
            }
            
            // Show loading state for search button
            if (searchBtn) {
                const originalText = searchBtn.innerHTML;
                searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Searching...';
                searchBtn.disabled = true;
                
                // Restore button after a timeout (in case form submission fails)
                setTimeout(function() {
                    searchBtn.innerHTML = originalText;
                    searchBtn.disabled = false;
                }, 5000);
            }
        });
    }
});
</script>

<style>
/* Filter button styling */
#clear-filters {
    transition: all 0.3s ease;
}

#clear-filters:hover {
    background-color: #dc3545;
    color: white;
    border-color: #dc3545;
}

/* Loading state for form submissions */
.form-loading {
    opacity: 0.7;
    pointer-events: none;
}

.form-loading::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Service card hover effects */
.service-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.service-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

/* Filter form styling */
.filter-form {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
}

.filter-form .form-group label {
    font-weight: 600;
    color: #495057;
}

/* Results info styling */
.results-info {
    font-size: 0.95rem;
}

.results-info .text-muted {
    color: #6c757d !important;
}
</style>
