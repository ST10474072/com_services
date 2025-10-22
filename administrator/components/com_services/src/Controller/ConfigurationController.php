<?php
namespace Jbaylet\Component\Services\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * Configuration controller for Services component.
 */
class ConfigurationController extends FormController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_SERVICES_CONFIGURATION';

    /**
     * Method to save the configuration and redirect.
     *
     * @param   string  $key    The name of the primary key of the URL variable.
     * @param   string  $urlVar The name of the URL variable if different from the primary key.
     *
     * @return  boolean  True if successful, false otherwise.
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        $this->checkToken();

        $app   = Factory::getApplication();
        $model = $this->getModel('Configuration');
        $data  = $this->input->post->get('jform', array(), 'array');

        // Validate the posted data.
        $form = $model->getForm();

        if (!$form) {
            $app->enqueueMessage($model->getError(), 'error');
            return false;
        }

        // Test whether the data is valid.
        $validData = $model->validate($form, $data);

        // Check for validation errors.
        if ($validData === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof \Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            // Save the data in the session.
            $app->setUserState('com_services.edit.configuration.data', $data);

            // Redirect back to the edit screen.
            $this->setRedirect(Route::_('index.php?option=com_services&view=configuration', false));
            return false;
        }

        // Attempt to save the configuration.
        if (!$model->save($validData)) {
            // Save the data in the session.
            $app->setUserState('com_services.edit.configuration.data', $validData);

            // Redirect back to the edit screen.
            $this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
            $this->setMessage($this->getError(), 'error');

            $this->setRedirect(Route::_('index.php?option=com_services&view=configuration', false));
            return false;
        }

        $this->setMessage(Text::_('COM_SERVICES_CONFIGURATION_SAVE_SUCCESS'));

        // Redirect to the configuration view.
        $this->setRedirect(Route::_('index.php?option=com_services&view=configuration', false));
        return true;
    }

    /**
     * Method to cancel configuration editing.
     *
     * @param   string  $key The name of the primary key of the URL variable.
     *
     * @return  boolean  True if access level checks pass, false otherwise.
     */
    public function cancel($key = null)
    {
        $this->checkToken();

        // Clean the session data.
        Factory::getApplication()->setUserState('com_services.edit.configuration.data', null);

        $this->setRedirect(Route::_('index.php?option=com_services&view=dashboard', false));
        return true;
    }

    /**
     * Method to restore default configuration values.
     *
     * @return  boolean  True if successful, false otherwise.
     */
    public function restoreDefaults()
    {
        // Check for request forgeries.
        $this->checkToken();

        $app   = Factory::getApplication();
        $model = $this->getModel('Configuration');

        // Attempt to restore defaults.
        if (!$model->restoreDefaults()) {
            $this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
            $this->setMessage($this->getError(), 'error');
        } else {
            $this->setMessage(Text::_('COM_SERVICES_CONFIGURATION_DEFAULTS_RESTORED'));
        }

        $this->setRedirect(Route::_('index.php?option=com_services&view=configuration', false));
        return true;
    }
}