<?php
session_start();

// check admin
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: connexion.php");
    exit();
}

$connexion = mysqli_connect("localhost", "root", "sae23", "sae23");
if (!$connexion) {
    echo "Erreur connexion base de donnees.";
    exit();
}

$message = "";

// form actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // add a building
    if ($action == 'ajout_batiment') {
        $nom = mysqli_real_escape_string($connexion, $_POST['nom']);
        $adresse = mysqli_real_escape_string($connexion, $_POST['adresse']);
        $requete = "INSERT INTO Batiment (nom, adresse) VALUES ('$nom', '$adresse')";
        if (mysqli_query($connexion, $requete)) {
            $message = "Batiment ajoute.";
        } else {
            $message = "Erreur lors de l'ajout.";
        }
    }

    // delete a building
    if ($action == 'suppr_batiment') {
        $id = 0 + ($_POST['id_batiment']);
        mysqli_query($connexion, "DELETE FROM Mesure WHERE id_capteur IN (SELECT id_capteur FROM Capteur WHERE id_salle IN (SELECT id_salle FROM Salle WHERE id_batiment = $id))");
        mysqli_query($connexion, "DELETE FROM Capteur WHERE id_salle IN (SELECT id_salle FROM Salle WHERE id_batiment = $id)");
        mysqli_query($connexion, "DELETE FROM Salle WHERE id_batiment = $id");
        mysqli_query($connexion, "DELETE FROM Utilisateur WHERE id_batiment = $id");
        mysqli_query($connexion, "DELETE FROM Batiment WHERE id_batiment = $id");
        $message = "Batiment supprime.";
    }

    // add a room
    if ($action == 'ajout_salle') {
        $nom = mysqli_real_escape_string($connexion, $_POST['nom']);
        $etage = 0 + ($_POST['etage']);
        $type = mysqli_real_escape_string($connexion, $_POST['type']);
        $capacite = 0 + ($_POST['capacite']);
        $id_batiment = 0 + ($_POST['id_batiment']);

        $requete = "INSERT INTO Salle (nom, etage, type, capacite, id_batiment)
                    VALUES ('$nom', $etage, '$type', $capacite, $id_batiment)";
        if (mysqli_query($connexion, $requete)) {
            $message = "Salle ajoutee.";
        } else {
            $message = "Erreur lors de l'ajout.";
        }
    }

    // delete a room
    if ($action == 'suppr_salle') {
        $id = 0 + ($_POST['id_salle']);
        mysqli_query($connexion, "DELETE FROM Mesure WHERE id_capteur IN (SELECT id_capteur FROM Capteur WHERE id_salle = $id)");
        mysqli_query($connexion, "DELETE FROM Capteur WHERE id_salle = $id");
        mysqli_query($connexion, "DELETE FROM Salle WHERE id_salle = $id");
        $message = "Salle supprimee.";
    }

    // add a sensor
    if ($action == 'ajout_capteur') {
        $nom = mysqli_real_escape_string($connexion, $_POST['nom']);
        $type_capteur = mysqli_real_escape_string($connexion, $_POST['type_capteur']);
        $unite = mysqli_real_escape_string($connexion, $_POST['unite']);
        $id_salle = 0 + ($_POST['id_salle']);

        $requete = "INSERT INTO Capteur (nom, type_capteur, unite, id_salle)
                    VALUES ('$nom', '$type_capteur', '$unite', $id_salle)";
        if (mysqli_query($connexion, $requete)) {
            $message = "Capteur ajoute.";
        } else {
            $message = "Erreur lors de l'ajout.";
        }
    }

    // delete a sensor
    if ($action == 'suppr_capteur') {
        $id = 0 + ($_POST['id_capteur']);
        mysqli_query($connexion, "DELETE FROM Mesure WHERE id_capteur = $id");
        mysqli_query($connexion, "DELETE FROM Capteur WHERE id_capteur = $id");
        $message = "Capteur supprime.";
    }

    // delete measures for a sensor
    if ($action == 'suppr_mesures') {
        $id = 0 + ($_POST['id_capteur']);
        mysqli_query($connexion, "DELETE FROM Mesure WHERE id_capteur = $id");
        $message = "Mesures supprimees.";
    }

    // add a user
    if ($action == 'ajout_utilisateur') {
        $login = mysqli_real_escape_string($connexion, $_POST['login']);
        $mdp_md5 = md5($_POST['mot_de_passe']);
        $role = mysqli_real_escape_string($connexion, $_POST['role']);
        $id_batiment = !empty($_POST['id_batiment']) ? 0 + ($_POST['id_batiment']) : 'NULL';

        $requete = "INSERT INTO Utilisateur (login, mot_de_passe, role, id_batiment)
                    VALUES ('$login', '$mdp_md5', '$role', $id_batiment)";
        if (mysqli_query($connexion, $requete)) {
            $message = "Utilisateur ajoute.";
        } else {
            $message = "Erreur lors de l'ajout.";
        }
    }

    // delete a user
    if ($action == 'suppr_utilisateur') {
        $id = 0 + ($_POST['id_utilisateur']);
        $requete_verif = "SELECT login FROM Utilisateur WHERE id_utilisateur = $id";
        $resultat_verif = mysqli_query($connexion, $requete_verif);
        $utilisateur_verif = mysqli_fetch_assoc($resultat_verif);

        // not delete yourself
        if ($utilisateur_verif['login'] == $_SESSION['login']) {
            $message = "Vous ne pouvez pas supprimer votre propre compte.";
        } else {
            mysqli_query($connexion, "DELETE FROM Utilisateur WHERE id_utilisateur = $id");
            $message = "Utilisateur supprime.";
        }
    }
}

