<?php
session_start();

// disconnect
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'];
    $mdp = $_POST['mot_de_passe'];

    $connexion = mysqli_connect("localhost", "root", "sae23", "sae23");

    if (!$connexion) {
        $erreur = "Erreur de connexion a la base de donnees.";
    } else {
        // clean input
        $login = mysqli_real_escape_string($connexion, $login);
        $mdp = mysqli_real_escape_string($connexion, $mdp);

        // check user
        $requete = "SELECT * FROM Utilisateur WHERE login='$login' AND mot_de_passe='$mdp'";
        $resultat = mysqli_query($connexion, $requete);

        if (mysqli_num_rows($resultat) == 1) {
            $utilisateur = mysqli_fetch_assoc($resultat);
            // save session
            $_SESSION['login'] = $utilisateur['login'];
            $_SESSION['role'] = $utilisateur['role'];
            $_SESSION['id_batiment'] = $utilisateur['id_batiment'];
            header("Location: index.php");
            exit();
        } else {
            $erreur = "Login ou mot de passe incorrect.";
        }
        mysqli_close($connexion);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SAE 23</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="consultation.php">Consultation</a>
        <a href="gestion.php">Gestion</a>
        <a href="admin.php">Administration</a>
        <a href="projet.php">Projet</a>
        <a href="connexion.php">Connexion</a>
    </nav>

    <div class="container">
        <h1>Connexion</h1>

        <?php if ($erreur != "") { ?>
            <p class="erreur"><?php echo htmlspecialchars($erreur); ?></p>
        <?php } ?>

        <form method="POST" action="connexion.php">
            <label for="login">Login :</label><br>
            <input type="text" id="login" name="login" required><br><br>

            <label for="mot_de_passe">Mot de passe :</label><br>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required><br><br>

            <input type="submit" value="Se connecter">
        </form>

        <footer>
            <p>SAE 23 - BUT R&T - IUT de Blagnac - 2026</p>
        </footer>
    </div>
</body>
</html>
