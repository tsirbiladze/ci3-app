<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property User_model $User_model
 * @property CI_Email $email
 */
class User_service
{
    /** @var CI_Controller $CI */
    protected $CI;
    /** @var User_model $user_model */
    protected $user_model;
    /** @var CI_Email $email */
    protected $email;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('User_model');
        $this->user_model = $this->CI->{'User_model'};
        $this->CI->load->library('email');
        $this->email = $this->CI->{'email'};
    }

    /**
     * Create user with generated temporary password and return [user_id, temp_password]
     * @return array|false
     */
    public function create_with_temp_password(string $name, string $email, string $phone)
    {
        $temp_password = $this->user_model->get_temp_password();
        $user_id = $this->user_model->create_user(array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $temp_password,
        ));

        if (!$user_id) {
            return false;
        }

        // Write a dedicated log entry for new user creation only (includes temp password)
        $this->write_user_creation_log($user_id, $name, $email, $phone, $temp_password);

        return array('user_id' => $user_id, 'temp_password' => $temp_password);
    }

    /**
     * Write a dedicated log file entry for user creation events only.
     */
    protected function write_user_creation_log(int $user_id, string $name, string $email, string $phone, string $temp_password): void
    {
        $log_dir = APPPATH . 'logs/user_creation/';
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0755, true);
        }

        $log_file = $log_dir . 'user_creation-' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $line = sprintf("%s | id=%d | name=%s | email=%s | phone=%s | temp_password=%s\n", $timestamp, $user_id, $name, $email, $phone, $temp_password);

        // Suppress warnings if directory permissions are restricted
        @file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Notify user with temp password. In development logs; otherwise sends email.
     */
    public function notify_temp_password(string $to_email, string $name, string $temp_password): void
    {
        $this->email->from('noreply@yoursite.com', 'Your Site Name');
        $this->email->to($to_email);
        $this->email->subject('Your Temporary Password');

        $message = "Hello {$name},\n\n";
        $message .= "Your account has been created successfully.\n";
        $message .= "Your temporary password is: {$temp_password}\n\n";
        $message .= "Please login and change your password as soon as possible.\n\n";
        $message .= "Best regards,\nYour Site Team";

        $this->email->message($message);

        $this->email->send();
    }

    public function update_user(int $id, array $data): bool
    {
        return (bool) $this->user_model->update_user($id, $data);
    }

    public function delete_user(int $id): bool
    {
        return (bool) $this->user_model->delete_user($id);
    }

    public function change_password(int $user_id, string $current_password, string $new_password): bool
    {
        return (bool) $this->user_model->change_password($user_id, $current_password, $new_password);
    }
}


