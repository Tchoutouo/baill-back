<script src="https://js.stripe.com/v3/"></script>
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
</script>

{{-- <script type="text/javascript" src="https://fr.monetbil.com/widget/v2/monetbil.min.js"></script>

<form action="" method="get" data-monetbil="form"><button class="" type="submit">Pay by Mobile Money</button></form> --}}