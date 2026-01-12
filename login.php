<?php
require_once "config/Database.php";
require_once "core/Auth.php";

if (Auth::check()) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    
    if (Auth::login($email, $password)) {
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Email ou mot de passe incorrect";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Connexion - Athena</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2><i class="fas fa-sign-in-alt"></i> Connexion</h2>
            <p>Bienvenue sur Athena</p>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="email" name="email" placeholder="Email" required>
                
                <input type="password" name="password" placeholder="Mot de passe" required>
                
                <button type="submit">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </form>
            
            <div class="auth-links">
                Nouveau? <a href="register.php">Créez un compte</a>
            </div>
        </div>
    </div>
</body>
</html>