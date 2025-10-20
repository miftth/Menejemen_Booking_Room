<?php
// Namespace PHPMailer yang digunakan (pastikan sesuai dengan versi PHPMailer kamu)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load file PHPMailer utama dari third_party
require APPPATH . 'third_party/PHPMailer/PHPMailer.php';
require APPPATH . 'third_party/PHPMailer/SMTP.php';
require APPPATH . 'third_party/PHPMailer/Exception.php';

class PHPMailer_lib {
    public function __construct() {}
    public function load() {
        return new PHPMailer(true);
    }
}
