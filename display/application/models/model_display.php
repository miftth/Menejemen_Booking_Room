<?php
class model_display extends CI_Model {
    private $table = 'tb_booking';

    public function get_booking_room_by_today($id_ruangan) {
        $this->db->select('
            tb_booking.id_booking,
            tb_ruangan.id_ruangan,
            tb_booking.name,
            tb_booking.email,
            tb_ruangan.nama_ruangan,
            tb_booking.date,
            tb_booking.start_time,
            tb_booking.end_time
        ');
        $this->db->from('tb_booking');
        $this->db->join('tb_ruangan', 'tb_ruangan.id_ruangan = tb_booking.room_id');
        $this->db->where('tb_booking.status !=', 'finished');
        $this->db->where('tb_booking.room_id', $id_ruangan);
        $this->db->where('tb_booking.date', date('Y-m-d'));
        $query = $this->db->get();
        return $query->result();
    }
    public function get_booking_room_by_nextday($id_ruangan) {
        $this->db->select('
            tb_booking.id_booking,
            tb_ruangan.id_ruangan,
            tb_booking.name,
            tb_booking.email,
            tb_ruangan.nama_ruangan,
            tb_booking.date,
            tb_booking.start_time,
            tb_booking.end_time
        ');
        $this->db->from('tb_booking');
        $this->db->join('tb_ruangan', 'tb_ruangan.id_ruangan = tb_booking.room_id');
        $this->db->where('tb_booking.room_id', $id_ruangan);
        $this->db->where('tb_booking.date !=', date('Y-m-d'));
        $this->db->where('tb_booking.date >', date('Y-m-d'));
        $this->db->order_by('tb_booking.date', 'ASC');
        $this->db->order_by('tb_booking.start_time', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }
    public function get_ruangan_by_id($id_ruangan) {
        $this->db->where('id_ruangan', $id_ruangan);
        return $this->db->get('tb_ruangan');
    }

}
?>
