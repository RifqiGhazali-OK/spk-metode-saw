<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends CI_Model
{
    protected $table = 'users';

    public function count_all()
    {
        return $this->db->count_all($this->table);
    }

    public function get_all()
    {
        $this->db->order_by('id', 'ASC');
        return $this->db->get($this->table)->result_array();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    public function get_user_by_email($email)
    {
        return $this->db->get_where($this->table, ['email' => $email])->row();
    }

    /* Method untuk login: cari user berdasarkan email atau username,
     * lalu cocokkan password (MD5)
     * @param string $identity email atau username
     * @param string $password password plain text
     * @return object|<null></null*/

    public function login($identity, $password)
    {
        $this->db->where('email', $identity);
        $this->db->or_where('username', $identity);
        $query = $this->db->get($this->table);

        if ($query->num_rows() == 1) {
            $user = $query->row();
            if (md5($password) === $user->password) {
                return $user;
            }
        }
        return null;
    }

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    public function delete($id)
    {
        return $this->db->delete($this->table, ['id' => $id]);
    }
}