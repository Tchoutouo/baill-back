<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe</title>
</head>
<body>
    <div>
        <img src="{{asset('images/bailleurnet.png')}}" alt="Bailleurnet" style="margin: 10px;">
        <p>Salut,</p>
        
        <p>Vous avez demandé une réinitialisation de votre mot de passe.</p>
        
        <p>Vous trouverez ci-dessous votre nouveau mot de passe :</p>
    
        <h1 style="font-weight: bold; color:green">{{ $newpassword }}</h1>
    
        <p>Cordialement l'équipe support</p>
    </div>
</body>
</html>