// get all buildings for dropdown
$batiments = mysqli_query($connexion, "SELECT * FROM Batiment ORDER BY nom");
$liste_batiments = array();
while ($b = mysqli_fetch_assoc($batiments)) {
    $liste_batiments[] = $b;
}

// get all rooms for dropdown
$salles = mysqli_query($connexion, "SELECT s.*, b.nom AS nom_batiment FROM Salle s JOIN Batiment b ON s.id_batiment = b.id_batiment ORDER BY b.nom, s.nom");
$liste_salles = array();
while ($s = mysqli_fetch_assoc($salles)) {
    $liste_salles[] = $s;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - SAE 23</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="consultation.php">Consultation</a>
        <a href="graphiques.php">Graphiques</a>
        <a href="gestion.php">Gestion</a>
        <a href="admin.php">Administration</a>
        <a href="projet.php">Projet</a>
        <a href="connexion.php?logout=1">Deconnexion (<?php echo htmlspecialchars($_SESSION['login']); ?>)</a>
    </nav>

    <div class="container">
        <h1>Administration</h1>
        <p>Connecte : <strong><?php echo htmlspecialchars($_SESSION['login']); ?></strong> (admin)</p>

        <?php if ($message != "") { ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>

        <!-- BUILDINGS -->
        <h2>Batiments</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Adresse</th>
                    <th>Nb salles</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res_bat = mysqli_query($connexion, "SELECT b.*, COUNT(s.id_salle) AS nb FROM Batiment b LEFT JOIN Salle s ON b.id_batiment = s.id_batiment GROUP BY b.id_batiment ORDER BY b.nom");
                while ($ligne = mysqli_fetch_assoc($res_bat)) {
                    echo "<tr>";
                    echo "<td>" . $ligne['id_batiment'] . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['nom']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['adresse']) . "</td>";
                    echo "<td>" . $ligne['nb'] . "</td>";
                    echo "<td>";
                    echo "<form method='POST' class='form-inline'>";
                    echo "<input type='hidden' name='action' value='suppr_batiment'>";
                    echo "<input type='hidden' name='id_batiment' value='" . $ligne['id_batiment'] . "'>";
                    echo "<input type='submit' value='Supprimer'>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <h3>Ajouter un batiment</h3>
        <form method="POST">
            <input type="hidden" name="action" value="ajout_batiment">
            <label>Nom :</label>
            <input type="text" name="nom" required><br><br>
            <label>Adresse :</label>
            <input type="text" name="adresse" required><br><br>
            <input type="submit" value="Ajouter le batiment">
        </form>

        <!-- ROOMS -->
        <h2>Salles</h2>

        <h3>Filtrer par batiment</h3>
        <form method="GET">
            <select name="filtre_bat">
                <option value="">Tous les batiments</option>
                <?php foreach ($liste_batiments as $b) { ?>
                    <option value="<?php echo $b['id_batiment']; ?>" <?php if (isset($_GET['filtre_bat']) && $_GET['filtre_bat'] == $b['id_batiment']) echo 'selected'; ?>><?php echo htmlspecialchars($b['nom']); ?></option>
                <?php } ?>
            </select>
            <input type="submit" value="Filtrer">
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Etage</th>
                    <th>Type</th>
                    <th>Capacite</th>
                    <th>Batiment</th>
                    <th>Nb capteurs</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql_salles = "SELECT s.*, b.nom AS nom_batiment, COUNT(c.id_capteur) AS nb_capteurs
                    FROM Salle s
                    JOIN Batiment b ON s.id_batiment = b.id_batiment
                    LEFT JOIN Capteur c ON s.id_salle = c.id_salle";
                if (isset($_GET['filtre_bat']) && $_GET['filtre_bat'] != '') {
                    $fb = 0 + $_GET['filtre_bat'];
                    $sql_salles = $sql_salles . " WHERE s.id_batiment = $fb";
                }
                $sql_salles = $sql_salles . " GROUP BY s.id_salle ORDER BY b.nom, s.nom";
                $res_salles = mysqli_query($connexion, $sql_salles);
                while ($ligne = mysqli_fetch_assoc($res_salles)) {
                    echo "<tr>";
                    echo "<td>" . $ligne['id_salle'] . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['nom']) . "</td>";
                    echo "<td>" . $ligne['etage'] . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['type']) . "</td>";
                    echo "<td>" . $ligne['capacite'] . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['nom_batiment']) . "</td>";
                    echo "<td>" . $ligne['nb_capteurs'] . "</td>";
                    echo "<td>";
                    echo "<form method='POST' class='form-inline'>";
                    echo "<input type='hidden' name='action' value='suppr_salle'>";
                    echo "<input type='hidden' name='id_salle' value='" . $ligne['id_salle'] . "'>";
                    echo "<input type='submit' value='Supprimer'>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <h3>Ajouter une salle</h3>
        <form method="POST">
            <input type="hidden" name="action" value="ajout_salle">
            <label>Nom :</label>
            <input type="text" name="nom" required><br><br>
            <label>Etage :</label>
            <input type="number" name="etage" required><br><br>
            <label>Type :</label>
            <select name="type" required>
                <option value="TP">TP</option>
                <option value="TD">TD</option>
                <option value="CM">CM</option>
                <option value="Reunion">Reunion</option>
                <option value="Commun">Commun</option>
            </select><br><br>
            <label>Capacite :</label>
            <input type="number" name="capacite" required><br><br>
            <label>Batiment :</label>
            <select name="id_batiment" required>
                <?php foreach ($liste_batiments as $b) { ?>
                    <option value="<?php echo $b['id_batiment']; ?>"><?php echo htmlspecialchars($b['nom']); ?></option>
                <?php } ?>
            </select><br><br>
            <input type="submit" value="Ajouter la salle">
        </form>

        <!-- SENSORS -->
        <h2>Capteurs</h2>

        <h3>Filtrer par salle</h3>
        <form method="GET">
            <?php if (isset($_GET['filtre_bat'])) { ?>
                <input type="hidden" name="filtre_bat" value="<?php echo htmlspecialchars($_GET['filtre_bat']); ?>">
            <?php } ?>
            <select name="filtre_salle">
                <option value="">Toutes les salles</option>
                <?php foreach ($liste_salles as $s) { ?>
                    <option value="<?php echo $s['id_salle']; ?>" <?php if (isset($_GET['filtre_salle']) && $_GET['filtre_salle'] == $s['id_salle']) echo 'selected'; ?>><?php echo htmlspecialchars($s['nom_batiment']) . ' - ' . htmlspecialchars($s['nom']); ?></option>
                <?php } ?>
            </select>
            <input type="submit" value="Filtrer">
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Type</th>
                    <th>Unite</th>
                    <th>Salle</th>
                    <th>Batiment</th>
                    <th>Nb mesures</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql_capt = "SELECT c.*, s.nom AS nom_salle, b.nom AS nom_batiment, COUNT(m.id_mesure) AS nb_mesures
                    FROM Capteur c
                    JOIN Salle s ON c.id_salle = s.id_salle
                    JOIN Batiment b ON s.id_batiment = b.id_batiment
                    LEFT JOIN Mesure m ON c.id_capteur = m.id_capteur";
                if (isset($_GET['filtre_salle']) && $_GET['filtre_salle'] != '') {
                    $fs = 0 + $_GET['filtre_salle'];
                    $sql_capt = $sql_capt . " WHERE c.id_salle = $fs";
                }
                $sql_capt = $sql_capt . " GROUP BY c.id_capteur ORDER BY b.nom, s.nom, c.type_capteur";
                $res_capt = mysqli_query($connexion, $sql_capt);
                while ($ligne = mysqli_fetch_assoc($res_capt)) {
                    echo "<tr>";
                    echo "<td>" . $ligne['id_capteur'] . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['nom']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['type_capteur']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['unite']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['nom_salle']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['nom_batiment']) . "</td>";
                    echo "<td>" . $ligne['nb_mesures'] . "</td>";
                    echo "<td>";
                    echo "<form method='POST' class='form-inline'>";
                    echo "<input type='hidden' name='action' value='suppr_capteur'>";
                    echo "<input type='hidden' name='id_capteur' value='" . $ligne['id_capteur'] . "'>";
                    echo "<input type='submit' value='Supprimer'>";
                    echo "</form> ";
                    echo "<form method='POST' class='form-inline'>";
                    echo "<input type='hidden' name='action' value='suppr_mesures'>";
                    echo "<input type='hidden' name='id_capteur' value='" . $ligne['id_capteur'] . "'>";
                    echo "<input type='submit' value='Vider mesures'>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <h3>Ajouter un capteur</h3>
        <form method="POST">
            <input type="hidden" name="action" value="ajout_capteur">
            <label>Nom :</label>
            <input type="text" name="nom" required><br><br>
            <label>Type :</label>
            <select name="type_capteur" required>
                <option value="temperature">Temperature</option>
                <option value="humidity">Humidite</option>
                <option value="co2">CO2</option>
                <option value="illumination">Luminosite</option>
            </select><br><br>
            <label>Unite :</label>
            <select name="unite" required>
                <option value="°C">°C (temperature)</option>
                <option value="%">% (humidite)</option>
                <option value="ppm">ppm (CO2)</option>
                <option value="lux">lux (luminosite)</option>
            </select><br><br>
            <label>Salle :</label>
            <select name="id_salle" required>
                <?php foreach ($liste_salles as $s) { ?>
                    <option value="<?php echo $s['id_salle']; ?>"><?php echo htmlspecialchars($s['nom_batiment']) . ' - ' . htmlspecialchars($s['nom']); ?></option>
                <?php } ?>
            </select><br><br>
            <input type="submit" value="Ajouter le capteur">
        </form>

        <!-- USERS -->
        <h2>Utilisateurs</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Login</th>
                    <th>Role</th>
                    <th>Batiment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res_users = mysqli_query($connexion,
                    "SELECT u.*, b.nom AS nom_batiment FROM Utilisateur u
                     LEFT JOIN Batiment b ON u.id_batiment = b.id_batiment
                     ORDER BY u.role, u.login");
                while ($ligne = mysqli_fetch_assoc($res_users)) {
                    echo "<tr>";
                    echo "<td>" . $ligne['id_utilisateur'] . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['login']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['role']) . "</td>";
                    echo "<td>" . ($ligne['nom_batiment'] ? htmlspecialchars($ligne['nom_batiment']) : '-') . "</td>";
                    echo "<td>";
                    echo "<form method='POST' class='form-inline'>";
                    echo "<input type='hidden' name='action' value='suppr_utilisateur'>";
                    echo "<input type='hidden' name='id_utilisateur' value='" . $ligne['id_utilisateur'] . "'>";
                    echo "<input type='submit' value='Supprimer'>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <h3>Ajouter un utilisateur</h3>
        <form method="POST">
            <input type="hidden" name="action" value="ajout_utilisateur">
            <label>Login :</label>
            <input type="text" name="login" required><br><br>
            <label>Mot de passe :</label>
            <input type="password" name="mot_de_passe" required><br><br>
            <label>Role :</label>
            <select name="role" required>
                <option value="admin">Admin</option>
                <option value="gestionnaire">Gestionnaire</option>
            </select><br><br>
            <label>Batiment (pour gestionnaire) :</label>
            <select name="id_batiment">
                <option value="">Aucun (admin)</option>
                <?php foreach ($liste_batiments as $b) { ?>
                    <option value="<?php echo $b['id_batiment']; ?>"><?php echo htmlspecialchars($b['nom']); ?></option>
                <?php } ?>
            </select><br><br>
            <input type="submit" value="Ajouter l'utilisateur">
        </form>

        <footer>
            <p>SAE 23 - BUT R&T - IUT de Blagnac - 2026</p>
        </footer>
    </div>
</body>
</html>
