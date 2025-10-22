/**
 * Admin JavaScript for Services Component
 * Handles batch operations, UI interactions, and form validations
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize batch operations
    initializeBatchOperations();
    
    // Initialize card view toggle
    initializeCardViewToggle();
    
    // Initialize form validations
    initializeFormValidations();
    
    // Initialize multiselect behavior
    initializeMultiselect();
    
    // Load saved view preference
    const savedView = localStorage.getItem('services-admin-view') || 'table';
    toggleView(savedView);
});

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize batch operations functionality
 */
function initializeBatchOperations() {
    const batchSelect = document.getElementById('batch-operation-select');
    const batchButton = document.getElementById('batch-operation-button');
    const checkboxes = document.querySelectorAll('input[name="cid[]"]');
    
    if (!batchSelect || !batchButton) return;
    
    // Enable/disable batch button based on selections
    function updateBatchButton() {
        const selectedItems = document.querySelectorAll('input[name="cid[]"]:checked');
        const hasOperation = batchSelect.value !== '';
        
        batchButton.disabled = !(selectedItems.length > 0 && hasOperation);
    }
    
    // Add event listeners
    batchSelect.addEventListener('change', updateBatchButton);
    
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', updateBatchButton);
    });
    
    // Handle batch operation execution
    batchButton.addEventListener('click', function(e) {
        e.preventDefault();
        
        const operation = batchSelect.value;
        const selectedItems = document.querySelectorAll('input[name="cid[]"]:checked');
        
        if (selectedItems.length === 0) {
            alert('Please select items to perform batch operation.');
            return;
        }
        
        if (!operation) {
            alert('Please select a batch operation.');
            return;
        }
        
        // Confirm destructive operations
        if (operation === 'delete' || operation === 'trash') {
            const confirmMsg = operation === 'delete' 
                ? 'Are you sure you want to permanently delete the selected items?'
                : 'Are you sure you want to move the selected items to trash?';
                
            if (!confirm(confirmMsg)) {
                return;
            }
        }
        
        // Execute batch operation
        executeBatchOperation(operation, selectedItems);
    });
}

/**
 * Execute batch operation
 */
function executeBatchOperation(operation, selectedItems) {
    const form = document.getElementById('adminForm');
    if (!form) return;
    
    // Set the task
    document.querySelector('input[name="task"]').value = 'items.batch';
    
    // Add batch operation parameter
    const batchInput = document.createElement('input');
    batchInput.type = 'hidden';
    batchInput.name = 'batch_operation';
    batchInput.value = operation;
    form.appendChild(batchInput);
    
    // Submit form
    form.submit();
}

/**
 * Initialize card view toggle
 */
function initializeCardViewToggle() {
    const toggleButton = document.getElementById('view-toggle');
    const itemsList = document.getElementById('items-list');
    
    if (!toggleButton || !itemsList) return;
    
    toggleButton.addEventListener('click', function() {
        const isCardView = itemsList.classList.contains('card-view');
        
        if (isCardView) {
            itemsList.classList.remove('card-view');
            itemsList.classList.add('list-view');
            toggleButton.innerHTML = '<i class="fas fa-th-large"></i> Card View';
        } else {
            itemsList.classList.remove('list-view');
            itemsList.classList.add('card-view');
            toggleButton.innerHTML = '<i class="fas fa-list"></i> List View';
        }
        
        // Save preference to localStorage
        localStorage.setItem('services-view-mode', isCardView ? 'list' : 'card');
    });
    
    // Load saved preference
    const savedView = localStorage.getItem('services-view-mode');
    if (savedView === 'card') {
        itemsList.classList.add('card-view');
        toggleButton.innerHTML = '<i class="fas fa-list"></i> List View';
    } else {
        itemsList.classList.add('list-view');
        toggleButton.innerHTML = '<i class="fas fa-th-large"></i> Card View';
    }
}

/**
 * Initialize form validations
 */
function initializeFormValidations() {
    const forms = document.querySelectorAll('form.needs-validation');
    
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            form.classList.add('was-validated');
        });
    });
}

/**
 * Initialize multiselect behavior for checkboxes
 */
function initializeMultiselect() {
    const checkAllBox = document.getElementById('checkall-toggle');
    const checkboxes = document.querySelectorAll('input[name="cid[]"]');
    
    if (checkAllBox) {
        checkAllBox.addEventListener('change', function() {
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = checkAllBox.checked;
            });
            updateBoxChecked();
        });
    }
    
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            updateBoxChecked();
            
            // Update check all state
            if (checkAllBox) {
                const checkedBoxes = document.querySelectorAll('input[name="cid[]"]:checked');
                checkAllBox.checked = (checkedBoxes.length === checkboxes.length);
                checkAllBox.indeterminate = (checkedBoxes.length > 0 && checkedBoxes.length < checkboxes.length);
            }
        });
    });
}

/**
 * Update the boxchecked hidden field
 */
function updateBoxChecked() {
    const checkedBoxes = document.querySelectorAll('input[name="cid[]"]:checked');
    const boxCheckedInput = document.querySelector('input[name="boxchecked"]');
    
    if (boxCheckedInput) {
        boxCheckedInput.value = checkedBoxes.length;
    }
}

/**
 * Show loading state for buttons
 */
