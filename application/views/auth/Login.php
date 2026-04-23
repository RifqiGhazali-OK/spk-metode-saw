<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DSS Kontrak Perpanjangan Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #1a1a2e;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-card {
            background: #16213e;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            padding: 30px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo-q {
            width: 80px;
            height: 80px;
            background: #4e00c2;
            color: white;
            font-size: 50px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 20%;
            box-shadow: 0 0 15px rgba(78, 0, 194, 0.6);
        }

        .system-name {
            color: #ffffff;
            font-size: 1.2rem;
            text-align: center;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .company-name {
            color: #94a3b8;
            font-size: 0.85rem;
            text-align: center;
            margin-bottom: 25px;
        }

        .form-label {
            color: #cbd5e1;
        }

        .form-control {
            background: #0f3460;
            border: 1px solid #1a1a2e;
            color: white;
            padding: 12px;
        }

        .form-control:focus {
            background: #1a1a2e;
            border-color: #4e00c2;
            color: white;
            box-shadow: none;
        }

        .btn-login {
            background: #4e00c2;
            border: none;
            padding: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 10px;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: #3a0091;
            transform: translateY(-2px);
        }

        .alert {
            font-size: 0.9rem;
            border-radius: 10px;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="logo-container">
            <div class="logo-q">Q</div>
        </div>
        <div class="system-name">DSS Perpanjangan Kontrak</div>
        <div class="company-name">PT. Internusa Jayaabadi Sentosa</div>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $this->session->flashdata('error'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="<?php echo base_url('auth/process'); ?>" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" id="email" placeholder="nama@gmail.com" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="password" placeholder="••••••••" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-login">MASUK SISTEM</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>