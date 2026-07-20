<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/svg+xml" href="<?= base_url('favicon.svg') ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN - Sistem Pendukung Keputusan Metode SAW</title>

    <!-- Mazer CSS & Bootstrap Icons -->
    <link rel="stylesheet" href="<?= base_url('assets/mazer/dist/assets/compiled/css/app.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/mazer/dist/assets/compiled/css/app-dark.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/mazer/dist/assets/extensions/bootstrap-icons/font/bootstrap-icons.min.css') ?>">

    <style>
        body {
            background: #d5ddff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .card-login {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 420px;
            background: #fff;
        }

        .card-body {
            padding: 2rem;
        }

        .system-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .subtitle {
            text-align: center;
            color: #6c757d;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .form-control {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
        }

        .form-control:focus {
            border-color: #435ebe;
            box-shadow: 0 0 0 0.2rem rgba(67, 94, 190, 0.25);
        }

        .input-group-text {
            background-color: #f8f9fa;
            border-radius: 0.5rem 0 0 0.5rem;
            border: 1px solid #dee2e6;
            border-right: none;
        }

        .btn-login {
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-weight: 600;
            background: #435ebe;
            border: none;
        }

        .btn-login:hover {
            background: #2d3d8a;
        }

        .toggle-password {
            cursor: pointer;
            background: #f8f9fa;
            border-radius: 0 0.5rem 0.5rem 0;
            border-left: none;
        }

        .alert {
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        footer {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.75rem;
            color: #6c757d;
        }
    </style>
</head>

<body>

    <div class="card card-login shadow-sm">
        <div class="card-body">
            <div class="system-name">LOGIN</div>
            <div class="subtitle">Sistem Pendukung Keputusan Metode SAW</div>

            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $this->session->flashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('auth/process'); ?>" method="POST">
                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                        <input type="email" name="email" class="form-control" id="email" placeholder="Masukkan email anda" required autofocus>
                    </div>
                </div>

                <!-- Password with toggle view -->
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="password" class="form-control" id="password" placeholder="Masukkan password anda" required>
                        <span class="input-group-text toggle-password" id="togglePassword">
                            <i class="bi bi-eye-slash-fill" id="eyeIcon"></i>
                        </span>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i> MASUK
                    </button>
                </div>
            </form>

            <footer>
                <?= date('Y') ?> &copy; (19220941 - 19221061)
            </footer>
        </div>
    </div>

    <script>
        // Toggle Password Visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            eyeIcon.classList.toggle('bi-eye-slash-fill');
            eyeIcon.classList.toggle('bi-eye-fill');
        });
    </script>

    <!-- Mazer JS -->
    <script src="<?= base_url('assets/mazer/dist/assets/compiled/js/app.js') ?>"></script>
</body>

</html>