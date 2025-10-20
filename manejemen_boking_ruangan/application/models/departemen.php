<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class departemen extends CI_Model {
    public function get_user_by_departement_name($input_departemen_name){
        $this->db->where('departement_name', $input_departemen_name);
        $query = $this->db->get('tb_departemen');

        if ($query->num_rows() > 0) {
            return $query->row(); 
        }

        return false;
    }
}
