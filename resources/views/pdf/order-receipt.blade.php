<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $forClient ? 'Reçu' : 'Facture' }} {{ $order->order_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1c1917; }
        .header { width: 100%; margin-bottom: 30px; }
        .header td { vertical-align: top; }
        .logo { height: 36px; }
        .title { font-size: 22px; font-weight: bold; text-align: right; color: #1c1917; }
        .subtitle { text-align: right; color: #78716c; font-size: 11px; }
        .parties { width: 100%; margin-bottom: 30px; }
        .parties td { vertical-align: top; width: 50%; padding-right: 20px; }
        .parties h3 { font-size: 10px; text-transform: uppercase; color: #a8a29e; margin: 0 0 6px 0; letter-spacing: 1px; }
        .parties p { margin: 0; line-height: 1.5; }
        table.lines { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.lines th { text-align: left; background: #f5f5f4; padding: 8px 10px; font-size: 10px; text-transform: uppercase; color: #78716c; border-bottom: 2px solid #e7e5e4; }
        table.lines td { padding: 10px; border-bottom: 1px solid #e7e5e4; }
        table.lines .amount { text-align: right; }
        .total-row td { font-weight: bold; font-size: 14px; border-top: 2px solid #1c1917; border-bottom: none; }
        .meta { width: 100%; margin-top: 10px; font-size: 11px; color: #57534e; }
        .meta td { padding: 3px 0; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 10px; background: #059669; color: white; font-size: 10px; font-weight: bold; }
        .footer { margin-top: 50px; padding-top: 15px; border-top: 1px solid #e7e5e4; font-size: 10px; color: #a8a29e; text-align: center; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width: 50%;">
                @if(file_exists(public_path('images/email-logo.png')))
                    <img src="{{ public_path('images/email-logo.png') }}" class="logo">
                @else
                    <strong style="font-size: 20px;">Azohub</strong>
                @endif
            </td>
            <td style="width: 50%;">
                <div class="title">{{ $forClient ? 'Reçu de paiement' : 'Facture de commission' }}</div>
                <div class="subtitle">N° {{ $order->order_number }}</div>
                <div class="subtitle">{{ now()->translatedFormat('d M Y') }}</div>
            </td>
        </tr>
    </table>

    <table class="parties">
        <tr>
            <td>
                <h3>Émis par</h3>
                <p>
                    <strong>Azohub</strong><br>
                    Cotonou, Bénin<br>
                    legal@azohub.bj
                </p>
            </td>
            <td>
                <h3>{{ $forClient ? 'Client' : 'Prestataire' }}</h3>
                <p>
                    <strong>{{ $forClient ? $order->client->name : $order->prestataire->name }}</strong><br>
                    {{ $forClient ? $order->client->email : $order->prestataire->email }}
                </p>
            </td>
        </tr>
    </table>

    <table class="lines">
        <thead>
            <tr>
                <th>Description</th>
                <th class="amount">Montant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ $order->display_title }}
                    <br>
                    <span style="color: #a8a29e; font-size: 10px;">
                        {{ $forClient ? 'Prestataire : ' . $order->prestataire->name : 'Client : ' . $order->client->name }}
                    </span>
                </td>
                <td class="amount">{{ number_format((float) $order->amount, 0, ',', ' ') }} FCFA</td>
            </tr>

            @if($forClient)
                <tr>
                    <td>Frais de service Azohub</td>
                    <td class="amount">{{ number_format((float) $order->client_fee, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr class="total-row">
                    <td>Total payé</td>
                    <td class="amount">{{ number_format((float) $order->total_charged, 0, ',', ' ') }} FCFA</td>
                </tr>
            @else
                <tr>
                    <td>Commission Azohub</td>
                    <td class="amount">- {{ number_format((float) $order->commission, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr class="total-row">
                    <td>Net {{ in_array($order->payment_status, ['released'], true) ? 'perçu' : 'à percevoir' }}</td>
                    <td class="amount">{{ number_format((float) $order->prestataire_amount, 0, ',', ' ') }} FCFA</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="meta">
        <tr>
            <td style="width: 160px;"><strong>Statut du paiement</strong></td>
            <td>
                <span class="status-badge">{{ \App\Filament\Resources\Orders\Tables\OrdersTable::PAYMENT_STATUSES[$order->payment_status] ?? $order->payment_status }}</span>
            </td>
        </tr>
        @if($payment)
            <tr>
                <td><strong>Moyen de paiement</strong></td>
                <td>{{ \App\Filament\Resources\Payments\Tables\PaymentsTable::METHODS[$payment->payment_method] ?? $payment->payment_method }}</td>
            </tr>
            <tr>
                <td><strong>Référence transaction</strong></td>
                <td>{{ $payment->transaction_id }}</td>
            </tr>
            @if($payment->paid_at)
                <tr>
                    <td><strong>Payé le</strong></td>
                    <td>{{ $payment->paid_at->format('d/m/Y à H:i') }}</td>
                </tr>
            @endif
        @endif
    </table>

    <div class="footer">
        Azohub — Plateforme de mise en relation de services au Bénin — azohub.bj<br>
        Ce document est un reçu récapitulatif, pas une facture fiscale au sens du droit béninois.
    </div>
</body>
</html>
