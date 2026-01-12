<?php
require_once "config/Database.php";
require_once "core/Auth.php";

if (Auth::check()) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"] ?? "";
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "member";
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Tous les champs sont requis";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit avoir au moins 6 caractères";
    } else {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = "Cet email est déjà utilisé";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $success = $stmt->execute([$name, $email, $hashed_password, $role]);
            
            if ($success) {
                $success = "Compte créé avec succès! Connectez-vous.";
            } else {
                $error = "Erreur lors de la création du compte";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inscription - Athena</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2><i class="fas fa-user-plus"></i> Inscription</h2>
            <p>Créez votre compte Athena</p>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="text" name="name" placeholder="Nom complet" required>
                
                <input type="email" name="email" placeholder="Email" required>
                
                <input type="password" name="password" placeholder="Mot de passe (6 caractères minimum)" required>
                
                <select name="role">
                    <option value="member">Membre</option>
                    <option value="manager">Manager</option>
                </select>
                
                <button type="submit">
                    <i class="fas fa-user-plus"></i> Créer mon compte
                </button>
            </form>
            
            <div class="auth-links">
                Déjà un compte? <a href="login.php">Connectez-vous</a>
            </div>
        </div>
    </div>
</body>
</html>