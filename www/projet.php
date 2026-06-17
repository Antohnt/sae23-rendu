<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de projet - SAE 23</title>
    <link rel="stylesheet" href="style.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['gantt']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Task ID');
            data.addColumn('string', 'Task Name');
            data.addColumn('string', 'Resource');
            data.addColumn('date', 'Start Date');
            data.addColumn('date', 'End Date');
            data.addColumn('number', 'Duration');
            data.addColumn('number', 'Percent Complete');
            data.addColumn('string', 'Dependencies');

            data.addRows([
                // LIVRABLE L1 - GANTT + Schema BD
                ['L1_analyse', 'Analyse consignes + roadmap', 'Antonin',
                    new Date(2026, 5, 2), new Date(2026, 5, 3), null, 100, null],
                ['L1_gantt', 'Creation GANTT previsionnel', 'Antonin',
                    new Date(2026, 5, 2), new Date(2026, 5, 3), null, 100, null],
                ['L1_css', 'Maquette CSS du site', 'Madeleine',
                    new Date(2026, 5, 2), new Date(2026, 5, 4), null, 100, null],
                ['L1_sql', 'Conception schema SQL', 'Timothee',
                    new Date(2026, 5, 2), new Date(2026, 5, 4), null, 100, null],
                ['L1_vm', 'Installation VM + MySQL', 'Timothee',
                    new Date(2026, 5, 3), new Date(2026, 5, 5), null, 100, null],
                ['L1_html', 'Page accueil HTML', 'Madeleine',
                    new Date(2026, 5, 4), new Date(2026, 5, 6), null, 100, null],
                ['L1_import', 'Import SQL + capture PhpMyAdmin', 'Timothee',
                    new Date(2026, 5, 5), new Date(2026, 5, 7), null, 100, 'L1_sql'],

                // LIVRABLE L2/L3 - Node-RED + Grafana
                ['L2_docker', 'Install Docker + conteneurs', 'Timothee',
                    new Date(2026, 5, 8), new Date(2026, 5, 10), null, 100, null],
                ['L2_nodered', 'Config flow Node-RED (MQTT vers InfluxDB)', 'Timothee',
                    new Date(2026, 5, 10), new Date(2026, 5, 13), null, 100, 'L2_docker'],
                ['L2_grafana', 'Dashboard Grafana (7 panels)', 'Antonin',
                    new Date(2026, 5, 11), new Date(2026, 5, 14), null, 100, 'L2_docker'],
                ['L2_admin', 'Page administration (CRUD)', 'Antonin',
                    new Date(2026, 5, 8), new Date(2026, 5, 11), null, 100, null],
                ['L2_consult', 'Page consultation (HTML/CSS)', 'Madeleine',
                    new Date(2026, 5, 8), new Date(2026, 5, 11), null, 100, null],
                ['L2_tableaux', 'Mise en forme tableaux', 'Madeleine',
                    new Date(2026, 5, 11), new Date(2026, 5, 14), null, 100, 'L2_consult'],

                // LIVRABLE L4 - Site web final
                ['L4_collecte', 'Script collecte MQTT vers MySQL', 'Timothee',
                    new Date(2026, 5, 15), new Date(2026, 5, 17), null, 100, null],
                ['L4_crontab', 'Automatisation crontab', 'Timothee',
                    new Date(2026, 5, 17), new Date(2026, 5, 18), null, 100, 'L4_collecte'],
                ['L4_gestion', 'Page gestion (min/max/moy)', 'Timothee',
                    new Date(2026, 5, 17), new Date(2026, 5, 19), null, 100, 'L4_collecte'],
                ['L4_connexion', 'Page connexion + sessions PHP', 'Antonin',
                    new Date(2026, 5, 15), new Date(2026, 5, 17), null, 100, null],
                ['L4_secu', 'Securisation acces (sessions)', 'Antonin',
                    new Date(2026, 5, 17), new Date(2026, 5, 18), null, 100, 'L4_connexion'],
                ['L4_projet', 'Page gestion de projet (GANTT)', 'Madeleine',
                    new Date(2026, 5, 15), new Date(2026, 5, 18), null, 100, null],
                ['L4_css', 'CSS responsive + finitions', 'Madeleine',
                    new Date(2026, 5, 18), new Date(2026, 5, 20), null, 100, 'L4_projet'],
                ['L4_doc', 'Documentation code', 'Antonin',
                    new Date(2026, 5, 19), new Date(2026, 5, 21), null, 100, 'L4_secu'],
                ['L4_tests', 'Tests integration + corrections', 'Commun',
                    new Date(2026, 5, 19), new Date(2026, 5, 21), null, 100, null],
                ['L4_push', 'Push Github + verification', 'Commun',
                    new Date(2026, 5, 21), new Date(2026, 5, 22), null, 100, 'L4_tests'],

                // ORAL
                ['O_slides', 'Preparation slides', 'Antonin',
                    new Date(2026, 5, 22), new Date(2026, 5, 25), null, 0, null],
                ['O_demo', 'Preparation demo technique', 'Timothee',
                    new Date(2026, 5, 22), new Date(2026, 5, 25), null, 0, null],
                ['O_oral', 'Preparation partie orale (site)', 'Madeleine',
                    new Date(2026, 5, 22), new Date(2026, 5, 25), null, 0, null],
                ['O_repet', 'Repetition orale', 'Commun',
                    new Date(2026, 5, 25), new Date(2026, 5, 26), null, 0, 'O_slides,O_demo,O_oral'],
                ['O_soutenance', 'SOUTENANCE ORALE', 'Commun',
                    new Date(2026, 5, 26), new Date(2026, 5, 27), null, 0, 'O_repet']
            ]);

            var options = {
                height: 800,
                gantt: {
                    trackHeight: 25,
                    barCornerRadius: 3,
                    labelStyle: {
                        fontName: 'Arial',
                        fontSize: 12
                    }
                }
            };

            var chart = new google.visualization.Gantt(document.getElementById('chart_gantt'));
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
        <h1>Gestion de projet</h1>

        <!-- Team -->
        <h2>Equipe projet</h2>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Antonin Marchand</td><td>Chef de projet / Developpeur</td></tr>
                <tr><td>Timothee Jean-Pierre</td><td>Developpeur backend</td></tr>
                <tr><td>Madeleine Nuadi</td><td>Developpeur frontend</td></tr>
            </tbody>
        </table>

        <!-- GANTT interactif -->
        <h2>Diagramme de GANTT</h2>
        <div id="chart_gantt"></div>

        <!-- Deliverables -->
        <h2>Livrables</h2>
        <table>
            <thead>
                <tr>
                    <th>Livrable</th>
                    <th>Contenu</th>
                    <th>Deadline</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>L1</td><td>GANTT + Schema conception BD</td><td>07/06/2026</td></tr>
                <tr><td>L2/L3</td><td>Flow Node-RED + Dashboard Grafana</td><td>14/06/2026</td></tr>
                <tr><td>L4</td><td>Site web complet + URL Github</td><td>21/06/2026</td></tr>
                <tr><td>Oral</td><td>Demonstration + Presentation</td><td>Semaine 26</td></tr>
            </tbody>
        </table>

        <!-- Technical choices -->
        <h2>Choix techniques</h2>
        <ul>
            <li>4 batiments : A, B, C, E (30 salles equipees)</li>
            <li>4 types de capteurs : temperature, humidite, CO2, luminosite</li>
            <li>Protocole MQTT pour la collecte (broker mqtt.iut-blagnac.fr, port 8883 TLS)</li>
            <li>Stockage MySQL (5 tables : Batiment, Salle, Capteur, Mesure, Utilisateur)</li>
            <li>Script Bash de collecte automatise via crontab (toutes les 10 min)</li>
            <li>Sessions PHP pour la securisation des acces</li>
            <li>Docker pour les conteneurs (Mosquitto, Node-RED, InfluxDB, Grafana)</li>
            <li>Hebergement sur conteneur LXC Proxmox (CT 104)</li>
            <li>Acces distant via Tailscale VPN</li>
        </ul>

        <h2>Architecture de la base de donnees</h2>
        <table>
            <thead>
                <tr>
                    <th>Table</th>
                    <th>Colonnes principales</th>
                    <th>Relations</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Batiment</td><td>id_batiment, nom, adresse</td><td>1-N avec Salle</td></tr>
                <tr><td>Salle</td><td>id_salle, nom, etage, type, capacite</td><td>FK id_batiment</td></tr>
                <tr><td>Capteur</td><td>id_capteur, nom, type_capteur, unite</td><td>FK id_salle</td></tr>
                <tr><td>Mesure</td><td>id_mesure, valeur, date_mesure, heure_mesure</td><td>FK id_capteur</td></tr>
                <tr><td>Utilisateur</td><td>id_utilisateur, login, mot_de_passe, role</td><td>FK id_batiment (nullable)</td></tr>
            </tbody>
        </table>

        <h2>Journal des problemes</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Probleme</th>
                    <th>Solution</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>02/06</td>
                    <td>Script test_mqtt.php : dependance mosquitto-clients</td>
                    <td>Installer le paquet avant de tester</td>
                </tr>
                <tr>
                    <td>02/06</td>
                    <td>Capteurs disponibles uniquement 7h30-19h</td>
                    <td>Fonctionnement normal (pas un bug)</td>
                </tr>
                <tr>
                    <td>03/06</td>
                    <td>Tailscale crash : /dev/net/tun absent dans LXC</td>
                    <td>Ajout cgroup2 + mount dans config Proxmox</td>
                </tr>
                <tr>
                    <td>03/06</td>
                    <td>DNS pointe vers Tailscale avant connexion</td>
                    <td>nameserver 8.8.8.8 temporaire dans resolv.conf</td>
                </tr>
                <tr>
                    <td>03/06</td>
                    <td>Script install_lampp.sh : import SQL echoue</td>
                    <td>sudo bash -c 'mysql ... &lt; fichier'</td>
                </tr>
                <tr>
                    <td>03/06</td>
                    <td>Table Utilisateur non creee (FK constraint fails)</td>
                    <td>Reimport avec DELETE dans l'ordre correct</td>
                </tr>
                <tr>
                    <td>09/06</td>
                    <td>collecte_mesures.sh : retours chariot Windows (\r)</td>
                    <td>sed -i "s/\r$//" sur le serveur</td>
                </tr>
                <tr>
                    <td>09/06</td>
                    <td>PhpMyAdmin : Popper.js manquant (bootstrap.bundle.min.js)</td>
                    <td>Concatenation popper.min.js + bootstrap.bundle.js</td>
                </tr>
            </tbody>
        </table>

        <footer>
            <p>SAE 23 - BUT R&T - IUT de Blagnac - 2026</p>
            <p>Projet realise par Antonin Marchand, Timothee Jean-Pierre et Madeleine Nuadi</p>
        </footer>
    </div>
</body>
</html>
