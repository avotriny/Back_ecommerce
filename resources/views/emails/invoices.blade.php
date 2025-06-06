{{-- resources/views/emails/invoice.blade.php --}}
<!DOCTYPE html>
<html>
<body>
  <p>Bonjour {{ $commande->nom }},</p>
  <p>Merci pour votre commande n°{{ $commande->id }}. Vous trouverez votre facture en pièce jointe.</p>
  <p>Cordialement,<br>L’équipe de Votre Société</p>
</body>
</html>
