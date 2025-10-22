<?php
namespace Jbaylet\Component\Services\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Filter\OutputFilter;

class ItemTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__services_items', 'id', $db);
    }

    public function check()
    {
        // Basic title check
        if (trim($this->title) === '') {
            $this->setError('Title is required');
            return false;
        }

        // Generate alias if empty
        if (empty($this->alias)) {
            $this->alias = OutputFilter::stringURLSafe($this->title);
        } else {
            $this->alias = OutputFilter::stringURLSafe($this->alias);
        }

        // Ensure created_by when creating
        if ((int) $this->id === 0 && empty($this->created_by)) {
            $this->created_by = (int) \Joomla\CMS\Factory::getUser()->id;
        }

        return true;
    }
}
