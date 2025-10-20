<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->model('room');
        $this->load->model('departemen');
        $this->load->library('phpmailer_lib');
        date_default_timezone_set('Asia/Jakarta');
    }

    public function index() {
        if ($this->session->userdata('id_departemen')) {
            redirect('dashboard/menage_bookings');
        } else {
            $this->load->view('template/header');
            $this->load->view('template/menu');
            $this->load->view('dashboard/auth_mybooking');
        }
    }
    public function authdepartement(){
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        } else {
            $this->load->view('template/header');
            $this->load->view('template/menu');
            $this->load->view('dashboard/auth_departemen');
        }
    }
    public function login() {
        $code_booking = $this->input->post('code_booking');
        $departement_name = $this->input->post('departement_name');
        $password     = $this->input->post('password');

        if ($code_booking) {
            $this->form_validation->set_rules('code_booking', 'Code Booking', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('template/header');
                $this->load->view('template/menu');
                $this->load->view('dashboard/auth_mybooking');
            } else {
                $booking = $this->room->get_booking_by_code($code_booking);
                if ($booking) {
                    $session_data = array(
                        'code_booking' => $booking->code_booking,
                        'name' => $booking->name,
                        'email' => $booking->email,
                        'phone' => $booking->phone,
                        'date' => $booking->date,
                        'start_time' => $booking->start_time,
                        'end_time' => $booking->end_time,
                        'nama_ruangan' => $booking->nama_ruangan,
                        'ip_address' => $booking->ip_address,
                        'logged_in' => TRUE
                    );
                    $this->session->set_userdata($session_data);
                    redirect('dashboard/booking_success/' . $booking->token);
                } else {
                    $this->session->set_flashdata('error', 'Invalid booking code. Please try again.');
                    redirect('auth');
                }
            }
        } elseif ($departement_name && $password) {
            $this->form_validation->set_rules('departement_name', 'Departement Name', 'required|trim');
            $this->form_validation->set_rules('password', 'Password', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('template/header');
                $this->load->view('template/menu');
                $this->load->view('dashboard/auth_departemen');
            } else {
                $user = $this->departemen->get_user_by_departement_name($departement_name); // Buat fungsi ini di model
                if ($user && password_verify($password, $user->password)) {
                    $session_data = array(
                        'id_departemen'      => $user->id_departemen,
                        'departement_name'  => $user->departement_name,
                        'role'           => $user->role,
                        'logged_in'      => TRUE
                    );
                    $this->session->set_userdata($session_data);
                    redirect('dashboard/menage_bookings');
                } else {
                    $this->session->set_flashdata('error', 'Invalid departement name or password.');
                    redirect('auth/authdepartement');
                }
            }
        } else {
            $this->load->view('template/header');
            $this->load->view('template/menu');
            $this->load->view('dashboard/auth_mybooking');
        }
    }
}