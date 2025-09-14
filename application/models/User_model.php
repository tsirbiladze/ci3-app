<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_DB_mysqli_driver $db
 */
class User_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all users
     */
    public function get_all_users()
    {
        return $this->db
            ->select('id, name, email, phone, created_at, updated_at')
            ->from('users')
            ->order_by('id', 'desc')
            ->get()
            ->result_array();
    }

    /**
     * Get users for DataTables server-side processing
     */
    public function get_users_paginated(int $start, int $length, ?string $search, string $orderColumn, string $orderDir)
    {
        $builder = $this->db
            ->select('id, name, email, phone, created_at, updated_at')
            ->from('users');

        if ($search) {
            $builder->group_start()
                    ->like('name', $search)
                    ->or_like('email', $search)
                    ->or_like('phone', $search)
                    ->group_end();
        }

        $builder->order_by($orderColumn, $orderDir)
                ->limit($length, $start);

        return $builder->get()->result_array();
    }

    public function count_all_users(): int
    {
        return (int) $this->db->count_all('users');
    }

    public function count_filtered_users(?string $search): int
    {
        if (!$search) {
            return $this->count_all_users();
        }
        $builder = $this->db->from('users')
            ->group_start()
            ->like('name', $search)
            ->or_like('email', $search)
            ->or_like('phone', $search)
            ->group_end();
        return (int) $builder->count_all_results();
    }

    /**
     * Get user by ID
     */
    public function get_user_by_id($id)
    {
        $query = $this->db->get_where('users', array('id' => $id));
        return $query->row_array();
    }

    /**
     * Get user by email
     */
    public function get_user_by_email($email)
    {
        $query = $this->db->get_where('users', array('email' => $email));
        return $query->row_array();
    }

    /**
     * Create new user
     */
    public function create_user($data)
    {
        if (!isset($data['password']) || empty($data['password'])) {
            $data['password'] = $this->generate_temp_password();
        }
        $now = date('Y-m-d H:i:s');
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        if (empty($data['created_at'])) $data['created_at'] = $now;
        $data['updated_at'] = $now;
        $result = $this->db->insert('users', $data);
        return $result ? $this->db->insert_id() : false;
    }

    /**
     * Update user
     */
    public function update_user($id, $data)
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }

    /**
     * Delete user
     */
    public function delete_user($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('users');
    }

    /**
     * Check if email exists
     */
    public function email_exists($email, $exclude_id = null)
    {
        $this->db->where('email', $email);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->from('users')->count_all_results() > 0;
    }

    /**
     * Verify login credentials
     */
    public function verify_login($email, $password)
    {
        $user = $this->get_user_by_email($email);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    /**
     * Change user password
     */
    public function change_password($user_id, $current_password, $new_password)
    {
        $user = $this->get_user_by_id($user_id);

        if (!$user || !password_verify($current_password, $user['password'])) {
            return false;
        }

        $data = array(
            'password' => password_hash($new_password, PASSWORD_DEFAULT)
        );

        $this->db->where('id', $user_id);
        return $this->db->update('users', $data);
    }

    /**
     * Generate temporary password
     */
    private function generate_temp_password($length = 12)
    {
        // Generate a cryptographically secure random password per user
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*_-';
        $alphabetLength = strlen($alphabet);
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, $alphabetLength - 1);
            $password .= $alphabet[$index];
        }
        return $password;
    }

    /**
     * Get temporary password for user creation
     */
    public function get_temp_password()
    {
        return $this->generate_temp_password();
    }
}
