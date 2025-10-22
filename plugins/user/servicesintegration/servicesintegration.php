<?php
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Services Integration User Plugin
 * Handles automatic profile creation and user integration
 */
class PlgUserServicesintegration extends CMSPlugin
{
    /**
     * Application object
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     */
    protected $app;

    /**
     * Database object
     *
     * @var    \Joomla\Database\DatabaseDriver
     */
    protected $db;

    /**
     * Load the language file on instantiation
     *
     * @var    boolean
     */
    protected $autoloadLanguage = true;

    /**
     * This method is called after user registration
     *
     * @param   array    $user     Holds the new user data
     * @param   boolean  $isNew    True if a new user is stored
     * @param   boolean  $success  True if user was successfully stored in the database
     * @param   string   $msg      Message
     *
     * @return  void
     */
    public function onUserAfterSave($user, $isNew, $success, $msg)
    {
        // Only proceed if this is a new user registration and it was successful
        if (!$isNew || !$success) {
            return;
        }

        // Check if Services component is installed and enabled
        if (!ComponentHelper::isEnabled('com_services')) {
            return;
        }

        // Get component parameters
        $params = ComponentHelper::getParams('com_services');
        $autoCreate = $params->get('auto_create_service_profile', 1);

        if ($autoCreate) {
            $this->createServiceProfile($user['id'], $user);
        }
    }

    /**
     * This method is called after user login
     *
     * @param   array  $user     Holds the user data
     * @param   array  $options  Array holding options (remember, autoregister, etc.)
     *
     * @return  boolean
     */
    public function onUserLogin($user, $options = array())
    {
        // Check if Services component is installed and enabled
        if (!ComponentHelper::isEnabled('com_services')) {
            return true;
        }

        // Get redirect URL from component configuration
        $params = ComponentHelper::getParams('com_services');
        $redirect = $params->get('redirect_after_login', 'same_page');

        if ($redirect !== 'same_page') {
            $app = Factory::getApplication();
            $redirectUrl = $this->getRedirectUrl($redirect);
            
            if ($redirectUrl) {
                $app->setUserState('users.login.form.return', base64_encode($redirectUrl));
            }
        }

        return true;
    }

    /**
     * Create a service provider profile for the user
     *
     * @param   int    $userId  User ID
     * @param   array  $user    User data
     *
     * @return  boolean
     */
    protected function createServiceProfile($userId, $user)
    {
        // Check if profile already exists
        $query = $this->db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__services_profiles')
            ->where($this->db->quoteName('user_id') . ' = ' . (int) $userId);

        $this->db->setQuery($query);
        
        if ($this->db->loadResult() > 0) {
            return true; // Already exists
        }

        // Create profile data
        $profileData = array(
            'user_id' => (int) $userId,
            'business_name' => $user['name'],
            'email' => $user['email'],
            'created' => Factory::getDate()->toSql()
        );

        try {
            $this->db->insertObject('#__services_profiles', (object) $profileData);
            return true;
        } catch (Exception $e) {
            // Log the error but don't fail the registration
            Factory::getApplication()->enqueueMessage(
                'Could not create service profile: ' . $e->getMessage(),
                'warning'
            );
            return false;
        }
    }

    /**
     * Get redirect URL based on configuration
     *
     * @param   string  $redirect  Redirect option
     *
     * @return  string|null
     */
    protected function getRedirectUrl($redirect)
    {
        switch ($redirect) {
            case 'profile':
                return 'index.php?option=com_users&view=profile';
                
            case 'dashboard':
                return 'index.php?option=com_services&view=dashboard';
                
            case 'services':
                return 'index.php?option=com_services&view=services';
                
            default:
                return null;
        }
    }
}