import paho.mqtt.client as mqtt
import mariadb
import json
import sys

# MariaDB Connection
try:
    conn = mariadb.connect(
        user="flood_user",
        password="your_password",
        host="127.0.0.1",
        database="flood_system"
    )
    cur = conn.cursor()
except mariadb.Error as e:
    print(f"Error connecting to MariaDB: {e}")
    sys.exit(1)

def on_message(client, userdata, msg):
    try:
        data = json.loads(msg.payload.decode())
        cur.execute(
            "INSERT INTO sensor_data (temp, hum, water_analog, distance_cm) VALUES (?, ?, ?, ?)",
            (data['temp'], data['hum'], data['water_analog'], data['distance_cm'])
        )
        conn.commit()
        print("Data inserted successfully")
    except Exception as e:
        print(f"Error: {e}")

client = mqtt.Client()
client.on_message = on_message
client.connect("localhost", 1883, 60)
client.subscribe("esp32/flood_system")

print("Bridge started, waiting for MQTT messages...")
client.loop_forever()
