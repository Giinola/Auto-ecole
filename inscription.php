<?php
session_start();
require_once 'db.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';
    $role = $_POST['role'] ?? 'eleve';

    $roles_autorises = ['admin', 'moniteur', 'eleve'];

    if ($nom === '' || $email === '' || $mot_de_passe === '' || $confirmation === '') {
        $erreur = 'Veuillez remplir tous les champs.';
    } elseif ($mot_de_passe !== $confirmation) {
        $erreur = 'Les deux mots de passe ne correspondent pas.';
    } elseif (!in_array($role, $roles_autorises, true)) {
        $erreur = 'Rôle invalide.';
    } else {
        
        $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $check->execute(['email' => $email]);

        if ($check->fetch()) {
            $erreur = 'Cette adresse e-mail est déjà utilisée.';
        } else {
            $insert = $pdo->prepare(
                'INSERT INTO users (nom, email, mot_de_passe, role) VALUES (:nom, :email, :mot_de_passe, :role)'
            );
            $insert->execute([
                'nom' => $nom,
                'email' => $email,
                'mot_de_passe' => $mot_de_passe,
                'role' => $role,
            ]);

            header('Location: connexion.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — Auto-école</title>
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
            max-width: 440px;
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
        input,
        select {
            padding: 12px 14px;
            border-radius: 12px;
            border: 1.5px solid rgba(1, 62, 55, 0.2);
            background-color: #FFF7D6;
            color: #013E37;
            font-size: 1rem;
            outline: none;
        }
        input:focus,
        select:focus {
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
        <h1>Inscription</h1>

        <?php if ($erreur !== ''): ?>
            <div class="erreur"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form id="formInscription" method="POST" action="">
            <div class="champ">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" required>
            </div>

            <div class="champ">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="champ">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            </div>

            <div class="champ">
                <label for="confirmation">Confirmer le mot de passe</label>
                <input type="password" id="confirmation" name="confirmation" required>
            </div>

            <div class="champ">
                <label for="role">Rôle</label>
                <select id="role" name="role">
                    <option value="eleve">Élève</option>
                    <option value="moniteur">Moniteur</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button type="submit">S'inscrire</button>
        </form>

        <p class="lien">Déjà un compte ? <a href="connexion.php">Se connecter</a></p>
    </div>

    <script>
        document.getElementById('formInscription').addEventListener('submit', function (e) {
            const nom = document.getElementById('nom').value.trim();
            const email = document.getElementById('email').value.trim();
            const mdp = document.getElementById('mot_de_passe').value;
            const confirmMdp = document.getElementById('confirmation').value;

            if (!nom || !email || !mdp || !confirmMdp) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs.');
                return;
            }

            if (mdp !== confirmMdp) {
                e.preventDefault();
                alert('Les deux mots de passe ne correspondent pas.');
            }
        });
    </script>
</body>
</html>