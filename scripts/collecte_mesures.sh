#!/bin/bash
# collecte_mesures.sh
# read AM107 sensors via MQTT and save in MySQL
# run by crontab every 10 min

BROKER="mqtt.iut-blagnac.fr"
PORT=8883
UTILISATEUR="student"
MOT_DE_PASSE="student"
DELAI=30

DB_BASE="sae23"
DB_USER="root"
DB_PASS="sae23"

TOPIC="sensors/AM107/by-room/#"

DATE_JOUR=$(date +%Y-%m-%d)
HEURE=$(date +%H:%M:%S)


# get one message per room (30 rooms publish every 10 min)
# wait 30 seconds to catch a batch
MESSAGES=$(mosquitto_sub -h "$BROKER" -p "$PORT" -u "$UTILISATEUR" -P "$MOT_DE_PASSE" --insecure --tls-version tlsv1.2 -t "$TOPIC" -W "$DELAI")

if [ -z "$MESSAGES" ]; then
    exit 0
fi


echo "$MESSAGES" | while read -r LINE; do
    if [ -z "$LINE" ]; then
        continue
    fi

    # extract room from the JSON metadata (second object in array)
    ROOM=$(echo "$LINE" | grep -o '"room":"[^"]*"' | cut -d'"' -f4)
    if [ -z "$ROOM" ]; then
        continue
    fi

    # extract each measurement value
    TEMP=$(echo "$LINE" | grep -o '"temperature":[0-9.]*' | cut -d':' -f2)
    HUM=$(echo "$LINE" | grep -o '"humidity":[0-9.]*' | cut -d':' -f2)
    CO2=$(echo "$LINE" | grep -o '"co2":[0-9.]*' | cut -d':' -f2)
    LUM=$(echo "$LINE" | grep -o '"illumination":[0-9.]*' | cut -d':' -f2)

    # save each type if value exists
    for TYPE_VAL in "temperature:$TEMP" "humidity:$HUM" "co2:$CO2" "illumination:$LUM"; do
        TYPE=$(echo "$TYPE_VAL" | cut -d':' -f1)
        VALEUR=$(echo "$TYPE_VAL" | cut -d':' -f2)

        if [ -z "$VALEUR" ]; then
            continue
        fi

        # find sensor id in database
        ID_CAPTEUR=$(mysql -u "$DB_USER" -p"$DB_PASS" "$DB_BASE" -N -e \
            "SELECT c.id_capteur FROM Capteur c INNER JOIN Salle s ON c.id_salle = s.id_salle WHERE s.nom = '$ROOM' AND c.type_capteur = '$TYPE' LIMIT 1")

        if [ -z "$ID_CAPTEUR" ]; then
            continue
        fi

        # save measure
        mysql -u "$DB_USER" -p"$DB_PASS" "$DB_BASE" -e \
            "INSERT INTO Mesure (valeur, date_mesure, heure_mesure, id_capteur) VALUES ('$VALEUR', '$DATE_JOUR', '$HEURE', '$ID_CAPTEUR')"

    done

done

