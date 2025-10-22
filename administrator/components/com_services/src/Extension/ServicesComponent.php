<?php

/**
 * @package     Jbaylet.Component
 * @subpackage  com_services
 *
 * @copyright   Copyright (C) 2024 JBaylet Development. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jbaylet\Component\Services\Administrator\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Psr\Container\ContainerInterface;

/**
 * Component class for Services component
 *
 * @since  1.0.0
 */
class ServicesComponent extends MVCComponent implements BootableExtensionInterface, RouterServiceInterface
{
    use RouterServiceTrait;

    /**
     * Booting the extension. This is the function to set up the environment of the extension like
     * registering new class loaders, etc.
     *
     * If required, some initial setup can be done from services of the container, eg
     * registering HTML helpers.
     *
     * @param   ContainerInterface  $container  The container
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function boot(ContainerInterface $container): void
    {
        // Minimal boot - component is ready
    }

    /**
     * Returns the table name for the count items functions for the given section.
     *
     * @param   string|null  $section  The section name
     *
     * @return  string|null  The table name or null
     *
     * @since   1.0.0
     */
    protected function getTableNameForSection(?string $section = null): ?string
    {
        return match($section) {
            'items' => 'services_items',
            'reviews' => 'services_reviews', 
            'messages' => 'services_messages',
            'profiles' => 'services_profiles',
            default => null
        };
    }

    /**
     * Returns the state column for the count items functions for the given section.
     *
     * @param   string|null  $section  The section name
     *
     * @return  string  The state column name
     *
     * @since   1.0.0
     */
    protected function getStateColumnForSection(?string $section = null): string
    {
        return match($section) {
            'messages' => 'seen', // Messages use 'seen' instead of 'state'
            default => 'state'
        };
    }

    /**
     * Method to get the component configuration
     *
     * @return  Registry  Component parameters
     *
     * @since   1.0.0
     */
    public function getParams(): Registry
    {
        return Factory::getApplication()->getParams('com_services');
    }

    /**
     * Method to check if reviews are enabled
     *
     * @return  boolean
     *
     * @since   1.0.0
     */
    public function isReviewsEnabled(): bool
    {
        return (bool) $this->getParams()->get('enable_reviews', 1);
    }

    /**
     * Method to check if messaging is enabled
     *
     * @return  boolean
     *
     * @since   1.0.0
     */
    public function isMessagingEnabled(): bool
    {
        return (bool) $this->getParams()->get('enable_messaging', 1);
    }

    /**
     * Method to check if featured services are enabled
     *
     * @return  boolean
     *
     * @since   1.0.0
     */
    public function isFeaturedEnabled(): bool
    {
        return (bool) $this->getParams()->get('enable_featured', 1);
    }

    /**
     * Method to get the items per page limit
     *
     * @return  integer
     *
     * @since   1.0.0
     */
    public function getListLimit(): int
    {
        return (int) $this->getParams()->get('items_limit', 20);
    }

    /**
     * Method to check if auto-approve reviews is enabled
     *
     * @return  boolean
     *
     * @since   1.0.0
     */
    public function isAutoApproveReviews(): bool
    {
        return (bool) $this->getParams()->get('auto_approve_reviews', 0);
    }

    /**
     * Method to get allowed file types for uploads
     *
     * @return  array
     *
     * @since   1.0.0
     */
    public function getAllowedFileTypes(): array
    {
        $types = $this->getParams()->get('allowed_file_types', 'jpg,jpeg,png,gif,pdf');
        return array_map('trim', explode(',', $types));
    }

    /**
     * Method to get maximum file size for uploads
     *
     * @return  integer  Size in megabytes
     *
     * @since   1.0.0
     */
    public function getMaxFileSize(): int
    {
        return (int) $this->getParams()->get('max_file_size', 5);
    }

    /**
     * Method to get custom categories
     *
     * @return  array
     *
     * @since   1.0.0
     */
    public function getCustomCategories(): array
    {
        $categories = $this->getParams()->get('custom_categories', 
            "Plumbing\nElectrical\nConstruction\nCleaning\nLandscaping\nHVAC\nPainting\nRoofing");
        return array_filter(array_map('trim', explode("\n", $categories)));
    }
}
