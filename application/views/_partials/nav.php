<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<nav class="navbar navbar-expand-md border-bottom bg-white">
  <div class="container-fluid">
    <a class="navbar-brand fw-semibold" href="<?= site_url('dashboard'); ?>">User Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav" aria-controls="topnav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="topnav">
      <ul class="navbar-nav me-auto mb-2 mb-md-0">
        <?php if (!empty($auth['logged_in'])): ?>
          <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard'); ?>">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= site_url('users'); ?>">Users</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= site_url('profile'); ?>">Profile</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if (!empty($auth['logged_in'])): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
              <?= html_escape($auth['name'] ?: $auth['email']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= site_url('profile'); ?>">My profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= site_url('logout'); ?>">Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= site_url('login'); ?>">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= site_url('register'); ?>">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<main class="container py-4">

