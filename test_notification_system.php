<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_once 'user_utils.php';

// Cek akses menggunakan utility function
require_login();
?>

<?php include './partials/layouts/layoutHorizontal.php'; ?>

<script src="assets/js/logo-notifications.js"></script>

<div class="dashboard-main-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Notification System Test</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Home
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">Notification Test</li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Test Activity Notifications</h5>
        </div>
        <div class="card-body">
            <p>Click the buttons below to test the notification system:</p>
            
            <div class="d-grid gap-3 d-md-flex justify-content-md-start">
                <button type="button" class="btn btn-success" onclick="testActivityCreated()">Test Activity Created</button>
                <button type="button" class="btn btn-info" onclick="testActivityUpdated()">Test Activity Updated</button>
                <button type="button" class="btn btn-warning" onclick="testActivityCanceled()">Test Activity Canceled</button>
                <button type="button" class="btn btn-danger" onclick="testActivityError()">Test Activity Error</button>
            </div>
        </div>
    </div>
</div>

<script>
// Test functions for activity notifications
function testActivityCreated() {
    if (window.logoNotificationManager) {
        window.logoNotificationManager.showActivityCreated('Activity berhasil dibuat!', 5000);
    } else {
        alert('Activity berhasil dibuat!');
    }
}

function testActivityUpdated() {
    if (window.logoNotificationManager) {
        window.logoNotificationManager.showActivityUpdated('Activity berhasil diperbarui!', 5000);
    } else {
        alert('Activity berhasil diperbarui!');
    }
}

function testActivityCanceled() {
    if (window.logoNotificationManager) {
        window.logoNotificationManager.showActivityCanceled('Activity berhasil dibatalkan!', 5000);
    } else {
        alert('Activity berhasil dibatalkan!');
    }
}

function testActivityError() {
    if (window.logoNotificationManager) {
        window.logoNotificationManager.showActivityError('Terjadi kesalahan pada activity!', 5000);
    } else {
        alert('Terjadi kesalahan pada activity!');
    }
}

// Auto-test after page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Notification System Test Ready!');
});
</script>

<?php include './partials/layouts/layoutBottom.php'; ?>