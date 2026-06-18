<?php
session_start();

$connexion = mysqli_connect("localhost", "root", "sae23", "sae23");
if (!$connexion) {
    echo "Erreur connexion base de donnees.";
    exit();
}

// get rooms list
$salles = mysqli_query($connexion, "SELECT DISTINCT s.nom FROM Salle s INNER JOIN Capteur c ON s.id_salle = c.id_salle ORDER BY s.nom");
$liste_salles = array();
while ($s = mysqli_fetch_assoc($salles)) {
    $liste_salles[] = $s['nom'];
}

// selected room
$salle_choisie = isset($_GET['salle']) ? $_GET['salle'] : $liste_salles[0];

// get last 20 measures for this room
$salle_protege = mysqli_real_escape_string($connexion, $salle_choisie);

$requete_temp = "SELECT m.valeur, m.heure_mesure
    FROM Mesure m
    INNER JOIN Capteur c ON m.id_capteur = c.id_capteur
    INNER JOIN Salle s ON c.id_salle = s.id_salle
    WHERE s.nom = '$salle_protege' AND c.type_capteur = 'temperature'
    ORDER BY m.date_mesure DESC, m.heure_mesure DESC
    LIMIT 20";
$res_temp = mysqli_query($connexion, $requete_temp);
$data_temp = array();
$labels_temp = array();
while ($ligne = mysqli_fetch_assoc($res_temp)) {
    $data_temp[] = $ligne['valeur'] + 0;
    $labels_temp[] = $ligne['heure_mesure'];
}
$data_temp = array_reverse($data_temp);
$labels_temp = array_reverse($labels_temp);

$requete_co2 = "SELECT m.valeur, m.heure_mesure
    FROM Mesure m
    INNER JOIN Capteur c ON m.id_capteur = c.id_capteur
    INNER JOIN Salle s ON c.id_salle = s.id_salle
    WHERE s.nom = '$salle_protege' AND c.type_capteur = 'co2'
    ORDER BY m.date_mesure DESC, m.heure_mesure DESC
    LIMIT 20";
$res_co2 = mysqli_query($connexion, $requete_co2);
$data_co2 = array();
while ($ligne = mysqli_fetch_assoc($res_co2)) {
    $data_co2[] = $ligne['valeur'] + 0;
}
$data_co2 = array_reverse($data_co2);

$requete_hum = "SELECT m.valeur, m.heure_mesure
    FROM Mesure m
    INNER JOIN Capteur c ON m.id_capteur = c.id_capteur
    INNER JOIN Salle s ON c.id_salle = s.id_salle
    WHERE s.nom = '$salle_protege' AND c.type_capteur = 'humidity'
    ORDER BY m.date_mesure DESC, m.heure_mesure DESC
    LIMIT 20";
$res_hum = mysqli_query($connexion, $requete_hum);
$data_hum = array();
while ($ligne = mysqli_fetch_assoc($res_hum)) {
    $data_hum[] = $ligne['valeur'] + 0;
}
$data_hum = array_reverse($data_hum);

// last values for gauges
$derniere_temp = count($data_temp) > 0 ? $data_temp[count($data_temp) - 1] : 0;
$derniere_co2 = count($data_co2) > 0 ? $data_co2[count($data_co2) - 1] : 0;
$derniere_hum = count($data_hum) > 0 ? $data_hum[count($data_hum) - 1] : 0;

