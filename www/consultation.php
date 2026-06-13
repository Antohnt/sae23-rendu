<?php
session_start();

$connexion = mysqli_connect("localhost", "root", "sae23", "sae23");
if (!$connexion) {
    echo "Erreur connexion base de donnees.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation - SAE 23</title>
    <meta http-equiv="refresh" content="60">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="consultation.php">Consultation</a>
        <a href="graphiques.php">Graphiques</a>
        <a href="gestion.php">Gestion</a>
        <a href="admin.php">Administration</a>
        <a href="projet.php">Projet</a><?php if (isset($_SESSION['login'])) { ?>
            <a href="connexion.php?logout=1">Deconnexion (<?php echo htmlspecialchars($_SESSION['login']); ?>)</a>
        <?php } else { ?>
            <a href="connexion.php">Connexion</a>
        <?php } ?>
    </nav>

    <div class="container">
        <h1>Consultation des capteurs</h1>
        <p>Dernieres mesures enregistrees pour chaque capteur.</p>

        <table>
            <thead>
                <tr>
                    <th>Batiment</th>
                    <th>Salle</th>
                    <th>Capteur</th>
                    <th>Type</th>
                    <th>Derniere valeur</th>
                    <th>Date</th>
                    <th>Heure</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // last value for each
                $requete = "SELECT
                    b.nom AS batiment,
                    s.nom AS salle,
                    c.nom AS capteur,
                    c.type_capteur,
                    c.unite,
                    m.valeur,
                    m.date_mesure,
                    m.heure_mesure
                FROM Capteur c
                JOIN Salle s ON c.id_salle = s.id_salle
                JOIN Batiment b ON s.id_batiment = b.id_batiment
                LEFT JOIN Mesure m ON m.id_capteur = c.id_capteur
                    AND m.id_mesure = (
                        SELECT m2.id_mesure
                        FROM Mesure m2
                        WHERE m2.id_capteur = c.id_capteur
                        ORDER BY m2.date_mesure DESC, m2.heure_mesure DESC
                        LIMIT 1
                    )
                ORDER BY b.nom, s.nom, c.type_capteur";

                $resultat = mysqli_query($connexion, $requete);

                while ($ligne = mysqli_fetch_assoc($resultat)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($ligne['batiment']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['salle']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['capteur']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['type_capteur']) . "</td>";

                    if ($ligne['valeur'] !== null) {
                        echo "<td>" . htmlspecialchars($ligne['valeur']) . " " . htmlspecialchars($ligne['unite']) . "</td>";
                        echo "<td>" . htmlspecialchars($ligne['date_mesure']) . "</td>";
                        echo "<td>" . htmlspecialchars($ligne['heure_mesure']) . "</td>";
                    } else {
                        echo "<td colspan='3'>Aucune mesure</td>";
                    }

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
