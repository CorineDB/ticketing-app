<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification de scan</title>
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
            background-color: {{ $action === 'in' ? '#FF9800' : '#9C27B0' }};
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
        .scan-info {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid {{ $action === 'in' ? '#FF9800' : '#9C27B0' }};
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
        <h1>{{ $action === 'in' ? 'Entrée confirmée' : 'Sortie confirmée' }}</h1>
    </div>

    <div class="content">
        <p>Bonjour,</p>

        <p>Votre billet a été scanné avec succès.</p>

        <div class="scan-info">
            <h2>Détails du scan</h2>
            <p><strong>Action:</strong> {{ $action === 'in' ? 'Entrée' : 'Sortie' }}</p>
            <p><strong>Événement:</strong> {{ $event->title }}</p>
            <p><strong>Code billet:</strong> {{ $ticket->code }}</p>
            <p><strong>Porte:</strong> {{ $gateName }}</p>
            <p><strong>Heure:</strong> {{ $scanTime->format('d/m/Y H:i:s') }}</p>
        </div>

        @if($action === 'in')
            <p>Profitez bien de l'événement!</p>
        @else
            <p>Merci d'avoir participé à {{ $event->title }}.</p>
            @if($event->allow_reentry)
                <p><strong>Note:</strong> Vous pouvez rentrer à nouveau avec ce billet.</p>
            @endif
        @endif

        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
