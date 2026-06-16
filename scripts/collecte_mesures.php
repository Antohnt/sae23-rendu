#!/opt/lampp/bin/php
<?php
// collecte_mesures.php
// MQTT data collection script - stores sensor data into MySQL
// Runs via crontab every 10 minutes

$broker = "mqtt.iut-blagnac.fr";
$port = 1883;
$timeout = 5;

// Sensor topics mapping: one topic can feed multiple sensors (e.g., E101 publishes both temp and co2)
$topics_to_rooms = array(
    "AM107/by-room/E101/data" => "E101",
    "AM107/by-room/E208/data" => "E208",
    "AM107/by-room/C101/data" => "C101",
    "AM107/by-room/C102/data" => "C102"
);

// Connect to MySQL
$connexion = mysqli_connect("localhost", "root", "", "sae23");
if (!$connexion) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: MySQL connection failed.\n";
    exit(1);
}

$mesures_inserted = 0;

foreach ($topics_to_rooms as $topic => $room_name) {
    // Fetch one message from MQTT broker
    $cmd = "mosquitto_sub -h $broker -p $port -t \"$topic\" -C 1 -W $timeout 2>&1";
    $output = shell_exec($cmd);

    if ($output === null || trim($output) === "") {
        echo "[" . date('Y-m-d H:i:s') . "] WARNING: No data received for $topic (timeout or offline).\n";
        continue;
    }

    // Parse JSON payload
    $data = json_decode($output, true);
    if ($data === null) {
        echo "[" . date('Y-m-d H:i:s') . "] WARNING: Invalid JSON for $topic.\n";
        continue;
    }

    // Get the first element if it's an array
    if (isset($data[0])) {
        $data = $data[0];
    }

    // Find ALL sensors for this room (one MQTT message can contain multiple sensor types)
    $room_escaped = mysqli_real_escape_string($connexion, $room_name);
    $requete_capteurs = "SELECT c.id_capteur, c.type_capteur, c.nom
                         FROM Capteur c
                         JOIN Salle s ON c.id_salle = s.id_salle
                         WHERE s.nom = '$room_escaped'";
    $resultat_capteurs = mysqli_query($connexion, $requete_capteurs);

    if (mysqli_num_rows($resultat_capteurs) == 0) {
        echo "[" . date('Y-m-d H:i:s') . "] WARNING: No sensors found for room '$room_name'.\n";
        continue;
    }

    // Process each sensor for this room
    while ($capteur = mysqli_fetch_assoc($resultat_capteurs)) {
        $id_capteur = $capteur['id_capteur'];
        $type_capteur = $capteur['type_capteur'];
        $capteur_name = $capteur['nom'];

        // Extract the relevant value based on sensor type
        $valeur = null;
        if ($type_capteur == 'temperature' && isset($data['temperature'])) {
            $valeur = floatval($data['temperature']);
        } elseif ($type_capteur == 'humidity' && isset($data['humidity'])) {
            $valeur = floatval($data['humidity']);
        } elseif ($type_capteur == 'co2' && isset($data['co2'])) {
            $valeur = floatval($data['co2']);
        } elseif ($type_capteur == 'luminosite' && isset($data['lux'])) {
            $valeur = floatval($data['lux']);
        }

        if ($valeur === null) {
            echo "[" . date('Y-m-d H:i:s') . "] WARNING: No matching value for sensor type '$type_capteur' in $topic.\n";
            continue;
        }

        // Insert measurement into database
        $date = date('Y-m-d');
        $heure = date('H:i:s');
        $valeur_echappe = mysqli_real_escape_string($connexion, $valeur);

        $requete_insert = "INSERT INTO Mesure (valeur, date_mesure, heure_mesure, id_capteur)
                           VALUES ('$valeur_echappe', '$date', '$heure', '$id_capteur')";

        if (mysqli_query($connexion, $requete_insert)) {
            $mesures_inserted++;
        } else {
            echo "[" . date('Y-m-d H:i:s') . "] ERROR: Insert failed for $capteur_name: " . mysqli_error($connexion) . "\n";
        }
    }
}

mysqli_close($connexion);

echo "[" . date('Y-m-d H:i:s') . "] Collection complete: $mesures_inserted measurements inserted.\n";
?>
