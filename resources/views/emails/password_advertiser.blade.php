<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identifiants de connexion</title>
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
        <img src="{{asset('images/logo.jpg')}}" alt="Bailleurnet" style="margin: 10px;">
        <p>Hello {{ $name }},</p>
        
        <p>Nous sommes ravi de vous comptez parmis nos utilisateurs.</p>
        
        <p>Vous trouverez ci-dessous vos identifiants de connexion.</p>
    
        <p> Email : <strong style="font-weight: bold; color:green">{{ $email }}</strong></p>
        <p> Password : <strong style="font-weight: bold; color:green">{{ $firstPassword }}</strong></p>

        <a href="https://www.bailleurnet.com/signin"style="font-weight:bold"> Cliquez sur ce lien pour vous connectez. </a>
    
        <p style="color: red">Veuillez ignorer ce mail si l'action n'a pas été affectée par vous.</p><br> 

        <p>Cordialement l'équipe support</p>
    </div>
</body>
</html>
