<?php
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'customer/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } else {
            $result = register($email, $name, $password);
            
            if ($result['success']) {
                // Auto-login after registration
                login($email, $password);
                redirect('customer/dashboard.php');
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LocalTechFix</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%);
        }
        .auth-card {
            background: var(--white);
            padding: 3rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            max-width: 450px;
            width: 100%;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-header h1 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }
        .auth-header p {
            color: var(--light-text);
        }
        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
        }
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--secondary-color);
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            font-family: inherit;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        .auth-footer a {
            color: var(--primary-color);
            font-weight: 500;
        }
        .auth-footer a:hover {
            text-decoration: underline;
        }
        .password-requirements {
            font-size: 0.875rem;
            color: var(--light-text);
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="fa-solid fa-microchip"></i> LocalTechFix</h1>
                <p>Create your account</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required placeholder="John Doe" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••" minlength="6">
                    <div class="password-requirements">
                        <i class="fa-solid fa-info-circle"></i> Minimum 6 characters
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••" minlength="6">
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fa-solid fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign in</a></p>
                <p><a href="index.php">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
