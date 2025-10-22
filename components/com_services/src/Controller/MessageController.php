<?php
namespace Jbaylet\Component\Services\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/**
 * Message controller for services messaging system
 */
class MessageController extends BaseController
{
    /**
     * Send a message to service provider
     *
     * @return  void
     */
    public function send()
    {
        // Check for request forgeries
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        
        $app = Factory::getApplication();
        $user = Factory::getUser();
        
        // Check if user is logged in
        if ($user->guest) {
            $app->enqueueMessage(Text::_('COM_SERVICES_LOGIN_REQUIRED'), 'error');
            $app->redirect(Route::_('index.php?option=com_users&view=login'));
            return;
        }
        
        $input = $app->input;
        $serviceId = $input->getInt('service_id', 0);
        $receiverId = $input->getInt('receiver_id', 0);
        $body = $input->getString('body', '');
        
        // Validate input
        if (!$serviceId || !$receiverId || empty(trim($body))) {
            $app->enqueueMessage(Text::_('COM_SERVICES_MESSAGE_INVALID_DATA'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_services&view=item&id=' . $serviceId));
            return;
        }
        
        // Prevent users from messaging themselves
        if ($user->id == $receiverId) {
            $app->enqueueMessage(Text::_('COM_SERVICES_CANNOT_MESSAGE_YOURSELF'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_services&view=item&id=' . $serviceId));
            return;
        }
        
        try {
            $db = Factory::getDbo();
            
            // Verify service exists and receiver is the owner
            $query = $db->getQuery(true)
                ->select('id, created_by, title')
                ->from('#__services_items')
                ->where('id = ' . (int) $serviceId)
                ->where('created_by = ' . (int) $receiverId)
                ->where('state = 1');
                
            $db->setQuery($query);
            $service = $db->loadObject();
            
            if (!$service) {
                $app->enqueueMessage(Text::_('COM_SERVICES_MESSAGE_INVALID_SERVICE'), 'error');
                $this->setRedirect(Route::_('index.php?option=com_services&view=items'));
                return;
            }
            
            // Generate thread ID (unique identifier for conversation between these two users about this service)
            $threadId = md5($serviceId . '_' . min($user->id, $receiverId) . '_' . max($user->id, $receiverId));
            
            // Insert message
            $messageData = [
                'thread_id' => $threadId,
                'service_id' => $serviceId,
                'sender_id' => $user->id,
                'receiver_id' => $receiverId,
                'body' => trim($body),
                'created' => Factory::getDate()->toSql(),
                'seen' => 0
            ];
            
            $query = $db->getQuery(true)
                ->insert('#__services_messages')
                ->columns(array_keys($messageData))
                ->values(implode(',', array_map([$db, 'quote'], $messageData)));
                
            $db->setQuery($query);
            $db->execute();
            
            // Send notification email to receiver
            $this->sendMessageNotification($service, $user, $body);
            
            $app->enqueueMessage(Text::_('COM_SERVICES_MESSAGE_SENT_SUCCESS'), 'success');
            
        } catch (\Exception $e) {
            $app->enqueueMessage(Text::_('COM_SERVICES_MESSAGE_SEND_ERROR') . ': ' . $e->getMessage(), 'error');
        }
        
        $this->setRedirect(Route::_('index.php?option=com_services&view=item&id=' . $serviceId));
    }
    
    /**
     * Mark messages as seen
     *
     * @return  void
     */
    public function markSeen()
    {
        $app = Factory::getApplication();
        $user = Factory::getUser();
        
        if ($user->guest) {
            echo json_encode(['success' => false, 'message' => 'Login required']);
            $app->close();
            return;
        }
        
        $input = $app->input;
        $serviceId = $input->getInt('service_id', 0);
        $senderId = $input->getInt('sender_id', 0);
        
        if (!$serviceId || !$senderId) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            $app->close();
            return;
        }
        
        try {
            $db = Factory::getDbo();
            
            // Mark messages from sender as seen by current user
            $query = $db->getQuery(true)
                ->update('#__services_messages')
                ->set('seen = 1')
                ->where('service_id = ' . (int) $serviceId)
                ->where('sender_id = ' . (int) $senderId)
                ->where('receiver_id = ' . (int) $user->id)
                ->where('seen = 0');
                
            $db->setQuery($query);
            $db->execute();
            
            echo json_encode(['success' => true, 'updated' => $db->getAffectedRows()]);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        
        $app->close();
    }
    
    /**
     * Send email notification for new message
     *
     * @param   object  $service  Service object
     * @param   object  $sender   Sender user object
     * @param   string  $message  Message body
     *
     * @return  void
     */
    private function sendMessageNotification($service, $sender, $message)
    {
        try {
            $app = Factory::getApplication();
            $db = Factory::getDbo();
            
            // Get receiver email
            $query = $db->getQuery(true)
                ->select('email, name')
                ->from('#__users')
                ->where('id = ' . (int) $service->created_by);
                
            $db->setQuery($query);
            $receiver = $db->loadObject();
            
            if (!$receiver || empty($receiver->email)) {
                return;
            }
            
            // Prepare email
            $sitename = $app->get('sitename');
            $subject = Text::sprintf('COM_SERVICES_MESSAGE_NOTIFICATION_SUBJECT', $sitename, $service->title);
            
            $body = Text::sprintf(
                'COM_SERVICES_MESSAGE_NOTIFICATION_BODY',
                $receiver->name,
                $sender->name,
                $service->title,
                substr($message, 0, 200) . (strlen($message) > 200 ? '...' : ''),
                $sitename
            );
            
            // Send email
            $mailer = Factory::getMailer();
            $mailer->addRecipient($receiver->email);
            $mailer->setSubject($subject);
            $mailer->setBody($body);
            $mailer->send();
            
        } catch (\Exception $e) {
            // Log error but don't fail the message sending
            Factory::getApplication()->enqueueMessage('Email notification failed: ' . $e->getMessage(), 'warning');
        }
    }
}