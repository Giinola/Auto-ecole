<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'db.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if ($email === '' || $mot_de_passe === '') {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
       
        $stmt = $pdo->prepare('SELECT id, nom, email, mot_de_passe, role FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
var_dump($user); exit;
        if ($user && $mot_de_passe === $user['mot_de_passe']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_role'] = $user['role'];

            header('Location: index.php');
            exit;
        } else {
            $erreur = 'E-mail ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Auto-école</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background-color: #FFEFB3;
            color: #013E37;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .box {
            width: 100%;
            max-width: 420px;
            background-color: #FFFFFF;
            border-radius: 22px;
            padding: 28px;
            box-shadow: 0 10px 24px rgba(1, 62, 55, 0.1);
        }
        h1 {
            font-size: 1.7rem;
            margin-bottom: 18px;
            text-align: center;
        }
        .erreur {
            background-color: #013E37;
            color: #FFEFB3;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 0.92rem;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .champ {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        label {
            font-weight: 600;
            font-size: 0.95rem;
        }
        input {
            padding: 12px 14px;
            border-radius: 12px;
            border: 1.5px solid rgba(1, 62, 55, 0.2);
            background-color: #FFF7D6;
            color: #013E37;
            font-size: 1rem;
            outline: none;
        }
        input:focus {
            background-color: #FFFFFF;
            border-color: #013E37;
        }
        button {
            margin-top: 6px;
            padding: 12px 16px;
            border: none;
            border-radius: 14px;
            background-color: #013E37;
            color: #FFEFB3;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover {
            background-color: #02544B;
        }
        .lien {
            margin-top: 16px;
            text-align: center;
            font-size: 0.95rem;
        }
        a {
            color: #013E37;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>Connexion</h1>

        <?php if ($erreur !== ''): ?>
            <div class="erreur"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="champ">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="champ">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            </div>

            <button type="submit">Se connecter</button>
        </form>

        <p class="lien">Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>
    </div>
</body>
</html>