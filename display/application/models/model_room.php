<?php
class model_room extends CI_Model {
    public function get_ruangan_by_id($id_ruangan) {
        $this->db->where('id_ruangan', $id_ruangan);
        $this->db->from('tb_ruangan');
        $query = $this->db->get();
        return $query->result_array();
    }
}