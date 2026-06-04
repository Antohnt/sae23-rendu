<?php
// Start session for navbar (public home page, no access restriction)
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAE 23 - Capteurs IoT</title>
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
        <h1>SAE 23 - Supervision des capteurs IoT</h1>

        <h2>Presentation du projet</h2>
        <p>
            Ce site permet de visualiser les donnees des capteurs repartis dans les batiments de l'IUT de Blagnac.
            Les capteurs mesurent la temperature, l'humidite, le CO2 et la luminosite des salles.
            Les donnees sont recuperees en temps reel via le protocole MQTT.
        </p>

<?php
        // Connect to database for dynamic data
        $connexion = mysqli_connect("localhost", "root", "", "sae23");

        // Query buildings with room count
        $requete_batiments = "SELECT b.nom, b.adresse, COUNT(s.id_salle) AS nb_salles
                              FROM Batiment b
                              LEFT JOIN Salle s ON b.id_batiment = s.id_batiment
                              GROUP BY b.id_batiment";
        $resultat_batiments = mysqli_query($connexion, $requete_batiments);

        // Query rooms with sensors
        $requete_salles = "SELECT s.nom, s.etage, GROUP_CONCAT(DISTINCT c.type_capteur SEPARATOR ', ') AS capteurs
                            FROM Salle s
                            LEFT JOIN Capteur c ON s.id_salle = c.id_salle
                            GROUP BY s.id_salle";
        $resultat_salles = mysqli_query($connexion, $requete_salles);
        ?>
        <h2>Batiments</h2>
        <table>
            <thead>
                <tr>
                    <th>Batiment</th>
                    <th>Adresse</th>
                    <th>Nombre de salles</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($batiment = mysqli_fetch_assoc($resultat_batiments)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($batiment['nom']); ?></td>
                    <td><?php echo htmlspecialchars($batiment['adresse']); ?></td>
                    <td><?php echo $batiment['nb_salles']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <h2>Salles equipees</h2>
        <table>
            <thead>
                <tr>
                    <th>Salle</th>
                    <th>Etage</th>
                    <th>Capteurs</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($salle = mysqli_fetch_assoc($resultat_salles)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($salle['nom']); ?></td>
                    <td><?php echo $salle['etage']; ?></td>
                    <td><?php echo htmlspecialchars($salle['capteurs'] ? $salle['capteurs'] : '-'); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php mysqli_close($connexion); ?>

        <footer>
            <p>SAE 23 - BUT R&T - IUT de Blagnac - 2026</p>
            <p>Projet realise par Antonin Marchand et Timothee Jean-Pierre</p>
        </footer>
    </div>
</body>
</html>
