<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .status {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .status-item {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
        .emoji {
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">📧 Mail Server Status Report</h1>
        <p style="margin: 10px 0 0 0;">md-solutions.hr</p>
    </div>
    
    <div class="content">
        <p>Pozdrav,</p>
        
        <p>Ovo je automatski test mail server konfiguracije za <strong>md-solutions.hr</strong> domenu.</p>
        
        <div class="status">
            <h2 style="margin-top: 0; color: #667eea;">🚀 Status Servisa</h2>
            
            <div class="status-item">
                <span><strong>Postfix MTA:</strong></span>
                <span class="badge badge-success">✅ Active (v3.6.4)</span>
            </div>
            
            <div class="status-item">
                <span><strong>Dovecot IMAP/POP3:</strong></span>
                <span class="badge badge-success">✅ Active (v2.3.16)</span>
            </div>
            
            <div class="status-item">
                <span><strong>OpenDKIM:</strong></span>
                <span class="badge badge-danger">❌ Inactive</span>
            </div>
            
            <div class="status-item">
                <span><strong>Fail2ban:</strong></span>
                <span class="badge badge-success">✅ Active</span>
            </div>
        </div>
        
        <div class="status">
            <h2 style="margin-top: 0; color: #667eea;">🌐 DNS Zapisi</h2>
            
            <div class="status-item">
                <span><strong>MX Record:</strong></span>
                <span class="badge badge-success">✅ Configured</span>
            </div>
            
            <div class="status-item">
                <span><strong>SPF Record:</strong></span>
                <span class="badge badge-success">✅ Valid</span>
            </div>
            
            <div class="status-item">
                <span><strong>DMARC Record:</strong></span>
                <span class="badge badge-success">✅ Valid</span>
            </div>
            
            <div class="status-item">
                <span><strong>DKIM Record:</strong></span>
                <span class="badge badge-danger">❌ Missing</span>
            </div>
        </div>
        
        <div class="status">
            <h2 style="margin-top: 0; color: #667eea;">📊 Test Rezultati</h2>
            
            <p><strong>✅ Mail slanje:</strong> Úspješno - primili ste ovaj email!</p>
            <p><strong>✅ SMTP konfiguracija:</strong> Laravel aplikacija koristi lokalni Postfix server (127.0.0.1:25)</p>
            <p><strong>✅ Attachment:</strong> Detaljan izvještaj priložen kao Markdown dokument</p>
            <p><strong>📅 Datum testa:</strong> {{ now()->format('d.m.Y H:i:s') }}</p>
        </div>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 20px 0;">
            <p style="margin: 0;"><strong>⚠️ Važna napomena:</strong></p>
            <p style="margin: 10px 0 0 0;">OpenDKIM servis nije pokrenut. Preporučuje se implementacija DKIM potpisa za bolji email deliverability. Detalji u priloženom dokumentu.</p>
        </div>
        
        <p>Za detaljan pregled konfiguracije, problema i preporuka, pogledajte priloženi <strong>MAIL-SERVER-STATUS.md</strong> dokument.</p>
        
        <p>Lijep pozdrav,<br>
        <strong>{{ config('app.name') }} - Automatski Mail System</strong></p>
    </div>
    
    <div class="footer">
        <p>Ovaj email je poslan s mail servera: <strong>server.md-solutions.hr</strong></p>
        <p>IP Adresa: 88.198.92.135 | Port: 25 (SMTP)</p>
        <p style="font-size: 12px; color: #999;">Powered by Laravel {{ app()->version() }} & Postfix {{ '3.6.4' }}</p>
    </div>
</body>
</html>
