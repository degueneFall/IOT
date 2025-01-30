<?php
// Paramètres de connexion à la base de données
$host = "mysql-degs.alwaysdata.net";
$dbname = "degs_portesimulation";
$username = "degs_examen";
$password = "passer1234";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Déterminer la date à afficher (par défaut : aujourd'hui)
$dateFiltre = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Requête pour récupérer les états de la porte filtrés par date
$queryHistorique = "SELECT etat, date FROM etat_porte WHERE DATE(date) = :date ORDER BY date DESC";
$stmtHistorique = $pdo->prepare($queryHistorique);
$stmtHistorique->bindParam(':date', $dateFiltre);
$stmtHistorique->execute();
$historique = $stmtHistorique->fetchAll(PDO::FETCH_ASSOC);

// Requête pour compter le nombre d'ouvertures à la date donnée
$queryOuvertures = "SELECT COUNT(*) AS ouvertures FROM etat_porte WHERE DATE(date) = :date AND etat = 'ouverte'";
$stmtOuvertures = $pdo->prepare($queryOuvertures);
$stmtOuvertures->bindParam(':date', $dateFiltre);
$stmtOuvertures->execute();
$ouvertures = $stmtOuvertures->fetch(PDO::FETCH_ASSOC)['ouvertures'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des États de la Porte</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 50px auto;
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #4CAF50;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            text-align: left;
            padding: 12px;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .form-filter {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-filter input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-filter button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-filter button:hover {
            background-color: #45a049;
        }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #FF9800;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-back:hover {
            background-color: #e68900;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Historique des États de la Porte</h1>

        <!-- Formulaire de filtrage -->
        <form method="GET" class="form-filter">
            <label for="date">Filtrer par date :</label>
            <input type="date" name="date" id="date" value="<?php echo $dateFiltre; ?>">
            <button type="submit">Filtrer</button>
        </form>

        <!-- Résumé des ouvertures -->
        <p>
            <strong>Date sélectionnée :</strong> <?php echo date("d-m-Y", strtotime($dateFiltre)); ?><br>
            <strong>Nombre d'ouvertures :</strong> <?php echo $ouvertures; ?>
        </p>

        <!-- Tableau de l'historique -->
        <table>
            <thead>
                <tr>
                    <th>État</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($historique)): ?>
                    <?php foreach ($historique as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['etat']); ?></td>
                            <td><?php echo date("d-m-Y H:i:s", strtotime($row['date'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" style="text-align: center;">Aucun état trouvé pour cette date.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Bouton Retour -->
        <div class="text-center">
            <a href="index.php" class="btn-back">Retour</a>
        </div>
    </div>
</body>
</html>
