<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// --- Route de diagnostic réseau SMTP ---
// Accessible via : https://votre-app-url.up.railway.app/test-connectivity
Route::get('/test-connectivity', function () {
    $output = "<h1>🔍 Diagnostic Réseau SMTP (Depuis Railway)</h1><pre style='background:#f4f4f4;padding:15px;border-radius:5px;'>";

    // 1. Test DNS
    $output .= "<strong>1. TEST DNS</strong>\n";
    $host = 'smtp.titan.email';
    $ip = gethostbyname($host);
    if ($ip == $host) {
        $output .= "❌ ÉCHEC DNS : Impossible de résoudre $host\n";
    } else {
        $output .= "✅ SUCCÈS DNS : $host = $ip\n";
    }
    $output .= "---------------------------------------------------\n\n";

    // 2. Tests de Ports
    $output .= "<strong>2. TEST DE CONNECTIVITÉ (Ports)</strong>\n";
    $tests = [
        ['host' => 'smtp.titan.email', 'port' => 465, 'name' => 'Titan SMTP (SSL/465)'],
        ['host' => 'smtp.titan.email', 'port' => 587, 'name' => 'Titan SMTP (TLS/587)'],
        ['host' => 'google.com', 'port' => 443, 'name' => 'Témoin : Google (HTTPS)'],
        ['host' => 'smtp.resend.com', 'port' => 465, 'name' => 'Témoin : Resend SMTP'],
        ['host' => 'smtp.mailgun.org', 'port' => 587, 'name' => 'Témoin : Mailgun SMTP'],
    ];

    foreach ($tests as $test) {
        $output .= "Test vers {$test['name']}... ";

        $start = microtime(true);
        // Timeout de 3 secondes pour ne pas bloquer la page trop longtemps
        $fp = @fsockopen($test['host'], $test['port'], $errno, $errstr, 3);
        $end = microtime(true);
        $duration = round(($end - $start) * 1000, 2);

        if ($fp) {
            $output .= "<span style='color:green'>✅ CONNECTÉ</span> ({$duration} ms)\n";
            fclose($fp);
        } else {
            $output .= "<span style='color:red'>❌ ÉCHEC</span> ($errstr - Code $errno)\n";
        }
    }

    $output .= "</pre>";
    $output .= "<h2>📊 Analyse :</h2>";
    $output .= "<ul>";
    $output .= "<li>Si <strong>Google/Resend/Mailgun</strong> sont ✅ verts mais <strong>Titan</strong> est ❌ rouge : <br><code>Titan bloque l'adresse IP de Railway</code></li>";
    $output .= "<li>Si tout est ❌ rouge : Problème de connectivité internet du conteneur (rare)</li>";
    $output .= "<li><strong>Solution :</strong> Utilisez <code>MAIL_MAILER=log</code> temporairement ou passez à Resend/Mailgun/SendGrid</li>";
    $output .= "</ul>";

    return $output;
});
