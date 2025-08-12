<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

// Ambil activities per status
$stmt = $pdo->query("SELECT id, no, information_date, priority, user_position, department, application, type, description, action_solution, customer, project, due_date, cnc_number, status FROM activities ORDER BY no ASC");
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

$columns = [
  'Open' => [],
  'On Progress' => [],
  'Need Requirement' => [],
  'Done' => [],
  'Cancel' => [],
];
foreach ($activities as $a) {
  $status = $a['status'] ?? 'Open';
  if (!isset($columns[$status])) { $columns['Open'][] = $a; } else { $columns[$status][] = $a; }
}

// Script tambahan di footer (HEREDOC untuk menghindari escape)
$script = ($script ?? '') . <<<'SCRIPT'
<script>
// Clean Kanban Implementation - Fixed Version
document.addEventListener('DOMContentLoaded', function() {
  console.log('Initializing Kanban...');
  
  // Initialize drag and drop
  initDragAndDrop();
  
  // Initialize card click events
  initCardEvents();

  // Initialize modal event listeners
  initModalEventListeners();
  
  console.log('Kanban initialized successfully');
});

function initModalEventListeners() {
  // Close modal when clicking outside
  document.addEventListener('click', function(e) {
    const modal = document.getElementById('editActivityModal');
    if (modal && e.target === modal) {
      closeEditModal();
    }
  });

  // Close modal with Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeEditModal();
    }
  });
}

function initDragAndDrop() {
  // Add drag events to cards
  document.querySelectorAll('.kanban-card').forEach(function(card) {
    card.addEventListener('dragstart', function(e) {
      e.dataTransfer.setData('text/plain', this.dataset.id);
      console.log('Drag started for card:', this.dataset.id);
    });
  });

  // Add drop events to columns
  document.querySelectorAll('.kanban-column').forEach(function(col) {
    col.addEventListener('dragover', function(e) {
      e.preventDefault();
      this.classList.add('drag-over');
    });
    
    col.addEventListener('dragleave', function() {
      this.classList.remove('drag-over');
    });
    
    col.addEventListener('drop', function(e) {
      e.preventDefault();
      this.classList.remove('drag-over');
      
      const cardId = e.dataTransfer.getData('text/plain');
      const newStatus = this.dataset.status;
      
      console.log('Dropping card:', cardId, 'to status:', newStatus);
      
      // Update status via AJAX
      updateActivityStatus(cardId, newStatus, this);
    });
  });
}

function initCardEvents() {
  console.log('Initializing card events...');
  
  // Add double click events to cards
  const cards = document.querySelectorAll('.kanban-card');
  console.log('Found', cards.length, 'kanban cards');
  
  cards.forEach(function(card, index) {
    console.log(`Setting up card ${index + 1}:`, card.dataset.id);
    
    card.addEventListener('dblclick', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      const cardId = this.dataset.id;
      console.log('Double click on card:', cardId);
      
      if (cardId) {
        openEditModal(cardId);
      } else {
        console.error('Card has no data-id attribute');
        showError('Card ID tidak valid');
      }
    });
  });
  
  console.log('Card events initialized successfully');
}

function updateActivityStatus(cardId, newStatus, targetColumn) {
  // Simple approach - just move the card and show success
  const cardElement = document.querySelector('[data-id="' + cardId + '"]');
  if (cardElement) {
    targetColumn.querySelector('.kanban-cards').prepend(cardElement);
    showSuccess('Status berhasil diubah ke ' + newStatus);
  }
  
  // Send update to server in background (don't wait for response)
  const xhr = new XMLHttpRequest();
  xhr.open('POST', 'update_activity_status.php', true);
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.send(JSON.stringify({id: cardId, status: newStatus}));
}

