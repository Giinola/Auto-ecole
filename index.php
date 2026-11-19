<?php
// index.php - Page d'accueil
$page_active = 'accueil';
$titre_page  = 'Accueil';
include 'navbar.php';   // session, vérification de connexion + navbar
?>

    <!-- ================= 2. BANNIÈRE ================= -->
    <section class="banniere">
        <div class="container banniere-contenu">

            <div class="banniere-texte">
                <h1>Bienvenue dans votre espace de gestion auto-école !</h1>
                <p>
                    Gérez en un seul endroit vos élèves, vos moniteurs, vos véhicules,
                    vos séances de conduite et vos examens, et suivez vos taux de réussite.
                </p>
                <a href="eleves.php" class="bouton bouton-blanc">Accéder à la gestion</a>
            </div>

            <!-- Image : voiture dessinée en SVG (pas besoin de fichier image).
                 Tu peux la remplacer par : <img src="images/voiture.jpg" alt="Voiture"> -->
            <div class="banniere-image">
                <svg viewBox="0 0 300 140" xmlns="http://www.w3.org/2000/svg">
                    <!-- route -->
                    <rect x="0" y="118" width="300" height="6" fill="#ffffff" opacity="0.35"/>
                    <!-- carrosserie -->
                    <path d="M30 100 L45 70 Q50 60 62 60 L130 60 Q145 40 175 40 L215 40 Q235 40 245 62 L262 75 Q275 80 275 95 L275 108 L30 108 Z" fill="#FFEFB3"/>
                    <!-- vitres -->
                    <path d="M140 62 Q152 48 172 48 L205 48 L212 66 L140 66 Z" fill="#013E37" opacity="0.6"/>
                    <!-- roues -->
                    <circle cx="80" cy="108" r="16" fill="#1e293b"/>
                    <circle cx="80" cy="108" r="7" fill="#cbd5e1"/>
                    <circle cx="225" cy="108" r="16" fill="#1e293b"/>
                    <circle cx="225" cy="108" r="7" fill="#cbd5e1"/>
                </svg>
            </div>

        </div>
    </section>

    <!-- ================= 3. SERVICES (6 CARTES) ================= -->
    <section class="section">
        <div class="container">
            <h2 class="titre-section">Gestion de votre auto-école</h2>

            <div class="grille-cartes">

                <div class="carte">
                    <div class="carte-icone">🧑‍🎓</div>
                    <h3>Gestion des élèves</h3>
                    <p>Inscrivez vos élèves conducteurs et suivez leur progression.</p>
                    <a href="eleves.php" class="bouton">Consulter</a>
                </div>

                <div class="carte">
                    <div class="carte-icone">👨‍🏫</div>
                    <h3>Gestion des moniteurs</h3>
                    <p>Consultez la liste des moniteurs et leurs informations.</p>
                    <a href="moniteurs.php" class="bouton">Consulter</a>
                </div>

                <div class="carte">
                    <div class="carte-icone">🚙</div>
                    <h3>Gestion des véhicules</h3>
                    <p>Gardez un oeil sur les véhicules de l'auto-école.</p>
                    <a href="vehicules.php" class="bouton">Consulter</a>
                </div>

                <div class="carte">
                    <div class="carte-icone">📅</div>
                    <h3>Séances de conduite</h3>
                    <p>Planifiez les séances entre élèves, moniteurs et véhicules.</p>
                    <a href="seances.php" class="bouton">Consulter</a>
                </div>

                <div class="carte">
                    <div class="carte-icone">📝</div>
                    <h3>Gestion des examens</h3>
                    <p>Enregistrez les examens et leurs résultats.</p>
                    <a href="examens.php" class="bouton">Consulter</a>
                </div>

                <div class="carte">
                    <div class="carte-icone">📊</div>
                    <h3>Statistiques de réussite</h3>
                    <p>Visualisez les taux de réussite aux examens.</p>
                    <a href="statistiques.php" class="bouton">Consulter</a>
                </div>

            </div>
        </div>
    </section>

    <!-- ================= 4. PRÉSENTATION ================= -->
    <section class="section section-grise">
        <div class="container presentation">

            <!-- Image : volant dessiné en SVG -->
            <div class="presentation-image">
                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="100" cy="100" r="80" fill="none" stroke="#013E37" stroke-width="16"/>
                    <circle cx="100" cy="100" r="18" fill="#013E37"/>
                    <rect x="22" y="94" width="68" height="12" fill="#013E37"/>
                    <rect x="110" y="94" width="68" height="12" fill="#013E37"/>
                    <rect x="94" y="110" width="12" height="70" fill="#013E37"/>
                </svg>
            </div>

            <div class="presentation-texte">
                <h2>À propos de l'application</h2>
                <p>
                    Cette application aide l'auto-école à organiser son travail au quotidien.
                    Elle remplace les fiches papier par un espace unique et facile à utiliser.
                </p>
                <ul>
                    <li>Centraliser les informations des élèves et des moniteurs</li>
                    <li>Planifier les séances de conduite et les examens</li>
                    <li>Suivre les statistiques de réussite</li>
                </ul>
            </div>

        </div>
    </section>

</body>
</html>

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


.header {
    background-color: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    position: sticky;
    top: 0;
    z-index: 10;
}

.header-contenu {
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
    background-color: #FFEFB3;
    border-radius: 8px;
    padding: 4px 8px;
}

.menu {
    display: flex;
    gap: 6px;
}

.menu a {
    color: #374151;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 15px;
}

.menu a:hover,
.menu a.actif {
    background-color: #FFEFB3;
    color: #013E37;
}

.utilisateur {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    color: #4b5563;
}

.bouton-deconnexion {
    border: 1px solid #013E37;
    color: #013E37;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 14px;
}

.bouton-deconnexion:hover {
    background-color: #013E37;
    color: #ffffff;
}

.bouton-menu {
    display: none;
    background: none;
    border: 1px solid #d1d5db;
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


.footer {
    background-color: #013E37;
    color: #FFEFB3;
    padding-top: 40px;
}

.footer-contenu {
    display: flex;
    justify-content: space-between;
    gap: 30px;
    padding-bottom: 30px;
}

.footer h3 {
    color: #ffffff;
    margin-bottom: 10px;
}

.footer p {
    font-size: 14px;
}

.copyright {
    text-align: center;
    border-top: 1px solid #0B5A50;
    padding: 14px 0;
    font-size: 13px;
}


@media (max-width: 900px) {

   
    .header-contenu {
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
    .presentation,
    .footer-contenu {
        flex-direction: column;
    }

    .banniere-texte h1 {
        font-size: 26px;
    }
}
</style>