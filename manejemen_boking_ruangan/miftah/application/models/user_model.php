// filepath: c:\xampp\htdocs\framwork\miftah\application\models\user_model.php
<?php
class user_model extends CI_Model {
    private $_table = "tb_user";

    public function getbyid($id) {
        return $this->db->get_where($this->_table, ['id' => $id])->result();
    }
    
    public function getall(){
        return $this->db->get($this->_table)->result_array();
    }

    public function getjabatan() {
        $this->_table = "tb_jabatan"; // Assuming the table for job titles is tb_jabatan
        return $this->db->get($this->_table)->result_array();
    }
}