function openEditModal(activityId) {
  console.log('Opening edit modal for activity:', activityId);
  
  if (!activityId) {
    showError('Invalid activity ID');
    return;
  }
  
  // Remove existing modal if any
  const existingModal = document.getElementById('editActivityModal');
  if (existingModal) {
    console.log('Removing existing modal');
    existingModal.remove();
  }
  
  console.log('Creating new modal HTML...');
  
  // Create modal HTML
  const modalHTML = `
    <div id="editActivityModal" class="custom-modal-overlay" style="display: flex; opacity: 1; visibility: visible;">
      <div class="custom-modal" style="max-width: 900px; width: 90%; margin: 20px auto;">
        <div class="custom-modal-header">
          <h5 class="custom-modal-title">Edit Activity</h5>
          <button type="button" class="custom-modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="custom-modal-body">
          <div id="editLoading" class="loading-text" style="display: block; margin-bottom: 8px; color: #6b7280;">Loading...</div>
          <div id="editFormContent" style="display: none;">
            <form id="editActivityForm" method="post" action="update_activity.php">
              <input type="hidden" name="id" id="edit_id">
              
              <div class="custom-modal-row">
                <div class="custom-modal-col">
                  <label class="custom-modal-label">No</label>
                  <input type="number" name="no" id="edit_no" class="custom-modal-input" readonly>
                </div>
                <div class="custom-modal-col">
                  <label class="custom-modal-label">Status *</label>
                  <select name="status" id="edit_status" class="custom-modal-select" required>
                    <option value="Open">Open</option>
                    <option value="On Progress">On Progress</option>
                    <option value="Need Requirement">Need Requirement</option>
                    <option value="Done">Done</option>
                    <option value="Cancel">Cancel</option>
                  </select>
                </div>
              </div>
              
              <div class="custom-modal-row">
                <div class="custom-modal-col">
                  <label class="custom-modal-label">Information Date *</label>
                  <input type="date" name="information_date" id="edit_information_date" class="custom-modal-input" required>
                </div>
                <div class="custom-modal-col">
                  <label class="custom-modal-label">Priority *</label>
                  <select name="priority" id="edit_priority" class="custom-modal-select" required>
                    <option value="Urgent">Urgent</option>
                    <option value="Normal">Normal</option>
                    <option value="Low">Low</option>
                  </select>
                </div>
              </div>
              
              <div class="custom-modal-row">
                <div class="custom-modal-col">
                  <label class="custom-modal-label">User Position</label>
                  <input type="text" name="user_position" id="edit_user_position" class="custom-modal-input">
                </div>
                <div class="custom-modal-col">
                  <label class="custom-modal-label">Department</label>
                  <select name="department" id="edit_department" class="custom-modal-select">
                    <option value="">Select Department</option>
                    <option value="Food & Beverage">Food & Beverage</option>
                    <option value="Kitchen">Kitchen</option>
                    <option value="Room Division">Room Division</option>
                    <option value="Front Office">Front Office</option>
                    <option value="Housekeeping">Housekeeping</option>
                    <option value="Engineering">Engineering</option>
                    <option value="Sales & Marketing">Sales & Marketing</option>
                    <option value="IT / EDP">IT / EDP</option>
                    <option value="Accounting">Accounting</option>
                    <option value="Executive Office">Executive Office</option>
                  </select>
                </div>
              </div>
              
              <div class="custom-modal-row">
                <div class="custom-modal-col">
                  <label class="custom-modal-label">Application *</label>
                  <select name="application" id="edit_application" class="custom-modal-select" required>
                    <option value="">-</option>
                    <option value="Power FO">Power FO</option>
                    <option value="My POS">My POS</option>
                    <option value="My MGR">My MGR</option>
                    <option value="Power AR">Power AR</option>
                    <option value="Power INV">Power INV</option>
                    <option value="Power AP">Power AP</option>
                    <option value="Power GL">Power GL</option>
                    <option value="Keylock">Keylock</option>
                    <option value="PABX">PABX</option>
                    <option value="DIM">DIM</option>
                    <option value="Dynamic Room Rate">Dynamic Room Rate</option>
                    <option value="Channel Manager">Channel Manager</option>
                    <option value="PB1">PB1</option>
                    <option value="Power SIGN">Power SIGN</option>
                    <option value="Multi Properties">Multi Properties</option>
                    <option value="Scanner ID">Scanner ID</option>
                    <option value="IPOS">IPOS</option>
                    <option value="Power Runner">Power Runner</option>
                    <option value="Power RA">Power RA</option>
                    <option value="Power ME">Power ME</option>
                    <option value="ECOS">ECOS</option>
                    <option value="Cloud WS">Cloud WS</option>
                    <option value="Power GO">Power GO</option>
                    <option value="Dashpad">Dashpad</option>
                    <option value="IPTV">IPTV</option>
                    <option value="HSIA">HSIA</option>
                    <option value="SGI">SGI</option>
                    <option value="Guest Survey">Guest Survey</option>
                    <option value="Loyalty Management">Loyalty Management</option>
                    <option value="AccPac">AccPac</option>
                    <option value="GL Consolidation">GL Consolidation</option>
                    <option value="Self Check In">Self Check In</option>
                    <option value="Check In Desk">Check In Desk</option>
                    <option value="Others">Others</option>
                  </select>
                </div>
                <div class="custom-modal-col">
                  <label class="custom-modal-label">Type</label>
                  <select name="type" id="edit_type" class="custom-modal-select">
                    <option value="Setup">Setup</option>
                    <option value="Question">Question</option>
                    <option value="Issue">Issue</option>
                    <option value="Report Issue">Report Issue</option>
                    <option value="Report Request">Report Request</option>
                    <option value="Feature Request">Feature Request</option>
                  </select>
                </div>
              </div>
              
              <div class="custom-modal-row">
                <div class="custom-modal-col">
                  <label class="custom-modal-label">Customer</label>
                  <input type="text" name="customer" id="edit_customer" class="custom-modal-input">
                </div>
                <div class="custom-modal-col">
                  <label class="custom-modal-label">Project</label>
                  <input type="text" name="project" id="edit_project" class="custom-modal-input">
                </div>
              </div>
              
              <div class="custom-modal-row">
                <div class="custom-modal-col">
                  <label class="custom-modal-label">Completed Date</label>
                  <input type="date" name="due_date" id="edit_due_date" class="custom-modal-input">
                </div>
                <div class="custom-modal-col">
                  <label class="custom-modal-label">CNC Number</label>
                  <input type="text" name="cnc_number" id="edit_cnc_number" class="custom-modal-input">
                </div>
              </div>
              
              <div class="custom-modal-row">
                <div class="custom-modal-col" style="grid-column: 1 / -1;">
                  <label class="custom-modal-label">Description</label>
                  <textarea name="description" id="edit_description" class="custom-modal-textarea" rows="3"></textarea>
                </div>
              </div>
              
              <div class="custom-modal-row">
                <div class="custom-modal-col" style="grid-column: 1 / -1;">
                  <label class="custom-modal-label">Action Solution</label>
                  <textarea name="action_solution" id="edit_action_solution" class="custom-modal-textarea" rows="3"></textarea>
                </div>
              </div>
            </form>
          </div>
        </div>
        <div class="custom-modal-footer">
          <button type="button" class="custom-btn custom-btn-primary" onclick="submitEditForm()">Update</button>
          <button type="button" class="custom-btn custom-btn-secondary" onclick="closeEditModal()">Close</button>
        </div>
      </div>
    </div>
  `;
  
  // Insert modal into DOM
  document.body.insertAdjacentHTML('beforeend', modalHTML);
  
  console.log('Modal created, loading activity data...');
  
  // Load activity data
  loadActivityData(activityId);
}

