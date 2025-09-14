<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h1 class="h3 mb-4">My profile</h1>
<div class="row g-4">
  <div class="col-lg-7">
    <div class="card p-3">
      <h2 class="h5">Account</h2>
      <form id="profileForm" method="post" action="<?= site_url('profile'); ?>">
        <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Full name</label>
            <input class="form-control" name="name" value="<?= html_escape($user['name']); ?>" required />
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" value="<?= html_escape($user['email']); ?>" required />
          </div>
          <div class="col-12">
            <label class="form-label">Phone</label>
            <input class="form-control" name="phone" value="<?= html_escape($user['phone']); ?>" required />
          </div>
        </div>
        <button class="btn btn-primary mt-3" type="submit">Save changes</button>
      </form>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card p-3">
      <h2 class="h5">Change password</h2>
      <form id="passwordForm" method="post" action="<?= site_url('dashboard/change_password'); ?>">
        <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
        <div class="mb-3"><label class="form-label">Current</label><input class="form-control" type="password" name="current_password" required /></div>
        <div class="mb-3"><label class="form-label">New</label><input class="form-control" type="password" name="new_password" required /></div>
        <div class="mb-3"><label class="form-label">Confirm</label><input class="form-control" type="password" name="confirm_password" required /></div>
        <button class="btn btn-outline-primary" type="submit">Update password</button>
      </form>
    </div>
  </div>
</div>
<script>
(function waitForjQuery(){
  if(!window.jQuery){ return setTimeout(waitForjQuery, 30); }
  $(function(){
    $('#profileForm').on('submit', function(e) {
      e.preventDefault();
      var $b = $(this).find('button[type="submit"]').prop('disabled', true).text('Saving…');
      $.post('<?= site_url('profile'); ?>', $(this).serialize())
        .done(function(r){
          toast(r && r.message ? r.message : 'Saved', r && r.success ? 'success' : 'danger');
        })
        .fail(function(xhr){
          var msg = 'Network error';
          try { var res = JSON.parse(xhr.responseText||'{}'); if(res.message) msg = res.message; } catch(e) {}
          toast(msg, 'danger');
        })
        .always(function(){ $b.prop('disabled', false).text('Save changes'); });
    });

    $('#passwordForm').on('submit', function(e) {
      e.preventDefault();
      var $b = $(this).find('button[type="submit"]').prop('disabled', true).text('Updating…');
      $.post('<?= site_url('dashboard/change_password'); ?>', $(this).serialize())
        .done(function(r){
          toast(r && r.message ? r.message : 'Updated', r && r.success ? 'success' : 'danger');
          if (r && r.success) $('#passwordForm')[0].reset();
        })
        .fail(function(xhr){
          var msg = 'Network error';
          try { var res = JSON.parse(xhr.responseText||'{}'); if(res.message) msg = res.message; } catch(e) {}
          toast(msg, 'danger');
        })
        .always(function(){ $b.prop('disabled', false).text('Update password'); });
    });
  });
})();
</script>