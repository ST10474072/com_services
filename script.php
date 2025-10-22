<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;

/**
 * Services component installer script
 *
 * @since 1.0.0
 */
class Com_ServicesInstallerScript
{
    /**
     * Minimum Joomla version required to install the extension
     *
     * @var string
     */
    public $minimumJoomla = '5.0.0';

    /**
     * Minimum PHP version required to install the extension
     *
     * @var string
     */
    public $minimumPhp = '8.0';

    /**
     * Called before any type of action
     *
     * @param string $type Which action is happening (install|uninstall|discover_install|update)
     * @param object $parent The object responsible for running this script
     *
     * @return boolean True on success
     */
    public function preflight($type, $parent)
    {
        // Check minimum Joomla version
        if (version_compare(JVERSION, $this->minimumJoomla, '<')) {
            Factory::getApplication()->enqueueMessage(
                'This extension requires Joomla ' . $this->minimumJoomla . ' or later', 'error'
            );
            return false;
        }

        // Check minimum PHP version
        if (version_compare(PHP_VERSION, $this->minimumPhp, '<')) {
            Factory::getApplication()->enqueueMessage(
                'This extension requires PHP ' . $this->minimumPhp . ' or later', 'error'
            );
            return false;
        }

        return true;
    }

    /**
     * Called after extension installation
     *
     * @param object $parent The object responsible for running this script
     *
     * @return void
     */
    public function install($parent)
    {
        Factory::getApplication()->enqueueMessage('Services Component installed successfully!', 'message');
    }

    /**
     * Called after extension update
     *
     * @param object $parent The object responsible for running this script
     *
     * @return void
     */
    public function update($parent)
    {
        Factory::getApplication()->enqueueMessage('Services Component updated successfully!', 'message');
    }

    /**
     * Called after extension uninstallation
     *
     * @param object $parent The object responsible for running this script
     *
     * @return void
     */
    public function uninstall($parent)
    {
        Factory::getApplication()->enqueueMessage('Services Component has been uninstalled.', 'info');
    }

    /**
     * Called after any type of action
     *
     * @param string $type Which action is happening (install|uninstall|discover_install|update)
     * @param object $parent The object responsible for running this script
     *
     * @return boolean True on success
     */
    public function postflight($type, $parent)
    {
        // Ensure at least one category exists for com_services so the category field shows options
        try {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__categories'))
                ->where($db->quoteName('extension') . ' = ' . $db->quote('com_services'));
            $db->setQuery($query);
            $count = (int) $db->loadResult();

            // Create default service categories if none exist
            if ($count === 0) {
                $this->createDefaultServiceCategories();
            }
        } catch (\Throwable $e) {
            // Non-fatal; component still works, but categories will be empty until created
            Factory::getApplication()->enqueueMessage('Category check failed: ' . $e->getMessage(), 'warning');
        }

        return true;
    }

    /**
     * Create default service categories
     *
     * @return  void
     */
    private function createDefaultServiceCategories()
    {
        $db = Factory::getDbo();
        
        // Define default categories
        $categories = [
            ['title' => 'Plumbing', 'alias' => 'plumbing', 'description' => 'Plumbing and water-related services'],
            ['title' => 'Electrical', 'alias' => 'electrical', 'description' => 'Electrical installation and repair services'],
            ['title' => 'Construction', 'alias' => 'construction', 'description' => 'Construction and building services'],
            ['title' => 'Cleaning', 'alias' => 'cleaning', 'description' => 'Cleaning and maintenance services'],
            ['title' => 'Landscaping', 'alias' => 'landscaping', 'description' => 'Landscaping and garden services'],
            ['title' => 'Automotive', 'alias' => 'automotive', 'description' => 'Car repair and automotive services'],
            ['title' => 'Home Services', 'alias' => 'home-services', 'description' => 'General home improvement and repair services']
        ];
        
        try {
            foreach ($categories as $catData) {
                /** @var \Joomla\CMS\Table\Category $category */
                $category = Table::getInstance('Category');
                
                $data = [
                    'title'       => $catData['title'],
                    'alias'       => $catData['alias'],
                    'extension'   => 'com_services',
                    'published'   => 1,
                    'language'    => '*',
                    'access'      => 1, // Public
                    'params'      => '{}',
                    'metadata'    => '{}',
                    'description' => $catData['description']
                ];
                
                // Place under the root (id = 1)
                $category->setLocation(1, 'last-child');
                
                if (!$category->bind($data)) {
                    // Fallback to minimal binding
                    $category->title = $catData['title'];
                    $category->alias = $catData['alias'];
                    $category->extension = 'com_services';
                    $category->published = 1;
                    $category->language = '*';
                    $category->access = 1;
                    $category->description = $catData['description'];
                }
                
                if (!$category->check() || !$category->store()) {
                    Factory::getApplication()->enqueueMessage(
                        'Could not create category "' . $catData['title'] . '": ' . $category->getError(), 
                        'warning'
                    );
                }
            }
            
            Factory::getApplication()->enqueueMessage(
                'Default service categories have been created successfully!', 
                'success'
            );
            
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage(
                'Error creating default categories: ' . $e->getMessage(), 
                'warning'
            );
        }
    }
}