function loadActivityData(activityId) {
  console.log('Loading data for activity:', activityId);
  
  const loadingEl = document.getElementById('editLoading');
  if (!loadingEl) {
    console.error('Loading element not found');
    return;
  }
  
  loadingEl.textContent = 'Loading activity data...';
  
  const xhr = new XMLHttpRequest();
  xhr.open('GET', 'get_activity.php?id=' + activityId, true);
  
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      const formEl = document.getElementById('editFormContent');
      
      if (xhr.status === 200) {
        try {
          console.log('Raw response:', xhr.responseText);
          const response = JSON.parse(xhr.responseText);
          console.log('Parsed response:', response);
          
          if (response.success && response.data) {
            // Populate form fields
            populateFormFields(response.data);
            
            // Show form content
            loadingEl.style.display = 'none';
            formEl.style.display = 'block';
            
            console.log('Data loaded successfully');
          } else {
            throw new Error(response.message || 'Failed to load data');
          }
        } catch (parseError) {
          console.error('Parse error:', parseError);
          console.error('Raw response:', xhr.responseText);
          loadingEl.textContent = 'Error: Failed to parse response';
          showError('Failed to load activity data: ' + parseError.message);
        }
      } else {
        console.error('HTTP error:', xhr.status);
        console.error('Response text:', xhr.responseText);
        loadingEl.textContent = 'Error: HTTP ' + xhr.status;
        showError('Failed to load activity data: HTTP ' + xhr.status);
      }
    }
  };
  
  xhr.onerror = function() {
    console.error('XHR error');
    const loadingEl = document.getElementById('editLoading');
    if (loadingEl) {
      loadingEl.textContent = 'Error: Network error';
    }
    showError('Network error occurred while loading data');
  };
  
  xhr.send();
}

