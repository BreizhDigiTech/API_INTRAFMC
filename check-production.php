<?php
/**
 * SCRIPT DE VALIDATION PRODUCTION
 * À exécuter après déploiement pour vérifier que tout fonctionne
 * Supprimer après validation !
 */

echo "<h1>🔍 Validation de l'installation production</h1>";

// 1. Vérification de l'environnement
echo "<h2>📋 Environnement</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Laravel: " . (file_exists('vendor/laravel/framework/composer.json') ? "✅ Installé" : "❌ Manquant") . "<br>";

// 2. Vérification des extensions PHP
echo "<h2>🔧 Extensions PHP</h2>";
$required_extensions = ['mbstring', 'openssl', 'PDO', 'pdo_mysql', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath'];
foreach ($required_extensions as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? "✅" : "❌") . "<br>";
}

// 3. Vérification des permissions
echo "<h2>📁 Permissions</h2>";
$directories = ['storage', 'bootstrap/cache', 'public/product_images', 'public/product_analysis'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "$dir: $perms " . (is_writable($dir) ? "✅ Écriture OK" : "❌ Pas d'écriture") . "<br>";
    } else {
        echo "$dir: ❌ N'existe pas<br>";
    }
}

// 4. Vérification du fichier .env
echo "<h2>⚙️ Configuration</h2>";
if (file_exists('.env')) {
    echo ".env: ✅ Présent<br>";
    $env = file_get_contents('.env');
    echo "APP_ENV: " . (strpos($env, 'APP_ENV=production') !== false ? "✅ Production" : "❌ Pas en production") . "<br>";
    echo "APP_DEBUG: " . (strpos($env, 'APP_DEBUG=false') !== false ? "✅ Debug désactivé" : "❌ Debug activé") . "<br>";
    echo "APP_KEY: " . (strpos($env, 'APP_KEY=base64:') !== false ? "✅ Configuré" : "❌ Non configuré") . "<br>";
} else {
    echo ".env: ❌ Fichier manquant<br>";
}

// 5. Test de la base de données
echo "<h2>🗄️ Base de données</h2>";
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    
    $pdo = new PDO(
        'mysql:host=' . env('DB_HOST') . ';dbname=' . env('DB_DATABASE'),
        env('DB_USERNAME'),
        env('DB_PASSWORD')
    );
    echo "Connexion MySQL: ✅ OK<br>";
    
    // Test d'une table
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    echo "Table users: " . ($stmt->rowCount() > 0 ? "✅ Existe" : "❌ N'existe pas") . "<br>";
    
} catch (Exception $e) {
    echo "Connexion MySQL: ❌ Erreur - " . $e->getMessage() . "<br>";
}

// 6. Test GraphQL
echo "<h2>🎯 GraphQL</h2>";
if (file_exists('public/index.php')) {
    echo "Point d'entrée: ✅ public/index.php existe<br>";
    
    // Test de la route GraphQL
    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/graphql";
    echo "Endpoint GraphQL: <a href='$url' target='_blank'>$url</a><br>";
} else {
    echo "Point d'entrée: ❌ public/index.php manquant<br>";
}

// 7. Cache et optimisations
echo "<h2>⚡ Optimisations</h2>";
echo "Cache config: " . (file_exists('bootstrap/cache/config.php') ? "✅" : "❌ Non généré") . "<br>";
echo "Cache routes: " . (file_exists('bootstrap/cache/routes-v7.php') ? "✅" : "❌ Non généré") . "<br>";
echo "Cache views: " . (is_dir('storage/framework/views') && count(glob('storage/framework/views/*')) > 0 ? "✅" : "❌ Non généré") . "<br>";

echo "<hr>";
echo "<h2>🚀 Actions recommandées</h2>";
echo "<p>Si tout est ✅, vous pouvez :</p>";
echo "<ul>";
echo "<li>Supprimer ce fichier <code>check-production.php</code></li>";
echo "<li>Tester vos endpoints GraphQL</li>";
echo "<li>Créer votre premier utilisateur admin</li>";
echo "<li>Configurer vos sauvegardes</li>";
echo "</ul>";

echo "<p><strong>⚠️ IMPORTANT :</strong> Supprimez ce fichier après validation pour des raisons de sécurité !</p>";
?>
