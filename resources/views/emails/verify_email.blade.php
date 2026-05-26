<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activez votre compte - Bailleurnet</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f4f6f8;
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
        .header h1 { font-size: 26px; font-weight: 400; letter-spacing: 0.5px; }
        .header p { margin-top: 8px; font-size: 14px; opacity: 0.85; }
        .content { padding: 50px 40px; }
        .greeting { font-size: 20px; font-weight: 600; color: #24583F; margin-bottom: 20px; }
        .message { font-size: 16px; line-height: 1.7; color: #555; margin-bottom: 20px; }
        .info-box {
            background: #f0faf0;
            border-left: 4px solid #5EB21E;
            padding: 18px 20px;
            border-radius: 0 8px 8px 0;
            margin: 24px 0;
        }
        .info-box p { color: #24583F; font-size: 14px; line-height: 1.6; margin: 0; }
        .button-container { text-align: center; margin: 36px 0; }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #5EB21E 0%, #24583F 100%);
            color: white !important;
            text-decoration: none;
            padding: 18px 44px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            letter-spacing: 0.5px;
            box-shadow: 0 8px 20px rgba(94, 178, 30, 0.35);
        }
        .expiry-notice {
            background: #fff8e1;
            border: 1px solid #ffe082;
            color: #856404;
            padding: 16px 20px;
            border-radius: 10px;
            margin: 24px 0;
            text-align: center;
            font-size: 14px;
        }
        .expiry-notice strong { color: #e65100; }
        .alternative-link {
            background: #f8f9fa;
            border: 1px dashed #dee2e6;
            padding: 18px 20px;
            border-radius: 8px;
            margin: 24px 0;
        }
        .alternative-link h4 { color: #6c757d; font-size: 13px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .alternative-link p { word-break: break-all; font-size: 12px; color: #6c757d; margin: 0; }
        .divider { height: 1px; background: linear-gradient(90deg, transparent, #dee2e6, transparent); margin: 28px 0; }
        .footer { background: #f8f9fa; padding: 28px 40px; text-align: center; border-top: 1px solid #dee2e6; }
        .footer p { color: #6c757d; font-size: 13px; margin: 8px 0; }
        .footer .signature { font-weight: 600; color: #5EB21E; margin-top: 16px; }
        @media (max-width: 600px) {
            .email-container { border-radius: 0; }
            .content { padding: 30px 20px; }
            .header { padding: 30px 20px; }
            .btn { padding: 15px 30px; font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Activez votre compte</h1>
            <p>Bienvenue sur Bailleurnet !</p>
        </div>

        <div class="content">
            <div class="greeting">Bonjour {{ $username }},</div>

            <p class="message">
                Merci de vous être inscrit sur <strong>Bailleurnet</strong>. Votre compte a bien été créé.
                Il ne vous reste plus qu'une étape : confirmer votre adresse e-mail pour activer votre compte.
            </p>

            <div class="info-box">
                <p>
                    En activant votre compte, vous aurez accès à toutes les fonctionnalités de la plateforme :
                    publier des annonces, gérer vos locations et bien plus encore.
                </p>
            </div>

            <div class="button-container">
                <a href="{{ $verifyUrl }}" class="btn">
                    Activer mon compte
                </a>
            </div>

            <div class="expiry-notice">
                <strong>Ce lien expire dans 24 heures.</strong><br>
                Passé ce délai, vous devrez créer un nouveau compte.
            </div>

            <div class="alternative-link">
                <h4>Le bouton ne fonctionne pas ?</h4>
                <p>Copiez et collez ce lien dans votre navigateur :</p>
                <p>{{ $verifyUrl }}</p>
            </div>

            <div class="divider"></div>

            <p class="message" style="font-size: 14px; color: #888;">
                Si vous n'avez pas créé de compte sur Bailleurnet, ignorez simplement cet email.
            </p>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé à {{ $email }}</p>
            <p class="signature">
                Cordialement,<br>
                <strong>L'équipe Bailleurnet</strong>
            </p>
        </div>
    </div>
</body>
</html>
