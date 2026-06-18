#!/usr/bin/php
<?php
// insert demo measures for Batiment C (no sandbox topics from outside)

$connexion = mysqli_connect("localhost", "root", "sae23", "sae23");
if (!$connexion) {
    echo "ERREUR: connexion MySQL impossible.\n";
    exit(1);
}

$demo = array(
    array("salle" => "C101", "type" => "temperature", "valeur" => 22.5),
    array("salle" => "C101", "type" => "co2", "valeur" => 450),
    array("salle" => "C102", "type" => "temperature", "valeur" => 21.8),
    array("salle" => "C102", "type" => "humidity", "valeur" => 38)
);

$date_jour = date('Y-m-d');
$heure = date('H:i:s');
$nb = 0;

foreach ($demo as $ligne) {
    $salle = mysqli_real_escape_string($connexion, $ligne['salle']);
    $type = mysqli_real_escape_string($connexion, $ligne['type']);
    $valeur = mysqli_real_escape_string($connexion, $ligne['valeur']);

    $requete = "SELECT c.id_capteur FROM Capteur c
                JOIN Salle s ON c.id_salle = s.id_salle
                WHERE s.nom = '$salle' AND c.type_capteur = '$type'";
    $resultat = mysqli_query($connexion, $requete);
    if (mysqli_num_rows($resultat) == 0) {
        continue;
    }
    $capteur = mysqli_fetch_assoc($resultat);
    $id = $capteur['id_capteur'];

    $insert = "INSERT INTO Mesure (valeur, date_mesure, heure_mesure, id_capteur)
               VALUES ('$valeur', '$date_jour', '$heure', '$id')";
    if (mysqli_query($connexion, $insert)) {
        $nb++;
    }
}

mysqli_close($connexion);
echo "Demo MySQL: $nb mesures inserees (Batiment C).\n";
?>
