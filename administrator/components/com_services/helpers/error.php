<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_services
 *
 * @copyright   Copyright (C) 2024 JBaylet Development. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * Error Helper Class for Services Component
 *
 * @since  1.0.0
 */
class ServicesHelperError
{
    /**
     * Log an error message
     *
     * @param   string  $message    The error message
     * @param   string  $category   The log category
     * @param   string  $priority   The log priority
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public static function logError($message, $category = 'com_services', $priority = Log::ERROR)
    {
        try {
            Log::add($message, $priority, $category);
        } catch (Exception $e) {
            // Fallback if logging fails
            error_log("Services Component Error: " . $message);
        }
    }

    /**
     * Handle database errors
     *
     * @param   Exception  $exception  The database exception
     * @param   string     $context    Context where error occurred
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public static function handleDatabaseError($exception, $context = 'database')
    {
        $message = sprintf(
            'Database error in %s: %s (Code: %s)',
            $context,
            $exception->getMessage(),
            $exception->getCode()
        );
        
        self::logError($message, 'com_services.database');
        
        // Set application error message for user
        $app = Factory::getApplication();
        if ($app) {
            $app->enqueueMessage(
                Text::_('COM_SERVICES_ERROR_DATABASE_OPERATION'),
                'error'
            );
        }
    }

    /**
     * Handle file system errors
     *
     * @param   string  $operation  The file operation that failed
     * @param   string  $path       The file path
     * @param   string  $message    Additional error message
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public static function handleFileError($operation, $path, $message = '')
    {
        $errorMessage = sprintf(
            'File system error during %s operation on path %s: %s',
            $operation,
            $path,
            $message
        );
        
        self::logError($errorMessage, 'com_services.filesystem');
        
        // Set application error message for user
        $app = Factory::getApplication();
        if ($app) {
            $app->enqueueMessage(
                Text::_('COM_SERVICES_ERROR_FILE_OPERATION'),
                'error'
            );
        }
    }

    /**
     * Handle validation errors
     *
     * @param   array   $errors   Array of validation errors
     * @param   string  $context  Context where validation failed
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public static function handleValidationErrors($errors, $context = 'validation')
    {
        $app = Factory::getApplication();
        
        foreach ($errors as $error) {
            $message = sprintf(
                'Validation error in %s: %s',
                $context,
                $error
            );
            
            self::logError($message, 'com_services.validation', Log::WARNING);
            
            // Add user-friendly error message
            if ($app) {
                $app->enqueueMessage($error, 'warning');
            }
        }
    }

    /**
     * Handle permission errors
     *
     * @param   string  $action   The action that was denied
     * @param   int     $userId   The user ID
     * @param   string  $asset    The asset being accessed
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public static function handlePermissionError($action, $userId = null, $asset = 'com_services')
    {
        if ($userId === null) {
            $user = Factory::getUser();
            $userId = $user->id;
        }
        
        $message = sprintf(
            'Permission denied for user %d attempting action %s on asset %s',
            $userId,
            $action,
            $asset
        );
        
        self::logError($message, 'com_services.permissions', Log::WARNING);
        
        // Set application error message for user
        $app = Factory::getApplication();
        if ($app) {
            $app->enqueueMessage(
                Text::_('JERROR_ALERTNOAUTHOR'),
                'error'
            );
        }
    }

    /**
     * Handle API errors
     *
     * @param   string  $endpoint  The API endpoint
     * @param   int     $code      HTTP response code
     * @param   string  $message   Error message
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public static function handleApiError($endpoint, $code, $message)
    {
        $errorMessage = sprintf(
            'API error on endpoint %s (HTTP %d): %s',
            $endpoint,
            $code,
            $message
        );
        
        self::logError($errorMessage, 'com_services.api');
        
        // Set application error message for user
        $app = Factory::getApplication();
        if ($app) {
            $app->enqueueMessage(
                Text::_('COM_SERVICES_ERROR_API_OPERATION'),
                'error'
            );
        }
    }

    /**
     * Get formatted error response for AJAX requests
     *
     * @param   string  $message  The error message
     * @param   int     $code     Error code
     * @param   array   $data     Additional error data
     *
     * @return  array   Formatted error response
     *
     * @since   1.0.0
     */
    public static function getAjaxErrorResponse($message, $code = 500, $data = [])
    {
        return [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'data' => $data
            ],
            'timestamp' => date('c')
        ];
    }

    /**
     * Handle general exceptions
     *
     * @param   Exception  $exception  The exception to handle
     * @param   string     $context    Context where exception occurred
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public static function handleException($exception, $context = 'general')
    {
        $message = sprintf(
            'Exception in %s: %s (File: %s, Line: %d)',
            $context,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        
        self::logError($message, 'com_services.exception');
        
        // Set application error message for user
        $app = Factory::getApplication();
        if ($app) {
            $app->enqueueMessage(
                Text::_('COM_SERVICES_ERROR_GENERAL'),
                'error'
            );
        }
    }
}