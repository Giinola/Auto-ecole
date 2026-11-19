<?php


session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}


$nom_utilisateur  = $_SESSION['user_nom'] ?? 'Utilisateur';
$role_utilisateur = $_SESSION['user_role'] ?? '';


$nom_ecole = "DriveLike Toretto";


if (!isset($page_active)) { $page_active = ''; }
if (!isset($titre_page)) { $titre_page = 'Accueil'; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titre_page; ?> - <?php echo $nom_ecole; ?></title>
    <style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    color: #1f2937;
    background-color: #FFFBEA;
    line-height: 1.6;
}

a {
    text-decoration: none;
}


.container {
    width: 90%;
    max-width: 1100px;
    margin: 0 auto;
}


.bouton {
    display: inline-block;
    background-color: #013E37;
    color: #ffffff;
    padding: 10px 22px;
    border-radius: 6px;
    font-weight: bold;
}

.bouton:hover {
    background-color: #012A26;
}

.bouton-blanc {
    background-color: #FFEFB3;
    color: #013E37;
}

.bouton-blanc:hover {
    background-color: #F5E08F;
}

.navbar {
    background-color: #FFEFB3;
    border-bottom: 2px solid #013E37;
    position: sticky;
    top: 0;
    z-index: 10;
}

.navbar-contenu {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 12px 0;
}

.logo {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #013E37;
    font-weight: bold;
    font-size: 18px;
}

.logo-icone {
    background-color: #013E37;
    border-radius: 8px;
    padding: 4px 8px;
}

.menu {
    display: flex;
    gap: 6px;
}

.menu a {
    color: #013E37;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 15px;
}

.menu a:hover,
.menu a.actif {
    background-color: #013E37;
    color: #FFEFB3;
}

.utilisateur {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    color: #013E37;
}

.utilisateur-nom {
    font-weight: bold;
    color: #013E37;
}

.utilisateur-role {
    background-color: #013E37;
    color: #FFEFB3;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 12px;
}

.bouton-deconnexion {
    background-color: #013E37;
    border: 1px solid #013E37;
    color: #FFEFB3;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 14px;
}

.bouton-deconnexion:hover {
    background-color: #012A26;
}


.bouton-menu {
    display: none;
    background: none;
    border: 1px solid #013E37;
    color: #013E37;
    border-radius: 6px;
    font-size: 20px;
    padding: 2px 10px;
    cursor: pointer;
}

.banniere {
    background-color: #013E37;
    color: #ffffff;
    padding: 60px 0;
}

.banniere-contenu {
    display: flex;
    align-items: center;
    gap: 40px;
}

.banniere-texte {
    flex: 1;
}

.banniere-texte h1 {
    font-size: 34px;
    line-height: 1.25;
    margin-bottom: 16px;
}

.banniere-texte p {
    margin-bottom: 24px;
    font-size: 17px;
    color: #FFEFB3;
}

.banniere-image {
    flex: 1;
}

.banniere-image svg {
    width: 100%;
    height: auto;
}

.section {
    padding: 60px 0;
}

.section-grise {
    background-color: #FFF8DA;
}

.titre-section {
    text-align: center;
    color: #013E37;
    font-size: 28px;
    margin-bottom: 36px;
}


.grille-cartes {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 24px;
}

.carte {
    background-color: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 24px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    transition: transform 0.2s, box-shadow 0.2s;
}


.carte:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
}

.carte-icone {
    font-size: 30px;
    background-color: #FFEFB3;
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    margin-bottom: 14px;
}

.carte h3 {
    color: #013E37;
    margin-bottom: 8px;
}

.carte p {
    color: #4b5563;
    margin-bottom: 18px;
    font-size: 15px;
}


.presentation {
    display: flex;
    align-items: center;
    gap: 40px;
}

.presentation-image {
    flex: 1;
    text-align: center;
}

.presentation-image svg {
    width: 70%;
    max-width: 240px;
    height: auto;
}

.presentation-texte {
    flex: 2;
}

.presentation-texte h2 {
    color: #013E37;
    margin-bottom: 12px;
}

.presentation-texte p {
    margin-bottom: 12px;
}

.presentation-texte ul {
    padding-left: 20px;
}


@media (max-width: 900px) {

    
    .navbar-contenu {
        flex-wrap: wrap;
    }

    .bouton-menu {
        display: block;
    }

    .menu {
        display: none;
        flex-direction: column;
        width: 100%;
        order: 4;
    }

    .menu.ouvert {
        display: flex;
    }

    .banniere-contenu,
    .presentation {
        flex-direction: column;
    }

    .banniere-texte h1 {
        font-size: 26px;
    }
}

    </style>
</head>
<body>

    <nav class="navbar">
        <div class="container navbar-contenu">

            <a href="index.php" class="logo">
                <span class="logo-icone">🚗</span>
                <span class="logo-nom"><?php echo $nom_ecole; ?></span>
            </a>

            <button class="bouton-menu" onclick="afficherMenu()">☰</button>

          
            <div class="menu" id="menu">
                <a href="index.php" <?php if ($page_active == 'accueil') echo 'class="actif"'; ?>>Accueil</a>
                <a href="eleves.php" <?php if ($page_active == 'eleves') echo 'class="actif"'; ?>>Élèves</a>
                <a href="moniteurs.php" <?php if ($page_active == 'moniteurs') echo 'class="actif"'; ?>>Moniteurs</a>
                <a href="vehicules.php" <?php if ($page_active == 'vehicules') echo 'class="actif"'; ?>>Véhicules</a>
                <a href="seances.php" <?php if ($page_active == 'seances') echo 'class="actif"'; ?>>Séances</a>
                <a href="examens.php" <?php if ($page_active == 'examens') echo 'class="actif"'; ?>>Examens</a>
                <a href="statistiques.php" <?php if ($page_active == 'statistiques') echo 'class="actif"'; ?>>Statistiques</a>
            </div>

            <div class="utilisateur">
        
                <span class="utilisateur-nom">👤 <?php echo htmlspecialchars($nom_utilisateur); ?></span>
                <?php if ($role_utilisateur != '') { ?>
                    <span class="utilisateur-role"><?php echo htmlspecialchars($role_utilisateur); ?></span>
                <?php } ?>

                <a href="logout.php" class="bouton-deconnexion">Se déconnecter</a>
            </div>

        </div>
    </nav>

    <script>
        function afficherMenu() {
            document.getElementById("menu").classList.toggle("ouvert");
        }
    </script>