<?php
session_start();

// Access control: admin only
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: connexion.php");
    exit();
}

$connexion = mysqli_connect("localhost", "root", "", "sae23");
if (!$connexion) {
    die("Database connection error.");
}

$message = "";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // Add room
    if ($action == 'add_salle') {
        $nom = mysqli_real_escape_string($connexion, $_POST['nom']);
        $etage = intval($_POST['etage']);
        $type = mysqli_real_escape_string($connexion, $_POST['type']);
        $capacite = intval($_POST['capacite']);
        $id_batiment = intval($_POST['id_batiment']);

        $requete = "INSERT INTO Salle (nom, etage, type, capacite, id_batiment)
                    VALUES ('$nom', $etage, '$type', $capacite, $id_batiment)";
        if (mysqli_query($connexion, $requete)) {
            $message = "Salle ajoutee avec succes.";
        } else {
            $message = "Erreur : " . mysqli_error($connexion);
        }
    }

    // Delete room
    if ($action == 'delete_salle') {
        $id = intval($_POST['id_salle']);
        mysqli_query($connexion, "DELETE FROM Mesure WHERE id_capteur IN (SELECT id_capteur FROM Capteur WHERE id_salle = $id)");
        mysqli_query($connexion, "DELETE FROM Capteur WHERE id_salle = $id");
        mysqli_query($connexion, "DELETE FROM Salle WHERE id_salle = $id");
        $message = "Salle supprimee.";
    }

    // Add sensor
    if ($action == 'add_capteur') {
        $nom = mysqli_real_escape_string($connexion, $_POST['nom']);
        $type_capteur = mysqli_real_escape_string($connexion, $_POST['type_capteur']);
        $unite = mysqli_real_escape_string($connexion, $_POST['unite']);
        $id_salle = intval($_POST['id_salle']);

        $requete = "INSERT INTO Capteur (nom, type_capteur, unite, id_salle)
                    VALUES ('$nom', '$type_capteur', '$unite', $id_salle)";
        if (mysqli_query($connexion, $requete)) {
            $message = "Capteur ajoute avec succes.";
        } else {
            $message = "Erreur : " . mysqli_error($connexion);
        }
    }

    // Delete sensor
    if ($action == 'delete_capteur') {
        $id = intval($_POST['id_capteur']);
        mysqli_query($connexion, "DELETE FROM Mesure WHERE id_capteur = $id");
        mysqli_query($connexion, "DELETE FROM Capteur WHERE id_capteur = $id");
        $message = "Capteur supprime.";
    }

    // Add user
    if ($action == 'add_utilisateur') {
        $login = mysqli_real_escape_string($connexion, $_POST['login']);
        $mot_de_passe = mysqli_real_escape_string($connexion, $_POST['mot_de_passe']);
        $role = mysqli_real_escape_string($connexion, $_POST['role']);
        $id_batiment = !empty($_POST['id_batiment']) ? intval($_POST['id_batiment']) : 'NULL';

        $requete = "INSERT INTO Utilisateur (login, mot_de_passe, role, id_batiment)
                    VALUES ('$login', '$mot_de_passe', '$role', $id_batiment)";
        if (mysqli_query($connexion, $requete)) {
            $message = "Utilisateur ajoute avec succes.";
        } else {
            $message = "Erreur : " . mysqli_error($connexion);
        }
    }

    // Delete user
    if ($action == 'delete_utilisateur') {
        $id = intval($_POST['id_utilisateur']);
        // Prevent deleting yourself
        $requete_check = "SELECT login FROM Utilisateur WHERE id_utilisateur = $id";
        $resultat_check = mysqli_query($connexion, $requete_check);
        $user_check = mysqli_fetch_assoc($resultat_check);

        if ($user_check['login'] == $_SESSION['login']) {
            $message = "Vous ne pouvez pas supprimer votre propre compte.";
        } else {
            mysqli_query($connexion, "DELETE FROM Utilisateur WHERE id_utilisateur = $id");
            $message = "Utilisateur supprime.";
        }
    }
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
        <a href="gestion.php">Gestion</a>
        <a href="admin.php">Administration</a>
        <a href="projet.php">Projet</a>
        <a href="connexion.php?logout=1">Deconnexion (<?php echo htmlspecialchars($_SESSION['login']); ?>)</a>
    </nav>

    <div class="container">
        <h1>Administration</h1>
        <p>Connecte en tant que : <strong><?php echo htmlspecialchars($_SESSION['login']); ?></strong> (admin)</p>

        <?php if ($message != "") { ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>

        <!-- Buildings -->
        <h2>Batiments</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Adresse</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $resultat = mysqli_query($connexion, "SELECT * FROM Batiment ORDER BY nom");
                while ($ligne = mysqli_fetch_assoc($resultat)) {
                    echo "<tr>";
                    echo "<td>" . $ligne['id_batiment'] . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['nom']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['adresse']) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Rooms management -->
        <h2>Salles</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Etage</th>
                    <th>Type</th>
                    <th>Capacite</th>
                    <th>Batiment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $resultat = mysqli_query($connexion,
                    "SELECT s.*, b.nom AS batiment_nom FROM Salle s
                     JOIN Batiment b ON s.id_batiment = b.id_batiment
                     ORDER BY b.nom, s.nom");
                while ($ligne = mysqli_fetch_assoc($resultat)) {
                    echo "<tr>";
                    echo "<td>" . $ligne['id_salle'] . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['nom']) . "</td>";
                    echo "<td>" . $ligne['etage'] . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['type']) . "</td>";
                    echo "<td>" . $ligne['capacite'] . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['batiment_nom']) . "</td>";
                    echo "<td>";
                    echo "<form method='POST' style='display:inline;' onsubmit='return confirm(\"Supprimer cette salle et tous ses capteurs ?\");'>";
                    echo "<input type='hidden' name='action' value='delete_salle'>";
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
            <input type="hidden" name="action" value="add_salle">
            <label>Nom :</label>
            <input type="text" name="nom" required><br><br>
            <label>Etage :</label>
            <input type="number" name="etage" required><br><br>
            <label>Type :</label>
            <input type="text" name="type" required><br><br>
            <label>Capacite :</label>
            <input type="number" name="capacite" required><br><br>
            <label>Batiment :</label>
            <select name="id_batiment" required>
                <option value="1">Batiment E</option>
                <option value="2">Batiment C</option>
            </select><br><br>
            <input type="submit" value="Ajouter la salle">
        </form>

        <!-- Sensors management -->
        <h2>Capteurs</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Type</th>
                    <th>Unite</th>
                    <th>Salle</th>
                    <th>Batiment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $resultat = mysqli_query($connexion,
                    "SELECT c.*, s.nom AS salle_nom, b.nom AS batiment_nom
                     FROM Capteur c
                     JOIN Salle s ON c.id_salle = s.id_salle
                     JOIN Batiment b ON s.id_batiment = b.id_batiment
                     ORDER BY b.nom, s.nom, c.type_capteur");
                while ($ligne = mysqli_fetch_assoc($resultat)) {
                    echo "<tr>";
                    echo "<td>" . $ligne['id_capteur'] . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['nom']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['type_capteur']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['unite']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['salle_nom']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['batiment_nom']) . "</td>";
                    echo "<td>";
                    echo "<form method='POST' style='display:inline;' onsubmit='return confirm(\"Supprimer ce capteur ?\");'>";
                    echo "<input type='hidden' name='action' value='delete_capteur'>";
                    echo "<input type='hidden' name='id_capteur' value='" . $ligne['id_capteur'] . "'>";
                    echo "<input type='submit' value='Supprimer'>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <h3>Ajouter un capteur</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_capteur">
            <label>Nom :</label>
            <input type="text" name="nom" required><br><br>
            <label>Type :</label>
            <select name="type_capteur" required>
                <option value="temperature">Temperature</option>
                <option value="humidity">Humidite</option>
                <option value="co2">CO2</option>
                <option value="luminosite">Luminosite</option>
            </select><br><br>
            <label>Unite :</label>
            <input type="text" name="unite" required><br><br>
            <label>Salle :</label>
            <select name="id_salle" required>
                <?php
                $salles = mysqli_query($connexion, "SELECT s.id_salle, s.nom, b.nom AS batiment_nom FROM Salle s JOIN Batiment b ON s.id_batiment = b.id_batiment ORDER BY b.nom, s.nom");
                while ($s = mysqli_fetch_assoc($salles)) {
                    echo "<option value='" . $s['id_salle'] . "'>" . htmlspecialchars($s['batiment_nom']) . " - " . htmlspecialchars($s['nom']) . "</option>";
                }
                ?>
            </select><br><br>
            <input type="submit" value="Ajouter le capteur">
        </form>

        <!-- Users management -->
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
                $resultat = mysqli_query($connexion,
                    "SELECT u.*, b.nom AS batiment_nom FROM Utilisateur u
                     LEFT JOIN Batiment b ON u.id_batiment = b.id_batiment
                     ORDER BY u.role, u.login");
                while ($ligne = mysqli_fetch_assoc($resultat)) {
                    echo "<tr>";
                    echo "<td>" . $ligne['id_utilisateur'] . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['login']) . "</td>";
                    echo "<td>" . htmlspecialchars($ligne['role']) . "</td>";
                    echo "<td>" . ($ligne['batiment_nom'] ? htmlspecialchars($ligne['batiment_nom']) : '-') . "</td>";
                    echo "<td>";
                    echo "<form method='POST' style='display:inline;' onsubmit='return confirm(\"Supprimer cet utilisateur ?\");'>";
                    echo "<input type='hidden' name='action' value='delete_utilisateur'>";
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
            <input type="hidden" name="action" value="add_utilisateur">
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
                <option value="1">Batiment E</option>
                <option value="2">Batiment C</option>
            </select><br><br>
            <input type="submit" value="Ajouter l'utilisateur">
        </form>

        <footer>
            <p>SAE 23 - BUT R&T - IUT de Blagnac - 2026</p>
        </footer>
    </div>
</body>
</html>
