{{-- resources/views/invoices/commande.blade.php --}}
<!DOCTYPE html>
<html>
<head>
  <style>
    body { font-family: DejaVu Sans, sans-serif; }
    .header { text-align: center; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; }
  </style>
</head>
<body>
  <div class="header">
    <h1>Facture #{{ $commande->id }}</h1>
    <p>Date : {{ now()->format('d/m/Y') }}</p>
  </div>
  <p><strong>Client :</strong> {{ $commande->nom }} – {{ $commande->email }}</p>
  <table>
    <thead>
      <tr>
        <th>Produit</th>
        <th>Quantité</th>
        <th>Prix unitaire</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>{{ $commande->produit->nom }}</td>
        <td>{{ $commande->quantite }}</td>
        <td>€{{ number_format($commande->produit->prix, 2) }}</td>
        <td>€{{ number_format($commande->prix_total, 2) }}</td>
      </tr>
    </tbody>
  </table>
  <h3>Total à payer : €{{ number_format($commande->prix_total, 2) }}</h3>
</body>
</html>
