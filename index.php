<?php
// Paramètres de connexion à la base de données
$host = "mysql-degs.alwaysdata.net";
$dbname = "degs_portesimulation";
$username = "degs_examen";
$password = "passer1234";

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Récupérer le dernier état de la porte et la date
$queryEtat = "SELECT etat, date FROM etat_porte ORDER BY date DESC LIMIT 1";
$stmtEtat = $pdo->query($queryEtat);
$etatPorte = $stmtEtat->fetch(PDO::FETCH_ASSOC);

// Récupérer le nombre d'ouvertures aujourd'hui
$queryOuvertures = "SELECT COUNT(*) AS ouvertures_today FROM etat_porte WHERE DATE(date) = CURDATE() AND etat = 'ouverte'";
$stmtOuvertures = $pdo->query($queryOuvertures);
$ouvertures = $stmtOuvertures->fetch(PDO::FETCH_ASSOC);

$dernierEtat = $etatPorte ? $etatPorte['etat'] : "Non disponible";
$dateDernierEtat = $etatPorte ? $etatPorte['date'] : "Non disponible";
$nombreOuvertures = $ouvertures ? $ouvertures['ouvertures_today'] : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>État de la Porte - Suivi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-light: #f9f9f9;
            --bg-dark: #1e1e2f;
            --card-light: #fff;
            --card-dark: #2c2c3e;
            --text-light: #333;
            --text-dark: #f5f5f5;
            --primary: #4CAF50;
            --secondary: #FF9800;
            --hover-light: rgba(76, 175, 80, 0.1);
            --hover-dark: rgba(76, 175, 80, 0.2);
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
            color: var(--text-light);
            transition: background-color 0.3s, color 0.3s;
        }

        body.dark-mode {
            background-color: var(--bg-dark);
            color: var(--text-dark);
        }

        .container {
       width: 200;
            margin: 50px auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .card {
            background-color: var(--card-light);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        body.dark-mode .card {
            background-color: var(--card-dark);
        }

        .icon {
            font-size: 2rem;
            margin-right: 15px;
            color: var(--primary);
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary);
            color: white;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            font-size: 1rem;
        }

        .btn:hover {
            color: #000;
        }

        .btn i {
            margin-right: 8px;
        }

        .dark-mode-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .chart-container {
            margin: 40px 0;
            padding: 20px;
            background-color: var(--card-light);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: center;
            align-items: center;
            width: 400px;
            margin: auto;
        }

        body.dark-mode .chart-container {
            background-color: var(--card-dark);
        }

        .chart-container canvas {
            max-width: 250px;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 0.9em;
            color: var(--text-light);
        }

        body.dark-mode .footer {
            color: var(--text-dark);
        }

        @keyframes hoverEffect {
            from {
                transform: scale(1);
            }
            to {
                transform: scale(1.05);
            }
        }

        .card:hover {
            animation: hoverEffect 0.3s forwards;
        }
    </style>
</head>
<body>
    <button class="dark-mode-toggle" onclick="toggleDarkMode()">🌙</button>

    <div class="container">
        <h1>État de la Porte - Suivi en Temps Réel</h1>

        <!-- Dernier état de la porte -->
        <div class="card">
            <div>
                <span class="icon">🚪</span>
                <p><strong>Dernier état de la porte :</strong> <?= $dernierEtat; ?></p>
                <p><strong>Date :</strong> <?= date("d-m-Y H:i:s", strtotime($dateDernierEtat)); ?></p>
            </div>
        </div>

        <!-- Nombre d'ouvertures aujourd'hui -->
        <div class="card">
            <div>
                <span class="icon">📅</span>
                <p><strong>Nombre d'ouvertures aujourd'hui :</strong> <?= $nombreOuvertures; ?></p>
            </div>
        </div>

        <!-- Boutons -->
        <div class="text-center" style="margin-top: 20px;">
            <a href="javascript:window.location.reload();" class="btn"><i class="bi bi-arrow-clockwise"></i>Rafraîchir</a>
            <a href="historique.php" class="btn"><i class="bi bi-clock-history"></i>Historique</a>
        </div>

        <div class="footer">
            <p>Suivi de l'état de la porte</p>
        </div>
    </div>

    <script>
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
        }

        const ctx = document.getElementById('chart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Ouvertures aujourd\'hui'],
                datasets: [{
                    label: 'Ouvertures',
                    data: [<?= $nombreOuvertures; ?>, 100 - <?= $nombreOuvertures; ?>],
                    backgroundColor: ['#4CAF50', '#e0e0e0'],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                    },
                },
                cutout: '70%',
            },
        });
    </script>
</body>
</html>
