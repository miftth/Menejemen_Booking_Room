<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class dashboard_display extends CI_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('model_display');
        $this->load->model('model_room');
    }
    
    public function index(){
        $id_ruangan = $this->input->get('id_ruangan');
        $cek = $this->model_display->get_ruangan_by_id($id_ruangan);
        if($cek->num_rows() > 0){
            $data_ruangan = $cek->row();
            $data['data_ruangan'] = $this->model_room->get_ruangan_by_id($id_ruangan);
            $data['data_booking_room_today'] = $this->model_display->get_booking_room_by_today($id_ruangan);
            $data['data_booking_room_nextday'] = $this->model_display->get_booking_room_by_nextday($id_ruangan);
            $this->load->view('dashboard_display/index',$data);
        } else {
            if(!$this->session->userdata('id_ruangan')){
                show_404();
            }
        }

        
    }

    

}