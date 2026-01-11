<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Athena</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-box {
            background: white;
            border-radius: var(--radius);
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border: 1px solid var(--border);
        }
        
        .login-logo {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .login-title {
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        
        .login-subtitle {
            color: var(--gray);
            margin-bottom: 2rem;
            font-size: 1rem;
        }
        
        .forgot-password {
            display: block;
            text-align: right;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: var(--primary);
        }
        
        .register-link {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
            color: var(--gray);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <i class="fas fa-project-diagram"></i>
            </div>
            
            <h1 class="login-title">Athena</h1>
            <p class="login-subtitle">Gestión de Proyectos Ágiles</p>
            
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'registered'): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> ¡Registro exitoso! Ya puedes iniciar sesión.
                </div>
            <?php endif; ?>
            
            <form action="login.php" method="POST">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
                
                <div class="register-link">
                    ¿No tienes cuenta? <a href="register.php" style="color: var(--primary); font-weight: 600;">Regístrate aquí</a>
                </div>
            </form>
        </div>
    </div>

<?php
require_once "../config/database.php";
require_once "../models/user.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    $userModel = new User($pdo);
    if ($userModel->login($email, $password)) {
        header("Location: dashboard.php");
        exit;
    } else {
        echo '<div class="container"><div class="message error"><i class="fas fa-exclamation-circle"></i> Email o contraseña incorrectos</div></div>';
    }
}
?>
</body>
</html>