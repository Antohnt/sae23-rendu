<?php
session_start();

$connexion = mysqli_connect("localhost", "root", "sae23", "sae23");
if (!$connexion) {
    echo "Erreur connexion base de donnees.";
    exit();
}

// get all buildings for filter
$res_bat = mysqli_query($connexion, "SELECT * FROM Batiment ORDER BY nom");
$liste_batiments = array();
while ($b = mysqli_fetch_assoc($res_bat)) {
    $liste_batiments[] = $b;
}

// get all rooms for filter
$res_sal = mysqli_query($connexion, "SELECT * FROM Salle ORDER BY nom");
$liste_salles = array();
while ($s = mysqli_fetch_assoc($res_sal)) {
    $liste_salles[] = $s;
}

// filters from form
$filtre_batiment = isset($_GET['batiment']) ? $_GET['batiment'] : '';
$filtre_salle    = isset($_GET['salle'])    ? $_GET['salle']    : '';
$filtre_type     = isset($_GET['type'])     ? $_GET['type']     : '';

// build query with filters
$where = "WHERE 1=1";

if ($filtre_batiment != '') {
    $fb = mysqli_real_escape_string($connexion, $filtre_batiment);
    $where = $where . " AND b.nom = '$fb'";
}
if ($filtre_salle != '') {
    $fs = mysqli_real_escape_string($connexion, $filtre_salle);
    $where = $where . " AND s.nom = '$fs'";
}
if ($filtre_type != '') {
    $ft = mysqli_real_escape_string($connexion, $filtre_type);
    $where = $where . " AND c.type_capteur = '$ft'";
}

$requete = "SELECT
    m.date_mesure,
    m.heure_mesure,
    b.nom AS batiment,
    s.nom AS salle,
    c.type_capteur,
    m.valeur,
    c.unite
FROM Mesure m
JOIN Capteur c ON m.id_capteur = c.id_capteur
JOIN Salle s ON c.id_salle = s.id_salle
JOIN Batiment b ON s.id_batiment = b.id_batiment
$where
ORDER BY m.date_mesure DESC, m.heure_mesure DESC
LIMIT 500";

$resultat = mysqli_query($connexion, $requete);

// count total
$requete_count = "SELECT COUNT(*) as nb FROM Mesure m
JOIN Capteur c ON m.id_capteur = c.id_capteur
JOIN Salle s ON c.id_salle = s.id_salle
JOIN Batiment b ON s.id_batiment = b.id_batiment
$where";
$res_count = mysqli_query($connexion, $requete_count);
$ligne_count = mysqli_fetch_assoc($res_count);
$nb_total = $ligne_count['nb'];

mysqli_close($connexion);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique - SAE 23</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="consultation.php">Consultation</a>
        <a href="graphiques.php">Graphiques</a>
        <a href="historique.php">Historique</a>
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
        <h1>Historique des mesures</h1>

        <!-- filters -->
        <form method="GET">
            <label>Batiment :</label>
            <select name="batiment">
                <option value="">Tous</option>
                <?php foreach ($liste_batiments as $b) { ?>
                    <option value="<?php echo htmlspecialchars($b['nom']); ?>" <?php if ($filtre_batiment == $b['nom']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($b['nom']); ?>
                    </option>
                <?php } ?>
            </select>

            <label>Salle :</label>
            <select name="salle">
                <option value="">Toutes</option>
                <?php foreach ($liste_salles as $s) { ?>
                    <option value="<?php echo htmlspecialchars($s['nom']); ?>" <?php if ($filtre_salle == $s['nom']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($s['nom']); ?>
                    </option>
                <?php } ?>
            </select>

            <label>Type :</label>
            <select name="type">
                <option value="">Tous</option>
                <option value="temperature"  <?php if ($filtre_type == 'temperature')  echo 'selected'; ?>>Temperature</option>
                <option value="humidity"     <?php if ($filtre_type == 'humidity')     echo 'selected'; ?>>Humidite</option>
                <option value="co2"          <?php if ($filtre_type == 'co2')          echo 'selected'; ?>>CO2</option>
                <option value="illumination" <?php if ($filtre_type == 'illumination') echo 'selected'; ?>>Luminosite</option>
                <option value="activity"     <?php if ($filtre_type == 'activity')     echo 'selected'; ?>>Activite</option>
                <option value="tvoc"         <?php if ($filtre_type == 'tvoc')         echo 'selected'; ?>>TVOC</option>
                <option value="pressure"     <?php if ($filtre_type == 'pressure')     echo 'selected'; ?>>Pression</option>
            </select>

            <input type="submit" value="Filtrer">
            <a href="historique.php">Reinitialiser</a>
        </form>

        <p><?php echo $nb_total; ?> mesure(s) au total — affichage des 500 plus recentes.</p>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Batiment</th>
                    <th>Salle</th>
                    <th>Type</th>
                    <th>Valeur</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($ligne = mysqli_fetch_assoc($resultat)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($ligne['date_mesure']); ?></td>
                    <td><?php echo htmlspecialchars($ligne['heure_mesure']); ?></td>
                    <td><?php echo htmlspecialchars($ligne['batiment']); ?></td>
                    <td><?php echo htmlspecialchars($ligne['salle']); ?></td>
                    <td><?php echo htmlspecialchars($ligne['type_capteur']); ?></td>
                    <td><?php echo htmlspecialchars($ligne['valeur']); ?> <?php echo htmlspecialchars($ligne['unite']); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <footer>
            <p>SAE 23 - BUT R&T - IUT de Blagnac - 2026</p>
        </footer>
    </div>
</body>
</html>
