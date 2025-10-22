<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

/** @var \Jbaylet\Component\Services\Administrator\View\Messages\HtmlView $this */

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('script', 'com_services/admin-services.js', ['version' => 'auto', 'relative' => true]);
?>

<form action="<?php echo Route::_('index.php?option=com_services&view=messages'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                
                <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
                
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('COM_SERVICES_NO_CHATS_FOUND'); ?>
                        <br><br>
                        <p><strong><?php echo Text::_('COM_SERVICES_DATABASE_CHECK'); ?></strong></p>
                        <p><?php echo Text::_('COM_SERVICES_DATABASE_INSTRUCTIONS_MESSAGES'); ?></p>
                        <div class="mt-3">
                            <a href="<?php echo Route::_('index.php?option=com_services&view=dashboard'); ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i><?php echo Text::_('COM_SERVICES_BACK_TO_DASHBOARD'); ?>
                            </a>
                        </div>
                    </div>
                <?php else : ?>
                    <!-- Chat Management Header -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <h3><i class="fas fa-comments me-2"></i><?php echo Text::_('COM_SERVICES_CHAT_THREADS'); ?></h3>
                            <p class="text-muted"><?php echo Text::_('COM_SERVICES_CHAT_MANAGEMENT_DESC'); ?></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-success" onclick="markAllChatsRead()" title="<?php echo Text::_('COM_SERVICES_MARK_ALL_READ'); ?>">
                                    <i class="fas fa-check-double"></i> <?php echo Text::_('COM_SERVICES_MARK_ALL_READ'); ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Threads List -->
                    <div class="chat-threads-container">
                        <?php foreach ($this->items as $i => $thread) : ?>
                            <div class="chat-thread-card mb-3 <?php echo ($thread->unread_count > 0) ? 'unread' : ''; ?>" data-thread-id="<?php echo $this->escape($thread->thread_id); ?>">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-1 text-center">
                                                <?php echo HTMLHelper::_('grid.id', $i, $thread->thread_id); ?>
                                            </div>
                                            
                                            <div class="col-md-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-2">
                                                        <i class="fas fa-store"></i>
                                                    </div>
                                                    <div>
                                                        <strong class="text-primary"><?php echo $this->escape($thread->business_name ?: $thread->service_title ?: 'Unknown Business'); ?></strong>
                                                        <br><small class="text-muted">ID: <?php echo (int) $thread->business_user_id; ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-2">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div>
                                                        <strong class="text-info"><?php echo $this->escape($thread->sender_name ?: $thread->receiver_name ?: 'Anonymous'); ?></strong>
                                                        <br><small class="text-muted">ID: <?php echo (int) ($thread->sender_id ?: $thread->receiver_id); ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-3">
                                                <div class="thread-info">
                                                    <strong class="thread-id"><?php echo $this->escape($thread->thread_id); ?></strong>
                                                    <br>
                                                    <small class="text-muted service-name">
                                                        <i class="fas fa-briefcase me-1"></i><?php echo $this->escape($thread->service_title ?: 'Unknown Service'); ?>
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-2">
                                                <div class="chat-stats">
                                                    <span class="badge bg-primary"><?php echo (int) $thread->message_count; ?> <?php echo Text::_('COM_SERVICES_MESSAGES'); ?></span>
                                                    <?php if ($thread->unread_count > 0) : ?>
                                                        <span class="badge bg-warning text-dark"><?php echo (int) $thread->unread_count; ?> <?php echo Text::_('COM_SERVICES_UNREAD'); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="mt-1">
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i><?php echo HTMLHelper::_('date', $thread->last_message_date, Text::_('DATE_FORMAT_LC4')); ?>
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-2 text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" onclick="viewChatThread('<?php echo $this->escape($thread->thread_id); ?>')" title="<?php echo Text::_('COM_SERVICES_VIEW_CHAT'); ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-success" onclick="markThreadRead('<?php echo $this->escape($thread->thread_id); ?>')" title="<?php echo Text::_('COM_SERVICES_MARK_READ'); ?>">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" onclick="deleteThread('<?php echo $this->escape($thread->thread_id); ?>')" title="<?php echo Text::_('COM_SERVICES_DELETE_THREAD'); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($thread->last_message_preview)) : ?>
                                            <div class="row mt-2">
                                                <div class="col-md-12">
                                                    <div class="last-message-preview">
                                                        <small class="text-muted">
                                                            <i class="fas fa-comment me-1"></i>
                                                            <strong><?php echo Text::_('COM_SERVICES_LAST_MESSAGE'); ?>:</strong> 
                                                            <?php echo HTMLHelper::_('string.truncate', $this->escape($thread->last_message_preview), 120); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
.chat-thread-card {
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.chat-thread-card.unread {
    border-left-color: #ffc107;
}

.chat-thread-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

.thread-id {
    font-family: 'Courier New', monospace;
    color: #495057;
}

.chat-stats .badge {
    margin-right: 4px;
}

.last-message-preview {
    background: #f8f9fa;
    padding: 8px;
    border-radius: 4px;
    border-left: 3px solid #dee2e6;
}
</style>

<script>
function viewChatThread(threadId) {
    window.open('index.php?option=com_services&view=messages&layout=chat&thread_id=' + encodeURIComponent(threadId), '_blank', 'width=1000,height=600,scrollbars=yes,resizable=yes');
}

function markThreadRead(threadId) {
    if (confirm('<?php echo Text::_('COM_SERVICES_CONFIRM_MARK_READ'); ?>')) {
        window.location.href = 'index.php?option=com_services&task=messages.markseen&thread_id=' + encodeURIComponent(threadId) + '&<?php echo HTMLHelper::_('form.token'); ?>=1';
    }
}

function deleteThread(threadId) {
    if (confirm('<?php echo Text::_('COM_SERVICES_CONFIRM_DELETE_THREAD'); ?>')) {
        window.location.href = 'index.php?option=com_services&task=messages.deletethread&thread_id=' + encodeURIComponent(threadId) + '&<?php echo HTMLHelper::_('form.token'); ?>=1';
    }
}

function markAllChatsRead() {
    if (confirm('<?php echo Text::_('COM_SERVICES_CONFIRM_MARK_ALL_READ'); ?>')) {
        // This would need a separate controller method to mark all chats as read
        alert('<?php echo Text::_('COM_SERVICES_MARK_ALL_READ_NOT_IMPLEMENTED'); ?>');
    }
}
</script>
