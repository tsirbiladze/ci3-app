<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<section class="row justify-content-center">
  <div class="col-md-5">
    <h1 class="h3 mb-3">Sign in</h1>
    <?php if ($this->session->flashdata('error')): ?>
      <div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div>
    <?php endif; ?>
    <form id="loginForm" method="post" action="<?= site_url('login'); ?>">
      <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
      <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input class="form-control" type="email" id="email" name="email" required />
      </div>
      <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <input class="form-control" type="password" id="password" name="password" required />
      </div>
      <button class="btn btn-primary w-100" type="submit">Sign in</button>
    </form>
    <div class="mt-3"><span class="muted">No account?</span> <a href="<?= site_url('register'); ?>">Register</a></div>
  </div>
</section>
<script>
(function waitForjQuery(){
  if(!window.jQuery){ return setTimeout(waitForjQuery, 30); }
  $(function(){
    $('#loginForm').on('submit', function(e){
      e.preventDefault();
      var $btn = $(this).find('button[type="submit"]').prop('disabled', true).text('Signing in…');
      $.post('<?= site_url('login'); ?>', $(this).serialize())
        .done(function(res){ if(res && res.success){ location.href = res.redirect; } else { toast((res && res.message)||'Login failed','danger'); } })
        .fail(function(xhr){
          var msg = 'Network error';
          try { var r = JSON.parse(xhr.responseText||'{}'); if(r.message) msg = r.message; } catch(e) {}
          toast(msg,'danger');
        })
        .always(function(){ $btn.prop('disabled', false).text('Sign in'); });
    });
  });
})();
</script>