<?php

// Script de diagnostic réseau AVANCÉ pour Titan Email sur Railway
// À exécuter avec : php test-connectivity.php

echo "\n🔍 DIAGNOSTIC RÉSEAU - TITAN EMAIL vs INTERNET\n";
echo "===================================================\n";

// 1. Test DNS
echo "1. TEST DNS (Résolution de nom)...\n";
$titanIp = gethostbyname('smtp.titan.email');
if ($titanIp == 'smtp.titan.email') {
    echo "❌ ÉCHEC DNS : Impossible de trouver l'IP de smtp.titan.email\n";
} else {
    echo "✅ SUCCÈS DNS : smtp.titan.email = $titanIp\n";
}
echo "---------------------------------------------------\n";

// 2. Tests de Ports
echo "\n2. TEST DE CONNECTIVITÉ (Ports)...\n";
$tests = [
    ['host' => 'smtp.titan.email', 'port' => 465, 'name' => 'Titan SMTP (SSL)'],
    ['host' => 'smtp.titan.email', 'port' => 587, 'name' => 'Titan SMTP (TLS)'],
    ['host' => 'pop.titan.email', 'port' => 995, 'name' => 'Titan POP3 (Test)'],
    ['host' => 'imap.titan.email', 'port' => 993, 'name' => 'Titan IMAP (Test)'],
    ['host' => 'google.com', 'port' => 443, 'name' => 'Témoin : Google.com (HTTPS)'],
    ['host' => 'smtp.resend.com', 'port' => 465, 'name' => 'Témoin : Resend SMTP'],
    ['host' => 'smtp.mailgun.org', 'port' => 587, 'name' => 'Témoin : Mailgun SMTP'],
];

foreach ($tests as $test) {
    echo "Test vers {$test['name']} ({$test['host']}:{$test['port']})... ";

    $start = microtime(true);
    // Timeout strict de 5 secondes
    $fp = @fsockopen($test['host'], $test['port'], $errno, $errstr, 5);
    $end = microtime(true);
    $duration = round(($end - $start) * 1000, 2);

    if ($fp) {
        echo "✅ CONNECTÉ ({$duration} ms)\n";
        fclose($fp);
    } else {
        echo "❌ ÉCHEC ({$duration} ms)\n";
        echo "   -> Erreur : $errstr ($errno)\n";
    }
}

echo "\n===================================================\n";
echo "💡 INTERPRÉTATION :\n";
echo "- Si Google/Resend/Mailgun passent (✅) mais Titan échoue (❌) :\n";
echo "  => Titan bloque activement Railway.\n";
echo "\n";
echo "- Si tout échoue (❌) :\n";
echo "  => Votre conteneur n'a pas accès internet (rare).\n";
echo "\n";
echo "- SOLUTION : Passez MAIL_MAILER=log pour avancer,\n";
echo "  ou utilisez un relais SMTP (Resend/SendGrid/Mailgun).\n";
echo "===================================================\n\n";
