<?php
namespace Jbaylet\Component\Services\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;

/**
 * Configuration model for Services component.
 */
class ConfigurationModel extends AdminModel
{
    /**
     * Constructor
     *
     * @param   array  $config  An optional associative array of configuration settings.
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        
        // Add the forms path
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_services/forms');
        Form::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_services/forms/fields');
    }
    /**
     * Method to get the configuration form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|boolean  A Form object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Load language file to ensure labels are translated
        $lang = Factory::getLanguage();
        $lang->load('com_services', JPATH_ADMINISTRATOR, null, false, true);
        
        // Get the form.
        $form = $this->loadForm(
            'com_services.configuration',
            'config',
            array(
                'control' => 'jform',
                'load_data' => $loadData
            )
        );

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $app = Factory::getApplication();
        $data = $app->getUserState('com_services.edit.configuration.data', array());

        if (empty($data)) {
            // Load the current component parameters.
            $params = ComponentHelper::getParams('com_services');
            $data = $params->toArray();
        }

        return $data;
    }

    /**
     * Method to save the configuration.
     *
     * @param   array  $data  The configuration data to save.
     *
     * @return  boolean  True on success, false on failure.
     */
    public function save($data)
    {
        $app = Factory::getApplication();

        // Convert the data to a Registry object.
        $params = new Registry($data);

        // Get the component extension record.
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('extension_id')
            ->from('#__extensions')
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_services'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));

        $db->setQuery($query);
        $extensionId = $db->loadResult();

        if (!$extensionId) {
            $this->setError('COM_SERVICES_ERROR_EXTENSION_NOT_FOUND');
            return false;
        }

        // Update the component parameters.
        $query = $db->getQuery(true)
            ->update('#__extensions')
            ->set($db->quoteName('params') . ' = ' . $db->quote($params->toString()))
            ->where($db->quoteName('extension_id') . ' = ' . (int) $extensionId);

        $db->setQuery($query);

        try {
            $db->execute();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // Clear the session data.
        $app->setUserState('com_services.edit.configuration.data', null);

        return true;
    }

    /**
     * Method to restore default configuration values.
     *
     * @return  boolean  True on success, false on failure.
     */
    public function restoreDefaults()
    {
        // Get the default values from the config.xml file.
        $form = $this->getForm();
        
        if (!$form) {
            $this->setError('COM_SERVICES_ERROR_LOADING_FORM');
            return false;
        }

        $defaults = array();
        
        // Extract default values from form fields.
        foreach ($form->getFieldsets() as $fieldset) {
            foreach ($form->getFieldset($fieldset->name) as $field) {
                $fieldName = $field->fieldname;
                $defaultValue = $field->getAttribute('default');
                
                if ($defaultValue !== null) {
                    $defaults[$fieldName] = $defaultValue;
                }
            }
        }

        // Save the default values.
        return $this->save($defaults);
    }

    /**
     * Method to get the configuration data.
     *
     * @return  Registry  The configuration parameters.
     */
    public function getItem($pk = null)
    {
        $params = ComponentHelper::getParams('com_services');
        return $params;
    }

    /**
     * Method to validate form data.
     *
     * @param   Form   $form   The form object.
     * @param   array  $data   The data to validate.
     * @param   string $group  The field group to validate.
     *
     * @return  array|boolean  Array of filtered data if valid, false otherwise.
     */
    public function validate($form, $data, $group = null)
    {
        // Filter and validate the form data.
        $validData = $form->filter($data);
        $return = $form->validate($validData, $group);

        // Check for an error.
        if ($return instanceof \Exception) {
            $this->setError($return->getMessage());
            return false;
        }

        // Check the validation results.
        if ($return === false) {
            // Get the validation messages from the form.
            foreach ($form->getErrors() as $message) {
                $this->setError($message);
            }

            return false;
        }

        return $validData;
    }
}