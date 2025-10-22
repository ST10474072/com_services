<?php
namespace Jbaylet\Component\Services\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

/**
 * User Helper class for Services Component
 * Integrates with Joomla's user system
 */
class UserHelper
{
    /**
     * Check if a user is logged in
     *
     * @return boolean
     */
    public static function isLoggedIn()
    {
        $user = Factory::getUser();
        return !$user->guest;
    }

    /**
     * Get the current user
     *
     * @return User
     */
    public static function getCurrentUser()
    {
        return Factory::getUser();
    }

    /**
     * Check if user can contact services (based on configuration)
     *
     * @return boolean
     */
    public static function canContact()
    {
        $params = ComponentHelper::getParams('com_services');
        $requireLogin = $params->get('require_login_to_contact', 1);
        
        if ($requireLogin) {
            return self::isLoggedIn();
        }
        
        return true;
    }

    /**
     * Check if user can submit reviews (based on configuration)
     *
     * @return boolean
     */
    public static function canReview()
    {
        $params = ComponentHelper::getParams('com_services');
        $requireLogin = $params->get('require_login_to_review', 1);
        
        if ($requireLogin) {
            return self::isLoggedIn();
        }
        
        return true;
    }

    /**
     * Get login URL with optional return URL
     *
     * @param string $return Return URL after login
     * @return string
     */
    public static function getLoginUrl($return = null)
    {
        if ($return === null) {
            $return = Uri::getInstance()->toString();
        }
        
        $return = base64_encode($return);
        return Route::_('index.php?option=com_users&view=login&return=' . $return);
    }

    /**
     * Get registration URL with optional return URL
     *
     * @param string $return Return URL after registration
     * @return string
     */
    public static function getRegistrationUrl($return = null)
    {
        $params = ComponentHelper::getParams('com_services');
        $useJoomlaRegistration = $params->get('use_joomla_registration', 1);
        
        if (!$useJoomlaRegistration) {
            return null;
        }
        
        if ($return === null) {
            $return = Uri::getInstance()->toString();
        }
        
        $return = base64_encode($return);
        return Route::_('index.php?option=com_users&view=registration&return=' . $return);
    }

    /**
     * Get logout URL with optional return URL
     *
     * @param string $return Return URL after logout
     * @return string
     */
    public static function getLogoutUrl($return = null)
    {
        if ($return === null) {
            $return = Uri::getInstance()->toString();
        }
        
        $return = base64_encode($return);
        $token = Factory::getSession()->getFormToken();
        return Route::_('index.php?option=com_users&task=user.logout&' . $token . '=1&return=' . $return);
    }

    /**
     * Get redirect URL after login (based on configuration)
     *
     * @return string
     */
    public static function getPostLoginRedirect()
    {
        $params = ComponentHelper::getParams('com_services');
        $redirect = $params->get('redirect_after_login', 'services');
        
        switch ($redirect) {
            case 'profile':
                return Route::_('index.php?option=com_users&view=profile');
                
            case 'dashboard':
                return Route::_('index.php?option=com_services&view=dashboard');
                
            case 'services':
                return Route::_('index.php?option=com_services&view=services');
                
            case 'same_page':
            default:
                return Uri::getInstance()->toString();
        }
    }

    /**
     * Check if user has a service provider profile
     *
     * @param int $userId User ID (optional, uses current user if not provided)
     * @return boolean
     */
    public static function hasServiceProfile($userId = null)
    {
        if ($userId === null) {
            $user = self::getCurrentUser();
            if ($user->guest) {
                return false;
            }
            $userId = $user->id;
        }
        
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__services_profiles')
            ->where($db->quoteName('user_id') . ' = ' . (int) $userId);
        
        $db->setQuery($query);
        return (bool) $db->loadResult();
    }

    /**
     * Create a service provider profile for a user
     *
     * @param int $userId User ID
     * @param array $profileData Additional profile data
     * @return boolean
     */
    public static function createServiceProfile($userId, $profileData = array())
    {
        if (self::hasServiceProfile($userId)) {
            return true; // Already exists
        }
        
        $user = Factory::getUser($userId);
        if (!$user->id) {
            return false;
        }
        
        $db = Factory::getDbo();
        
        // Default profile data
        $defaultData = array(
            'user_id' => $userId,
            'business_name' => $user->name,
            'email' => $user->email,
            'created' => Factory::getDate()->toSql()
        );
        
        $data = array_merge($defaultData, $profileData);
        
        try {
            $db->insertObject('#__services_profiles', (object) $data);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Auto-create service profile on user registration (if enabled)
     *
     * @param int $userId New user ID
     * @return boolean
     */
    public static function autoCreateServiceProfile($userId)
    {
        $params = ComponentHelper::getParams('com_services');
        $autoCreate = $params->get('auto_create_service_profile', 1);
        
        if ($autoCreate) {
            return self::createServiceProfile($userId);
        }
        
        return true;
    }

    /**
     * Get user's service provider profile
     *
     * @param int $userId User ID (optional, uses current user if not provided)
     * @return object|null
     */
    public static function getServiceProfile($userId = null)
    {
        if ($userId === null) {
            $user = self::getCurrentUser();
            if ($user->guest) {
                return null;
            }
            $userId = $user->id;
        }
        
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__services_profiles')
            ->where($db->quoteName('user_id') . ' = ' . (int) $userId);
        
        $db->setQuery($query);
        return $db->loadObject();
    }

    /**
     * Check if user can create services
     *
     * @param int $userId User ID (optional, uses current user if not provided)
     * @return boolean
     */
    public static function canCreateServices($userId = null)
    {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        if ($userId === null) {
            $userId = self::getCurrentUser()->id;
        }
        
        // Check if user has necessary permissions
        $user = Factory::getUser($userId);
        
        // Check component permissions
        if ($user->authorise('core.create', 'com_services')) {
            return true;
        }
        
        // For regular users, check if they have a service profile
        return self::hasServiceProfile($userId);
    }

    /**
     * Generate login/registration buttons HTML
     *
     * @param string $message Custom message to show
     * @param string $returnUrl Return URL after login
     * @return string HTML
     */
    public static function getLoginPromptHtml($message = null, $returnUrl = null)
    {
        if (self::isLoggedIn()) {
            return '';
        }
        
        if ($message === null) {
            $message = Text::_('COM_SERVICES_LOGIN_REQUIRED');
        }
        
        $loginUrl = self::getLoginUrl($returnUrl);
        $registerUrl = self::getRegistrationUrl($returnUrl);
        
        $html = '<div class="alert alert-info login-prompt">';
        $html .= '<p>' . htmlspecialchars($message) . '</p>';
        $html .= '<p>';
        $html .= '<a href="' . $loginUrl . '" class="btn btn-primary">' . Text::_('JLOGIN') . '</a>';
        
        if ($registerUrl) {
            $html .= ' <a href="' . $registerUrl . '" class="btn btn-secondary">' . Text::_('JREGISTER') . '</a>';
        }
        
        $html .= '</p>';
        $html .= '</div>';
        
        return $html;
    }
}