mysqli_close($connexion);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphiques - SAE 23</title>
    <meta http-equiv="refresh" content="60">
    <link rel="stylesheet" href="style.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart', 'gauge']});
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            drawGauges();
            drawTemp();
            drawCO2();
            drawHum();
        }

        function drawGauges() {
            var data = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['Temp °C', <?php echo $derniere_temp; ?>],
                ['CO2 ppm', <?php echo $derniere_co2; ?>],
                ['Humidite %', <?php echo $derniere_hum; ?>]
            ]);

            var optionsTemp = {
                width: 200, height: 200,
                min: 0, max: 40,
                greenFrom: 18, greenTo: 24,
                yellowFrom: 24, yellowTo: 28,
                redFrom: 28, redTo: 40,
                minorTicks: 5
            };

            var optionsCO2 = {
                width: 200, height: 200,
                min: 0, max: 2000,
                greenFrom: 0, greenTo: 800,
                yellowFrom: 800, yellowTo: 1200,
                redFrom: 1200, redTo: 2000,
                minorTicks: 5
            };

            var optionsHum = {
                width: 200, height: 200,
                min: 0, max: 100,
                greenFrom: 30, greenTo: 60,
                yellowFrom: 60, yellowTo: 75,
                redFrom: 75, redTo: 100,
                minorTicks: 5
            };

            var dataTemp = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['Temp °C', <?php echo $derniere_temp; ?>]
            ]);
            var dataCO2 = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['CO2 ppm', <?php echo $derniere_co2; ?>]
            ]);
            var dataHum = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['Humidite %', <?php echo $derniere_hum; ?>]
            ]);

            new google.visualization.Gauge(document.getElementById('gauge_temp')).draw(dataTemp, optionsTemp);
            new google.visualization.Gauge(document.getElementById('gauge_co2')).draw(dataCO2, optionsCO2);
            new google.visualization.Gauge(document.getElementById('gauge_hum')).draw(dataHum, optionsHum);
        }

        function drawTemp() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Heure');
            data.addColumn('number', 'Temperature (°C)');

            var labels = <?php echo json_encode($labels_temp); ?>;
            var values = <?php echo json_encode($data_temp); ?>;

            for (var i = 0; i < labels.length; i++) {
                data.addRow([labels[i], values[i]]);
            }

            var options = {
                title: 'Temperature (°C)',
                hAxis: {title: 'Heure'},
                vAxis: {title: '°C', minValue: 0},
                colors: ['#e74c3c'],
                areaOpacity: 0.3,
                legend: {position: 'none'}
            };

            var chart = new google.visualization.AreaChart(document.getElementById('chart_temp'));
            chart.draw(data, options);
        }

        function drawCO2() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Heure');
            data.addColumn('number', 'CO2 (ppm)');

            var labels = <?php echo json_encode($labels_temp); ?>;
            var values = <?php echo json_encode($data_co2); ?>;

            for (var i = 0; i < labels.length; i++) {
                if (i < values.length) {
                    data.addRow([labels[i], values[i]]);
                }
            }

            var options = {
                title: 'CO2 (ppm)',
                hAxis: {title: 'Heure'},
                vAxis: {title: 'ppm', minValue: 0},
                colors: ['#3498db'],
                areaOpacity: 0.3,
                legend: {position: 'none'}
            };

            var chart = new google.visualization.AreaChart(document.getElementById('chart_co2'));
            chart.draw(data, options);
        }

        function drawHum() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Heure');
            data.addColumn('number', 'Humidite (%)');

            var labels = <?php echo json_encode($labels_temp); ?>;
            var values = <?php echo json_encode($data_hum); ?>;

            for (var i = 0; i < labels.length; i++) {
                if (i < values.length) {
                    data.addRow([labels[i], values[i]]);
                }
            }

            var options = {
                title: 'Humidite (%)',
                hAxis: {title: 'Heure'},
                vAxis: {title: '%', minValue: 0, maxValue: 100},
                colors: ['#2ecc71'],
                areaOpacity: 0.3,
                legend: {position: 'none'}
            };

            var chart = new google.visualization.AreaChart(document.getElementById('chart_hum'));
            chart.draw(data, options);
        }
    </script>
</head>
<body>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="consultation.php">Consultation</a>
        <a href="graphiques.php">Graphiques</a>
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
        <h1>Graphiques - <?php echo htmlspecialchars($salle_choisie); ?></h1>

        <form method="GET">
            <label>Salle :</label>
            <select name="salle">
                <?php foreach ($liste_salles as $s) { ?>
                    <option value="<?php echo htmlspecialchars($s); ?>" <?php if ($s == $salle_choisie) echo 'selected'; ?>><?php echo htmlspecialchars($s); ?></option>
                <?php } ?>
            </select>
            <input type="submit" value="Afficher">
        </form>

        <p>Rafraichissement automatique toutes les 60 secondes.</p>

        <!-- Jauges -->
        <h2>Derniere valeur</h2>
        <table class="table-jauges">
            <tr>
                <td><div id="gauge_temp"></div></td>
                <td><div id="gauge_co2"></div></td>
                <td><div id="gauge_hum"></div></td>
            </tr>
        </table>

        <!-- Courbes -->
        <h2>Temperature (°C)</h2>
        <div id="chart_temp" style="width:100%; height:300px;"></div>

        <h2>CO2 (ppm)</h2>
        <div id="chart_co2" style="width:100%; height:300px;"></div>

        <h2>Humidite (%)</h2>
        <div id="chart_hum" style="width:100%; height:300px;"></div>

        <footer>
            <p>SAE 23 - BUT R&T - IUT de Blagnac - 2026</p>
        </footer>
    </div>
</body>
</html>
