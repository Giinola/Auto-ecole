<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function exiger_connexion(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: connexion.php');
        exit;
    }
}


function historique(PDO $pdo, string $action, string $table, ?int $record_id, string $details): void {
    $stmt = $pdo->prepare(
        "INSERT INTO historique (user_id, action, table_concernee, record_id, details)
         VALUES (:u, :a, :t, :r, :d)"
    );
    $stmt->execute([
        'u' => $_SESSION['user_id'] ?? null,
        'a' => $action,
        't' => $table,
        'r' => $record_id,
        'd' => $details,
    ]);
}


function flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function afficher_flash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="message message-' . htmlspecialchars($f['type']) . '">'
           . htmlspecialchars($f['message']) . '</div>';
    }
}


function telecharger_photo(array $fichier, ?string &$erreur = null): ?string {
    $code = $fichier['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($code === UPLOAD_ERR_NO_FILE) return null;
    if ($code !== UPLOAD_ERR_OK) { $erreur = "Erreur lors de l'envoi de la photo."; return null; }
    if ($fichier['size'] > 2 * 1024 * 1024) { $erreur = 'La photo dépasse 2 Mo.'; return null; }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($fichier['tmp_name']);
    $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if ($ext === null) { $erreur = 'Format de photo invalide (JPG, PNG ou WEBP).'; return null; }

    if (!is_dir(__DIR__ . '/uploads')) { mkdir(__DIR__ . '/uploads', 0755, true); }
    $nom = 'eleve_' . bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($fichier['tmp_name'], __DIR__ . '/uploads/' . $nom)) {
        $erreur = "Impossible d'enregistrer la photo.";
        return null;
    }
    return $nom;
}