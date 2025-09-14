<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Form_validation $form_validation
 * @property CI_Session $session
 * @property CI_Email $email
 * @property CI_Output $output
 * @property CI_Security $security
 * @property CI_Loader $load
 * @property User_model $User_model
 * @property User_service $user_service
 */
class Auth extends MY_Controller
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
		if ($this->session->userdata('logged_in')) {
			redirect('dashboard');
		}
		redirect('login');
	}

	public function login()
	{
		if ($this->session->userdata('logged_in')) {
			redirect('dashboard');
		}

		if ($this->input->method() === 'post') {
			$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
			$this->form_validation->set_rules('password', 'Password', 'trim|required');

			if ($this->form_validation->run()) {
				$email = $this->input->post('email', true);
				$password = $this->input->post('password', true);

				$user = $this->User_model->verify_login($email, $password);

				if ($user) {
					$this->session->set_userdata(array(
						'user_id' => $user['id'],
						'user_name' => $user['name'],
						'user_email' => $user['email'],
						'logged_in' => TRUE
					));

					return $this->input->is_ajax_request()
						? $this->json(array('success' => true, 'redirect' => site_url('dashboard')))
						: redirect('dashboard');
				} else {
					$error = 'Invalid email or password.';
					return $this->input->is_ajax_request()
						? $this->json(array('success' => false, 'message' => $error), 422)
						: ($this->session->set_flashdata('error', $error) || true) && $this->render('auth/login', array('title' => 'Login'));
				}
			} else {
				return $this->input->is_ajax_request()
					? $this->json(array('success' => false, 'message' => validation_errors()), 422)
					: $this->render('auth/login', array('title' => 'Login'));
			}
		}

		$this->render('auth/login', array('title' => 'Login'));
	}

	public function register()
	{
		if ($this->session->userdata('logged_in')) {
			redirect('dashboard');
		}


		if ($this->input->method() === 'post') {
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
					$message = 'Registration successful. Check your email for a temporary password.';
					return $this->input->is_ajax_request()
						? $this->json(array('success' => true, 'message' => $message))
						: ($this->session->set_flashdata('success', $message) || true) && redirect('login');
				} else {
					$error = 'Registration failed. Please try again.';
					return $this->input->is_ajax_request()
						? $this->json(array('success' => false, 'message' => $error), 500)
						: ($this->session->set_flashdata('error', $error) || true) && $this->render('auth/register', array('title' => 'Register'));
				}
			} else {
				return $this->input->is_ajax_request()
					? $this->json(array('success' => false, 'message' => validation_errors()), 422)
					: $this->render('auth/register', array('title' => 'Register'));
			}
		}

		$this->render('auth/register', array('title' => 'Register'));
	}

	public function logout()
	{
		$this->session->sess_destroy();
		redirect('login');
	}

	private function send_temp_password_email($to_email, $name, $temp_password)
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
}
