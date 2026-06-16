<?php
// Start session for navbar display (public page, no access restriction)
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de projet - SAE 23</title>
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
                <tr><td>Antonin Marchand</td><td>Chef de projet</td></tr>
                <tr><td>Timothee Jean-Pierre</td><td>Developpeur / Ouvrier</td></tr>
            </tbody>
        </table>

        <!-- GANTT -->
        <h2>Diagramme de GANTT previsionnel</h2>
        <table>
            <thead>
                <tr>
                    <th>Phase</th>
                    <th>Tache</th>
                    <th>Debut</th>
                    <th>Fin</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Phase 0</td>
                    <td>Infrastructure (CT LXC, LAMPP, MySQL)</td>
                    <td>02/06</td>
                    <td>04/06</td>
                    <td>Termine</td>
                </tr>
                <tr>
                    <td>L1</td>
                    <td>Schema BD + GANTT + preparation rendu</td>
                    <td>02/06</td>
                    <td>07/06</td>
                    <td>Termine</td>
                </tr>
                <tr>
                    <td>L2/L3</td>
                    <td>Docker (Mosquitto, Node-RED, InfluxDB, Grafana)</td>
                    <td>08/06</td>
                    <td>09/06</td>
                    <td>En cours</td>
                </tr>
                <tr>
                    <td>L2/L3</td>
                    <td>Flow Node-RED (MQTT vers InfluxDB)</td>
                    <td>10/06</td>
                    <td>11/06</td>
                    <td>A faire</td>
                </tr>
                <tr>
                    <td>L2/L3</td>
                    <td>Dashboard Grafana (4 panels)</td>
                    <td>12/06</td>
                    <td>13/06</td>
                    <td>A faire</td>
                </tr>
                <tr>
                    <td>L4</td>
                    <td>Pages web (consultation, gestion, admin, projet)</td>
                    <td>15/06</td>
                    <td>17/06</td>
                    <td>Termine</td>
                </tr>
                <tr>
                    <td>L4</td>
                    <td>Script collecte MQTT -> MySQL + crontab</td>
                    <td>18/06</td>
                    <td>18/06</td>
                    <td>Termine</td>
                </tr>
                <tr>
                    <td>L4</td>
                    <td>Tests, corrections, documentation</td>
                    <td>19/06</td>
                    <td>20/06</td>
                    <td>A faire</td>
                </tr>
                <tr>
                    <td>L4</td>
                    <td>Rendu final + URL Github</td>
                    <td>21/06</td>
                    <td>21/06</td>
                    <td>A faire</td>
                </tr>
            </tbody>
        </table>

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
            <li>2 batiments : Batiment E et Batiment C</li>
            <li>4 capteurs etudies : temperature E101, CO2 E101, temperature C101, humidite C101</li>
            <li>Protocole MQTT pour la collecte (broker mqtt.iut-blagnac.fr)</li>
            <li>Stockage MySQL (5 tables : Batiment, Salle, Capteur, Mesure, Utilisateur)</li>
            <li>Script PHP de collecte automatise via crontab</li>
            <li>Sessions PHP pour la securisation des acces</li>
            <li>Docker pour les conteneurs (Mosquitto, Node-RED, InfluxDB, Grafana)</li>
            <li>Hebergement sur conteneur LXC Proxmox (CT 104)</li>
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
                    <td>sudo bash -c 'mysql ... < fichier'</td>
                </tr>
                <tr>
                    <td>03/06</td>
                    <td>Table Utilisateur non creee (FK constraint fails)</td>
                    <td>Reimport avec DELETE dans l'ordre correct</td>
                </tr>
            </tbody>
        </table>

        <footer>
            <p>SAE 23 - BUT R&T - IUT de Blagnac - 2026</p>
            <p>Projet realise par Antonin Marchand et Timothee Jean-Pierre</p>
        </footer>
    </div>
</body>
</html>
