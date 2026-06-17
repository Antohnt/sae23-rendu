<?php
session_start();

// Access control: gestionnaire or admin only
if (!isset($_SESSION['login']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'gestionnaire')) {
    header("Location: connexion.php");
    exit();
}

$connexion = mysqli_connect("localhost", "root", "", "sae23");
if (!$connexion) {
    die("Database connection error.");
}

// If gestionnaire, restrict to their building
$restriction_batiment = "";
if ($_SESSION['role'] == 'gestionnaire' && isset($_SESSION['id_batiment'])) {
    $id_bat = intval($_SESSION['id_batiment']);
    $restriction_batiment = "AND b.id_batiment = $id_bat";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion - SAE 23</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="consultation.php">Consultation</a>
        <a href="gestion.php">Gestion</a>
        <a href="admin.php">Administration</a>
        <a href="projet.php">Projet</a>
        <?php if (isset($_SESSION['login'])) { ?>
            <a href="connexion.php?logout=1">Deconnexion (<?php echo htmlspecialchars($_SESSION['login']); ?>)</a>
        <?php } else { ?>
            <a href="connexion.php">Connexion</a>
        <?php } ?>
    </nav>

    <div class="container">
        <h1>Gestion des mesures</h1>
        <p>Connecte en tant que : <strong><?php echo htmlspecialchars($_SESSION['login']); ?></strong> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>

        <h2>Statistiques par capteur</h2>
        <table>
            <thead>
                <tr>
                    <th>Batiment</th>
                    <th>Salle</th>
                    <th>Capteur</th>
                    <th>Type</th>
                    <th>Minimum</th>
                    <th>Maximum</th>
                    <th>Moyenne</th>
                    <th>Nb mesures</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get min, max, avg per sensor
                $requete_stats = "SELECT
                    b.nom AS batiment,
                    s.nom AS salle,
                    c.nom AS capteur,
                    c.type_capteur,
                    c.unite,
                    MIN(m.valeur) AS min_val,
                    MAX(m.valeur) AS max_val,
                    ROUND(AVG(m.valeur), 2) AS avg_val,
                    COUNT(m.id_mesure) AS nb_mesures
                FROM Capteur c
                JOIN Salle s ON c.id_salle = s.id_salle
                JOIN Batiment b ON s.id_batiment = b.id_batiment
                LEFT JOIN Mesure m ON m.id_capteur = c.id_capteur
                WHERE 1=1 $restriction_batiment
                GROUP BY c.id_capteur
                ORDER BY b.nom, s.nom, c.type_capteur";

                $resultat_stats = mysqli_query($connexion, $requete_stats);

                while ($ligne = mysqli_fetch_assoc($resultat_stats)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($ligne['batiment']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['salle']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['capteur']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['type_capteur']) . "</td>";

                    if ($ligne['nb_mesures'] > 0) {
                        echo "<td>" . htmlspecialchars($ligne['min_val']) . " " . htmlspecialchars($ligne['unite']) . "</td>";
                        echo "<td>" . htmlspecialchars($ligne['max_val']) . " " . htmlspecialchars($ligne['unite']) . "</td>";
                        echo "<td>" . htmlspecialchars($ligne['avg_val']) . " " . htmlspecialchars($ligne['unite']) . "</td>";
                        echo "<td>" . htmlspecialchars($ligne['nb_mesures']) . "</td>";
                    } else {
                        echo "<td colspan='4'>Aucune mesure</td>";
                    }

                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>Dernieres mesures</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Batiment</th>
                    <th>Salle</th>
                    <th>Capteur</th>
                    <th>Valeur</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get last 50 measurements
                $requete_mesures = "SELECT
                    m.date_mesure,
                    m.heure_mesure,
                    m.valeur,
                    b.nom AS batiment,
                    s.nom AS salle,
                    c.nom AS capteur,
                    c.unite
                FROM Mesure m
                JOIN Capteur c ON m.id_capteur = c.id_capteur
                JOIN Salle s ON c.id_salle = s.id_salle
                JOIN Batiment b ON s.id_batiment = b.id_batiment
                WHERE 1=1 $restriction_batiment
                ORDER BY m.date_mesure DESC, m.heure_mesure DESC
                LIMIT 50";

                $resultat_mesures = mysqli_query($connexion, $requete_mesures);

                while ($ligne = mysqli_fetch_assoc($resultat_mesures)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($ligne['date_mesure']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['heure_mesure']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['batiment']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['salle']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['capteur']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['valeur']) . " " . htmlspecialchars($ligne['unite']) . "</td>";
                    echo "</tr>";
                }

                mysqli_close($connexion);
                ?>
            </tbody>
        </table>

        <footer>
            <p>SAE 23 - BUT R&T - IUT de Blagnac - 2026</p>
        </footer>
    </div>
</body>
</html>
