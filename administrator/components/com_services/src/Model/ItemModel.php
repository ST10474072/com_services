<?php
namespace Jbaylet\Component\Services\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;

class ItemModel extends AdminModel
{
    protected $text_prefix = 'COM_SERVICES_ITEM';

    public function getTable($type = 'ItemTable', $prefix = 'Jbaylet\\Component\\Services\\Administrator\\Table\\', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_services.item', 'item', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        
        // Ensure the category list is synced from component options (custom_categories)
        try {
            $component = \Joomla\CMS\Component\ComponentHelper::getComponent('com_services');
            $params = new \Joomla\Registry\Registry($component->params);
            $raw = (string) $params->get('custom_categories', '');
            $names = array_filter(array_map('trim', preg_split("/\r?\n/", $raw)));
            if (!empty($names)) {
                $db = \Joomla\CMS\Factory::getDbo();
                $categoryTable = \Joomla\CMS\Table\Table::getInstance('Category');
                // Fetch existing categories for this extension
                $query = $db->getQuery(true)
                    ->select(['id','title','published'])
                    ->from($db->quoteName('#__categories'))
                    ->where($db->quoteName('extension') . ' = ' . $db->quote('com_services'))
                    ->where($db->quoteName('parent_id') . ' = 1'); // direct children of ROOT for this extension
                $db->setQuery($query);
                $existing = (array) $db->loadObjectList('title');
                // Create / publish categories present in config
                foreach ($names as $title) {
                    if (!isset($existing[$title])) {
                        $cat = clone $categoryTable;
                        $cat->reset();
                        $cat->id = 0;
                        $cat->title = $title;
                        $cat->alias = '';
                        $cat->extension = 'com_services';
                        $cat->published = 1;
                        $cat->access = 1;
                        $cat->language = '*';
                        $cat->params = '{}';
                        $cat->metadata = '{}';
                        $cat->parent_id = 1;
                        $cat->setLocation($cat->parent_id, 'last-child');
                        if (!$cat->check() || !$cat->store()) {
                            // non-fatal
                        }
                    } else {
                        // ensure published if present in config
                        $row = $existing[$title];
                        if ((int)$row->published === 0) {
                            $categoryTable->load($row->id);
                            $categoryTable->published = 1;
                            $categoryTable->store();
                        }
                    }
                }
                // Unpublish categories that are not listed anymore (soft remove)
                foreach ($existing as $title => $row) {
                    if (!in_array($title, $names, true)) {
                        $categoryTable->load($row->id);
                        $categoryTable->published = 0;
                        $categoryTable->store();
                    }
                }
            }
        } catch (\Throwable $e) {
            // Do not block the form if syncing fails
        }

        return $form;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_services.edit.item.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function save($data)
    {
        // Ensure state keys exist
        $data['state'] = isset($data['state']) ? (int) $data['state'] : 1;

        // Handle logo upload if a file was provided
        $input = Factory::getApplication()->input;
        $files = $input->files->get('jform', [], 'array');
        $logo  = $files['logo'] ?? null;

        if ($logo && isset($logo['error']) && (int) $logo['error'] === UPLOAD_ERR_OK && !empty($logo['tmp_name'])) {
            // Prepare destination folder
            $destFolder = JPATH_ROOT . '/media/com_services/logos';
            if (!Folder::exists($destFolder)) {
                Folder::create($destFolder);
                // Protect with index.html
                if (!File::exists($destFolder . '/index.html')) {
                    File::write($destFolder . '/index.html', "");
                }
            }

            // Sanitize and ensure unique filename
            $safeName = File::makeSafe($logo['name']);
            $ext = strtolower(File::getExt($safeName));
            $allowed = ['jpg','jpeg','png','gif','webp','svg'];

            if (!in_array($ext, $allowed, true)) {
                throw new \RuntimeException(Text::_('COM_SERVICES_ERROR_LOGO_EXTENSION_NOT_ALLOWED'));
            }

            // Add unique suffix if file exists
            $base = basename($safeName, '.' . $ext);
            $target = $destFolder . '/' . $safeName;
            $i = 1;
            while (File::exists($target)) {
                $target = $destFolder . '/' . $base . '_' . $i . '.' . $ext;
                $i++;
            }

            // Move the uploaded file
            if (!File::upload($logo['tmp_name'], $target)) {
                throw new \RuntimeException(Text::_('COM_SERVICES_ERROR_LOGO_UPLOAD_FAILED'));
            }

            // Store relative path
            $data['logo'] = str_replace(JPATH_ROOT . '/', '', $target);
        } else {
            // Prevent wiping the existing logo when no new file is uploaded
            if (array_key_exists('logo', $data)) {
                unset($data['logo']);
            }
        }

        return parent::save($data);
    }

    /**
     * Method to change the published state of one or more records.
     *
     * @param   array    $pks    A list of the primary keys to change.
     * @param   integer  $value  The value of the published state.
     *
     * @return  boolean  True on success.
     */
    public function publish(&$pks, $value = 1)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__services_items'))
            ->set($db->quoteName('state') . ' = ' . (int) $value)
            ->where($db->quoteName('id') . ' IN (' . implode(',', array_map('intval', $pks)) . ')');
        
        $db->setQuery($query);
        
        try {
            $db->execute();
            return true;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * Method to change the featured state of one or more records.
     *
     * @param   array    $pks    A list of the primary keys to change.
     * @param   integer  $value  The value of the featured state.
     *
     * @return  boolean  True on success.
     */
    public function feature(&$pks, $value = 1)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__services_items'))
            ->set($db->quoteName('is_featured') . ' = ' . (int) $value);
            
        // Set featured_until to null when unfeaturing, or 1 month from now when featuring
        if ($value == 1) {
            $query->set($db->quoteName('featured_until') . ' = DATE_ADD(NOW(), INTERVAL 1 MONTH)');
        } else {
            $query->set($db->quoteName('featured_until') . ' = NULL');
        }
        
        $query->where($db->quoteName('id') . ' IN (' . implode(',', array_map('intval', $pks)) . ')');
        
        $db->setQuery($query);
        
        try {
            $db->execute();
            return true;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * Method to delete one or more records.
     *
     * @param   array  $pks  An array of primary keys to delete.
     *
     * @return  boolean  True on success.
     */
    public function delete(&$pks)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__services_items'))
            ->where($db->quoteName('id') . ' IN (' . implode(',', array_map('intval', $pks)) . ')');
        
        $db->setQuery($query);
        
        try {
            $db->execute();
            return true;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }
}
