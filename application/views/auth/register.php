<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<section class="row justify-content-center">
  <div class="col-md-6">
    <h1 class="h3 mb-3">Register</h1>
    <form id="registerForm" method="post" action="<?= site_url('register'); ?>">
      <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" for="name">Full name</label>
          <input class="form-control" id="name" name="name" required />
        </div>
        <div class="col-md-6">
          <label class="form-label" for="phone">Phone</label>
          <input class="form-control" id="phone" name="phone" required />
        </div>
        <div class="col-12">
          <label class="form-label" for="email">Email</label>
          <input class="form-control" type="email" id="email" name="email" required />
        </div>
      </div>
      <p class="mt-3 muted">We will email you a temporary password.</p>
      <button class="btn btn-primary" type="submit">Create account</button>
    </form>
  </div>
</section>
<script>
(function waitForjQuery(){
  if(!window.jQuery){ return setTimeout(waitForjQuery, 30); }
  $(function(){
    $('#registerForm').on('submit', function(e){
      e.preventDefault();
      var $btn = $(this).find('button[type="submit"]').prop('disabled', true).text('Creating…');
      $.post('<?= site_url('register'); ?>', $(this).serialize())
        .done(function(res){ if(res && res.success){ toast(res.message,'success'); setTimeout(function(){ location.href='<?= site_url('login'); ?>'; }, 900); } else { toast((res && res.message)||'Failed','danger'); } })
        .fail(function(xhr){
          var msg='Network error';
          try{var r=JSON.parse(xhr.responseText||'{}'); if(r.message) msg=r.message;}catch(e){}
          toast(msg,'danger');
        })
        .always(function(){ $btn.prop('disabled', false).text('Create account'); });
    });
  });
})();
</script>