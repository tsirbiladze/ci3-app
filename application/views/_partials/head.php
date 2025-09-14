<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= isset($title) ? html_escape($title) . ' — ' : '' ?>User Admin</title>
  <meta name="csrf-token-name" content="<?= html_escape($this->security->get_csrf_token_name()); ?>">
  <meta name="csrf-token" content="<?= html_escape($this->security->get_csrf_hash()); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="<?= base_url('assets/app.css'); ?>" />
</head>

<body>