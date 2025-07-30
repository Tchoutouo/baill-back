<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vos identifiants de connexion - Bailleurnet</title>
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
        
        .welcome-notice {
            background: #f8f9fa;
            border-left: 4px solid #5EB21E;
            padding: 20px;
            margin: 30px 0;
            border-radius: 0 8px 8px 0;
        }
        
        .welcome-notice h3 {
            color: #5EB21E;
            font-size: 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .welcome-notice h3::before {
            content: "🎉";
            margin-right: 8px;
        }
        
        .welcome-notice p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        
        .credentials-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #5EB21E;
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
            text-align: center;
        }
        
        .credentials-box h3 {
            color: #24583F;
            font-size: 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .credentials-box h3::before {
            content: "🔑";
            margin-right: 8px;
        }
        
        .credential-item {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .credential-label {
            font-weight: 600;
            color: #24583F;
            font-size: 14px;
        }
        
        .credential-value {
            font-weight: bold;
            color: #5EB21E;
            font-size: 16px;
            font-family: monospace;
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 4px;
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
        
        .security-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 20px;
            border-radius: 12px;
            margin: 30px 0;
        }
        
        .security-warning::before {
            content: "⚠️";
            font-size: 20px;
            display: block;
            margin-bottom: 10px;
        }
        
        .security-warning strong {
            color: #dc3545;
            font-size: 16px;
        }
        
        .first-login-notice {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 20px;
            margin: 30px 0;
            border-radius: 0 8px 8px 0;
        }
        
        .first-login-notice h4 {
            color: #1976d2;
            font-size: 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .first-login-notice h4::before {
            content: "💡";
            margin-right: 8px;
        }
        
        .first-login-notice p {
            color: #1565c0;
            font-size: 14px;
            margin: 5px 0;
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
            
            .credential-item {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .credential-value {
                word-break: break-all;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{{asset('images/bailleurnet.png')}}" alt="Bailleurnet" class="logo">
            <h1>Vos identifiants de connexion</h1>
        </div>
        
        <div class="content">
            <div class="greeting">Hello {{ $name }},</div>
            
            <div class="welcome-notice">
                <h3>Bienvenue sur Bailleurnet !</h3>
                <p>Nous sommes ravis de vous compter parmi nos utilisateurs. Votre compte a été créé avec succès.</p>
            </div>
            
            <p class="message">
                Vous trouverez ci-dessous vos identifiants de connexion pour accéder à votre espace personnel sur Bailleurnet.
            </p>
            
            <div class="credentials-box">
                <h3>Vos identifiants de connexion</h3>
                
                <div class="credential-item">
                    <span class="credential-label">Email :</span>
                    <span class="credential-value">{{ $email }}</span>
                </div>
                
                <div class="credential-item">
                    <span class="credential-label">Mot de passe temporaire :</span>
                    <span class="credential-value">{{ $firstPassword }}</span>
                </div>
            </div>
            
            <div class="button-container">
                <a href="https://test.bailleurnet.com/signin" class="btn">
                    Se connecter maintenant
                </a>
            </div>
            
            <div class="first-login-notice">
                <h4>Première connexion</h4>
                <p>• Nous vous recommandons de changer votre mot de passe temporaire lors de votre première connexion</p>
                <p>• Conservez vos identifiants en lieu sûr</p>
                <p>• En cas de problème, contactez notre équipe support</p>
            </div>
            
            <div class="security-warning">
                <strong>Important pour votre sécurité</strong>
                <p>Si vous n'êtes pas à l'origine de la création de ce compte, veuillez ignorer cet email et nous contacter immédiatement.</p>
            </div>
            
            <div class="divider"></div>
            
            <p class="message">
                <strong>Conseils de sécurité :</strong><br>
                • Changez votre mot de passe temporaire dès votre première connexion<br>
                • Choisissez un mot de passe fort contenant au moins 8 caractères<br>
                • Ne partagez jamais vos identifiants avec personne<br>
                • Déconnectez-vous toujours après utilisation sur un ordinateur partagé
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