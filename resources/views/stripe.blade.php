
<?php

set_time_limit(360);
$url = "https://demo.campay.net/api/token/";
$token = "67dc6177efdcf6ae9ce4919918a6e382a423c861";
$data = [
    "username" => "5EOl8CQAXIeRlU8q0PA2GlKVekBcafJcCBP70321_bQ9PxV6T76f2goAL-IOmg9jMyVWdH69zVSzwhE697QLcQ",
    "password" => "YWfSi6jeoHtiXapsxUJfdfOoUVMm9KuQkgngOszn6DeH5CQYMZVCckOSnmiYFvooLUN9f1nz7j4kL6U3oh1kzw"
];

$options = [
    "http" => [
        "header"  => "Content-Type: application/json\r\n","Authorization: Bearer " . $token . "\r\n",
        "method"  => "POST",
        "content" => json_encode($data)
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    die("Erreur lors de la requête d'authentification.");
}

$token_data = json_decode($response, true);
$token = $token_data['token'];

// dd("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsInVpZCI6Mn0.eyJpYXQiOjE2MDM4MjQyODMsIm5iZiI6MTYwMzgyNDI4MywiZXhwIjoxNjAzODI3ODgzfQ.ufW8sCrf_W2RFpVvH6zri0l7pJLnkPXCZi1zc10ZvOg",$token); 

$url = "https://demo.campay.net/api/collect/";
$data = [
    "amount" => 100, // Montant en XAF
    "currency" => "XAF", // Numéro de téléphone
    "from" => "237653021876", // Numéro de téléphone
    "description" => "Paiement de test"
];

$options = [
    "http" => [
        "header"  => "Content-Type: application/json\r\n" .
                     "Authorization: Token  " . $token . "\r\n",
        "method"  => "POST",
        "content" => json_encode($data)
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    die("Erreur lors de la requête de collecte.");
}

$response_data = json_decode($response, true);
dd($response_data);
?>


{{-- <script src="https://js.stripe.com/v3/"></script>
<form id="payment-form">
    <div id="card-element"></div>
    <button id="submit">Payer</button>
    <div id="error-message"></div>
</form>

<script>
    const stripe = Stripe('pk_test_51QeaLSQrON1MZ63RYWzjlt0JVdwTLMvfZi4duXNpyeqiP4ENhDsJbjUW4qI6ZqPyMArw6XCyRACLA1gn7Dc0hpjX00tSz8i5wx'); // Votre clé publique Stripe
    const elements = stripe.elements();
    const cardElement = elements.create('card');
    cardElement.mount('#card-element');

    const form = document.getElementById('payment-form');
    form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const { paymentMethod, error } = await stripe.createPaymentMethod({
        type: 'card',
        card: cardElement,
    });
    console.log("paiement",paymentMethod.id);
    
    if (error) {
        document.getElementById('error-message').textContent = error.message;
    } else {
        // Envoyer le paymentMethod.id dans mon backend
        fetch("{{ url('/api/stripe-payment') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ payment_method: paymentMethod.id, 
                                    amount: 5000, 
                                    abonnement_id : '3',
                                    user_id : '3',
                                    whatsapp_number : '237693124855',
                                }),
        })
        .then(response => response.json())
        .then(data => console.log(data))
        .catch(error => console.error('Erreur :', error));
    }
    });
</script> --}}

{{-- <script type="text/javascript" src="https://fr.monetbil.com/widget/v2/monetbil.min.js"></script>

<form action="" method="get" data-monetbil="form"><button class="" type="submit">Pay by Mobile Money</button></form> --}}