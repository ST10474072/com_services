<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Jbaylet\Component\Services\Administrator\View\Configuration\HtmlView $this */

// Force load language file from multiple locations with cache clearing
$lang = \Joomla\CMS\Factory::getLanguage();

// Disable language debug to prevent ** markers
if (method_exists($lang, 'setDebug')) {
    $lang->setDebug(false);
}

// Load language files from multiple locations, forcing reload
$lang->load('com_services', JPATH_ADMINISTRATOR, 'en-GB', true, true);
$lang->load('com_services', JPATH_ADMINISTRATOR . '/components/com_services', 'en-GB', true, true);
$lang->load('com_services', JPATH_ADMINISTRATOR . '/components/com_services/language/en-GB', 'en-GB', true, true);

// Define translations directly as backup
$translations = array(
    'COM_SERVICES_CONFIG_GENERAL_LABEL' => 'General Settings',
    'COM_SERVICES_CONFIG_FEATURES_LABEL' => 'Features',
    'COM_SERVICES_CONFIG_DISPLAY_LABEL' => 'Display Options',
    'COM_SERVICES_CONFIG_CONTACT_LABEL' => 'Contact & Leads',
    'COM_SERVICES_CONFIG_SEARCH_LABEL' => 'Search & Filters',
    'COM_SERVICES_CONFIG_SECURITY_LABEL' => 'Security & Moderation',
    'COM_SERVICES_CONFIG_GENERAL_DESC' => 'Basic component settings and functionality',
    'COM_SERVICES_CONFIG_FEATURES_DESC' => 'Enable or disable component features',
    'COM_SERVICES_CONFIG_DISPLAY_DESC' => 'Control what information is displayed to users',
    'COM_SERVICES_CONFIG_CONTACT_DESC' => 'Configure how users can contact service providers',
    'COM_SERVICES_CONFIG_SEARCH_DESC' => 'Configure search functionality and available filters',
    'COM_SERVICES_CONFIG_SECURITY_DESC' => 'Security settings and content moderation options',
    // Field labels
    'COM_SERVICES_CONFIG_ITEMS_PER_PAGE_LABEL' => 'Items Per Page',
    'COM_SERVICES_CONFIG_ITEMS_PER_PAGE_DESC' => 'Number of services to display per page',
    'COM_SERVICES_CONFIG_AUTO_CREATE_PROFILE_LABEL' => 'Auto-Create Service Profile',
    'COM_SERVICES_CONFIG_AUTO_CREATE_PROFILE_DESC' => 'Automatically create a service provider profile for new users',
    'COM_SERVICES_CONFIG_REQUIRE_LOGIN_TO_CONTACT_LABEL' => 'Require Login to Contact',
    'COM_SERVICES_CONFIG_REQUIRE_LOGIN_TO_CONTACT_DESC' => 'Users must be logged in to contact service providers',
    'COM_SERVICES_CONFIG_REQUIRE_LOGIN_TO_REVIEW_LABEL' => 'Require Login to Review',
    'COM_SERVICES_CONFIG_REQUIRE_LOGIN_TO_REVIEW_DESC' => 'Users must be logged in to submit reviews',
    'COM_SERVICES_CONFIG_DEFAULT_SERVICE_STATE_LABEL' => 'Default Service State',
    'COM_SERVICES_CONFIG_DEFAULT_SERVICE_STATE_DESC' => 'Default publication state for new services',
    'COM_SERVICES_CONFIG_ENABLE_REVIEWS_LABEL' => 'Enable Reviews',
    'COM_SERVICES_CONFIG_ENABLE_REVIEWS_DESC' => 'Allow users to submit reviews and ratings',
    'COM_SERVICES_CONFIG_AUTO_APPROVE_REVIEWS_LABEL' => 'Auto-approve Reviews',
    'COM_SERVICES_CONFIG_AUTO_APPROVE_REVIEWS_DESC' => 'Automatically approve new reviews without moderation',
    'COM_SERVICES_CONFIG_ENABLE_MESSAGING_LABEL' => 'Enable Messaging',
    'COM_SERVICES_CONFIG_ENABLE_MESSAGING_DESC' => 'Allow direct messaging between users and service providers',
    'COM_SERVICES_CONFIG_ENABLE_FEATURED_LABEL' => 'Enable Featured Services',
    'COM_SERVICES_CONFIG_ENABLE_FEATURED_DESC' => 'Allow services to be marked as featured',
    'COM_SERVICES_CONFIG_FEATURED_DURATION_LABEL' => 'Featured Duration (Days)',
    'COM_SERVICES_CONFIG_FEATURED_DURATION_DESC' => 'How long services remain featured',
    'COM_SERVICES_CONFIG_ENABLE_EMERGENCY_LABEL' => 'Enable Emergency Services',
    'COM_SERVICES_CONFIG_ENABLE_EMERGENCY_DESC' => 'Allow services to be marked as emergency services',
    'COM_SERVICES_CONFIG_ENABLE_247_LABEL' => 'Enable 24/7 Services',
    'COM_SERVICES_CONFIG_ENABLE_247_DESC' => 'Allow services to be marked as 24/7 available',
    'COM_SERVICES_CONFIG_MAX_FILE_SIZE_LABEL' => 'Maximum File Size',
    'COM_SERVICES_CONFIG_MAX_FILE_SIZE_DESC' => 'Maximum file size for uploads (in MB)',
    'COM_SERVICES_CONFIG_ALLOWED_FILE_TYPES_LABEL' => 'Allowed File Types',
    'COM_SERVICES_CONFIG_ALLOWED_FILE_TYPES_DESC' => 'Comma-separated list of allowed file extensions',
    'COM_SERVICES_CONFIG_ENABLE_CAPTCHA_LABEL' => 'Enable CAPTCHA',
    'COM_SERVICES_CONFIG_ENABLE_CAPTCHA_DESC' => 'Require CAPTCHA verification for forms',
    'COM_SERVICES_CONFIG_MODERATE_NEW_SERVICES_LABEL' => 'Moderate New Services',
    'COM_SERVICES_CONFIG_MODERATE_NEW_SERVICES_DESC' => 'Require admin approval for new service listings',
    'COM_SERVICES_CONFIG_SPAM_PROTECTION_LABEL' => 'Spam Protection',
    'COM_SERVICES_CONFIG_SPAM_PROTECTION_DESC' => 'Enable automatic spam detection and filtering',
    'COM_SERVICES_CONFIG_MIN_REVIEW_LENGTH_LABEL' => 'Minimum Review Length',
    'COM_SERVICES_CONFIG_MIN_REVIEW_LENGTH_DESC' => 'Minimum number of characters required for reviews',
    'COM_SERVICES_CONFIG_MAX_REVIEWS_PER_DAY_LABEL' => 'Max Reviews Per Day',
    'COM_SERVICES_CONFIG_MAX_REVIEWS_PER_DAY_DESC' => 'Maximum reviews one user can submit per day',
    'COM_SERVICES_CONFIG_ENABLE_REPORT_SYSTEM_LABEL' => 'Enable Report System',
    'COM_SERVICES_CONFIG_ENABLE_REPORT_SYSTEM_DESC' => 'Allow users to report inappropriate content',
);

