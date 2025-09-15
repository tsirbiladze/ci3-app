<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Email $email
 */
class Tools extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('email');
    }

    // CLI:
    //   php index.php tools email_test you@example.com
    // or set SMTP_TEST_TO in .env
    public function email_test($to = null)
    {
        if (!$this->input->is_cli_request()) {
            show_404();
        }

        if (!$to) {
            // fallback to env or default
            $to = getenv('SMTP_TEST_TO') ?: 'inbox@mailtrap.io';
        }

        $from = getenv('SMTP_FROM') ?: 'noreply@example.com';
        $name = getenv('SMTP_FROM_NAME') ?: 'CI3 App';

        $this->email->from($from, $name);
        $this->email->to($to);
        $this->email->subject('CI3 Email Test');
        $this->email->message("This is a CI3 SMTP test at " . date('Y-m-d H:i:s'));

        $ok = $this->email->send();

        echo "SMTP_HOST=" . getenv('SMTP_HOST') . "\n";
        echo "SMTP_PORT=" . getenv('SMTP_PORT') . "\n";
        echo "SMTP_USER set=" . (getenv('SMTP_USER') ? 'yes' : 'no') . "\n";
        echo "SMTP_CRYPTO=" . getenv('SMTP_CRYPTO') . "\n";
        echo "TO=" . $to . "\n";
        echo "Result=" . ($ok ? 'OK' : 'FAIL') . "\n\n";
        echo $this->email->print_debugger(array('headers', 'subject', 'body')) . "\n";
    }
}


