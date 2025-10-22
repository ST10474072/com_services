<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Jbaylet\Component\Services\Administrator\View\Dashboard\HtmlView $this */

$stats = $this->stats;
$recentServices = $this->recentServices;
$recentReviews = $this->recentReviews;
$categoryStats = $this->categoryStats;
$pendingReviews = $this->pendingReviews;

HTMLHelper::_('stylesheet', 'com_services/admin.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('script', 'https://cdn.jsdelivr.net/npm/chart.js', ['version' => 'auto']);
HTMLHelper::_('script', 'com_services/dashboard-charts.js', ['version' => 'auto', 'relative' => true]);
?>

<div class="row">
    <div class="col-12">
        <h1 class="page-title">
            <i class="fas fa-tachometer-alt me-2"></i>
            <?php echo Text::_('COM_SERVICES_DASHBOARD'); ?>
        </h1>
    </div>
</div>

<!-- Dashboard Data for Charts -->
<script>
window.dashboardData = {
    stats: {
        totalServices: <?php echo (int) $stats->totalServices; ?>,
        totalReviews: <?php echo (int) $stats->totalReviews; ?>,
        totalMessages: <?php echo (int) $stats->totalMessages; ?>,
        featuredServices: <?php echo (int) $stats->featuredServices; ?>,
        servicesThisMonth: <?php echo (int) $stats->servicesThisMonth; ?>,
        averageRating: <?php echo (float) ($stats->averageRating ?: 0); ?>,
        pendingReviews: <?php echo count($pendingReviews); ?>
    },
    recentServices: <?php echo json_encode(array_slice($recentServices, 0, 7)); ?>,
    categoryStats: <?php echo json_encode($categoryStats ?: []); ?>
};
</script>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title mb-0"><?php echo (int) $stats->totalServices; ?></h3>
                        <p class="card-text mb-0"><?php echo Text::_('COM_SERVICES_TOTAL_SERVICES'); ?></p>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-concierge-bell fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title mb-0"><?php echo (int) $stats->totalReviews; ?></h3>
                        <p class="card-text mb-0"><?php echo Text::_('COM_SERVICES_TOTAL_REVIEWS'); ?></p>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-star fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title mb-0"><?php echo (int) $stats->totalMessages; ?></h3>
                        <p class="card-text mb-0"><?php echo Text::_('COM_SERVICES_TOTAL_MESSAGES'); ?></p>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-comments fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title mb-0"><?php echo (int) $stats->featuredServices; ?></h3>
                        <p class="card-text mb-0"><?php echo Text::_('COM_SERVICES_FEATURED_SERVICES'); ?></p>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-crown fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats Row -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="text-primary"><?php echo (int) $stats->servicesThisMonth; ?></h4>
                <p class="mb-0"><?php echo Text::_('COM_SERVICES_SERVICES_THIS_MONTH'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="text-success"><?php echo $stats->averageRating ?: '0.0'; ?>/5</h4>
                <p class="mb-0"><?php echo Text::_('COM_SERVICES_AVERAGE_RATING'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="text-danger"><?php echo count($pendingReviews); ?></h4>
                <p class="mb-0"><?php echo Text::_('COM_SERVICES_PENDING_REVIEWS'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Charts Section -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    <?php echo Text::_('COM_SERVICES_ANALYTICS_OVERVIEW'); ?>
                </h5>
            </div>
            <div class="card-body">
                <canvas id="servicesChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    <?php echo Text::_('COM_SERVICES_STATUS_BREAKDOWN'); ?>
                </h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="statusChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-left-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-success mb-1"><?php echo Text::_('COM_SERVICES_GROWTH_RATE'); ?></h6>
                        <h4 class="mb-0 text-success">+<?php echo number_format((($stats->servicesThisMonth / max($stats->totalServices - $stats->servicesThisMonth, 1)) * 100), 1); ?>%</h4>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-arrow-up fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-left-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-info mb-1"><?php echo Text::_('COM_SERVICES_ENGAGEMENT_RATE'); ?></h6>
                        <h4 class="mb-0 text-info"><?php echo $stats->totalServices > 0 ? number_format(($stats->totalReviews / $stats->totalServices), 1) : '0'; ?></h4>
                        <small class="text-muted"><?php echo Text::_('COM_SERVICES_REVIEWS_PER_SERVICE'); ?></small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-chart-bar fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-left-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-warning mb-1"><?php echo Text::_('COM_SERVICES_FEATURED_RATE'); ?></h6>
                        <h4 class="mb-0 text-warning"><?php echo $stats->totalServices > 0 ? number_format(($stats->featuredServices / $stats->totalServices * 100), 1) : '0'; ?>%</h4>
                        <small class="text-muted"><?php echo Text::_('COM_SERVICES_OF_TOTAL_SERVICES'); ?></small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-crown fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-left-danger">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-danger mb-1"><?php echo Text::_('COM_SERVICES_PENDING_RATE'); ?></h6>
                        <h4 class="mb-0 text-danger"><?php echo $stats->totalReviews > 0 ? number_format((count($pendingReviews) / $stats->totalReviews * 100), 1) : '0'; ?>%</h4>
                        <small class="text-muted"><?php echo Text::_('COM_SERVICES_PENDING_MODERATION'); ?></small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-hourglass-half fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Services -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo Text::_('COM_SERVICES_RECENT_SERVICES'); ?></h5>
                <a href="<?php echo Route::_('index.php?option=com_services&view=items'); ?>" class="btn btn-sm btn-primary">
                    <?php echo Text::_('COM_SERVICES_VIEW_ALL'); ?>
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentServices)) : ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentServices as $service) : ?>
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="me-auto">
                                    <h6 class="mb-1">
                                        <a href="<?php echo Route::_('index.php?option=com_services&view=item&id=' . $service->id); ?>">
                                            <?php echo htmlspecialchars($service->title); ?>
                                        </a>
                                        <?php if ($service->is_featured) : ?>
                                            <span class="badge bg-warning ms-1">
                                                <i class="fas fa-crown"></i>
                                            </span>
                                        <?php endif; ?>
                                    </h6>
                                    <p class="mb-1 text-muted small">
                                        <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($service->author ?? 'Unknown'); ?>
                                        <?php if ($service->location) : ?>
                                            <i class="fas fa-map-marker-alt ms-2 me-1"></i><?php echo htmlspecialchars($service->location); ?>
                                        <?php endif; ?>
                                    </p>
                                    <?php if ($service->rating_avg > 0) : ?>
                                        <small class="text-warning">
                                            <?php for ($i = 0; $i < floor($service->rating_avg); $i++) : ?>
                                                <i class="fas fa-star"></i>
                                            <?php endfor; ?>
                                            <?php if (($service->rating_avg - floor($service->rating_avg)) >= 0.5) : ?>
                                                <i class="fas fa-star-half-alt"></i>
                                            <?php endif; ?>
                                            <?php echo number_format($service->rating_avg, 1); ?> (<?php echo $service->reviews_count; ?>)
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">
                                    <?php echo HTMLHelper::_('date', $service->created, Text::_('DATE_FORMAT_LC4')); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="text-muted"><?php echo Text::_('COM_SERVICES_NO_RECENT_SERVICES'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Reviews -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo Text::_('COM_SERVICES_RECENT_REVIEWS'); ?></h5>
                <a href="<?php echo Route::_('index.php?option=com_services&view=reviews'); ?>" class="btn btn-sm btn-primary">
                    <?php echo Text::_('COM_SERVICES_VIEW_ALL'); ?>
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentReviews)) : ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentReviews as $review) : ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <div class="me-auto">
                                        <h6 class="mb-1">
                                            <span class="text-warning">
                                                <?php for ($i = 0; $i < $review->rating; $i++) : ?>
                                                    <i class="fas fa-star"></i>
                                                <?php endfor; ?>
                                                <?php for ($i = $review->rating; $i < 5; $i++) : ?>
                                                    <i class="far fa-star"></i>
                                                <?php endfor; ?>
                                            </span>
                                            <?php echo htmlspecialchars($review->service_title ?? 'Unknown Service'); ?>
                                        </h6>
                                        <p class="mb-1 small">
                                            <?php echo Text::_('COM_SERVICES_BY'); ?> <strong><?php echo htmlspecialchars($review->reviewer_name ?? 'Anonymous'); ?></strong>
                                        </p>
                                        <?php if (!empty($review->comment)) : ?>
                                            <p class="mb-1 text-muted small">
                                                <?php echo HTMLHelper::_('string.truncate', htmlspecialchars($review->comment), 100); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo HTMLHelper::_('date', $review->created, Text::_('DATE_FORMAT_LC4')); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="text-muted"><?php echo Text::_('COM_SERVICES_NO_RECENT_REVIEWS'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Pending Reviews Section -->
<?php if (!empty($pendingReviews)) : ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning">
                    <h5 class="mb-0 text-dark">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo Text::_('COM_SERVICES_PENDING_REVIEWS'); ?> (<?php echo count($pendingReviews); ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($pendingReviews as $review) : ?>
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="me-auto">
                                    <h6 class="mb-1">
                                        <span class="text-warning">
                                            <?php for ($i = 0; $i < $review->rating; $i++) : ?>
                                                <i class="fas fa-star"></i>
                                            <?php endfor; ?>
                                        </span>
                                        <?php echo htmlspecialchars($review->service_title); ?>
                                    </h6>
                                    <p class="mb-1">
                                        <?php echo Text::_('COM_SERVICES_BY'); ?> <strong><?php echo htmlspecialchars($review->reviewer_name ?? 'Anonymous'); ?></strong>
                                    </p>
                                    <?php if (!empty($review->comment)) : ?>
                                        <p class="mb-1 text-muted">
                                            <?php echo htmlspecialchars($review->comment); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex flex-column">
                                    <small class="text-muted mb-2">
                                        <?php echo HTMLHelper::_('date', $review->created, Text::_('DATE_FORMAT_LC3')); ?>
                                    </small>
                                    <div class="btn-group-sm">
                                        <a href="<?php echo Route::_('index.php?option=com_services&view=review&id=' . $review->id); ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <?php echo Text::_('COM_SERVICES_REVIEW'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?php echo Text::_('COM_SERVICES_QUICK_ACTIONS'); ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="<?php echo Route::_('index.php?option=com_services&task=item.add'); ?>" 
                           class="btn btn-success w-100 mb-2">
                            <i class="fas fa-plus me-2"></i>
                            <?php echo Text::_('COM_SERVICES_ADD_SERVICE'); ?>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo Route::_('index.php?option=com_services&view=items'); ?>" 
                           class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-list me-2"></i>
                            <?php echo Text::_('COM_SERVICES_MANAGE_SERVICES'); ?>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo Route::_('index.php?option=com_services&view=reviews'); ?>" 
                           class="btn btn-warning w-100 mb-2">
                            <i class="fas fa-star me-2"></i>
                            <?php echo Text::_('COM_SERVICES_MANAGE_REVIEWS'); ?>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo Route::_('index.php?option=com_services&view=messages'); ?>" 
                           class="btn btn-info w-100 mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <?php echo Text::_('COM_SERVICES_MANAGE_MESSAGES'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>