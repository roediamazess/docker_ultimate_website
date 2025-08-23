document.addEventListener('DOMContentLoaded', function() {
  console.log('DOM Loaded. Initializing Kanban events.');
  initDragAndDrop();
  initCardEvents();
  initModalEventListeners();
});

function initModalEventListeners() {
  console.log('Initializing modal event listeners.');
  document.addEventListener('click', function(e) {
    const editModal = document.getElementById('editActivityModal');
    if (editModal && e.target === editModal) {
      console.log('Closing modal via overlay click.');
      closeEditModal();
    }
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      console.log('Closing modal via Escape key.');
      closeEditModal();
    }
  });
}

function initDragAndDrop() {
  console.log('Initializing drag and drop.');
  document.querySelectorAll('.kanban-card').forEach(function(card) {
    card.addEventListener('dragstart', function(e) {
      e.dataTransfer.setData('text/plain', this.dataset.id);
    });
  });

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
      updateActivityStatus(cardId, newStatus, this);
    });
  });
}

function initCardEvents() {
  console.log('Initializing card click events.');
  const kanbanBoard = document.querySelector('.kanban-board');
  if (!kanbanBoard) {
    console.error('Kanban board element not found!');
    return;
  }

  let mouseDownPos = { x: 0, y: 0 };
  let isDragging = false;

  kanbanBoard.addEventListener('mousedown', function(e) {
    const card = e.target.closest('.kanban-card');
    if (card) {
      mouseDownPos = { x: e.clientX, y: e.clientY };
      isDragging = false;
    }
  });

  kanbanBoard.addEventListener('mousemove', function(e) {
    const card = e.target.closest('.kanban-card');
    if (card && !isDragging) {
        const deltaX = Math.abs(e.clientX - mouseDownPos.x);
        const deltaY = Math.abs(e.clientY - mouseDownPos.y);
        if (deltaX > 5 || deltaY > 5) {
            isDragging = true;
        }
    }
  });

  kanbanBoard.addEventListener('mouseup', function(e) {
    const card = e.target.closest('.kanban-card');
    if (card && !isDragging) {
      e.preventDefault();
      e.stopPropagation();
      const cardId = card.dataset.id;
      console.log(`Card with ID ${cardId} clicked.`);
      if (cardId) {
        openEditModal(cardId);
      }
    }
    isDragging = false;
  });
}

function openEditModal(activityId) {
  console.log(`Opening edit modal for activity ID: ${activityId}`);
  fetch(`api_activity.php?id=${activityId}`)
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      if (data.error) {
        alert(data.error);
        return;
      }

      let modalEl = document.getElementById('editActivityModal');
      if (!modalEl) {
        modalEl = document.createElement('div');
        modalEl.id = 'editActivityModal';
        modalEl.className = 'custom-modal-overlay';
        document.body.appendChild(modalEl);
      }

      modalEl.innerHTML = `
        <div class="custom-modal">
          <div class="custom-modal-header">
            <h5 class="custom-modal-title">Edit Activity</h5>
            <button type="button" class="custom-modal-close" onclick="closeEditModal()">&times;</button>
          </div>
          <form method="post" action="activity.php" id="editActivityForm">
            <div class="custom-modal-body">
              <input type="hidden" name="csrf_token" value="${window.csrfToken}">
              <input type="hidden" name="id" id="edit_id">
              <div class="custom-modal-row">
                <div class="custom-modal-col">
                  <label class="custom-modal-label">No</label>
                  <input type="number" name="no" id="edit_no" class="custom-modal-input">
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
                  <input type="text" name="project" id="edit_project_name" class="custom-modal-input">
                </div>
              </div>
              <div class="custom-modal-row">
                <div class="custom-modal-col">
                  <label class="custom-modal-label">Due Date</label>
                  <input type="date" name="due_date" id="edit_due_date" class="custom-modal-input">
                </div>
                <div class="custom-modal-col">
                  <label class="custom-modal-label">CNC Number</label>
                  <input type="text" name="cnc_number" id="edit_cnc_number" class="custom-modal-input">
                </div>
              </div>
              <div class="custom-modal-row">
                <div class="custom-modal-col">
                  <label class="custom-modal-label">Description</label>
                  <textarea name="description" id="edit_description" class="custom-modal-textarea" rows="3"></textarea>
                </div>
                <div class="custom-modal-col">
                  <label class="custom-modal-label">Action Solution</label>
                  <textarea name="action_solution" id="edit_action_solution" class="custom-modal-textarea" rows="3"></textarea>
                </div>
              </div>
            </div>
            <div class="custom-modal-footer">
              <button type="submit" name="update" value="1" class="custom-btn custom-btn-primary">Update</button>
              <button type="button" class="custom-btn custom-btn-secondary" onclick="closeEditModal()">Close</button>
            </div>
          </form>
        </div>
      `;

      // Populate the form
      document.getElementById('edit_id').value = data.id;
      document.getElementById('edit_no').value = data.no;
      document.getElementById('edit_status').value = data.status;
      document.getElementById('edit_information_date').value = data.information_date ? data.information_date.substring(0, 10) : '';
      document.getElementById('edit_priority').value = data.priority;
      document.getElementById('edit_user_position').value = data.user_position;
      document.getElementById('edit_department').value = data.department;
      document.getElementById('edit_application').value = data.application;
      document.getElementById('edit_type').value = data.type;
      document.getElementById('edit_customer').value = data.customer;
      document.getElementById('edit_project_name').value = data.project;
      document.getElementById('edit_due_date').value = data.due_date ? data.due_date.substring(0, 10) : '';
      document.getElementById('edit_cnc_number').value = data.cnc_number;
      document.getElementById('edit_description').value = data.description;
      document.getElementById('edit_action_solution').value = data.action_solution;

      // Show the modal
      modalEl.style.display = 'block';
      modalEl.style.visibility = 'visible';
      modalEl.style.opacity = '1';
    })
    .catch(error => {
      console.error('Error fetching activity details:', error);
      alert('An error occurred while fetching activity details. Please check the console for more information.');
    });
}

function closeEditModal() {
  const modal = document.getElementById('editActivityModal');
  if (modal) {
    modal.style.display = 'none';
    modal.style.visibility = 'hidden';
    modal.style.opacity = '0';
  }
}

function updateActivityStatus(cardId, newStatus, targetColumn) {
  const cardElement = document.querySelector(`[data-id="${cardId}"]`);
  if (cardElement) {
    targetColumn.querySelector('.kanban-cards').prepend(cardElement);
    if(window.logoNotificationManager) window.logoNotificationManager.showInfo(`Status updated to ${newStatus}`);
  }
  const xhr = new XMLHttpRequest();
  xhr.open('POST', 'update_activity_status.php', true);
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.send(JSON.stringify({id: cardId, status: newStatus}));
}