function showButtonLoading(button, loadingText = 'Processing...') {
    if (!button) return;
    
    button.dataset.originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + loadingText;
    button.disabled = true;
}

/**
 * Hide loading state for buttons
 */
function hideButtonLoading(button) {
    if (!button || !button.dataset.originalText) return;
    
    button.innerHTML = button.dataset.originalText;
    button.disabled = false;
    delete button.dataset.originalText;
}

/**
 * Show confirmation dialog
 */
function showConfirmDialog(message, callback) {
    if (confirm(message)) {
        if (typeof callback === 'function') {
            callback();
        }
    }
}

/**
 * Show success message
 */
function showSuccessMessage(message) {
    // Create a simple alert for now - can be enhanced with toast notifications
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible fade show';
    alert.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    
    const container = document.querySelector('.main-content') || document.body;
    container.insertBefore(alert, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 5000);
}

/**
 * Show error message
 */
function showErrorMessage(message) {
    // Create a simple alert for now - can be enhanced with toast notifications
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show';
    alert.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    
    const container = document.querySelector('.main-content') || document.body;
    container.insertBefore(alert, container.firstChild);
    
    // Auto-dismiss after 8 seconds
    setTimeout(function() {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 8000);
}

/**
 * Batch Publish Selected Items
 */
function batchPublish() {
    const selectedItems = getSelectedItems();
    if (selectedItems.length === 0) {
        alert('Please select at least one item to publish.');
        return;
    }
    
    if (confirm('Are you sure you want to publish the selected items?')) {
        submitTask('items.publish');
    }
}

/**
 * Batch Unpublish Selected Items
 */
function batchUnpublish() {
    const selectedItems = getSelectedItems();
    if (selectedItems.length === 0) {
        alert('Please select at least one item to unpublish.');
        return;
    }
    
    if (confirm('Are you sure you want to unpublish the selected items?')) {
        submitTask('items.unpublish');
    }
}

/**
 * Batch Feature Selected Items
 */
function batchFeature() {
    const selectedItems = getSelectedItems();
    if (selectedItems.length === 0) {
        alert('Please select at least one item to feature.');
        return;
    }
    
    if (confirm('Are you sure you want to mark the selected items as featured?')) {
        submitTask('items.featured');
    }
}

/**
 * Batch Location Update
 */
function batchLocation() {
    const selectedItems = getSelectedItems();
    if (selectedItems.length === 0) {
        alert('Please select at least one item to update location.');
        return;
    }
    
    const location = prompt('Enter new location for selected items:');
    if (location && location.trim() !== '') {
        // This would need a specific controller method to handle bulk location updates
        alert('Location update functionality needs to be implemented in the backend.');
    }
}

/**
 * Batch Delete Selected Items
 */
function batchDelete() {
    const selectedItems = getSelectedItems();
    if (selectedItems.length === 0) {
        alert('Please select at least one item to delete.');
        return;
    }
    
    if (confirm('Are you sure you want to delete the selected items? This action cannot be undone!')) {
        submitTask('items.delete');
    }
}

/**
 * Toggle Card/Table View
 */
function toggleView(viewType) {
    const cardView = document.getElementById('cardView');
    const tableView = document.getElementById('tableView');
    const cardBtn = document.getElementById('cardViewBtn');
    const tableBtn = document.getElementById('tableViewBtn');
    
    if (viewType === 'card') {
        cardView.style.display = 'flex';
        tableView.style.display = 'none';
        cardBtn.classList.add('btn-primary');
        cardBtn.classList.remove('btn-outline-primary');
        tableBtn.classList.add('btn-outline-primary');
        tableBtn.classList.remove('btn-primary');
    } else {
        cardView.style.display = 'none';
        tableView.style.display = 'block';
        tableBtn.classList.add('btn-primary');
        tableBtn.classList.remove('btn-outline-primary');
        cardBtn.classList.add('btn-outline-primary');
        cardBtn.classList.remove('btn-primary');
    }
    
    // Save preference
    localStorage.setItem('services-admin-view', viewType);
}

/**
 * View Details of an Item
 */
function viewDetails(itemId) {
    window.open('index.php?option=com_services&task=item.edit&id=' + itemId, '_blank');
}

/**
 * Delete Individual Item
 */
function deleteItem(itemId) {
    if (confirm('Are you sure you want to delete this item? This action cannot be undone!')) {
        // Check the checkbox for this item
        const checkbox = document.querySelector('input[name="cid[]"][value="' + itemId + '"]');
        if (checkbox) {
            checkbox.checked = true;
            submitTask('items.delete');
        }
    }
}

/**
 * Toggle Featured Status of Individual Item
 */
function toggleFeature(itemId) {
    if (confirm('Are you sure you want to mark this item as featured?')) {
        const checkbox = document.querySelector('input[name="cid[]"][value="' + itemId + '"]');
        if (checkbox) {
            checkbox.checked = true;
            submitTask('items.featured');
        }
    }
}

/**
 * Get Selected Items
 */
function getSelectedItems() {
    const checkboxes = document.querySelectorAll('input[name="cid[]"]:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

/**
 * Submit Task to Form
 */
function submitTask(task) {
    const form = document.getElementById('adminForm');
    const taskInput = document.querySelector('input[name="task"]');
    
    if (form && taskInput) {
        taskInput.value = task;
        form.submit();
    }
}

