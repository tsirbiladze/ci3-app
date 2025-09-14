<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Email Configuration (SMTP)
|--------------------------------------------------------------------------
| Reads SMTP settings from environment variables (.env).
| Defaults are set for Mailtrap sandbox host and port.
*/

$config['protocol']    = 'smtp';
$config['smtp_host']   = getenv('SMTP_HOST') ?: 'sandbox.smtp.mailtrap.io';
$config['smtp_port']   = (int) (getenv('SMTP_PORT') ?: 2525);
$config['smtp_user']   = getenv('SMTP_USER') ?: '';
$config['smtp_pass']   = getenv('SMTP_PASS') ?: '';
$config['smtp_crypto'] = getenv('SMTP_CRYPTO') ?: 'tls'; // '', 'ssl', or 'tls'

$config['mailtype']    = 'text';
$config['charset']     = 'utf-8';
$config['newline']     = "\r\n";
$config['crlf']        = "\r\n";
$config['useragent']   = 'CodeIgniter3';
$config['smtp_timeout']= 10;


