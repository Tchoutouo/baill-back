<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe</title>
    <style>
        .btn{
            background: green;
            border-radius: 8px;
            padding: 5px;
            color: white;
            text-decoration: none;
            box-shadow: 0 3px 6px rgba(0,0, 0,0.1)
        }
    </style>
</head>
<body>
    <div>
        <img src="{{asset('images/bailleurnet.png')}}" alt="Bailleurnet" style="margin: 10px;">
        <p>Salut,</p>
        
        <p>Vous avez demandé une réinitialisation de votre mot de passe.</p>
        
        <p>Cliquez sur le lien ci-dessus</p>
    
        {{-- <h1 style="font-weight: bold; color:green">{{ $newpassword }}</h1> --}}
        <a href="{{ $url }}" class="btn" style="color: white; font-weight:bold"> Réinitialiser le mot de passe </a>
    
        <p style="color: red">Ce lien expire dans 60 minutes</p><br> 

        <p>Cordialement l'équipe support</p>
    </div>
</body>
</html>
