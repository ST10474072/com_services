<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

/** @var \Jbaylet\Component\Services\Administrator\View\Reviews\HtmlView $this */

$serviceId = $this->input->get('service_id', 0, 'int');
$model = $this->getModel();
$businessInfo = $model->getBusinessInfo($serviceId);
$reviews = $model->getBusinessReviews($serviceId);

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('script', 'com_services/admin-services.js', ['version' => 'auto', 'relative' => true]);
?>

<div class="business-reviews-container">
    <?php if (empty($businessInfo)) : ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo Text::_('COM_SERVICES_BUSINESS_NOT_FOUND'); ?>
        </div>
    <?php else : ?>
        
        <!-- Business Header -->
        <div class="business-header bg-primary text-white p-4 mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <?php if (!empty($businessInfo->business_logo)) : ?>
                            <img src="<?php echo $this->escape($businessInfo->business_logo); ?>" alt="Business Logo" class="business-header-logo me-4">
                        <?php else : ?>
                            <div class="business-header-logo-placeholder me-4">
                                <i class="fas fa-store"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h2 class="mb-1"><?php echo $this->escape($businessInfo->service_title); ?></h2>
                            <p class="mb-1 opacity-75">
                                <strong><?php echo $this->escape($businessInfo->business_name ?: 'Unknown Business'); ?></strong>
                                <small>(Business ID: <?php echo (int) $businessInfo->business_user_id; ?>)</small>
                            </p>
                            <?php if (!empty($businessInfo->business_location)) : ?>
                                <p class="mb-0 opacity-75">
                                    <i class="fas fa-map-marker-alt me-2"></i><?php echo $this->escape($businessInfo->business_location); ?>
                                </p>
                            <?php endif; ?>
                            <small class="opacity-75">
                                <?php echo Text::_('COM_SERVICES_MEMBER_SINCE'); ?>: <?php echo HTMLHelper::_('date', $businessInfo->business_created, 'M Y'); ?>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?php echo Route::_('index.php?option=com_services&view=reviews'); ?>" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-1"></i><?php echo Text::_('COM_SERVICES_BACK_TO_BUSINESSES'); ?>
                    </a>
                    <button type="button" class="btn btn-outline-light" onclick="window.print();">
                        <i class="fas fa-print me-1"></i><?php echo Text::_('COM_SERVICES_PRINT_REVIEWS'); ?>
                    </button>
                </div>
            </div>
        </div>

        <?php if (empty($reviews)) : ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <?php echo Text::_('COM_SERVICES_NO_REVIEWS_FOR_BUSINESS'); ?>
            </div>
        <?php else : ?>
            
            <!-- Reviews Statistics -->
            <div class="reviews-stats mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i><?php echo Text::_('COM_SERVICES_REVIEWS_OVERVIEW'); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <div class="stat-number text-primary"><?php echo count($reviews); ?></div>
                                    <div class="stat-label"><?php echo Text::_('COM_SERVICES_TOTAL_REVIEWS'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <?php 
                                    $avgRating = 0;
                                    $totalRating = 0;
                                    foreach ($reviews as $review) {
                                        $totalRating += $review->rating;
                                    }
                                    if (count($reviews) > 0) {
                                        $avgRating = $totalRating / count($reviews);
                                    }
                                    ?>
                                    <?php if ($avgRating > 0) : ?>
                                        <div class="stat-number text-warning"><?php echo number_format($avgRating, 1); ?></div>
                                        <div class="stat-label">
                                            <span class="text-warning">
                                                <?php for ($star = 1; $star <= 5; $star++) : ?>
                                                    <?php if ($star <= round($avgRating)) : ?>
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
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <?php
                                    $approvedCount = 0;
                                    foreach ($reviews as $review) {
                                        if ($review->state == 1) $approvedCount++;
                                    }
                                    ?>
                                    <div class="stat-number text-success"><?php echo $approvedCount; ?></div>
                                    <div class="stat-label"><?php echo Text::_('COM_SERVICES_APPROVED_REVIEWS'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <?php
                                    $pendingCount = 0;
                                    foreach ($reviews as $review) {
                                        if ($review->state == 0) $pendingCount++;
                                    }
                                    ?>
                                    <div class="stat-number text-warning"><?php echo $pendingCount; ?></div>
                                    <div class="stat-label"><?php echo Text::_('COM_SERVICES_PENDING_REVIEWS'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews Actions -->
            <div class="reviews-actions mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-0"><?php echo Text::_('COM_SERVICES_BULK_MODERATION'); ?></h6>
                            </div>
                            <div class="col-md-6 text-end">
                                <form id="reviewsForm" method="post" action="<?php echo Route::_('index.php?option=com_services&view=reviews'); ?>">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success btn-sm" onclick="approveSelectedReviews()">
                                            <i class="fas fa-check me-1"></i><?php echo Text::_('COM_SERVICES_APPROVE_SELECTED'); ?>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="rejectSelectedReviews()">
                                            <i class="fas fa-times me-1"></i><?php echo Text::_('COM_SERVICES_REJECT_SELECTED'); ?>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteSelectedReviews()">
                                            <i class="fas fa-trash me-1"></i><?php echo Text::_('COM_SERVICES_DELETE_SELECTED'); ?>
                                        </button>
                                    </div>
                                    <input type="hidden" name="task" value="">
                                    <?php echo HTMLHelper::_('form.token'); ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Individual Reviews -->
            <div class="individual-reviews">
                <div class="row">
                    <div class="col-md-2">
                        <div class="review-filters sticky-top" style="top: 20px;">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><?php echo Text::_('COM_SERVICES_FILTER_REVIEWS'); ?></h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo Text::_('COM_SERVICES_FILTER_BY_STATUS'); ?></label>
                                        <select class="form-select form-select-sm" onchange="filterReviewsByStatus(this.value)">
                                            <option value=""><?php echo Text::_('COM_SERVICES_ALL_REVIEWS'); ?></option>
                                            <option value="1"><?php echo Text::_('COM_SERVICES_APPROVED_ONLY'); ?></option>
                                            <option value="0"><?php echo Text::_('COM_SERVICES_PENDING_ONLY'); ?></option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo Text::_('COM_SERVICES_FILTER_BY_RATING'); ?></label>
                                        <select class="form-select form-select-sm" onchange="filterReviewsByRating(this.value)">
                                            <option value=""><?php echo Text::_('COM_SERVICES_ALL_RATINGS'); ?></option>
                                            <option value="5">5 <?php echo Text::_('COM_SERVICES_STARS'); ?></option>
                                            <option value="4">4 <?php echo Text::_('COM_SERVICES_STARS'); ?></option>
                                            <option value="3">3 <?php echo Text::_('COM_SERVICES_STARS'); ?></option>
                                            <option value="2">2 <?php echo Text::_('COM_SERVICES_STARS'); ?></option>
                                            <option value="1">1 <?php echo Text::_('COM_SERVICES_STAR'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-10">
                        <?php foreach ($reviews as $i => $review) : ?>
                            <div class="review-item mb-3 <?php echo ($review->state == 0) ? 'pending-review' : 'approved-review'; ?>" data-review-id="<?php echo (int) $review->id; ?>" data-rating="<?php echo (int) $review->rating; ?>" data-status="<?php echo (int) $review->state; ?>">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-1">
                                                <div class="form-check">
                                                    <input class="form-check-input review-checkbox" type="checkbox" value="<?php echo (int) $review->id; ?>" id="review_<?php echo (int) $review->id; ?>">
                                                    <label class="form-check-label" for="review_<?php echo (int) $review->id; ?>"></label>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-11">
                                                <div class="review-header d-flex justify-content-between align-items-start mb-3">
                                                    <div class="reviewer-info">
                                                        <h6 class="mb-1">
                                                            <i class="fas fa-user me-2 text-primary"></i>
                                                            <?php echo $this->escape($review->reviewer_name ?: 'Anonymous User'); ?>
                                                            <small class="text-muted">(User ID: <?php echo (int) $review->user_id; ?>)</small>
                                                        </h6>
                                                        <div class="rating-display mb-2">
                                                            <span class="text-warning me-2">
                                                                <?php for ($star = 1; $star <= 5; $star++) : ?>
                                                                    <?php if ($star <= $review->rating) : ?>
                                                                        <i class="fas fa-star"></i>
                                                                    <?php else : ?>
                                                                        <i class="far fa-star"></i>
                                                                    <?php endif; ?>
                                                                <?php endfor; ?>
                                                            </span>
                                                            <span class="fw-bold"><?php echo (int) $review->rating; ?>/5</span>
                                                        </div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>
                                                            <?php echo HTMLHelper::_('date', $review->created, Text::_('DATE_FORMAT_LC4')); ?>
                                                        </small>
                                                    </div>
                                                    
                                                    <div class="review-status">
                                                        <?php if ($review->state == 1) : ?>
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-check me-1"></i><?php echo Text::_('COM_SERVICES_APPROVED'); ?>
                                                            </span>
                                                        <?php else : ?>
                                                            <span class="badge bg-warning text-dark">
                                                                <i class="fas fa-clock me-1"></i><?php echo Text::_('COM_SERVICES_PENDING'); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <?php if (!empty($review->comment)) : ?>
                                                    <div class="review-content mb-3">
                                                        <div class="review-comment p-3 bg-light rounded">
                                                            <i class="fas fa-quote-left text-muted me-2"></i>
                                                            <?php echo nl2br($this->escape($review->comment)); ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="review-actions d-flex justify-content-between align-items-center">
                                                    <div class="review-stats">
                                                        <?php if ($review->helpful > 0) : ?>
                                                            <small class="text-muted">
                                                                <i class="fas fa-thumbs-up me-1"></i>
                                                                <?php echo (int) $review->helpful; ?> <?php echo Text::_('COM_SERVICES_FOUND_HELPFUL'); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($review->state == 0) : ?>
                                                            <button type="button" class="btn btn-outline-success" onclick="approveReview(<?php echo (int) $review->id; ?>)" title="<?php echo Text::_('COM_SERVICES_APPROVE_REVIEW'); ?>">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if ($review->state == 1) : ?>
                                                            <button type="button" class="btn btn-outline-warning" onclick="rejectReview(<?php echo (int) $review->id; ?>)" title="<?php echo Text::_('COM_SERVICES_REJECT_REVIEW'); ?>">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-outline-danger" onclick="deleteReview(<?php echo (int) $review->id; ?>)" title="<?php echo Text::_('COM_SERVICES_DELETE_REVIEW'); ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        <?php endif; ?>
        
    <?php endif; ?>
</div>

<style>
.business-reviews-container {
    max-width: 1400px;
    margin: 0 auto;
}

.business-header-logo, .business-header-logo-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.business-header-logo-placeholder {
    font-size: 32px;
    color: rgba(255, 255, 255, 0.8);
}

.stat-item {
    padding: 12px;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    display: block;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 0.8rem;
    text-transform: uppercase;
    font-weight: 500;
    color: #6c757d;
}

.review-item.pending-review {
    border-left: 4px solid #ffc107;
}

.review-item.approved-review {
    border-left: 4px solid #28a745;
}

.review-comment {
    font-style: italic;
    line-height: 1.6;
    border-left: 3px solid #007bff;
}

.review-filters .card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.rating-display {
    min-height: 24px;
}

@media print {
    .business-header .btn,
    .reviews-actions,
    .review-filters,
    .review-actions .btn-group {
        display: none !important;
    }
    
    .review-item {
        break-inside: avoid;
        page-break-inside: avoid;
    }
    
    .col-md-2 {
        display: none !important;
    }
    
    .col-md-10 {
        width: 100% !important;
    }
}
</style>

<script>
function approveReview(reviewId) {
    if (confirm('<?php echo Text::_('COM_SERVICES_CONFIRM_APPROVE_REVIEW'); ?>')) {
        const form = document.getElementById('reviewsForm');
        form.innerHTML += '<input type="hidden" name="cid[]" value="' + reviewId + '">';
        form.elements['task'].value = 'reviews.approve';
        form.submit();
    }
}

function rejectReview(reviewId) {
    if (confirm('<?php echo Text::_('COM_SERVICES_CONFIRM_REJECT_REVIEW'); ?>')) {
        const form = document.getElementById('reviewsForm');
        form.innerHTML += '<input type="hidden" name="cid[]" value="' + reviewId + '">';
        form.elements['task'].value = 'reviews.reject';
        form.submit();
    }
}

function deleteReview(reviewId) {
    if (confirm('<?php echo Text::_('COM_SERVICES_CONFIRM_DELETE_REVIEW'); ?>')) {
        const form = document.getElementById('reviewsForm');
        form.innerHTML += '<input type="hidden" name="cid[]" value="' + reviewId + '">';
        form.elements['task'].value = 'reviews.delete';
        form.submit();
    }
}

function approveSelectedReviews() {
    const selected = getSelectedReviews();
    if (selected.length === 0) {
        alert('<?php echo Text::_('COM_SERVICES_NO_REVIEWS_SELECTED'); ?>');
        return;
    }
    
    if (confirm('<?php echo Text::_('COM_SERVICES_CONFIRM_APPROVE_SELECTED'); ?>')) {
        const form = document.getElementById('reviewsForm');
        selected.forEach(id => {
            form.innerHTML += '<input type="hidden" name="cid[]" value="' + id + '">';
        });
        form.elements['task'].value = 'reviews.approve';
        form.submit();
    }
}

function rejectSelectedReviews() {
    const selected = getSelectedReviews();
    if (selected.length === 0) {
        alert('<?php echo Text::_('COM_SERVICES_NO_REVIEWS_SELECTED'); ?>');
        return;
    }
    
    if (confirm('<?php echo Text::_('COM_SERVICES_CONFIRM_REJECT_SELECTED'); ?>')) {
        const form = document.getElementById('reviewsForm');
        selected.forEach(id => {
            form.innerHTML += '<input type="hidden" name="cid[]" value="' + id + '">';
        });
        form.elements['task'].value = 'reviews.reject';
        form.submit();
    }
}

function deleteSelectedReviews() {
    const selected = getSelectedReviews();
    if (selected.length === 0) {
        alert('<?php echo Text::_('COM_SERVICES_NO_REVIEWS_SELECTED'); ?>');
        return;
    }
    
    if (confirm('<?php echo Text::_('COM_SERVICES_CONFIRM_DELETE_SELECTED'); ?>')) {
        const form = document.getElementById('reviewsForm');
        selected.forEach(id => {
            form.innerHTML += '<input type="hidden" name="cid[]" value="' + id + '">';
        });
        form.elements['task'].value = 'reviews.delete';
        form.submit();
    }
}

function getSelectedReviews() {
    const checkboxes = document.querySelectorAll('.review-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function filterReviewsByStatus(status) {
    const reviews = document.querySelectorAll('.review-item');
    reviews.forEach(review => {
        if (status === '' || review.dataset.status === status) {
            review.style.display = 'block';
        } else {
            review.style.display = 'none';
        }
    });
}

function filterReviewsByRating(rating) {
    const reviews = document.querySelectorAll('.review-item');
    reviews.forEach(review => {
        if (rating === '' || review.dataset.rating === rating) {
            review.style.display = 'block';
        } else {
            review.style.display = 'none';
        }
    });
}
</script>