<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avis des utilisateurs</title>
</head>
<body>
    <div>
        <img src="{{asset('images/bailleurnet.png')}}" alt="Bailleurnet" style="margin: 10px;"><br><br>
        <h3 style="font-weight: bold">Informations du contact </h3>
        <p>- Nom : <strong>{{ $name }}</strong></p>
        <p>- Email : <strong>{{ $email }}</strong></p>
        <p>- Numéro : <strong>{{ $phone }}</strong></p>

        <br>
        <h3 style="font-weight: bold">Message </h3>
        <p>{{ $msg }}</p>
    </div>
</body>
</html>