function populateFormFields(data) {
  console.log('Populating form fields with data:', data);
  
  // Map database fields to form fields
  const fieldMappings = {
    'id': 'edit_id',
    'no': 'edit_no',
    'status': 'edit_status',
    'information_date': 'edit_information_date',
    'priority': 'edit_priority',
    'user_position': 'edit_user_position',
    'department': 'edit_department',
    'application': 'edit_application',
    'type': 'edit_type',
    'description': 'edit_description',
    'action_solution': 'edit_action_solution',
    'customer': 'edit_customer',
    'project': 'edit_project',
    'due_date': 'edit_due_date',
    'cnc_number': 'edit_cnc_number'
  };
  
  let populatedCount = 0;
  let errorCount = 0;
  
  Object.keys(fieldMappings).forEach(dbField => {
    const formFieldId = fieldMappings[dbField];
    const formField = document.getElementById(formFieldId);
    
    if (formField && data[dbField] !== undefined && data[dbField] !== null) {
      let value = data[dbField];
      
      // Handle date formatting
      if ((dbField === 'information_date' || dbField === 'due_date') && value) {
        // Convert date format if needed
        if (typeof value === 'string') {
          // If date is in format "dd/mm/yyyy", convert to "yyyy-mm-dd"
          if (value.includes('/')) {
            const parts = value.split('/');
            if (parts.length === 3) {
              value = `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
            }
          } else {
            // Remove time part if present
            value = value.split(' ')[0];
          }
        }
      }
      
      // Handle select fields - set selected option
      if (formField.tagName === 'SELECT') {
        // Find and select the matching option
        const option = formField.querySelector(`option[value="${value}"]`);
        if (option) {
          option.selected = true;
          populatedCount++;
          console.log(`✓ Set ${formFieldId} to:`, value);
        } else {
          // If exact match not found, try to find partial match
          const options = Array.from(formField.options);
          const partialMatch = options.find(opt => 
            opt.value.toLowerCase().includes(value.toLowerCase()) || 
            value.toLowerCase().includes(opt.value.toLowerCase())
          );
          if (partialMatch) {
            partialMatch.selected = true;
            populatedCount++;
            console.log(`✓ Set ${formFieldId} to partial match:`, partialMatch.value, '(original:', value, ')');
          } else {
            errorCount++;
            console.warn(`✗ No match found for ${formFieldId} with value:`, value);
          }
        }
      } else {
        formField.value = value;
        populatedCount++;
        console.log(`✓ Set ${formFieldId} to:`, value);
      }
    } else {
      if (!formField) {
        errorCount++;
        console.warn(`✗ Form field ${formFieldId} not found`);
      } else if (data[dbField] === undefined || data[dbField] === null) {
        console.log(`- Field ${dbField} is empty or undefined`);
      }
    }
  });
  
  console.log(`Form population complete: ${populatedCount} fields populated, ${errorCount} errors`);
}

function submitEditForm() {
  const form = document.getElementById('editActivityForm');
  if (!form) {
    showError('Form not found');
    return;
  }
  
  // Validate required fields
  const requiredFields = ['status', 'information_date', 'priority', 'application'];
  const missingFields = [];
  
  requiredFields.forEach(fieldName => {
    const field = form.querySelector(`[name="${fieldName}"]`);
    if (field && !field.value.trim()) {
      missingFields.push(fieldName);
    }
  });
  
  if (missingFields.length > 0) {
    showError('Required fields missing: ' + missingFields.join(', '));
    return;
  }
  
  // Handle empty date fields - set to empty string if not filled
  const dueDateField = form.querySelector('[name="due_date"]');
  if (dueDateField && !dueDateField.value.trim()) {
    dueDateField.value = ''; // Ensure empty string for empty date
  }
  
  const formData = new FormData(form);
  
  // Log form data for debugging
  console.log('Submitting form data:');
  for (let [key, value] of formData.entries()) {
    console.log(`${key}: ${value}`);
  }
  
  // Show loading state
  const submitBtn = document.querySelector('.custom-btn-primary');
  const originalText = submitBtn.textContent;
  submitBtn.textContent = 'Updating...';
  submitBtn.disabled = true;
  
  const xhr = new XMLHttpRequest();
  xhr.open('POST', 'update_activity.php', true);
  
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      // Reset button state
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
      
      if (xhr.status === 200) {
        try {
          const response = JSON.parse(xhr.responseText);
          console.log('Server response:', response);
          
          if (response.success) {
            showSuccess('Activity berhasil diperbarui!');
            closeEditModal();
            
            // Refresh the page to show updated data
            setTimeout(() => {
              location.reload();
            }, 1000);
          } else {
            showError('Server error: ' + (response.message || 'Unknown error'));
          }
        } catch (parseError) {
          console.error('Parse error:', parseError);
          console.error('Raw response:', xhr.responseText);
          showError('Failed to parse server response');
        }
      } else {
        console.error('HTTP error:', xhr.status);
        console.error('Response text:', xhr.responseText);
        showError('HTTP error: ' + xhr.status + ' - ' + xhr.statusText);
      }
    }
  };
  
  xhr.onerror = function() {
    console.error('XHR error');
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
    showError('Network error occurred while updating');
  };
  
  xhr.send(formData);
}

function closeEditModal() {
  const modal = document.getElementById('editActivityModal');
  if (modal) {
    modal.remove();
  }
}

// Global utility functions
function showSuccess(message) {
  // Try to use notification manager if available
  if (window.logoNotificationManager && window.logoNotificationManager.showActivityUpdated) {
    window.logoNotificationManager.showActivityUpdated(message, 3000);
  } else {
    // Fallback to simple alert
    alert('Success: ' + message);
  }
}

function showError(message) {
  // Try to use notification manager if available
  if (window.logoNotificationManager && window.logoNotificationManager.showActivityError) {
    window.logoNotificationManager.showActivityError(message, 5000);
  } else {
    // Fallback to simple alert
    console.error('Error:', message);
    alert('Error: ' + message);
  }
}
</script>
SCRIPT;
?>
<?php include './partials/layouts/layoutHorizontal.php'; ?>

<style>
.kanban-board{display:grid;grid-template-columns:repeat(5,1fr);gap:16px}
.kanban-column{background:var(--glass-bg,rgba(255,255,255,.95));backdrop-filter:blur(10px);border:1px solid rgba(0,0,0,.06);border-radius:12px;overflow:hidden;min-height:60vh;display:flex;flex-direction:column}
.kanban-header{padding:12px 14px;font-weight:700;background:linear-gradient(135deg,var(--brand-accent-strong,#6BB2C8),var(--brand-accent,#90C5D8));color:#fff}
.kanban-cards{padding:12px;display:flex;flex-direction:column;gap:12px}
.kanban-card{position:relative;background:linear-gradient(180deg,#ffffff, #f8fafc);border:1px solid #e5e7eb;border-radius:14px;padding:12px 12px 10px 16px;cursor:grab;box-shadow:0 10px 24px rgba(2,6,23,.06);transition:transform .18s ease, box-shadow .18s ease}
.kanban-card{user-select:none;-webkit-user-select:none;-ms-user-select:none}
.kanban-card::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;border-top-left-radius:14px;border-bottom-left-radius:14px;background:var(--accent,#90C5D8)}
.kanban-card:hover{transform:translateY(-2px);box-shadow:0 14px 32px rgba(2,6,23,.12)}
.kanban-title{display:flex;align-items:center;gap:8px;font-weight:700;color:#0f172a}
.badge{display:inline-block;font-size:11px;line-height:1;padding:4px 8px;border-radius:9999px;border:1px solid rgba(2,6,23,.08);background:#eef2ff;color:#3730a3}
.badge.app{background:#ecfeff;color:#155e75;border-color:#a5f3fc}
.badge.type{background:#f0fdf4;color:#14532d;border-color:#bbf7d0}
.badge.pri{background:#eff6ff;color:#1e40af;border-color:#bfdbfe}
.meta{font-size:12px;color:#64748b;display:flex;gap:8px;flex-wrap:wrap;margin-top:6px}
.action-solution{font-size:12px;color:#475569;margin-top:6px}
.accent-urgent{--accent:#ef4444}
.accent-normal{--accent:#3b82f6}
.accent-low{--accent:#f59e0b}
.kanban-column.drag-over{outline:2px dashed var(--brand-accent-strong,#6BB2C8);outline-offset:-6px}
[data-theme="dark"] .kanban-column{background:#1f2937;border-color:rgba(148,163,184,.18)}
[data-theme="dark"] .kanban-card{background:linear-gradient(180deg,#111827,#0b1220);border-color:#374151;color:#e5e7eb}
[data-theme="dark"] .kanban-title{color:#e5e7eb}
[data-theme="dark"] .badge{border-color:#334155}
[data-theme="dark"] .badge.app{background:#0e7490;color:#ecfeff}
[data-theme="dark"] .badge.type{background:#14532d;color:#ecfdf5}
[data-theme="dark"] .badge.pri{background:#1e3a8a;color:#dbeafe}
[data-theme="dark"] .meta{color:#9ca3af}
@media(max-width:1200px){.kanban-board{grid-template-columns:repeat(3,1fr)}}
@media(max-width:768px){.kanban-board{grid-template-columns:1fr}}

/* Enhanced modal styles */
.custom-modal-overlay{
  position:fixed;
  inset:0;
  z-index:9999;
  background:rgba(15,23,42,.6);
  display:none;
  opacity:0;
  visibility:hidden;
  transition:opacity .2s ease, visibility .2s ease;
}
.custom-modal{
  position:relative;
  margin:20px auto;
  max-width:900px;
  width:90%;
  max-height:90vh;
  overflow-y:auto;
  background:var(--glass-bg, #fff);
  border:1px solid rgba(0,0,0,.08);
  border-radius:14px;
  box-shadow:0 20px 60px rgba(2,6,23,.25);
}
.custom-modal-header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:16px 20px;
  border-bottom:1px solid rgba(0,0,0,.06);
  background:linear-gradient(135deg,var(--brand-accent-strong,#6BB2C8),var(--brand-accent,#90C5D8));
  color:#fff;
  border-radius:14px 14px 0 0;
}
.custom-modal-title{
  margin:0;
  font-weight:700;
  font-size:18px;
}
.custom-modal-close{
  border:none;
  background:transparent;
  font-size:24px;
  line-height:1;
  cursor:pointer;
  padding:8px;
  border-radius:6px;
  transition:background-color .2s;
  color:#fff;
}
.custom-modal-close:hover{
  background-color:rgba(255,255,255,.2);
}
.custom-modal-body{
  padding:20px;
  max-height:70vh;
  overflow-y:auto;
}
.custom-modal-row{
  display:flex;
  gap:16px;
  margin-bottom:16px;
  align-items:flex-start;
}
.custom-modal-col{
  flex:1;
  min-width:0;
}
.custom-modal-label{
  display:block;
  margin-bottom:8px;
  font-weight:600;
  color:#374151;
  font-size:14px;
}
.custom-modal-input,
.custom-modal-select,
.custom-modal-textarea{
  width:100%;
  padding:12px 16px;
  border:1px solid #e5e7eb;
  border-radius:8px;
  background:#fff;
  font-size:14px;
  transition:all .2s;
  box-sizing:border-box;
}
.custom-modal-input:focus,
.custom-modal-select:focus,
.custom-modal-textarea:focus{
  outline:none;
  border-color:#3b82f6;
  box-shadow:0 0 0 3px rgba(59,130,246,.1);
  transform:translateY(-1px);
}
.custom-modal-textarea{
  resize:vertical;
  min-height:80px;
  font-family:inherit;
}
.custom-modal-footer{
  display:flex;
  gap:12px;
  justify-content:flex-end;
  padding:16px 20px;
  border-top:1px solid rgba(0,0,0,.06);
  background:#f9fafb;
  border-radius:0 0 14px 14px;
}
.custom-btn{
  border:none;
  border-radius:8px; /* match list corner */
  padding:10px 16px;
  cursor:pointer;
  font-weight:600;
  font-size:14px;
  transition:all .2s;
}
.custom-btn-primary{
  background:linear-gradient(135deg,var(--login-primary-start) 0%, var(--login-primary-end) 100%) !important;
  color:#fff;
  border:none;
  border-radius:8px; /* match list corner */
  box-shadow:0 8px 20px rgba(102,126,234,0.25) !important; /* softer like list */
}
.custom-btn-primary:hover{
  transform:translateY(-2px);
  box-shadow:0 8px 25px rgba(107,178,200,.3);
}
.custom-btn-primary:disabled{
  opacity:0.6;
  cursor:not-allowed;
  transform:none;
  box-shadow:none;
}
.custom-btn-secondary{
  background:#6b7280; /* gray-500 like list */
  color:#ffffff;
  border:none;
  border-radius:8px; /* match list corner */
}
.custom-btn-secondary:hover{ filter:brightness(1.03); transform:translateY(-1px); }
[data-theme="dark"] .custom-modal{background:#1f2937;border-color:#374151}
[data-theme="dark"] .custom-modal-input,
[data-theme="dark"] .custom-modal-select,
[data-theme="dark"] .custom-modal-textarea{background:#0b1220;border-color:#334155;color:#e5e7eb}
[data-theme="dark"] .custom-modal-label{color:#e5e7eb}
[data-theme="dark"] .custom-modal-close:hover{background-color:rgba(255,255,255,.1)}

.loading-text {
  text-align: center;
  padding: 20px;
  color: #6b7280;
  font-style: italic;
}
</style>

<div class="dashboard-main-body">
  <div class="d-flex align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Activity Kanban</h6>
    <div class="d-flex gap-2">
      <a href="activity.php" class="btn btn-secondary">List View</a>
      <a href="activity_kanban.php" class="btn btn-primary">Kanban View</a>
      <a href="activity_gantt.php" class="btn btn-secondary">Gantt Chart</a>
    </div>
  </div>

  <div class="kanban-board">
    <?php foreach($columns as $status => $cards): ?>
      <div class="kanban-column" data-status="<?= htmlspecialchars($status) ?>" draggable="false">
        <div class="kanban-header"><?= htmlspecialchars($status) ?></div>
        <div class="kanban-cards">
          <?php foreach($cards as $c): ?>
            <?php 
              $pri = strtolower($c['priority'] ?? 'normal');
              $accent = in_array($pri,['urgent','normal','low']) ? 'accent-'.$pri : 'accent-normal';
            ?>
            <div class="kanban-card <?= $accent ?>" draggable="true" data-id="<?= (int)$c['id'] ?>">
              <div class="kanban-title">
                <span><?= htmlspecialchars($c['no']) ?></span>
                <span class="badge type" title="Type"><?= htmlspecialchars($c['type']) ?></span>
                <span class="badge app" title="Application"><?= htmlspecialchars($c['application']) ?></span>
                <span class="badge pri" title="Priority"><?= htmlspecialchars($c['priority']) ?></span>
              </div>
              <div class="text-truncate mt-1" title="<?= htmlspecialchars($c['description']) ?>"><?= htmlspecialchars($c['description']) ?></div>
              <?php $as = $c['action_solution'] ?? ''; if($as !== ''): ?>
              <div class="action-solution text-truncate" title="<?= htmlspecialchars($as) ?>">Action / Solution: <?= htmlspecialchars($as) ?></div>
              <?php endif; ?>
              <div class="meta"><span><?= htmlspecialchars($c['user_position'] ?? '-') ?></span><span><?= htmlspecialchars($c['department']) ?></span><span><?= $c['information_date']?date('d M Y',strtotime($c['information_date'])):'-' ?></span></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include './partials/layouts/layoutBottom.php'; ?>

