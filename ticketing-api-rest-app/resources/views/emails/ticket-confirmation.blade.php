<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de votre billet</title>
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
            background-color: #4CAF50;
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
        .ticket-info {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #4CAF50;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4CAF50;
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
        <h1>Votre billet est prêt!</h1>
    </div>

    <div class="content">
        <p>Bonjour,</p>

        <p>Merci pour votre achat! Voici les détails de votre billet:</p>

        <div class="ticket-info">
            <h2>{{ $event->title }}</h2>
            <p><strong>Type de billet:</strong> {{ $ticketType->name }}</p>
            <p><strong>Code billet:</strong> {{ $ticket->code }}</p>
            <p><strong>Date de l'événement:</strong> {{ $event->start_datetime->format('d/m/Y H:i') }}</p>
            @if($event->location)
                <p><strong>Lieu:</strong> {{ $event->location }}</p>
            @endif
            <p><strong>Prix:</strong> {{ number_format($ticketType->price, 0, ',', ' ') }} XOF</p>
        </div>

        <p>Votre code QR est joint à cet email. Vous pouvez également accéder à votre billet via ce lien:</p>

        <a href="{{ $magicLink }}" class="button">Voir mon billet</a>

        <p><strong>Important:</strong> Présentez ce QR code à l'entrée de l'événement pour scanner votre billet.</p>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