// Override Text::_ function behavior for this page
if (!function_exists('translateConfig')) {
    function translateConfig($key) {
        global $translations;
        
        // Get translation from Joomla
        $text = \Joomla\CMS\Language\Text::_($key);
        
        // If translation failed (text equals key), use our fallback
        if ($text === $key && isset($translations[$key])) {
            $text = $translations[$key];
        }
        
        // Clean up any debug markers that Joomla might add (but preserve the translation)
        $text = str_replace(array('**', '??'), '', $text);
        $text = trim($text);
        
        return $text;
    }
}

// Load basic form validation if available
try {
    HTMLHelper::_('behavior.formvalidator');
} catch (Exception $e) {
    // Form validator not available in this Joomla version
}

// Load keepalive if available
try {
    HTMLHelper::_('behavior.keepalive');
} catch (Exception $e) {
    // Keepalive not available in this Joomla version
}

// Load the JavaScript for the form
HTMLHelper::_('script', 'com_services/admin-configuration.js', ['version' => 'auto', 'relative' => true]);

?>

<form action="<?php echo Route::_('index.php?option=com_services&view=configuration'); ?>" 
      method="post" 
      name="adminForm" 
      id="adminForm" 
      class="form-validate">

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs" id="configurationTabs" role="tablist">
                        <?php
                        $fieldsets = $this->form->getFieldsets();
                        $active = true;
                        foreach ($fieldsets as $fieldset) :
                        ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $active ? 'active' : ''; ?>" 
                                   id="tab-<?php echo $fieldset->name; ?>-tab" 
                                   href="#tab-<?php echo $fieldset->name; ?>" 
                                   data-bs-toggle="tab" 
                                   data-bs-target="#tab-<?php echo $fieldset->name; ?>" 
                                   role="tab" 
                                   aria-controls="tab-<?php echo $fieldset->name; ?>" 
                                   aria-selected="<?php echo $active ? 'true' : 'false'; ?>">
                                    <?php echo translateConfig($fieldset->label); ?>
                                </a>
                            </li>
                        <?php
                            $active = false;
                        endforeach;
                        ?>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content mt-3" id="configurationTabContent">
                        <?php
                        $active = true;
                        foreach ($fieldsets as $fieldset) :
                        ?>
                            <div class="tab-pane fade <?php echo $active ? 'show active' : ''; ?>" 
                                 id="tab-<?php echo $fieldset->name; ?>" 
                                 role="tabpanel" 
                                 aria-labelledby="tab-<?php echo $fieldset->name; ?>-tab">
                                
                                <?php if (isset($fieldset->description) && !empty($fieldset->description)) : ?>
                                    <div class="alert alert-info">
                                        <p class="mb-0"><?php echo translateConfig($fieldset->description); ?></p>
                                    </div>
                                <?php endif; ?>

                                <div class="row">
                                    <?php foreach ($this->form->getFieldset($fieldset->name) as $field) : ?>
                                        <?php 
                                        // Debug: Show what we're working with
                                        // echo '<!-- Field: ' . $field->fieldname . ' Label: ' . $field->label . ' -->';
                                        ?>
                                        <div class="col-12 mb-3">
                                            <?php if ($field->type == 'Spacer') : ?>
                                                <hr class="my-4">
                                                <?php if ($field->label) : ?>
                                                    <h5 class="text-muted"><?php echo translateConfig($field->label); ?></h5>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <div class="form-group">
                                                    <?php if ($field->label) : ?>
                                                        <?php 
                                                        // Get the field label and apply translations
                                                        $fieldLabel = $field->label;
                                                        $translatedLabel = translateConfig($fieldLabel);
                                                        ?>
                                                        <label class="form-label" for="<?php echo $field->id; ?>">
                                                            <?php echo $translatedLabel; ?>
                                                            <?php if ($field->required) : ?>
                                                                <span class="text-danger">*</span>
                                                            <?php endif; ?>
                                                        </label>
                                                    <?php endif; ?>
                                                    
                                                    <div class="form-control-wrap">
                                                        <?php 
                                                        $fieldInput = $field->input;
                                                        
                                                        // Replace any language constants in the field input HTML
                                                        foreach ($translations as $constant => $translation) {
                                                            $fieldInput = str_replace($constant, $translation, $fieldInput);
                                                        }
                                                        
                                                        // Aggressively clean up ALL ** and ?? markers from field input
                                                        $fieldInput = preg_replace('/\*\*([^*]+)\*\*/', '$1', $fieldInput);
                                                        $fieldInput = preg_replace('/\?\?([^?]+)\?\?/', '$1', $fieldInput);
                                                        $fieldInput = str_replace(array('**', '??', '*', '?'), '', $fieldInput);
                                                        
                                                        // Clean up specific Joomla language constants that might appear
                                                        $joomlaConstants = array(
                                                            'JYES' => 'Yes',
                                                            'JNO' => 'No',
                                                            'COM_SERVICES_PUBLISHED' => 'Published',
                                                            'COM_SERVICES_UNPUBLISHED' => 'Unpublished',
                                                            'Yes**' => 'Yes',
                                                            '**Yes**' => 'Yes',
                                                            'No**' => 'No',
                                                            '**No**' => 'No'
                                                        );
                                                        
                                                        foreach ($joomlaConstants as $constant => $replacement) {
                                                            $fieldInput = str_replace($constant, $replacement, $fieldInput);
                                                        }
                                                        
                                                        echo $fieldInput;
                                                        ?>
                                                    </div>
                                                    
                                                    <?php if ($field->description) : ?>
                                                        <?php 
                                                        // Get the field description and apply translations
                                                        $fieldDesc = $field->description;
                                                        $translatedDesc = translateConfig($fieldDesc);
                                                        ?>
                                                        <div class="form-text text-muted">
                                                            <?php echo $translatedDesc; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php
                            $active = false;
                        endforeach;
                        ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Form Fields -->
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<style>
.nav-tabs {
    border-bottom: 2px solid #dee2e6;
}

