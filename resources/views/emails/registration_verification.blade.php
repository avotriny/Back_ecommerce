{{-- resources/views/emails/registration_verification.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Finalisation de votre inscription</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container {
            max-width: 600px; margin: 40px auto; background: #fff;
            padding: 20px; border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .button {
            display: inline-block; background: #007bff;
            color: #fff !important; padding: 10px 20px;
            text-decoration: none; border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Finalisez votre inscription</h1>
        <p>
            Pour activer votre compte, cliquez sur le bouton ci‑dessous.
            Vous serez redirigé vers une page où vous entrerez votre code
            de confirmation et vos informations seront validées.
        </p>


        <a class="button" href="{{ $verificationUrl }}">Activer mon compte</a>
    </div>
</body>
</html>
