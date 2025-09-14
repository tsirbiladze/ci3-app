<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h1 class="h3 mb-4">Dashboard</h1>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="muted">Welcome</div>
                    <div class="h5 mb-0"><?= html_escape($user['name']); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 text-center" id="totalUsersCard">
            <div class="muted">Total users</div>
            <div class="display-6" id="totalUsers">–</div>
            <a class="btn btn-outline-primary btn-sm mt-2" href="<?= site_url('users'); ?>">View</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <div class="muted mb-2">Quick actions</div>
            <div class="d-grid gap-2">
                <a class="btn btn-primary" href="<?= site_url('users'); ?>">Add user</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('profile'); ?>">Edit profile</a>
            </div>
        </div>
    </div>
</div>
<script>
    (function() {
        var url = '<?= site_url('users/index'); ?>?draw=1&start=0&length=1';
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r){ return r.json(); })
        .then(function(res){
            var el = document.getElementById('totalUsers');
            if (!el) return;
            var count = 0;
            if (res && typeof res.recordsTotal !== 'undefined') {
                count = res.recordsTotal;
            } else if (res && res.success && Array.isArray(res.data)) {
                count = res.data.length;
            }
            el.textContent = count;
        })
        .catch(function(){});
    })();
</script>