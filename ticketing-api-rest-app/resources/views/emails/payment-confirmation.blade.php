<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de paiement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2196F3;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 0 0 5px 5px;
        }
        .payment-info {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #2196F3;
        }
        .success-badge {
            background-color: #4CAF50;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            margin: 10px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Paiement confirmé</h1>
        <span class="success-badge">✓ Succès</span>
    </div>

    <div class="content">
        <p>Bonjour,</p>

        <p>Votre paiement a été traité avec succès!</p>

        <div class="payment-info">
            <h2>Détails du paiement</h2>
            <p><strong>Montant payé:</strong> {{ number_format($amount, 0, ',', ' ') }} XOF</p>
            <p><strong>ID Transaction:</strong> {{ $transactionId }}</p>
            <p><strong>Date:</strong> {{ now()->format('d/m/Y H:i') }}</p>
        </div>

        <div class="payment-info">
            <h2>Détails du billet</h2>
            <p><strong>Événement:</strong> {{ $event->name }}</p>
            <p><strong>Type de billet:</strong> {{ $ticketType->name }}</p>
            <p><strong>Code billet:</strong> {{ $ticket->code }}</p>
            <p><strong>Statut:</strong> <span style="color: #4CAF50;">Payé</span></p>
        </div>

        <p>Vous pouvez maintenant accéder à votre billet:</p>

        <a href="{{ $magicLink }}" class="button">Voir mon billet</a>

        <div class="footer">
            <p>Merci pour votre confiance!</p>
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
