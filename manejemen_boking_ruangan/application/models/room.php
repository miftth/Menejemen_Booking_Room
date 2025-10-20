<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class room extends CI_Model {

    private $table = 'tb_ruangan';

    public function get_all_rooms() {
        return $this->db->get($this->table)->result_array();
    }

    public function get_room_by_id($id) {
        return $this->db->get_where($this->table, ['id_ruangan' => $id])->row();
    }

    public function get_booking_by_code($input_code_booking) {
        $this->db->select('*');
        $query = $this->db->get('tb_booking');

        foreach ($query->result() as $booking) {
            if (password_verify($input_code_booking, $booking->code_booking)) {
                return $booking;
            }
        }
        return false; 
    }

    public function get_all_bookings() {
        $this->db->select('tb_booking.*, tb_ruangan.nama_ruangan');
        $this->db->from('tb_booking');
        $this->db->join('tb_ruangan', 'tb_booking.room_id = tb_ruangan.id_ruangan', 'left');
        $this->db->order_by('tb_booking.date DESC, tb_booking.start_time DESC');
        return $this->db->get()->result();
    }

    public function get_all_bookings_with_room() {
    $this->db->select('tb_booking.*, tb_ruangan.nama_ruangan, tb_ruangan.kapasitas, tb_ruangan.fasilitas');
    $this->db->from('tb_booking');
    $this->db->join('tb_ruangan', 'tb_booking.room_id = tb_ruangan.id_ruangan', 'left');
    $this->db->order_by('tb_booking.date DESC, tb_booking.start_time DESC');
    return $this->db->get()->result();
    }

    public function get_room_stats() {
        $query = $this->db->select('tb_ruangan.nama_ruangan, COUNT(tb_booking.id_booking) as total')
            ->from('tb_booking')
            ->join('tb_ruangan', 'tb_booking.room_id = tb_ruangan.id_ruangan', 'left')
            ->group_by('tb_ruangan.nama_ruangan')
            ->order_by('total', 'DESC')
            ->get();
        return $query->result();
    }

    public function get_monthly_stats() {
        $year = date('Y');
        $query = $this->db->select('MONTH(date) as month, COUNT(*) as total')
            ->from('tb_booking')
            ->where('YEAR(date)', $year)
            ->group_by('MONTH(date)')
            ->order_by('month', 'ASC')
            ->get();
        $result = $query->result();
        $stats = array_fill(1, 12, 0);
        foreach ($result as $row) {
            $stats[(int)$row->month] = (int)$row->total;
        }
        return $stats;
    }
}
