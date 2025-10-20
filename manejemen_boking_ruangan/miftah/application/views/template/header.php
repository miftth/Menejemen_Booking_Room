<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home Page</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Arial', sans-serif;
                line-height: 1.6;
                display: flex;
                flex-direction: column;
                height: 100vh;
            }

            .container {
                width: 80%;
                margin: 0 auto;
                flex: 1;
            }

            header {
                background-color: #333;
                color: #fff;
                padding: 20px 0;
                text-align: center;

            }

            h1 {
                font-size: 2.5rem;
                margin-bottom: 10px;
            }

            nav ul {
                list-style: none;
                display: flex;
                justify-content: center;
                gap: 20px;
                margin-top: 20px;
            }

            nav ul li {
                display: inline;
            }

            nav ul li a {
                color: #fff;
                text-decoration: none;
                font-size: 1.1rem;
            }

            nav ul li a:hover {
                text-decoration: underline;
            }

            footer {
                background-color: #333;
                color: #fff;
                padding: 20px 0;
                text-align: center;
                margin-top: 400px;
            }

            footer p {
                font-size: 1rem;
            }

            .social-media a {
                color: #fff;
                text-decoration: none;
                margin: 0 10px;
            }

            .social-media a:hover {
                text-decoration: underline;
            }
        </style>
    </head>

    <body>
        <header>
            <div class="container">
                <h1>Selamat Datang <?php 
                    foreach($datauser as $user):    ?>
                    <?=$user->nama; ?>
                    <?php endforeach; ?>
                </h1>
                <nav>
                    <ul>
                        <li><a href="<?=base_url('dasboard'); ?>">Dasboard</a></li>
                        <li><a href="<?=base_url('datauser');?>">Data User</a></li>
                        <li><a href="<?=base_url('jabatan');?>">Jabatan</a></li>
                        <li><a href="<?=base_url();?>login/log_out">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </header>