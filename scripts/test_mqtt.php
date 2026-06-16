#!/opt/lampp/bin/php
<?php
// test_mqtt.php
// MQTT broker connection test script using mosquitto_sub

$broker = "mqtt.iut-blagnac.fr";
$port = 1883;
$topic = "AM107/by-room/E208/data";
$timeout = 15;

echo "Broker : $broker\n";
echo "Port : $port\n";
echo "Topic : $topic\n";
echo "Timeout : $timeout seconds\n";
echo "---\n";

// Build the mosquitto_sub command
$cmd = "mosquitto_sub -h $broker -p $port -t \"$topic\" -C 1 -W $timeout 2>&1";

echo "Command : $cmd\n";
echo "Waiting for message...\n\n";

// Execute the command
$output = shell_exec($cmd);

if ($output === null || $output === "") {
    echo "ERROR : No data received (timeout after $timeout seconds).\n";
    echo "Check :\n";
    echo "  - mosquitto-clients is installed\n";
    echo "  - Broker is accessible\n";
    echo "  - Sensors are publishing (between 7:30 AM and 7 PM)\n";
    exit(1);
} else {
    echo "OK - Message received :\n";
    echo $output . "\n";

    // Try to decode JSON
    $data = json_decode($output, true);
    if ($data !== null) {
        echo "\nDecoded data :\n";
        if (isset($data[0]['temperature'])) {
            echo "  Temperature : " . $data[0]['temperature'] . " C\n";
        }
        if (isset($data[0]['humidity'])) {
            echo "  Humidity : " . $data[0]['humidity'] . " %\n";
        }
        if (isset($data[0]['co2'])) {
            echo "  CO2 : " . $data[0]['co2'] . " ppm\n";
        }
    }
    echo "\nMQTT connection OK !\n";
    exit(0);
}
?>
