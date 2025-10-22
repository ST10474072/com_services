<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/** @var \Jbaylet\Component\Services\Site\View\Item\HtmlView $this */

$item = $this->item;
$reviews = $this->reviews;
$relatedServices = $this->relatedServices;
$user = Factory::getUser();

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
?>

<div class="com-services-item">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Service Header -->
                <div class="service-header mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <?php if (!empty($item->logo)) : ?>
                                <img src="<?php echo htmlspecialchars($item->logo); ?>" 
                                     alt="<?php echo htmlspecialchars($item->title); ?>" 
                                     class="service-logo img-fluid rounded">
                            <?php else : ?>
                                <div class="service-logo-placeholder bg-light rounded d-flex align-items-center justify-content-center">
                                    <i class="fas fa-building fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-9">
                            <div class="service-title-section">
                                <h1 class="service-title"><?php echo htmlspecialchars($item->title); ?></h1>
                                
                                <?php if (!empty($item->business_name)) : ?>
                                    <p class="business-name text-muted mb-2">
                                        <i class="fas fa-store me-2"></i>
                                        <?php echo htmlspecialchars($item->business_name); ?>
                                    </p>
                                <?php endif; ?>

                                <?php if ($item->rating_avg > 0) : ?>
                                    <div class="rating-display mb-2">
                                        <?php 
                                        $fullStars = floor($item->rating_avg);
                                        $halfStar = ($item->rating_avg - $fullStars) >= 0.5;
                                        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                        ?>
                                        <div class="stars-container">
                                            <?php for ($i = 0; $i < $fullStars; $i++) : ?>
                                                <i class="fas fa-star text-warning"></i>
                                            <?php endfor; ?>
                                            <?php if ($halfStar) : ?>
                                                <i class="fas fa-star-half-alt text-warning"></i>
                                            <?php endif; ?>
                                            <?php for ($i = 0; $i < $emptyStars; $i++) : ?>
                                                <i class="far fa-star text-warning"></i>
                                            <?php endfor; ?>
                                            <span class="rating-text ms-2">
                                                <?php echo number_format($item->rating_avg, 1); ?>/5 
                                                (<?php echo (int) $item->reviews_count; ?> <?php echo Text::_('COM_SERVICES_REVIEWS'); ?>)
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="service-badges">
                                    <?php if ($item->is_247) : ?>
                                        <span class="badge bg-success me-1">
                                            <i class="fas fa-clock me-1"></i>24/7
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($item->is_emergency) : ?>
                                        <span class="badge bg-danger me-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo Text::_('COM_SERVICES_EMERGENCY'); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($item->is_featured) : ?>
                                        <span class="badge bg-primary me-1">
                                            <i class="fas fa-star me-1"></i><?php echo Text::_('COM_SERVICES_FEATURED'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Description -->
<?php if (!empty($item->long_description)) : ?>
                    <div class="service-description mb-4">
                        <h3><?php echo Text::_('COM_SERVICES_ABOUT_SERVICE'); ?></h3>
                        <div class="description-content">
                            <?php echo $item->long_description; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Business About -->
                <?php if (!empty($item->about)) : ?>
                    <div class="business-about mb-4">
                        <h3><?php echo Text::_('COM_SERVICES_ABOUT_BUSINESS'); ?></h3>
                        <div class="about-content">
                            <?php echo nl2br(htmlspecialchars($item->about)); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Location & Map -->
                <?php if (!empty($item->location) || (!empty($item->lat) && !empty($item->lng))) : ?>
                    <div class="location-section mb-4">
                        <h3><?php echo Text::_('COM_SERVICES_LOCATION'); ?></h3>
                        <?php if (!empty($item->location)) : ?>
                            <p><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($item->location); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item->address)) : ?>
                            <p><i class="fas fa-address-card me-2"></i><?php echo htmlspecialchars($item->address); ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($item->lat) && !empty($item->lng)) : ?>
                            <div class="map-container mb-3">
                                <div id="service-map" style="height: 300px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <p class="text-muted"><?php echo Text::_('COM_SERVICES_MAP_PLACEHOLDER'); ?></p>
                                    <!-- Map integration would go here -->
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Conversation History -->
                <?php if ($user->id > 0 && isset($item->message_thread) && !empty($item->message_thread)) : ?>
                    <div class="collapse mb-4" id="conversationHistory">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><?php echo Text::_('COM_SERVICES_CONVERSATION_HISTORY'); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="conversation-thread" style="max-height: 300px; overflow-y: auto;">
                                    <?php foreach ($item->message_thread as $message) : ?>
                                        <div class="message-item mb-3 <?php echo ($message->sender_id == $user->id) ? 'message-sent' : 'message-received'; ?>">
                                            <div class="message-content p-2 rounded">
                                                <div class="message-text"><?php echo nl2br($this->escape($message->body)); ?></div>
                                                <small class="message-meta text-muted d-block mt-1">
                                                    <strong><?php echo $this->escape($message->sender_name); ?></strong> - 
                                                    <?php echo HTMLHelper::_('date', $message->created, 'M j, Y g:i A'); ?>
                                                    <?php if ($message->sender_id != $user->id && !$message->seen) : ?>
                                                        <span class="badge bg-primary ms-1">New</span>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Reviews Section -->
                <div class="reviews-section mb-4">
                    <?php $reviewStats = $item->review_stats ?? null; ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3><?php echo Text::_('COM_SERVICES_REVIEWS'); ?> 
                            (<?php echo $reviewStats ? $reviewStats->total_reviews : count($item->reviews ?? []); ?>)
                        </h3>
                        <?php if ($reviewStats && $reviewStats->average_rating > 0) : ?>
                            <div class="overall-rating">
                                <span class="rating-number h4 mb-0"><?php echo number_format($reviewStats->average_rating, 1); ?></span>
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                                        <i class="fa<?php echo $i <= round($reviewStats->average_rating) ? 's' : 'r'; ?> fa-star text-warning"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Rating Breakdown -->
                    <?php if ($reviewStats && $reviewStats->total_reviews > 0) : ?>
                        <div class="rating-breakdown mb-4">
                            <div class="row">
                                <?php for ($star = 5; $star >= 1; $star--) : ?>
                                    <?php $count = $reviewStats->{$star === 1 ? 'one_star' : ($star === 2 ? 'two_star' : ($star === 3 ? 'three_star' : ($star === 4 ? 'four_star' : 'five_star')))}; ?>
                                    <?php $percent = $reviewStats->{$star === 1 ? 'one_star_percent' : ($star === 2 ? 'two_star_percent' : ($star === 3 ? 'three_star_percent' : ($star === 4 ? 'four_star_percent' : 'five_star_percent')))}; ?>
                                    <div class="col-12 mb-1">
                                        <div class="d-flex align-items-center">
                                            <span class="rating-label me-2"><?php echo $star; ?> <i class="fas fa-star text-warning"></i></span>
                                            <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                                <div class="progress-bar bg-warning" style="width: <?php echo $percent; ?>%"></div>
                                            </div>
                                            <span class="rating-count text-muted"><?php echo $count; ?></span>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php $reviews = $item->reviews ?? $reviews ?? []; ?>
                    <?php if (!empty($reviews)) : ?>
                        <div class="reviews-list">
                            <?php foreach ($reviews as $review) : ?>
                                <div class="review-item border rounded p-3 mb-3">
                                    <div class="review-header d-flex justify-content-between align-items-start mb-2">
                                        <div class="reviewer-info">
                                            <strong><?php echo htmlspecialchars($review->reviewer_name ?? 'Anonymous'); ?></strong>
                                            <div class="review-rating">
                                                <?php 
                                                $reviewFullStars = floor($review->rating);
                                                $reviewHalfStar = ($review->rating - $reviewFullStars) >= 0.5;
                                                $reviewEmptyStars = 5 - $reviewFullStars - ($reviewHalfStar ? 1 : 0);
                                                ?>
                                                <?php for ($i = 0; $i < $reviewFullStars; $i++) : ?>
                                                    <i class="fas fa-star text-warning"></i>
                                                <?php endfor; ?>
                                                <?php if ($reviewHalfStar) : ?>
                                                    <i class="fas fa-star-half-alt text-warning"></i>
                                                <?php endif; ?>
                                                <?php for ($i = 0; $i < $reviewEmptyStars; $i++) : ?>
                                                    <i class="far fa-star text-warning"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo HTMLHelper::_('date', $review->created, Text::_('DATE_FORMAT_LC3')); ?>
                                        </small>
                                    </div>
                                    <?php if (!empty($review->comment)) : ?>
                                        <div class="review-comment">
                                            <?php echo nl2br(htmlspecialchars($review->comment)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($review->helpful > 0) : ?>
                                        <div class="review-helpful mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-thumbs-up me-1"></i>
                                                <?php echo $review->helpful; ?> <?php echo Text::_('COM_SERVICES_FOUND_HELPFUL'); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="text-muted"><?php echo Text::_('COM_SERVICES_NO_REVIEWS_YET'); ?></p>
                    <?php endif; ?>

                    <?php if ($user->id > 0) : ?>
                        <div class="write-review mt-4">
                            <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#reviewForm">
                                <i class="fas fa-star me-2"></i><?php echo Text::_('COM_SERVICES_WRITE_REVIEW'); ?>
                            </button>
                            <div class="collapse mt-3" id="reviewForm">
                                <form action="<?php echo Route::_('index.php?option=com_services&task=review.submit'); ?>" method="post" class="needs-validation">
                                    <input type="hidden" name="service_id" value="<?php echo (int) $item->id; ?>">
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo Text::_('COM_SERVICES_RATING'); ?></label>
                                        <div class="rating-input">
                                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                <input type="radio" name="rating" value="<?php echo $i; ?>" id="rating<?php echo $i; ?>" required>
                                                <label for="rating<?php echo $i; ?>" class="star-label">
                                                    <i class="fas fa-star"></i>
                                                </label>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="comment" class="form-label"><?php echo Text::_('COM_SERVICES_REVIEW_COMMENT'); ?></label>
                                        <textarea class="form-control" id="comment" name="comment" rows="4" placeholder="<?php echo Text::_('COM_SERVICES_REVIEW_COMMENT_PLACEHOLDER'); ?>"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <?php echo Text::_('COM_SERVICES_SUBMIT_REVIEW'); ?>
                                    </button>
                                    <?php echo HTMLHelper::_('form.token'); ?>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Related Services -->
                <?php if (!empty($relatedServices)) : ?>
                    <div class="related-services">
                        <h3><?php echo Text::_('COM_SERVICES_RELATED_SERVICES'); ?></h3>
                        <div class="row">
                            <?php foreach ($relatedServices as $related) : ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card service-card">
                                        <?php if (!empty($related->logo)) : ?>
                                            <img src="<?php echo htmlspecialchars($related->logo); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($related->title); ?>">
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a href="<?php echo Route::_('index.php?option=com_services&view=item&id=' . $related->id); ?>">
                                                    <?php echo htmlspecialchars($related->title); ?>
                                                </a>
                                            </h5>
                                            <?php if ($related->rating_avg > 0) : ?>
                                                <div class="rating-small mb-2">
                                                    <?php echo number_format($related->rating_avg, 1); ?>/5 
                                                    (<?php echo $related->reviews_count; ?>)
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($related->location)) : ?>
                                                <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($related->location); ?></small></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sidebar-sticky">
                    <!-- Contact Card -->
                    <div class="contact-card card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo Text::_('COM_SERVICES_CONTACT_INFO'); ?></h5>
                            
                            <?php if (!empty($item->phone)) : ?>
                                <div class="contact-item mb-2">
                                    <i class="fas fa-phone text-primary me-2"></i>
                                    <a href="tel:<?php echo htmlspecialchars($item->phone); ?>" class="contact-link">
                                        <?php echo htmlspecialchars($item->phone); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($item->email)) : ?>
                                <div class="contact-item mb-2">
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($item->email); ?>" class="contact-link">
                                        <?php echo htmlspecialchars($item->email); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($item->whatsapp)) : ?>
                                <div class="contact-item mb-2">
                                    <i class="fab fa-whatsapp text-success me-2"></i>
                                    <a href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/[^0-9]/', '', $item->whatsapp)); ?>" target="_blank" class="contact-link">
                                        <?php echo htmlspecialchars($item->whatsapp); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($item->website)) : ?>
                                <div class="contact-item mb-2">
                                    <i class="fas fa-globe text-primary me-2"></i>
                                    <a href="<?php echo htmlspecialchars($item->website); ?>" target="_blank" class="contact-link">
                                        <?php echo Text::_('COM_SERVICES_WEBSITE'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <!-- Message Button -->
                            <?php if ($user->id > 0 && isset($item->can_message) && $item->can_message) : ?>
                                <button type="button" class="btn btn-primary w-100 mt-3" data-bs-toggle="modal" data-bs-target="#messageModal">
                                    <i class="fas fa-comment me-2"></i><?php echo Text::_('COM_SERVICES_SEND_MESSAGE'); ?>
                                </button>
                                
                                <!-- Show existing conversation if available -->
                                <?php if (isset($item->message_thread) && !empty($item->message_thread)) : ?>
                                    <div class="mt-3">
                                        <small class="text-muted"><?php echo Text::sprintf('COM_SERVICES_EXISTING_CONVERSATION', count($item->message_thread)); ?></small>
                                        <button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-1" data-bs-toggle="collapse" data-bs-target="#conversationHistory">
                                            <i class="fas fa-history me-1"></i><?php echo Text::_('COM_SERVICES_VIEW_CONVERSATION'); ?>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            <?php elseif ($user->guest) : ?>
                                <div class="alert alert-info mt-3">
                                    <small><?php echo Text::_('COM_SERVICES_LOGIN_TO_MESSAGE'); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Enquiry Form -->
                    <div class="enquiry-card card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo Text::_('COM_SERVICES_QUICK_ENQUIRY'); ?></h5>
                            <form action="<?php echo Route::_('index.php?option=com_services&task=enquiry.submit'); ?>" method="post">
                                <input type="hidden" name="service_id" value="<?php echo (int) $item->id; ?>">
                                <div class="mb-3">
                                    <label for="enquiry_name" class="form-label"><?php echo Text::_('COM_SERVICES_NAME'); ?> *</label>
                                    <input type="text" class="form-control" id="enquiry_name" name="name" 
                                           value="<?php echo $user->id ? htmlspecialchars($user->name) : ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="enquiry_phone" class="form-label"><?php echo Text::_('COM_SERVICES_PHONE'); ?> *</label>
                                    <input type="tel" class="form-control" id="enquiry_phone" name="phone" required>
                                </div>
                                <div class="mb-3">
                                    <label for="enquiry_email" class="form-label"><?php echo Text::_('COM_SERVICES_EMAIL'); ?></label>
                                    <input type="email" class="form-control" id="enquiry_email" name="email" 
                                           value="<?php echo $user->id ? htmlspecialchars($user->email) : ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="enquiry_message" class="form-label"><?php echo Text::_('COM_SERVICES_MESSAGE'); ?> *</label>
                                    <textarea class="form-control" id="enquiry_message" name="message" rows="4" 
                                              placeholder="<?php echo Text::_('COM_SERVICES_ENQUIRY_PLACEHOLDER'); ?>" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <?php echo Text::_('COM_SERVICES_SEND_ENQUIRY'); ?>
                                </button>
                                <?php echo HTMLHelper::_('form.token'); ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo Text::_('COM_SERVICES_SEND_MESSAGE'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo Route::_('index.php?option=com_services&task=message.send'); ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="service_id" value="<?php echo (int) $item->id; ?>">
                    <input type="hidden" name="receiver_id" value="<?php echo (int) $item->created_by; ?>">
                    <div class="mb-3">
                        <label for="message_body" class="form-label"><?php echo Text::_('COM_SERVICES_MESSAGE'); ?></label>
                        <textarea class="form-control" id="message_body" name="body" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo Text::_('JCANCEL'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo Text::_('COM_SERVICES_SEND'); ?></button>
                </div>
                <?php echo HTMLHelper::_('form.token'); ?>
            </form>
        </div>
    </div>
</div>

<style>
/* Rating input stars */
.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.rating-input input[type="radio"] {
    display: none;
}

.star-label {
    color: #ddd;
    font-size: 1.5rem;
    cursor: pointer;
    transition: color 0.2s;
}

.rating-input input[type="radio"]:checked ~ .star-label,
.star-label:hover,
.rating-input input[type="radio"]:checked ~ .star-label ~ .star-label {
    color: #ffd43b;
}

/* Service logo */
.service-logo, .service-logo-placeholder {
    width: 150px;
    height: 150px;
    object-fit: cover;
}

/* Contact links */
.contact-link {
    text-decoration: none;
}

.contact-link:hover {
    text-decoration: underline;
}

/* Conversation Styling */
.message-sent {
    text-align: right;
}

.message-received {
    text-align: left;
}

.message-sent .message-content {
    background-color: #007bff;
    color: white;
    margin-left: 20%;
}

.message-received .message-content {
    background-color: #f8f9fa;
    color: #333;
    margin-right: 20%;
}

/* Rating Breakdown */
.rating-breakdown .progress {
    background-color: #e9ecef;
}

.rating-label {
    min-width: 60px;
    font-size: 0.9rem;
}

.rating-count {
    min-width: 30px;
    font-size: 0.9rem;
}

.overall-rating {
    text-align: center;
}

.rating-number {
    font-weight: bold;
    color: #ffc107;
}

/* Enhanced Review Cards */
.review-item {
    background: #fff;
    border: 1px solid #e9ecef;
    transition: box-shadow 0.2s;
}

.review-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>