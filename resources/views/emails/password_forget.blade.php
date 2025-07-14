<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe - Bailleurnet</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, rgba(250, 200, 78, 0.8) 0%, #FAC84E 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #5EB21E 0%, #24583F 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        
        .logo {
            max-width: 200px;
            height: auto;
            margin-bottom: 20px;
            filter: brightness(0) invert(1);
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 300;
            margin: 0;
            letter-spacing: 0.5px;
        }
        
        .content {
            padding: 50px 40px;
        }
        
        .greeting {
            font-size: 22px;
            font-weight: 600;
            color: #2c5aa0;
            margin-bottom: 30px;
        }
        
        .message {
            font-size: 16px;
            line-height: 1.6;
            color: #555;
            margin-bottom: 20px;
        }
        
        .security-notice {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 30px 0;
            border-radius: 0 8px 8px 0;
        }
        
        .security-notice h3 {
            color: #28a745;
            font-size: 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .security-notice h3::before {
            content: "🔒";
            margin-right: 8px;
        }
        
        .security-notice p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, rgba(76, 152, 19, 1) 0%, #5EB21E 100%);
            color: white !important;
            text-decoration: none;
            padding: 18px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
            text-transform: uppercase;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(40, 167, 69, 0.4);
        }
        
        .expiry-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 20px;
            border-radius: 12px;
            margin: 30px 0;
            text-align: center;
        }
        
        .expiry-notice::before {
            content: "⏰";
            font-size: 20px;
            display: block;
            margin-bottom: 10px;
        }
        
        .expiry-notice strong {
            color: #dc3545;
            font-size: 18px;
        }
        
        .alternative-link {
            background: #f8f9fa;
            border: 1px dashed #dee2e6;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
        }
        
        .alternative-link h4 {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .alternative-link p {
            word-break: break-all;
            font-size: 12px;
            color: #6c757d;
            margin: 0;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 30px 40px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        
        .footer p {
            color: #6c757d;
            font-size: 14px;
            margin: 10px 0;
        }
        
        .footer .signature {
            font-weight: 600;
            color: #5EB21E;
            margin-top: 20px;
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #dee2e6, transparent);
            margin: 30px 0;
        }
        
        @media (max-width: 600px) {
            .email-container {
                border-radius: 0;
                margin: 0;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .btn {
                padding: 15px 30px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{{asset('images/bailleurnet.png')}}" alt="Bailleurnet" class="logo">
            <h1>Réinitialisation de mot de passe</h1>
        </div>
        
        <div class="content">
            <div class="greeting">Bonjour,</div>
            
            <p class="message">
                Nous avons reçu une demande de réinitialisation de mot de passe pour votre compte Bailleurnet. 
                Si vous êtes à l'origine de cette demande, cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe.
            </p>
            
            <div class="security-notice">
                <h3>Sécurité de votre compte</h3>
                <p>Si vous n'avez pas demandé cette réinitialisation, ignorez simplement cet email. Votre mot de passe actuel restera inchangé.</p>
            </div>
            
            <div class="button-container">
                <a href="{{ $url }}" class="btn">
                    Réinitialiser mon mot de passe
                </a>
            </div>
            
            <div class="expiry-notice">
                <strong>Ce lien expire dans 60 minutes</strong>
                <p>Pour votre sécurité, ce lien de réinitialisation n'est valide que pendant une heure.</p>
            </div>
            
            <div class="alternative-link">
                <h4>Lien ne fonctionne pas ?</h4>
                <p>Copiez et collez cette adresse dans votre navigateur :</p>
                <p>{{ $url }}</p>
            </div>
            
            <div class="divider"></div>
            
            <p class="message">
                <strong>Conseils de sécurité :</strong><br>
                • Choisissez un mot de passe fort contenant au moins 8 caractères<br>
                • Utilisez une combinaison de lettres, chiffres et symboles<br>
                • Ne partagez jamais votre mot de passe avec personne
            </p>
        </div>
        
        <div class="footer">
            <p>Si vous avez des questions ou besoin d'aide, contactez notre équipe support.</p>
            <p class="signature">
                Cordialement,<br>
                <strong>L'équipe Bailleurnet</strong>
            </p>
        </div>
    </div>
</body>
</html>