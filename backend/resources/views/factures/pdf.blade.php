<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture - {{ $facture->numero_facture }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
            margin: 35px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 4px 0;
        }

        .section {
            margin-bottom: 18px;
        }

        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
            border-bottom: 1px solid #999;
            padding-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 7px;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
        }

        .montants {
            width: 50%;
            margin-left: auto;
            margin-top: 20px;
        }

        .montants td:first-child {
            font-weight: bold;
        }

        .total-net {
            font-weight: bold;
            font-size: 14px;
        }

        .footer {
            margin-top: 35px;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>VITALIS</h1>
        <p>Clinique Médicale</p>
        <p><strong>FACTURE</strong></p>
        <p>N° {{ $facture->numero_facture }}</p>
    </div>

    <div class="section">
        <div class="section-title">Informations de la facture</div>

        <p>
            <strong>Date :</strong>
            {{ $facture->date_facture }}
        </p>

        <p>
            <strong>Statut :</strong>
            {{ ucfirst($facture->statut_paiement) }}
        </p>
    </div>

    <div class="section">
        <div class="section-title">Informations du patient</div>

        <p>
            <strong>Nom :</strong>
            {{ $patient?->nom }} {{ $patient?->prenom }}
        </p>

        <p>
            <strong>CIN :</strong>
            {{ $patient?->cin }}
        </p>

        <p>
            <strong>Téléphone :</strong>
            {{ $patient?->telephone }}
        </p>
    </div>

    <div class="section">
        <div class="section-title">Médecin</div>

        <p>
            <strong>Dr :</strong>
            {{ $medecin?->nom }} {{ $medecin?->prenom }}
        </p>

        <p>
            <strong>Matricule :</strong>
            {{ $medecin?->matricule }}
        </p>
    </div>

    <div class="section">
        <div class="section-title">Détails de la prestation</div>

        @if ($facture->consultation)
            <p>
                <strong>Consultation :</strong>
                N° {{ $facture->id_consultation }}
            </p>

            <p>
                <strong>Tarif consultation :</strong>
                {{ number_format(
                    (float) ($facture->consultation?->rendezVous?->medecin?->tarif_consultation ?? 0),
                    2,
                    ',',
                    ' '
                ) }} DH
            </p>
        @endif

        @if ($facture->hospitalisation)
            <p>
                <strong>Hospitalisation :</strong>
                N° {{ $facture->id_hospitalisation }}
            </p>

            <p>
                <strong>Date d'entrée :</strong>
                {{ $facture->hospitalisation->date_entree }}
            </p>

            <p>
                <strong>Date de sortie :</strong>
                {{ $facture->hospitalisation->date_sortie ?? 'En cours' }}
            </p>

            <p>
                <strong>Tarif journalier de la chambre :</strong>
                {{ number_format(
                    (float) ($facture->hospitalisation?->chambre?->tarif_journalier ?? 0),
                    2,
                    ',',
                    ' '
                ) }} DH
            </p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Montants</div>

        <table class="montants">
            <tr>
                <td>Montant total</td>
                <td>
                    {{ number_format((float) $facture->montant_total, 2, ',', ' ') }} DH
                </td>
            </tr>

            <tr>
                <td>Remise</td>
                <td>
                    {{ number_format((float) $facture->remise, 2, ',', ' ') }} DH
                </td>
            </tr>

            <tr class="total-net">
                <td>Montant net</td>
                <td>
                    {{ number_format((float) $facture->montant_net, 2, ',', ' ') }} DH
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Paiements</div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Mode</th>
                    <th>Montant</th>
                    <th>Statut</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($facture->paiements as $paiement)
                    <tr>
                        <td>{{ $paiement->date_paiement }}</td>
                        <td>{{ $paiement->heure_paiement }}</td>
                        <td>{{ $paiement->mode_paiement }}</td>
                        <td>
                            {{ number_format((float) $paiement->montant_paye, 2, ',', ' ') }} DH
                        </td>
                        <td>{{ ucfirst($paiement->statut) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            Aucun paiement enregistré.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($facture->observations)
        <div class="section">
            <div class="section-title">Observations</div>

            <p>{{ $facture->observations }}</p>
        </div>
    @endif

    <div class="footer">
        <p>VITALIS - Clinique Médicale</p>
        <p>Facture générée électroniquement</p>
    </div>

</body>
</html>