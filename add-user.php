<?php 
session_start();
require_once 'db.php';
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

include './partials/layouts/layoutTop.php' 
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
                    <div class="row justify-content-center">
                        <div class="col-xxl-6 col-xl-8 col-lg-10">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="text-md text-primary-light mb-16">Profile Image</h6>

                                    <!-- Upload Image Start -->
                                    <div class="mb-24 mt-16">
                                        <div class="avatar-upload">
                                            <div class="avatar-edit position-absolute bottom-0 end-0 me-24 mt-16 z-1 cursor-pointer">
                                                <input type='file' id="imageUpload" accept=".png, .jpg, .jpeg" hidden>
                                                <label for="imageUpload" class="w-32-px h-32-px d-flex justify-content-center align-items-center bg-primary-50 text-primary-600 border border-primary-600 bg-hover-primary-100 text-lg rounded-circle">
                                                    <iconify-icon icon="solar:camera-outline" class="icon"></iconify-icon>
                                                </label>
                                            </div>
                                            <div class="avatar-preview">
                                                <div id="imagePreview"> </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Upload Image End -->

                                    <form action="user_crud.php" method="post">
                                        <div class="mb-20">
                                            <label for="display_name" class="form-label fw-semibold text-primary-light text-sm mb-8">Display Name <span class="text-danger-600">*</span></label>
                                            <input type="text" class="form-control radius-8" id="display_name" name="display_name" placeholder="Enter Display Name" required>
                                        </div>
                                        <div class="mb-20">
                                            <label for="full_name" class="form-label fw-semibold text-primary-light text-sm mb-8">Full Name <span class="text-danger-600">*</span></label>
                                            <input type="text" class="form-control radius-8" id="full_name" name="full_name" placeholder="Enter Full Name" required>
                                        </div>
                                        <div class="mb-20">
                                            <label for="email" class="form-label fw-semibold text-primary-light text-sm mb-8">Email <span class="text-danger-600">*</span></label>
                                            <input type="email" class="form-control radius-8" id="email" name="email" placeholder="Enter email address" required>
                                        </div>
                                        <div class="mb-20">
                                            <label for="password" class="form-label fw-semibold text-primary-light text-sm mb-8">Password <span class="text-danger-600">*</span></label>
                                            <input type="password" class="form-control radius-8" id="password" name="password" placeholder="Enter password" required>
                                        </div>
                                        <div class="mb-20">
                                            <label for="tier" class="form-label fw-semibold text-primary-light text-sm mb-8">Tier <span class="text-danger-600">*</span> </label>
                                            <select class="form-control radius-8 form-select" id="tier" name="tier" required>
                                                <option value="">Select Tier</option>
                                                <option value="New Born">New Born</option>
                                                <option value="Tier 1">Tier 1</option>
                                                <option value="Tier 2">Tier 2</option>
                                                <option value="Tier 3">Tier 3</option>
                                            </select>
                                        </div>
                                        <div class="mb-20">
                                            <label for="role" class="form-label fw-semibold text-primary-light text-sm mb-8">Role <span class="text-danger-600">*</span> </label>
                                            <select class="form-control radius-8 form-select" id="role" name="role" required>
                                                <option value="">Select Role</option>
                                                <option value="Administrator">Administrator</option>
                                                <option value="Management">Management</option>
                                                <option value="Admin Office">Admin Office</option>
                                                <option value="User">User</option>
                                                <option value="Client">Client</option>
                                            </select>
                                        </div>
                                        <div class="mb-20">
                                            <label for="start_work" class="form-label fw-semibold text-primary-light text-sm mb-8">Start Work Date</label>
                                            <input type="date" class="form-control radius-8" id="start_work" name="start_work">
                                        </div>
                                        <div class="d-flex align-items-center justify-content-center gap-3">
                                            <a href="users-list.php" class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-56 py-11 radius-8">
                                                Cancel
                                            </a>
                                            <button type="submit" name="create" class="btn btn-primary border border-primary-600 text-md px-56 py-12 radius-8">
                                                Save
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<?php include './partials/layouts/layoutBottom.php' ?>
