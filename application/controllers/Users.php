<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Form_validation $form_validation
 * @property CI_Session $session
 * @property CI_Email $email
 * @property User_model $User_model
 * @property User_service $user_service
 */
class Users extends Authenticated_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library('form_validation');
        $this->load->library('User_service');
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            // DataTables server-side parameters
            $draw = (int) ($this->input->get('draw') ?: 1);
            $start = (int) ($this->input->get('start') ?: 0);
            $length = (int) ($this->input->get('length') ?: 10);
            if ($length <= 0) {
                $length = 10;
            }
            $search = $this->input->get('search[value]');

            $orderColIndex = (int) ($this->input->get('order[0][column]') ?: 0);
            $orderDir = $this->input->get('order[0][dir]') === 'asc' ? 'asc' : 'desc';
            $columns = array('id', 'name', 'email', 'phone', 'created_at');
            $orderColumn = isset($columns[$orderColIndex]) ? $columns[$orderColIndex] : 'id';

            $data = $this->User_model->get_users_paginated($start, $length, $search, $orderColumn, $orderDir);
            $recordsTotal = $this->User_model->count_all_users();
            $recordsFiltered = $this->User_model->count_filtered_users($search);

            return $this->json(array(
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ));
        }
        $this->render('users/index', array('title' => 'Users'));
    }

    public function get($id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $user = $this->User_model->get_user_by_id($id);

        if ($user && array_key_exists('password', $user)) {
            unset($user['password']);
        }

        return $user ? $this->json(array('success' => true, 'data' => $user))
            : $this->json(array('success' => false, 'message' => 'User not found'), 404);
    }

    public function create()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[2]');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('phone', 'Phone', 'trim|required');

        if ($this->form_validation->run()) {
            $name  = $this->input->post('name', true);
            $email = $this->input->post('email', true);
            $phone = $this->input->post('phone', true);

            $result = $this->user_service->create_with_temp_password($name, $email, $phone);
            if ($result) {
                $this->user_service->notify_temp_password($email, $name, $result['temp_password']);
                return $this->json_success(null, 'User created');
            }

            return $this->json_error('Failed to create user', 500);
        }

        return $this->json_error(validation_errors(), 422);
    }

    public function edit($id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[2]');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('phone', 'Phone', 'trim|required');

        if ($this->form_validation->run()) {
            $email = $this->input->post('email', true);

            // Check if email exists for other users
            if ($this->User_model->email_exists($email, $id)) {
                echo json_encode(array('success' => false, 'message' => 'Email already exists'));
                return;
            }

            $update_data = array(
                'name' => $this->input->post('name', true),
                'email' => $email,
                'phone' => $this->input->post('phone', true)
            );

            if ($this->User_model->update_user($id, $update_data)) {
                return $this->json(array('success' => true, 'message' => 'User updated'));
            } else {
                return $this->json(array('success' => false, 'message' => 'Failed to update user'), 500);
            }
        } else {
            return $this->json(array('success' => false, 'message' => validation_errors()), 422);
        }
    }

    public function delete($id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        // Prevent users from deleting themselves
        if ($id == $this->session->userdata('user_id')) {
            return $this->json(array('success' => false, 'message' => 'Cannot delete your own account'), 422);
        }

        if ($this->User_model->delete_user($id)) {
            return $this->json(array('success' => true, 'message' => 'User deleted'));
        } else {
            return $this->json(array('success' => false, 'message' => 'Failed to delete user'), 500);
        }
    }

    private function send_temp_password_email($to_email, $name, $temp_password)
    {
        $this->email->from('noreply@yoursite.com', 'Your Site Name');
        $this->email->to($to_email);
        $this->email->subject('Your Temporary Password');

        $message = "Hello {$name},\n\n";
        $message .= "Your account has been created by an administrator.\n";
        $message .= "Your temporary password is: {$temp_password}\n\n";
        $message .= "Please login and change your password as soon as possible.\n\n";
        $message .= "Best regards,\nYour Site Team";

        $this->email->message($message);

        // For development, you might want to log this instead of actually sending
        if (ENVIRONMENT === 'development') {
            log_message('info', "Temp password email for {$to_email}: {$temp_password}");
        } else {
            $this->email->send();
        }
    }
}
