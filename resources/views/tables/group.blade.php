@extends('layouts.app')

@section('content')
<div class="dashboard-main-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Hotel Groups</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium"><a href="{{ url('/') }}" class="d-flex align-items-center gap-1 hover-text-primary"><iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon> Dashboard</a></li>
            <li>-</li>
            <li class="fw-medium">Hotel Groups</li>
        </ul>
    </div>

    <div class="card h-100 radius-12">
        <div class="card-header">
            <button type="button" class="btn btn-sm btn-primary-600" onclick="showGroupModal()">Create Group</button>
        </div>
        <div class="card-body">
            @if(session('notification'))
                @php
                    $notification = session('notification');
                    $alert_class = $notification['type'] === 'error' ? 'danger' : 'success';
                @endphp
                <div class="alert alert-{{ $alert_class }}" role="alert">
                    {{ $notification['message'] }}
                </div>
            @endif
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Group Name</th>
                            <th>Customer Count</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groups ?? [] as $group)
                        <tr class="group-row" 
                            data-group='{{ json_encode($group) }}'
                            style="cursor: pointer;">
                            <td>{{ $group['name'] }}</td>
                            <td>{{ $group['customer_count'] }}</td>
                            <td>{{ date('d M Y, H:i', strtotime($group['created_at'])) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Universal Group Modal -->
<div class="custom-modal-overlay" id="groupModal">
    <div class="custom-modal">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title" id="groupModalTitle"></h5>
            <button type="button" class="custom-modal-close" onclick="hideGroupModal()">&times;</button>
        </div>
        <form id="groupForm" method="post" action="{{ url('/group.php') }}">
            @csrf
            <div class="custom-modal-body">
                <input type="hidden" name="id" id="group_id">
                <label class="custom-modal-label">Group Name *</label>
                <input type="text" name="name" id="group_name" class="custom-modal-input" required>
            </div>
            <div class="custom-modal-footer">
                <button type="submit" id="saveButton" name="save" class="custom-btn custom-btn-primary"></button>
                <button type="button" class="custom-btn custom-btn-secondary" onclick="hideGroupModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.custom-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1050;
}
.custom-modal-overlay.show {
    display: flex;
}
.custom-modal {
    width: min(500px, 96vw);
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
}
.custom-modal-header {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #e5e7eb;
}
.custom-modal-title { margin: 0; font-size: 18px; font-weight: 600; }
.custom-modal-close { background: transparent; border: 0; font-size: 22px; cursor: pointer; line-height: 1; }
.custom-modal-body { padding: 20px; }
.custom-modal-footer {
    padding: 14px 20px;
    background: #f8fafc;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
.custom-modal-label { font-weight: 600; font-size: 12px; margin-bottom: 6px; display: block; }
.custom-modal-input { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #e5e7eb; }
.custom-btn { padding: 10px 14px; border-radius: 8px; border: 0; cursor: pointer; font-weight: 600; }
.custom-btn-primary { background: #2563eb; color: #fff; }
.custom-btn-secondary { background: #6b7280; color: #fff; }
</style>

<script>
function showGroupModal(group = null) {
    const modal = document.getElementById('groupModal');
    const title = document.getElementById('groupModalTitle');
    const form = document.getElementById('groupForm');
    const idInput = document.getElementById('group_id');
    const nameInput = document.getElementById('group_name');
    const saveButton = document.getElementById('saveButton');

    if (group) {
        // Edit mode
        title.textContent = 'Edit Group';
        idInput.value = group.id;
        nameInput.value = group.name;
        saveButton.textContent = 'Update';
        saveButton.name = 'update';
        form.method = 'post';
        form.action = '{{ url("/group.php") }}';
        // Add method override for PUT
        let methodInput = form.querySelector('input[name="_method"]');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            form.appendChild(methodInput);
        }
        methodInput.value = 'PUT';
    } else {
        // Create mode
        title.textContent = 'Create Group';
        form.reset();
        idInput.value = '';
        saveButton.textContent = 'Create';
        saveButton.name = 'create';
        form.method = 'post';
        form.action = '{{ url("/group.php") }}';
        // Remove method override for POST
        let methodInput = form.querySelector('input[name="_method"]');
        if (methodInput) {
            methodInput.remove();
        }
    }
    modal.classList.add('show');
}

function hideGroupModal() {
    document.getElementById('groupModal').classList.remove('show');
}

document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.group-row');
    rows.forEach(row => {
        row.addEventListener('click', function() {
            const groupData = JSON.parse(this.dataset.group);
            showGroupModal(groupData);
        });
    });

    // Also hide modal on overlay click or escape key
    document.getElementById('groupModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideGroupModal();
        }
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideGroupModal();
        }
    });
});
</script>
@endsection
