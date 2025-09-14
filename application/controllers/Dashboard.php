<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Form_validation $form_validation
 * @property CI_Session $session
 * @property CI_Output $output
 * @property CI_Security $security
 * @property CI_Loader $load
 * @property User_model $User_model
 */
class Dashboard extends Authenticated_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data['title'] = 'Dashboard';
        $data['user'] = $this->User_model->get_user_by_id($this->session->userdata('user_id'));
        $this->render('dashboard/index', $data);
    }

    public function profile()
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[2]');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
            $this->form_validation->set_rules('phone', 'Phone', 'trim|required');

            if ($this->form_validation->run()) {
                $user_id = $this->session->userdata('user_id');
                $email = $this->input->post('email', true);

                // Check if email exists for other users
                if ($this->User_model->email_exists($email, $user_id)) {
                    $error = 'Email already exists';

                    if ($this->input->is_ajax_request()) {
                        echo json_encode(array('success' => false, 'message' => $error));
                        return;
                    } else {
                        $this->session->set_flashdata('error', $error);
                    }
                } else {
                    $update_data = array(
                        'name' => $this->input->post('name', true),
                        'email' => $email,
                        'phone' => $this->input->post('phone', true)
                    );

                    if ($this->User_model->update_user($user_id, $update_data)) {
                        // Update session data
                        $this->session->set_userdata('user_name', $update_data['name']);
                        $this->session->set_userdata('user_email', $update_data['email']);

                        $message = 'Profile updated successfully';

                        if ($this->input->is_ajax_request()) { return $this->json(array('success' => true, 'message' => $message)); }
                        else { $this->session->set_flashdata('success', $message); }
                    } else {
                        $error = 'Failed to update profile';

                        if ($this->input->is_ajax_request()) { return $this->json(array('success' => false, 'message' => $error), 500); }
                        else { $this->session->set_flashdata('error', $error); }
                    }
                }
            } else {
                if ($this->input->is_ajax_request()) { return $this->json(array('success' => false, 'message' => validation_errors()), 422); }
            }
        }

        $data['title'] = 'My Profile';
        $data['user'] = $this->User_model->get_user_by_id($this->session->userdata('user_id'));
        $this->render('dashboard/profile', $data);
    }

    public function change_password()
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('current_password', 'Current Password', 'required');
            $this->form_validation->set_rules('new_password', 'New Password', 'required|min_length[6]');
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[new_password]');

            if ($this->form_validation->run()) {
                $user_id = $this->session->userdata('user_id');
                $current_password = $this->input->post('current_password');
                $new_password = $this->input->post('new_password');

                if ($this->User_model->change_password($user_id, $current_password, $new_password)) {
                    $message = 'Password changed successfully';

                    if ($this->input->is_ajax_request()) { return $this->json(array('success' => true, 'message' => $message)); }
                    else { $this->session->set_flashdata('success', $message); redirect('profile'); }
                } else {
                    $error = 'Current password is incorrect';

                    if ($this->input->is_ajax_request()) { return $this->json(array('success' => false, 'message' => $error), 422); }
                    else { $this->session->set_flashdata('error', $error); }
                }
            } else {
                if ($this->input->is_ajax_request()) { return $this->json(array('success' => false, 'message' => validation_errors()), 422);
                }
            }
        }

        if ($this->input->is_ajax_request()) { return $this->json(array('success' => false, 'message' => 'Invalid request'), 400); }
        else { redirect('profile'); }
    }
}
