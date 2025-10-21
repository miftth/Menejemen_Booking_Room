<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->model('room');
        $this->load->library('phpmailer_lib');
        date_default_timezone_set('Asia/Jakarta');

    }

    public function index() {
        $data['rooms'] = $this->room->get_all_rooms();
        $this->load->view('template/header', $data);
        $this->load->view('template/menu', $data);
        $this->load->view('dashboard/index', $data);
    }
    public function back(){
        $this->session->sess_destroy();
        redirect('dashboard');
    }
    public function cek_ketersediaan_ruangan() {
        $date = $this->input->post('date');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        $ignore_booking = $this->input->post('ignore_booking');

        $rooms = $this->room->get_all_rooms();
        $result_rooms = [];
        $start_time_obj = new DateTime($start_time);
        $start_time_new = $start_time_obj->modify('+1 minute')->format('H:i');

        foreach ($rooms as $room) {
            $this->db->where('room_id', $room['id_ruangan'])
                ->where('date', $date);

            if ($ignore_booking) {
                $this->db->where('id_booking !=', $ignore_booking);
            }

            $this->db->group_start()
                ->where("('$start_time_new' BETWEEN start_time AND end_time)")
                ->or_where("('$end_time' BETWEEN start_time AND end_time)")
                ->or_where("(start_time BETWEEN '$start_time_new' AND '$end_time')")
                ->or_where("(end_time BETWEEN '$start_time_new' AND '$end_time')")
            ->group_end();

            $conflict = $this->db->get('tb_booking')->num_rows();

            $booked_times = [];
            if ($conflict > 0) {
                // Get booked times for this room on this date
                $this->db->select('start_time, end_time, name');
                $this->db->where('room_id', $room['id_ruangan']);
                $this->db->where('date', $date);
                if ($ignore_booking) {
                    $this->db->where('id_booking !=', $ignore_booking);
                }
                $bookings = $this->db->get('tb_booking')->result_array();
                // Sort bookings by start_time ascending (nearest time first)
                usort($bookings, function($a, $b) {
                    return strtotime($a['start_time']) <=> strtotime($b['start_time']);
                });
                foreach ($bookings as $booking) {
                    $booked_times[] = $booking['start_time'] . ' - ' . $booking['end_time'] . ' (' . $booking['name'] . ')';
                }
            }

            $room['available'] = ($conflict == 0);
            $room['booked_times'] = $booked_times;
            $result_rooms[] = $room;
        }

        echo json_encode($result_rooms);
    }
    public function proses_booking() {
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('date', 'Date', 'required|regex_match[/^\d{4}-\d{2}-\d{2}$/]');
        $this->form_validation->set_rules('start_time', 'Start Time', 'required');
        $this->form_validation->set_rules('end_time', 'End Time', 'required');
        $this->form_validation->set_rules('selected_room', 'Room', 'required|integer');

        if ($this->form_validation->run() == FALSE) {
            show_error('Invalid input data', 400);
        }

        $id_ruangan = (int) $this->input->post('selected_room');
        $code_booking = strtoupper(bin2hex(random_bytes(4)));
        $data_ruangan = $this->db->get_where('tb_ruangan', ['id_ruangan' => $id_ruangan])->row_array();

        if (!$data_ruangan) {
            show_error('Room not found', 404);
        }
        $start_time = $this->input->post('start_time');
        try {
            $start_time_obj = new DateTime($start_time);
            $start_time_obj->modify('+1 minute');
            $start_time_new = $start_time_obj->format('H:i');
        } catch (Exception $e) {
            show_error('Invalid time format', 400);
        }
        $cek_bentrok = $this->db->where('room_id', $id_ruangan)
            ->where('date', $this->input->post('date'))
            ->group_start()
                ->where("('".$start_time_new."' BETWEEN start_time AND end_time)")
                ->or_where("('".$this->input->post('end_time')."' BETWEEN start_time AND end_time)")
                ->or_where("(start_time BETWEEN '".$start_time_new."' AND '".$this->input->post('end_time')."')")
                ->or_where("(end_time BETWEEN '".$start_time_new."' AND '".$this->input->post('end_time')."')")
            ->group_end()
            ->get('tb_booking')->num_rows();
        if ($cek_bentrok > 0) {
            $this->session->set_flashdata('error', 'Waktu booking bentrok dengan jadwal lain. Silakan pilih waktu lain.');
            redirect('dashboard');
        }

        $token = bin2hex(random_bytes(16)); 

        $data = [
            'code_booking' => password_hash($code_booking, PASSWORD_BCRYPT),
            'name' => $this->input->post('name', TRUE),
            'date' => $this->input->post('date', TRUE),
            'email' => $this->input->post('email', TRUE),
            'start_time' => $this->input->post('start_time', TRUE),
            'end_time' => $this->input->post('end_time', TRUE),
            'ip_address' => $data_ruangan['ip_address'],
            'room_id' => $id_ruangan,
            'token' => $token,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('tb_booking', $data);

        $title = "Booking Room " . $data_ruangan['nama_ruangan'] . " - " . $data['name'];
        $details = "Permintaan reservasi ruangan melalui sistem web Booking Room.";
        $location = "PT. Toppan Plasindo Lestari - Cibitung Division, Jl. Raya Teuku Umar No.KM.44, Telaga Asih, Kec. Cikarang Bar., Kabupaten Bekasi, Jawa Barat 17520, Indonesia";
        $start_outlook = date("Y-m-d\TH:i:s", strtotime($data['date'] . ' ' . $data['start_time']));
        $end_outlook   = date("Y-m-d\TH:i:s", strtotime($data['date'] . ' ' . $data['end_time']));

        $outlook_calendar_url = "https://outlook.live.com/calendar/0/deeplink/compose?path=/calendar/action/compose"
            . "&subject=" . rawurlencode($title)
            . "&body=" . rawurlencode($details)
            . "&startdt=" . rawurlencode($start_outlook)
            . "&enddt=" . rawurlencode($end_outlook)
            . "&location=" . rawurlencode($location);

        $mail = $this->phpmailer_lib->load();
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'defaultbooking09@gmail.com';
        $mail->Password   = 'hlxqskqdmhomunjz';  
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->setFrom('defaultbooking09@gmail.com', 'Menejemen Booking Room');
        $mail->addAddress($data['email'], $data['name']);
        $mail->addEmbeddedImage(FCPATH . 'assets/image/toppan.png', 'toppanimg');
        $mail->isHTML(true);
        $mail->Subject = '[Konfirmasi Booking] Booking ' . htmlspecialchars($data_ruangan['nama_ruangan']) . ' pada ' . strftime('%d %B %Y', strtotime(htmlspecialchars($data['date'])));
        $mail->Body = "
                <div style='font-family: Arial, sans-serif;color: #333;padding: 20px;max-width: 700px;margin: auto;border: 1px solid #eee;border-radius: 8px;'>
                    <div style='display: flex;justify-content: space-between;align-items: center;margin-bottom: 20px;'>
                        <table style='width: 100%;'>
                            <tr>
                                <td style='width: 40%;'>
                                    <img src='cid:toppanimg' alt='Logo' style='height: 40px;'>
                                </td>
                                <td>
                                    <p style='text-align: right; font-weight: bold;'>". htmlspecialchars($data['name']) ."</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <h2 style='color: #2E86C1; text-align: center;'>Booking Confirmation</h2>
                    <p style='text-align: center;'>Your booking has been successfully made. Here are the reservation details:</p>
                    <table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>Booking Code</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>". $code_booking ."</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>Name</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>". htmlspecialchars($data['name']) ."</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>Room</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>". htmlspecialchars($data_ruangan['nama_ruangan']) ."</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>Date</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>". strftime('%d %B %Y', strtotime(htmlspecialchars($data['date']))) ."</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>Time</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>". htmlspecialchars($data['start_time']) ." - ". htmlspecialchars($data['end_time']) ."</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>Capacity</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>". htmlspecialchars($data_ruangan['kapasitas']) ." people</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>IP Address</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>". htmlspecialchars($data['ip_address']) ."</td>
                        </tr>
                    </table>

                    <div style='text-align: center; margin-top: 25px;'>
                        <a href='". base_url('dashboard/booking_success/' . $token) ."' style='display: inline-block; padding: 12px 25px; background-color: #2E86C1; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold;'>View Booking Details</a>
                    </div>

                    <div style='text-align: center; margin-top: 15px;'>
                        <a href='". $outlook_calendar_url ."' target='_blank' style='display: inline-block; padding: 12px 25px; background-color: #00c63bff; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold;'>Add to Outlook</a>
                    </div>

                    <p style='margin-top: 30px; font-size: 0.9em; color: #666;'>Please keep this information safe. This email was sent automatically, please do not reply.</p>
                </div>
        ";
        $mail->send();

        redirect('dashboard/booking_success/' . $token);
    }
    public function cancel_booking($id_booking) {
        $booking = $this->db->get_where('tb_booking', ['id_booking' => $id_booking])->row_array();
        if (!$booking) {
            echo 'Booking not found';
        }
        $mail = $this->phpmailer_lib->load();
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'defaultbooking09@gmail.com';
        $mail->Password   = 'hlxqskqdmhomunjz';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->setFrom('defaultbooking09@gmail.com', 'Menejemen Booking Room');
        $mail->addAddress($booking['email'], $booking['name']);
        $mail->isHTML(true);
        $mail->Subject = '[Pembatalan Booking] Ruangan pada ' . strftime('%d %B %Y', strtotime(htmlspecialchars($booking['date'])));
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; color: #333; padding: 20px; max-width: 700px; margin: auto; border: 1px solid #eee; border-radius: 8px;'>
                <div style='display: flex;justify-content: space-between;align-items: center;margin-bottom: 20px;'>
                    <table style='width: 100%;'>
                        <tr>
                            <td style='width: 40%;'>
                                <img src='cid:toppanimg' alt='Logo' style='height: 40px;'>
                            </td>
                            <td>
                                <p style='text-align: right; font-weight: bold;'>". htmlspecialchars($booking['name']) ."</p>
                            </td>
                        </tr>
                    </table>
                </div>
                <h2 style='color: #E74C3C; text-align: center;'> Room Booking Cancellation</h2>
                <p style='text-align: center;'>Your booking has been successfully cancelled. Here are the cancellation details:</p>
                <table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>Name</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>". htmlspecialchars($booking['name']) ."</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>Date</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>". strftime('%d %B %Y', strtotime(htmlspecialchars($booking['date']))) ."</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>Time</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>". htmlspecialchars($booking['start_time']) ." - ". htmlspecialchars($booking['end_time']) ."</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>IP Address</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>". htmlspecialchars($booking['ip_address']) ."</td>
                    </tr>
                </table>
                <p style='margin-top: 30px; font-size: 0.9em; color: #666;'>If you did not make this cancellation, please contact the admin immediately. This email was sent automatically, please do not reply.</p>
            </div>
        ";
        $mail->send();


        $this->db->delete('tb_booking', ['id_booking' => $id_booking]);

        if (file_exists(FCPATH . 'assets/penyimpanan/qrcode/' . $booking['token'] . '.png')) {
            unlink(FCPATH . 'assets/penyimpanan/qrcode/' . $booking['token'] . '.png');
        }

        $this->session->set_flashdata('success', 'Booking berhasil dibatalkan.');
        redirect('dashboard');
    }
    public function proses_update_booking($id_booking) {
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('new_date', 'Date', 'required|regex_match[/^\d{4}-\d{2}-\d{2}$/]');
        $this->form_validation->set_rules('new_start_time', 'Start Time', 'required');
        $this->form_validation->set_rules('new_end_time', 'End Time', 'required');
        $this->form_validation->set_rules('selected_room_reschedule', 'Room', 'required|integer');

        if ($this->form_validation->run() == FALSE) {
            echo validation_errors();
        }
        $code_booking = strtoupper(bin2hex(random_bytes(4)));
        $booking = $this->db->get_where('tb_booking', ['id_booking' => $id_booking])->row_array();
        if (!$booking) {
            show_error('Booking not found', 404);
        }

        $id_ruangan = (int) $this->input->post('selected_room_reschedule');
        $data_ruangan = $this->db->get_where('tb_ruangan', ['id_ruangan' => $id_ruangan])->row_array();

        if (!$data_ruangan) {
            show_error('Room not found', 404);
        }
        $start_time = $this->input->post('start_time');
        try {
            $start_time_obj = new DateTime($start_time);
            $start_time_obj->modify('+1 minute');
            $start_time_new = $start_time_obj->format('H:i');
        } catch (Exception $e) {
            show_error('Invalid time format', 400);
        }
        $cek_bentrok = $this->db->where('room_id', $id_ruangan)
            ->where('date', $this->input->post('date'))
            ->where('id_booking !=', $id_booking)
            ->group_start()
                ->where("('".$start_time_new."' BETWEEN start_time AND end_time)")
                ->or_where("('".$this->input->post('end_time')."' BETWEEN start_time AND end_time)")
                ->or_where("(start_time BETWEEN '".$start_time_new."' AND '".$this->input->post('end_time')."')")
                ->or_where("(end_time BETWEEN '".$start_time_new."' AND '".$this->input->post('end_time')."')")
            ->group_end()
            ->get('tb_booking')->num_rows();
        if ($cek_bentrok > 0) {
            $this->session->set_flashdata('error', 'Waktu booking bentrok dengan jadwal lain. Silakan pilih waktu lain.');
            redirect('dashboard/booking_success/' . $booking['token']);
        }
        $sisa_update = $booking['sisa_update'] - 1;
        if ($sisa_update < 0) {
            $this->session->set_flashdata('error', 'Batas maksimal update booking telah tercapai.');
            redirect('dashboard/booking_success/' . $booking['token']);
        }
        $data = [
            'code_booking' => password_hash($code_booking, PASSWORD_BCRYPT), 
            'email' => $this->input->post('email', TRUE),
            'date' => $this->input->post('new_date', TRUE),
            'start_time' => $this->input->post('new_start_time', TRUE),
            'end_time' => $this->input->post('new_end_time', TRUE),
            'ip_address' => $data_ruangan['ip_address'],
            'room_id' => $id_ruangan,
            'sisa_update' => $sisa_update,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('id_booking', $id_booking);
        $this->db->update('tb_booking', $data);

        $title = "Booking Room " . $data_ruangan['nama_ruangan'] . " - " . $booking['name'];
        $details = "Permintaan reservasi ruangan melalui sistem web Booking Room.";
        $location = "PT. Toppan Plasindo Lestari - Cibitung Division, Jl. Raya Teuku Umar No.KM.44, Telaga Asih, Kec. Cikarang Bar., Kabupaten Bekasi, Jawa Barat 17520, Indonesia";
        $start_outlook = date("Y-m-d\TH:i:s", strtotime($data['date'] . ' ' . $data['start_time']));
        $end_outlook   = date("Y-m-d\TH:i:s", strtotime($data['date'] . ' ' . $data['end_time']));

        $outlook_calendar_url = "https://outlook.live.com/calendar/0/deeplink/compose?path=/calendar/action/compose"
            . "&subject=" . rawurlencode($title)
            . "&body=" . rawurlencode($details)
            . "&startdt=" . rawurlencode($start_outlook)
            . "&enddt=" . rawurlencode($end_outlook)
            . "&location=" . rawurlencode($location);
        $mail = $this->phpmailer_lib->load();
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'defaultbooking09@gmail.com';
        $mail->Password   = 'hlxqskqdmhomunjz';  
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->setFrom('defaultbooking09@gmail.com', 'Menejemen Booking Room');
        $mail->addAddress($data['email'], $data['name']);
        $mail->addEmbeddedImage(FCPATH . 'assets/image/toppan.png', 'toppanimg');
        $mail->isHTML(true);
        $mail->Subject = '[Update Booking] Booking' . htmlspecialchars($data_ruangan['nama_ruangan']) . ' pada ' . strftime('%d %B %Y', strtotime(htmlspecialchars($data['date'])));
        $mail->Body = "
                <div style='font-family: Arial, sans-serif;color: #333;padding: 20px;max-width: 700px;margin: auto;border: 1px solid #eee;border-radius: 8px;'>
                    <div style='display: flex;justify-content: space-between;align-items: center;margin-bottom: 20px;'>
                        <table style='width: 100%;'>
                            <tr>
                                <td style='width: 40%;'>
                                    <img src='cid:toppanimg' alt='Logo' style='height: 40px;'>
                                </td>
                                <td>
                                    <p style='text-align: right; font-weight: bold;'>". htmlspecialchars($booking['name']) ."</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <h2 style='color: #2E86C1; text-align: center;'>Room Booking Update</h2>
                    <p style='text-align: center;'>Your booking has been successfully updated. Here are the latest reservation details:</p>
                    <table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>Booking Code</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>". $code_booking ."</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>Name</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>". htmlspecialchars($booking['name']) ."</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>Room</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>". htmlspecialchars($data_ruangan['nama_ruangan']) ."</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>Date</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>". strftime('%d %B %Y', strtotime(htmlspecialchars($data['date']))) ."</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>Time</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>". htmlspecialchars($data['start_time']) ." - ". htmlspecialchars($data['end_time']) ."</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>Capacity</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>". htmlspecialchars($data_ruangan['kapasitas']) ." people</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'><strong>IP Address</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>". htmlspecialchars($data['ip_address']) ."</td>
                        </tr>
                    </table>
                    <div style='text-align: center; margin-top: 25px;'>
                        <a href='". base_url('dashboard/booking_success/' . $booking['token']) ."' style='display: inline-block; padding: 12px 25px; background-color: #2E86C1; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold;'> View Booking Details</a>
                    </div>
                    <div style='text-align: center; margin-top: 15px;'>
                        <a href='". $outlook_calendar_url ."' target='_blank' style='display: inline-block; padding: 12px 25px; background-color: #00c63bff; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold;'>Add to Outlook</a>
                    </div>
                    <p style='margin-top: 30px; font-size: 0.9em; color: #666;'>Please keep this information safe. This email was sent automatically, please do not reply.</p>
                </div>
        ";
        $mail->send();

        $this->session->set_flashdata('success', 'Booking berhasil diubah.');
        redirect('dashboard/booking_success/' . $booking['token']);
    }
    public function menage_bookings() {
        if ($this->session->userdata('role') != 'HRGA-ICT') {
            redirect('dashboard/back');
        }
        $data['bookings'] = $this->room->get_all_bookings_with_room();
        $data['rooms'] = $this->room->get_all_rooms();
        $data['stats_room'] = $this->room->get_room_stats();
        $data['stats_month'] = $this->room->get_monthly_stats();
        $data['total_booking'] = count($data['bookings']);
        $data['total_room'] = count($data['rooms']);
        $data['favorite_room'] = !empty($data['stats_room']) ? $data['stats_room'][0]->nama_ruangan : '-';
        $this->load->view('template/header');
        $this->load->view('template/menu');
        $this->load->view('dashboard/menage_bookings', $data);
    }
    public function booking_success($token) {
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
            show_404();
        }

        $data['booking'] = $this->db->get_where('tb_booking', ['token' => $token])->row_array();
        if (!$data['booking']) {
            show_404();
        }

        $data['room'] = $this->room->get_room_by_id($data['booking']['room_id']);
        $data['qrcode'] = 'assets/penyimpanan/qrcode/' . $token . '.png';

        $this->load->view('dashboard/booking_success', $data);
    }
}
