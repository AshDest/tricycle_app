<?php
/**
 * Webhook de déploiement automatique
 * Ce script est appelé par GitHub lors d'un push sur la branche main
 *
 * Installation sur le serveur:
 * 1. Placer ce fichier dans /var/www/tricycle_app/scripts/
 * 2. Créer un lien symbolique: ln -s /var/www/tricycle_app/scripts/webhook-deploy.php /var/www/tricycle_app/public/webhook-deploy.php
 * 3. Configurer le webhook sur GitHub avec l'URL: https://tricycle.newtechnologyhub.org/webhook-deploy.php
 * 4. Définir un secret dans le fichier .env: DEPLOY_SECRET=votre_secret_ici
 */

// Configuration
$secret = getenv('DEPLOY_SECRET') ?: trim(file_get_contents(__DIR__ . '/../.deploy_secret'));
$logFile = __DIR__ . '/../storage/logs/deploy.log';
$branch = 'main';
$repoPath = '/var/www/tricycle_app';

// Fonction de log
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed');
}

// Récupération du payload
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

// Vérification de la signature GitHub
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
if (!empty($secret)) {
    $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
    if (!hash_equals($expectedSignature, $signature)) {
        logMessage('ERREUR: Signature invalide');
        http_response_code(403);
        die('Invalid signature');
    }
}

// Vérification de la branche
$ref = $data['ref'] ?? '';
if ($ref !== "refs/heads/$branch") {
    logMessage("INFO: Push ignoré (branche: $ref)");
    http_response_code(200);
    die("Push ignored (not $branch branch)");
}

logMessage('=== DÉBUT DU DÉPLOIEMENT ===');
logMessage('Commit: ' . ($data['after'] ?? 'N/A'));
logMessage('Auteur: ' . ($data['pusher']['name'] ?? 'N/A'));

// Exécution du déploiement
$output = [];
$returnCode = 0;

// Commandes de déploiement
$commands = [
    "cd $repoPath",
    "git fetch origin $branch",
    "git reset --hard origin/$branch",
    "composer install --no-dev --optimize-autoloader --no-interaction",
    "php artisan migrate --force",
    "php artisan config:cache",
    "php artisan route:cache",
    "php artisan view:cache",
    "php artisan storage:link --force 2>/dev/null || true",
    "npm install --production 2>/dev/null || true",
    "npm run build 2>/dev/null || true",
    "chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true",
    "chmod -R 775 storage bootstrap/cache 2>/dev/null || true",
];

$fullCommand = implode(' && ', $commands) . ' 2>&1';

// Exécution
exec($fullCommand, $output, $returnCode);

$outputStr = implode("\n", $output);
logMessage("Sortie:\n$outputStr");
logMessage("Code de retour: $returnCode");

if ($returnCode === 0) {
    logMessage('=== DÉPLOIEMENT RÉUSSI ===');
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Deployment completed successfully',
        'output' => $output
    ]);
} else {
    logMessage('=== ÉCHEC DU DÉPLOIEMENT ===');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Deployment failed',
        'output' => $output,
        'code' => $returnCode
    ]);
}