.nav-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    color: #6c757d;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #007bff;
    color: #007bff;
}

.nav-tabs .nav-link.active {
    color: #007bff;
    border-bottom-color: #007bff;
    background-color: transparent;
}

.tab-content {
    min-height: 400px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
}

.form-control-wrap .form-control,
.form-control-wrap .form-select {
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    padding: 0.5rem 0.75rem;
}

.form-control-wrap .form-control:focus,
.form-control-wrap .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-group-yesno .btn {
    border-radius: 0.375rem;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}
</style>

<script>
// JavaScript to replace any remaining language constants on page load
document.addEventListener('DOMContentLoaded', function() {
    const translations = {
        // No login-related translations needed since Joomla handles authentication
    };
    
    // Aggressively clean up any ** or ?? markers from the entire page
    function cleanupMarkers() {
        // Clean up all text elements
        const allElements = document.querySelectorAll('*');
        allElements.forEach(element => {
            if (element.textContent && element.children.length === 0) {
                // Clean text content
                let text = element.textContent;
                text = text.replace(/\*\*([^*]+)\*\*/g, '$1'); // **text**
                text = text.replace(/\?\?([^?]+)\?\?/g, '$1'); // ??text??
                text = text.replace(/\*\*/g, ''); // remaining **
                text = text.replace(/\?\?/g, ''); // remaining ??
                
                if (text !== element.textContent) {
                    element.textContent = text;
                }
            }
            
            // Clean up form element values
            if (element.tagName === 'OPTION' || element.tagName === 'INPUT') {
                if (element.value) {
                    element.value = element.value.replace(/\*\*/g, '').replace(/\?\?/g, '');
                }
                if (element.innerHTML) {
                    element.innerHTML = element.innerHTML.replace(/\*\*/g, '').replace(/\?\?/g, '');
                }
            }
        });
        
        // Specifically target Joomla toolbar buttons
        const toolbarButtons = document.querySelectorAll('.toolbar .btn, .toolbar-list .btn, .subhead .btn, .btn-toolbar .btn, button, .button');
        toolbarButtons.forEach(button => {
            if (button.textContent) {
                let text = button.textContent.trim();
                
                // Clean up common toolbar button patterns
                text = text.replace(/\*\*Save & Close\*\*/g, 'Save & Close');
                text = text.replace(/\*\*Cancel\*\*/g, 'Cancel');
                text = text.replace(/\*\*Restore Defaults\*\*/g, 'Restore Defaults');
                text = text.replace(/\*\*Save\*\*/g, 'Save');
                text = text.replace(/\*\*Apply\*\*/g, 'Apply');
                text = text.replace(/\*\*Close\*\*/g, 'Close');
                
                // General cleanup
                text = text.replace(/\*\*([^*]+)\*\*/g, '$1');
                text = text.replace(/\*\*/g, '');
                text = text.replace(/\?\?/g, '');
                
                if (text !== button.textContent.trim()) {
                    button.textContent = text;
                }
            }
            
            // Also clean innerHTML for buttons with icons
            if (button.innerHTML && button.innerHTML.includes('**')) {
                button.innerHTML = button.innerHTML.replace(/\*\*/g, '');
            }
        });
        
        // Clean up any remaining ** in the page title and headers
        const headers = document.querySelectorAll('h1, h2, h3, h4, h5, h6, .page-title, .component-title');
        headers.forEach(header => {
            if (header.textContent && header.textContent.includes('**')) {
                header.textContent = header.textContent.replace(/\*\*/g, '');
            }
        });
    }
    
    // Replace text content in the entire document
    function replaceText(node) {
        if (node.nodeType === Node.TEXT_NODE) {
            let text = node.textContent;
            for (const [constant, translation] of Object.entries(translations)) {
                text = text.replace(constant, translation);
            }
            if (text !== node.textContent) {
                node.textContent = text;
            }
        } else {
            for (let child of node.childNodes) {
                replaceText(child);
            }
        }
    }
    
    // Apply replacements to the entire configuration form
    const form = document.getElementById('adminForm');
    if (form) {
        replaceText(form);
    }
    
    // Run cleanup functions immediately and repeatedly
    cleanupMarkers();
    
    // Run cleanup multiple times to catch dynamically loaded content (like toolbar buttons)
    setTimeout(cleanupMarkers, 50);
    setTimeout(cleanupMarkers, 100);
    setTimeout(cleanupMarkers, 250);
    setTimeout(cleanupMarkers, 500);
    setTimeout(cleanupMarkers, 1000);
    setTimeout(cleanupMarkers, 2000);
    
    // Set up a MutationObserver to clean up any new content that gets added to the page
    const observer = new MutationObserver(function(mutations) {
        let needsCleanup = false;
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                needsCleanup = true;
            }
        });
        if (needsCleanup) {
            setTimeout(cleanupMarkers, 10);
        }
    });
    
    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});
</script>
