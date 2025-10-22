<?php
/**
 * Services Component Installation Checker
 * Upload this file to your Joomla root directory and run via browser to check missing files
 */

// Only allow execution from web browser for security
if (php_sapi_name() === 'cli') {
    die('This script can only be run from a web browser');
}

define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

echo "<h2>Services Component - Installation Checker</h2>\n";
echo "<p>This script checks for missing files that need to be uploaded to your server.</p>\n";

$requiredFiles = array(
    // Core component files
    'administrator/components/com_services/helpers/database.php' => 'Database Helper Class',
    'administrator/components/com_services/language/en-GB/en-GB.com_services.ini' => 'Admin Language File',
    'administrator/components/com_services/media/css/admin.css' => 'Admin CSS Styles',
    'administrator/components/com_services/media/js/admin-services.js' => 'Admin Services JavaScript',
    'administrator/components/com_services/media/js/dashboard-charts.js' => 'Dashboard Charts JavaScript',
    'administrator/components/com_services/tmpl/configuration/default.php' => 'Configuration Template',
    'administrator/components/com_services/tmpl/dashboard/default.php' => 'Dashboard Template',
    'administrator/components/com_services/tmpl/items/default.php' => 'Items List Template',
    'administrator/components/com_services/src/Model/DashboardModel.php' => 'Dashboard Model',
    
    // Language files in other locations
    'administrator/language/en-GB/en-GB.com_services.ini' => 'Global Admin Language File',
    'components/com_services/language/en-GB/en-GB.com_services.ini' => 'Frontend Language File',
    
    // Configuration files
    'administrator/components/com_services/forms/config.xml' => 'Configuration Form XML',
    'administrator/components/com_services/config.xml' => 'Legacy Configuration XML'
);

echo "<h3>File Status Check:</h3>\n";
echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>\n";
echo "<tr style='background-color: #f0f0f0;'><th>File Path</th><th>Description</th><th>Status</th></tr>\n";

$missingFiles = array();
$totalFiles = count($requiredFiles);
$foundFiles = 0;

foreach ($requiredFiles as $file => $description) {
    $fullPath = JPATH_BASE . '/' . $file;
    $exists = file_exists($fullPath);
    $status = $exists ? '<span style="color: green;">✅ Found</span>' : '<span style="color: red;">❌ Missing</span>';
    
    if ($exists) {
        $foundFiles++;
    } else {
        $missingFiles[] = $file;
    }
    
    echo "<tr>\n";
    echo "<td><code>{$file}</code></td>\n";
    echo "<td>{$description}</td>\n";
    echo "<td>{$status}</td>\n";
    echo "</tr>\n";
}

echo "</table>\n";

echo "<h3>Summary:</h3>\n";
echo "<p><strong>Total Files:</strong> {$totalFiles}</p>\n";
echo "<p><strong>Found:</strong> <span style='color: green;'>{$foundFiles}</span></p>\n";
echo "<p><strong>Missing:</strong> <span style='color: red;'>" . count($missingFiles) . "</span></p>\n";

if (!empty($missingFiles)) {
    echo "<h3>Files That Need To Be Uploaded:</h3>\n";
    echo "<ol>\n";
    foreach ($missingFiles as $file) {
        echo "<li><code>{$file}</code></li>\n";
    }
    echo "</ol>\n";
    
    echo "<h4>Upload Instructions:</h4>\n";
    echo "<p>Please upload the missing files from your local component development folder to your server at the paths shown above.</p>\n";
    
    echo "<h4>Critical Files (Must Upload First):</h4>\n";
    $criticalFiles = array(
        'administrator/components/com_services/helpers/database.php',
        'administrator/components/com_services/language/en-GB/en-GB.com_services.ini',
        'administrator/language/en-GB/en-GB.com_services.ini'
    );
    
    echo "<ul>\n";
    foreach ($criticalFiles as $criticalFile) {
        if (in_array($criticalFile, $missingFiles)) {
            echo "<li style='color: red; font-weight: bold;'><code>{$criticalFile}</code> - <strong>CRITICAL</strong></li>\n";
        }
    }
    echo "</ul>\n";
    
} else {
    echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px;'>\n";
    echo "<h4>✅ All Files Found!</h4>\n";
    echo "<p>All required component files are present on your server.</p>\n";
    echo "</div>\n";
}

echo "<hr>\n";
echo "<h3>Quick Fixes:</h3>\n";

// Check if database helper exists and test it
$dbHelperPath = JPATH_BASE . '/administrator/components/com_services/helpers/database.php';
if (file_exists($dbHelperPath)) {
    try {
        require_once $dbHelperPath;
        if (class_exists('ServicesHelperDatabase')) {
            echo "<p>✅ Database helper class loaded successfully.</p>\n";
            
            // Test database helper
            $tablesExist = ServicesHelperDatabase::checkTables();
            echo "<p>Database tables exist: " . ($tablesExist ? 'Yes' : 'No') . "</p>\n";
            
            if (!$tablesExist) {
                echo "<p><a href='#' onclick='createTables()' style='background: #007cba; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px;'>Create Database Tables</a></p>\n";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error loading database helper: " . $e->getMessage() . "</p>\n";
    }
} else {
    echo "<p style='color: red;'>❌ Database helper file missing - upload <code>administrator/components/com_services/helpers/database.php</code> first.</p>\n";
}

echo "<script>\n";
echo "function createTables() {\n";
echo "    if (confirm('Create database tables for Services component?')) {\n";
echo "        fetch('', {\n";
echo "            method: 'POST',\n";
echo "            headers: {'Content-Type': 'application/x-www-form-urlencoded'},\n";
echo "            body: 'action=create_tables'\n";
echo "        }).then(() => location.reload());\n";
echo "    }\n";
echo "}\n";
echo "</script>\n";

// Handle table creation request
if (isset($_POST['action']) && $_POST['action'] === 'create_tables') {
    if (file_exists($dbHelperPath)) {
        require_once $dbHelperPath;
        if (class_exists('ServicesHelperDatabase')) {
            $result = ServicesHelperDatabase::createTables();
            echo $result ? "<p style='color: green;'>✅ Tables created successfully!</p>\n" : "<p style='color: red;'>❌ Failed to create tables.</p>\n";
        }
    }
}

echo "<p><em>After uploading missing files, refresh this page to check again.</em></p>\n";
?>