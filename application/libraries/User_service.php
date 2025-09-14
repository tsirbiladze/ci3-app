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

        return array('user_id' => $user_id, 'temp_password' => $temp_password);
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

        if (ENVIRONMENT === 'development') {
            log_message('info', "Temp password email for {$to_email}: {$temp_password}");
        } else {
            $this->email->send();
        }
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


