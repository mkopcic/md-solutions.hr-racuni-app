<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obavijest o autentifikaciji</title>
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
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .info-box {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #4F46E5;
            border-radius: 4px;
        }
        .info-row {
            display: flex;
            margin: 10px 0;
        }
        .info-label {
            font-weight: bold;
            min-width: 150px;
            color: #6b7280;
        }
        .info-value {
            color: #111827;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 12px;
        }
        .event-login {
            background-color: #10b981;
        }
        .event-logout {
            background-color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="header {{ $eventType === 'login' ? 'event-login' : 'event-logout' }}">
        <h1>{{ $eventType === 'login' ? '🔓 Prijava u sustav' : '🔒 Odjava iz sustava' }}</h1>
    </div>

    <div class="content">
        <p>
            @if($eventType === 'login')
                Korisnik se upravo prijavio u sustav.
            @else
                Korisnik se upravo odjavio iz sustava.
            @endif
        </p>

        <div class="info-box">
            <h3 style="margin-top: 0; color: #4F46E5;">Informacije o korisniku</h3>

            <div class="info-row">
                <span class="info-label">Korisnik:</span>
                <span class="info-value">{{ $userData['name'] ?? 'N/A' }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $userData['email'] ?? 'N/A' }}</span>
            </div>

            @if(isset($userData['user_id']))
            <div class="info-row">
                <span class="info-label">ID korisnika:</span>
                <span class="info-value">{{ $userData['user_id'] }}</span>
            </div>
            @endif
        </div>

        <div class="info-box">
            <h3 style="margin-top: 0; color: #4F46E5;">Detalji zahtjeva</h3>

            <div class="info-row">
                <span class="info-label">IP adresa:</span>
                <span class="info-value">{{ $requestData['ip_address'] ?? 'N/A' }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Preglednik:</span>
                <span class="info-value" style="word-break: break-all;">{{ $requestData['user_agent'] ?? 'N/A' }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Vrijeme:</span>
                <span class="info-value">{{ $requestData['timestamp'] ? $requestData['timestamp']->format('d.m.Y H:i:s') : 'N/A' }}</span>
            </div>

            @if(isset($requestData['session_id']))
            <div class="info-row">
                <span class="info-label">ID sesije:</span>
                <span class="info-value" style="font-family: monospace; font-size: 11px;">{{ Str::limit($requestData['session_id'], 30) }}</span>
            </div>
            @endif
        </div>

        <div style="margin-top: 20px; padding: 15px; background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px;">
            <strong>⚠️ Napomena:</strong> Ako niste izvršili ovu radnju, odmah kontaktirajte administratora sustava.
        </div>
    </div>

    <div class="footer">
        <p>Ovo je automatska obavijest iz sustava za izradu računa.</p>
        <p>{{ config('app.name') }} &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
