<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ordonnance médicale</title>

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
            font-size: 22px;
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

        .footer {
            margin-top: 35px;
            text-align: right;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>VITALIS</h1>
        <p>Ordonnance médicale</p>
        <p>N° {{ $ordonnance->id_ordonnance }}</p>
    </div>

    <div class="section">
        <div class="section-title">Informations du patient</div>

        <p>
            <strong>Nom :</strong>
            {{ $patient?->nom }} {{ $patient?->prenom }}
        </p>

        <p>
            <strong>Date de naissance :</strong>
            {{ $patient?->date_naissance }}
        </p>

        <p>
            <strong>CIN :</strong>
            {{ $patient?->cin }}
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
        <div class="section-title">Ordonnance</div>

        <p>
            <strong>Date :</strong>
            {{ $ordonnance->date_ordonnance }}
        </p>

        <p>
            <strong>Type :</strong>
            {{ $ordonnance->type }}
        </p>

        <p>
            <strong>Durée du traitement :</strong>
            {{ $ordonnance->duree_traitement }}
        </p>

        <p>
            <strong>Instructions générales :</strong>
            {{ $ordonnance->instructions_generales }}
        </p>
    </div>

    <div class="section">
        <div class="section-title">Médicaments prescrits</div>

        <table>
            <thead>
                <tr>
                    <th>Médicament</th>
                    <th>Dosage</th>
                    <th>Dosologie</th>
                    <th>Fréquence</th>
                    <th>Durée</th>
                    <th>Quantité</th>
                    <th>Instructions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($ordonnance->lignes as $ligne)
                    <tr>
                        <td>
                            {{ $ligne->medicament?->nom_medicament }}
                        </td>

                        <td>
                            {{ $ligne->medicament?->dosage }}
                        </td>

                        <td>
                            {{ $ligne->dosologie }}
                        </td>

                        <td>
                            {{ $ligne->frequence }}
                        </td>

                        <td>
                            {{ $ligne->duree }}
                        </td>

                        <td>
                            {{ $ligne->quantite }}
                        </td>

                        <td>
                            {{ $ligne->instructions }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            Aucun médicament prescrit.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Signature du médecin</p>
    </div>

</body>
</html>