<?php 
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_once 'user_utils.php';

// Cek akses menggunakan utility function
require_login();

$script = '<script>
    // ================== Image Upload Js Start ===========================
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $("#imagePreview").css("background-image", "url(" + e.target.result + ")");
                $("#imagePreview").hide();
                $("#imagePreview").fadeIn(650);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    $("#imageUpload").change(function() {
        readURL(this);
    });
    // ================== Image Upload Js End ===========================
    </script>';

include './partials/layouts/layoutHorizontal.php' 
?>

        <div class="dashboard-main-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
                <h6 class="fw-semibold mb-0">Add User</h6>
                <ul class="d-flex align-items-center gap-2">
                    <li class="fw-medium">
                        <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
                            <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                            Dashboard
                        </a>
                    </li>
                    <li>-</li>
                    <li class="fw-medium">Add User</li>
                </ul>
            </div>

            <div class="card h-100 p-0 radius-12">
                <div class="card-body p-24">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0">Users</h6>
                        <button type="button" class="btn btn-primary-600 d-flex align-items-center gap-2" onclick="showCreateUserModal()">
                            <iconify-icon icon="solar:add-circle-outline" class="icon"></iconify-icon>
                            Add User
                        </button>
                    </div>
                    <div class="text-secondary-light">Klik tombol Add User untuk menambah user baru.</div>
                </div>
            </div>

            <!-- Create User Modal - Custom Modal -->
            <div class="custom-modal-overlay" id="createUserModal" style="display:none;">
                <div class="custom-modal">
                    <div class="custom-modal-header">
                        <h5 class="custom-modal-title">Add User</h5>
                        <button type="button" class="custom-modal-close" onclick="closeCreateUserModal()">&times;</button>
                    </div>
                    <form action="user_crud.php" method="post" id="createUserForm">
                        <div class="custom-modal-body">
                            <?= csrf_field() ?>
                            <div class="custom-modal-row">
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Display Name *</label>
                                    <input type="text" name="display_name" class="custom-modal-input" required>
                                </div>
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Full Name *</label>
                                    <input type="text" name="full_name" class="custom-modal-input" required>
                                </div>
                            </div>
                            <div class="custom-modal-row">
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Email *</label>
                                    <input type="email" name="email" class="custom-modal-input" required>
                                </div>
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Password *</label>
                                    <input type="password" name="password" class="custom-modal-input" required>
                                </div>
                            </div>
                            <div class="custom-modal-row">
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Tier *</label>
                                    <select name="tier" class="custom-modal-select" required>
                                        <option value="">Select Tier</option>
                                        <option value="New Born">New Born</option>
                                        <option value="Tier 1">Tier 1</option>
                                        <option value="Tier 2">Tier 2</option>
                                        <option value="Tier 3">Tier 3</option>
                                    </select>
                                </div>
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Role *</label>
                                    <select name="role" class="custom-modal-select" required>
                                        <option value="">Select Role</option>
                                        <option value="Administrator">Administrator</option>
                                        <option value="Management">Management</option>
                                        <option value="Admin Office">Admin Office</option>
                                        <option value="User">User</option>
                                        <option value="Client">Client</option>
                                    </select>
                                </div>
                            </div>
                            <div class="custom-modal-row">
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Start Work</label>
                                    <input type="date" name="start_work" class="custom-modal-input">
                                </div>
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Redirect After Save</label>
                                    <input type="text" name="redirect_to" class="custom-modal-input" value="users-list.php">
                                </div>
                            </div>
                        </div>
                        <div class="custom-modal-footer">
                            <button type="submit" name="create" class="custom-btn custom-btn-primary">Save</button>
                            <button type="button" class="custom-btn custom-btn-secondary" onclick="closeCreateUserModal()">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

<?php include './partials/layouts/layoutBottom.php' ?>
<script>
function showCreateUserModal(){ document.getElementById('createUserModal').style.display='flex'; }
function closeCreateUserModal(){ document.getElementById('createUserModal').style.display='none'; }
document.addEventListener('keydown',function(e){ if(e.key==='Escape'){ closeCreateUserModal(); }});
</script>
