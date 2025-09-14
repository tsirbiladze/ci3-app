<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_Loader $load
 */
class MY_Controller extends CI_Controller
{
    protected function render(string $view, array $data = []): void
    {
        $data['auth'] = [
            'id'        => $this->session->userdata('user_id'),
            'name'      => $this->session->userdata('user_name'),
            'email'     => $this->session->userdata('user_email'),
            'logged_in' => (bool) $this->session->userdata('logged_in'),
        ];
        $this->load->view('_partials/head', $data);
        $this->load->view('_partials/nav', $data);
        $this->load->view($view, $data);
        $this->load->view('_partials/footer', $data);
    }

    protected function json($payload, int $status = 200)
    {
        return $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    protected function json_success($data = null, string $message = 'OK', int $status = 200)
    {
        $payload = array('success' => true, 'message' => $message);
        if ($data !== null) { $payload['data'] = $data; }
        return $this->json($payload, $status);
    }

    protected function json_error(string $message = 'Error', int $status = 400, $errors = null)
    {
        $payload = array('success' => false, 'message' => $message);
        if ($errors !== null) { $payload['errors'] = $errors; }
        return $this->json($payload, $status);
    }
}

/**
 * @property CI_Session $session
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_Loader $load
 */
class Authenticated_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            if ($this->input->is_ajax_request()) {
                $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
                exit;
            }
            redirect('login');
        }
    }
}
