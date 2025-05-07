<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle inscription</title>
</head>
<body>
    <div>
        <img src="{{asset('images/bailleurnet.png')}}" alt="Bailleurnet" style="margin: 10px;">
        <p>Salut,</p>
        
        <p>Vous avez reçu un nouveau paiement sur votre compte {{ $mode_paiement }}</p>
        <h3 style="font-weight: bold">Détails du paiement </h3>
        <p>- Intitulé de l'annonce : <strong>{{ $intitule }}</strong></p>
        <p>- Type d'abonnement : <strong>{{ $typeAbonnement }}</strong></p>
        <p>- Montant : <strong>{{ $amount }}</strong> XAF</p>
    </div>
</body>
</html>
