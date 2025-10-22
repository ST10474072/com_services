<?php
namespace Jbaylet\Component\Services\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * Messages list controller class.
 */
class MessagesController extends AdminController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_SERVICES_MESSAGES';

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
     */
    public function getModel($name = 'Message', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * View full chat conversation
     *
     * @return  void
     */
    public function viewchat()
    {
        $threadId = $this->input->get('thread_id', '', 'string');
        
        if (empty($threadId)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SERVICES_ERROR_INVALID_THREAD'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_services&view=messages'));
            return;
        }

        // Set the thread ID in the application state for the view
        Factory::getApplication()->setUserState('com_services.messages.thread_id', $threadId);
        
        // Redirect to chat detail view
        $this->setRedirect(Route::_('index.php?option=com_services&view=messages&layout=chat&thread_id=' . urlencode($threadId)));
    }

    /**
     * Mark messages as seen
     *
     * @return  void
     */
    public function markseen()
    {
        $threadId = $this->input->get('thread_id', '', 'string');
        
        if (empty($threadId)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SERVICES_ERROR_INVALID_THREAD'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_services&view=messages'));
            return;
        }

        $model = $this->getModel('Messages');
        
        if ($model->markThreadAsSeen($threadId)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SERVICES_MESSAGES_MARKED_AS_READ'), 'message');
        } else {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SERVICES_ERROR_MARKING_MESSAGES'), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_services&view=messages'));
    }

    /**
     * Delete entire chat thread
     *
     * @return  void
     */
    public function deletethread()
    {
        $threadId = $this->input->get('thread_id', '', 'string');
        
        if (empty($threadId)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SERVICES_ERROR_INVALID_THREAD'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_services&view=messages'));
            return;
        }

        $model = $this->getModel('Messages');
        
        if ($model->deleteThread($threadId)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SERVICES_THREAD_DELETED_SUCCESS'), 'message');
        } else {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SERVICES_ERROR_DELETING_THREAD'), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_services&view=messages'));
    }
}
