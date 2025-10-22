<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

/** @var \Jbaylet\Component\Services\Administrator\View\Messages\HtmlView $this */

$threadId = $this->input->get('thread_id', '', 'string');
$model = $this->getModel();
$conversation = $model->getChatConversation($threadId);

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('script', 'com_services/admin-services.js', ['version' => 'auto', 'relative' => true]);
?>

<div class="chat-detail-container">
    <div class="chat-header bg-primary text-white p-3 mb-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-0">
                    <i class="fas fa-comments me-2"></i>
                    <?php echo Text::_('COM_SERVICES_CHAT_CONVERSATION'); ?>
                </h4>
                <small class="opacity-75">
                    <?php echo Text::_('COM_SERVICES_THREAD_ID'); ?>: 
                    <code class="text-white"><?php echo $this->escape($threadId); ?></code>
                </small>
            </div>
            <div class="col-md-4 text-end">
                <a href="<?php echo Route::_('index.php?option=com_services&view=messages'); ?>" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i><?php echo Text::_('COM_SERVICES_BACK_TO_MESSAGES'); ?>
                </a>
                <button type="button" class="btn btn-outline-light btn-sm" onclick="window.print();">
                    <i class="fas fa-print me-1"></i><?php echo Text::_('COM_SERVICES_PRINT_CHAT'); ?>
                </button>
            </div>
        </div>
    </div>

    <?php if (empty($conversation)) : ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo Text::_('COM_SERVICES_NO_MESSAGES_IN_THREAD'); ?>
        </div>
    <?php else : ?>
        
        <!-- Participants Info -->
        <div class="participants-info mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i><?php echo Text::_('COM_SERVICES_CHAT_PARTICIPANTS'); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="participant-info">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar-circle bg-primary text-white me-3">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <div>
                                        <strong><?php echo Text::_('COM_SERVICES_BUSINESS'); ?></strong>
                                        <br><?php echo $this->escape($conversation[0]->service_title ?? 'Unknown Service'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="participant-info">
                                <strong><?php echo Text::_('COM_SERVICES_PARTICIPANTS'); ?>:</strong>
                                <?php
                                $participants = [];
                                foreach ($conversation as $msg) {
                                    if (!empty($msg->sender_name)) {
                                        $participants[$msg->sender_id] = $msg->sender_name . ' (ID: ' . $msg->sender_id . ')';
                                    }
                                    if (!empty($msg->receiver_name)) {
                                        $participants[$msg->receiver_id] = $msg->receiver_name . ' (ID: ' . $msg->receiver_id . ')';
                                    }
                                }
                                foreach ($participants as $participant) {
                                    echo '<br><small class="text-muted">' . $this->escape($participant) . '</small>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="chat-meta">
                                <strong><?php echo Text::_('COM_SERVICES_CHAT_INFO'); ?>:</strong>
                                <br><small class="text-muted"><?php echo count($conversation); ?> <?php echo Text::_('COM_SERVICES_MESSAGES'); ?></small>
                                <br><small class="text-muted"><?php echo Text::_('COM_SERVICES_STARTED'); ?>: <?php echo HTMLHelper::_('date', $conversation[0]->created, Text::_('DATE_FORMAT_LC4')); ?></small>
                                <?php if (count($conversation) > 1) : ?>
                                    <br><small class="text-muted"><?php echo Text::_('COM_SERVICES_LAST_ACTIVITY'); ?>: <?php echo HTMLHelper::_('date', end($conversation)->created, Text::_('DATE_FORMAT_LC4')); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="chat-messages">
            <?php foreach ($conversation as $i => $message) : ?>
                <div class="message-bubble mb-3 <?php echo ($i % 2 == 0) ? 'sender-left' : 'sender-right'; ?>">
                    <div class="card">
                        <div class="card-body">
                            <div class="message-header d-flex justify-content-between align-items-start mb-2">
                                <div class="sender-info">
                                    <strong class="text-primary">
                                        <?php echo $this->escape($message->sender_name ?? 'Anonymous'); ?>
                                        <small class="text-muted">(ID: <?php echo (int) $message->sender_id; ?>)</small>
                                    </strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-arrow-right me-1"></i>
                                        <?php echo $this->escape($message->receiver_name ?? 'Anonymous'); ?>
                                        <small>(ID: <?php echo (int) $message->receiver_id; ?>)</small>
                                    </small>
                                </div>
                                <div class="message-meta text-end">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo HTMLHelper::_('date', $message->created, Text::_('DATE_FORMAT_LC4')); ?>
                                    </small>
                                    <?php if ($message->seen) : ?>
                                        <br><small class="text-success">
                                            <i class="fas fa-check-double me-1"></i><?php echo Text::_('COM_SERVICES_SEEN'); ?>
                                        </small>
                                    <?php else : ?>
                                        <br><small class="text-warning">
                                            <i class="fas fa-check me-1"></i><?php echo Text::_('COM_SERVICES_UNREAD'); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="message-content">
                                <div class="message-body">
                                    <?php echo nl2br($this->escape($message->body)); ?>
                                </div>
                                
                                <?php if (!empty($message->attachment)) : ?>
                                    <div class="message-attachment mt-2">
                                        <div class="alert alert-info">
                                            <i class="fas fa-paperclip me-2"></i>
                                            <strong><?php echo Text::_('COM_SERVICES_ATTACHMENT'); ?>:</strong>
                                            <a href="<?php echo $this->escape($message->attachment); ?>" target="_blank" class="ms-2">
                                                <?php echo basename($this->escape($message->attachment)); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Actions -->
        <div class="chat-actions mt-4 p-3 bg-light">
            <div class="row">
                <div class="col-md-6">
                    <h6><?php echo Text::_('COM_SERVICES_MODERATION_ACTIONS'); ?></h6>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-success btn-sm" onclick="markThreadRead('<?php echo $this->escape($threadId); ?>')">
                            <i class="fas fa-check me-1"></i><?php echo Text::_('COM_SERVICES_MARK_READ'); ?>
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="exportChat('<?php echo $this->escape($threadId); ?>')">
                            <i class="fas fa-download me-1"></i><?php echo Text::_('COM_SERVICES_EXPORT_CHAT'); ?>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteThread('<?php echo $this->escape($threadId); ?>')">
                            <i class="fas fa-trash me-1"></i><?php echo Text::_('COM_SERVICES_DELETE_THREAD'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<style>
.chat-detail-container {
    max-width: 1000px;
    margin: 0 auto;
}

.avatar-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.message-bubble {
    max-width: 85%;
}

.message-bubble.sender-left {
    margin-left: 0;
    margin-right: auto;
}

.message-bubble.sender-right {
    margin-left: auto;
    margin-right: 0;
}

.message-bubble.sender-left .card {
    border-left: 4px solid #007bff;
}

.message-bubble.sender-right .card {
    border-right: 4px solid #28a745;
    background-color: #f8f9fa;
}

.message-body {
    line-height: 1.5;
    word-wrap: break-word;
}

.participants-info .avatar-circle {
    width: 40px;
    height: 40px;
}

@media print {
    .chat-header .btn,
    .chat-actions {
        display: none !important;
    }
    
    .message-bubble {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}
</style>

<script>
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

function exportChat(threadId) {
    // This would need a separate controller method to export chat as PDF/CSV
    alert('<?php echo Text::_('COM_SERVICES_EXPORT_NOT_IMPLEMENTED'); ?>');
}

// Auto-refresh chat every 30 seconds if in popup window
if (window.opener) {
    setInterval(function() {
        window.location.reload();
    }, 30000);
}
</script>