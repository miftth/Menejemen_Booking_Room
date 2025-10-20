<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Booking Ruangan</title>
    <link href="<?= base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/flatpickr.min.css'); ?>">
</head>
<style>
    html {
        overflow-y: scroll; 
        scrollbar-width: none;
    }
    body {
            min-height: 100vh;
            background: url('<?= base_url('assets/image/image.png'); ?>') center center/cover no-repeat fixed;
            position: relative;
        }
</style>
<body>
    <nav class="navbar navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">BookingApp</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js');?>"></script>
