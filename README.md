🌊 Flood Shield Monitoring System
A complete IoT flood monitoring solution using ESP32 sensors, an MQTT broker on a GCP VM, a MariaDB database, and a Raspberry Pi AI camera for human detection and Telegram alerts.

🚀 1. Hardware Setup (NodeMCU-32S)
Required Libraries
Install these via the Library Manager in Arduino IDE:

PubSubClient (Nick O'Leary): MQTT communication.

DHT sensor library (Adafruit): Temperature/Humidity data.

Adafruit Unified Sensor (Adafruit): Required dependency.

ESP32Servo (Kevin Harrington): PWM control for the gate.

Configuration & Upload
Code: Download and open esp32.ino.

Board Selection: Tools > Board > ESP32 Arduino > NodeMCU-32S.

Upload Speed: 115200.

Critical Checks: * Update ssid and password.

Set mqtt_server to your GCP External IP (34.124.166.142).

☁️ 2. Cloud Server Setup (GCP VM)
Database (MariaDB)
Login to MariaDB (sudo mariadb) and run the following:

SQL

-- Create User
CREATE USER 'flood_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON flood_system.* TO 'flood_user'@'localhost';
FLUSH PRIVILEGES;

-- Setup Schema
CREATE DATABASE IF NOT EXISTS flood_system;
USE flood_system;

CREATE TABLE `sensor_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `temp` float DEFAULT NULL,
  `hum` float DEFAULT NULL,
  `water_analog` int(11) DEFAULT NULL,
  `distance_cm` int(11) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
MQTT Broker (Mosquitto)
Install: sudo apt install mosquitto mosquitto-clients -y

Configure: sudo nano /etc/mosquitto/conf.d/default.conf

Plaintext

listener 1883 0.0.0.0
allow_anonymous true
Restart: sudo systemctl restart mosquitto

Python Bridge (MQTT to DB)
Run this inside a Virtual Environment on your VM:

Bash

sudo apt install python3-venv -y
mkdir ~/flood_bridge && cd ~/flood_bridge
python3 -m venv venv
source venv/bin/activate
pip install paho-mqtt mysql-connector-python
Script: Download mqtt_bridge.py and run with python3 mqtt_bridge.py.

Web Server & Uploads (Apache2/PHP)
Install: sudo apt install apache2 php libapache2-mod-php php-mysql -y

Dashboard: Save index.php to /var/www/html/.

Upload Logic: Save upload.php to /var/www/html/.

Permissions:

Bash

sudo mkdir -p /var/www/html/uploads
sudo chown -R www-data:www-data /var/www/html/
sudo chmod 755 /var/www/html/uploads
📸 3. Edge AI Setup (Raspberry Pi)
System Requirements
Install dependencies for OpenCV:

Bash

sudo apt update
sudo apt install -y python3-venv python3-pip libglib2.0-0 libsm6 libxext6 libxrender1 libgl1-mesa-glx libgtk-3-dev
Environment Setup
Bash

mkdir ~/flood_camera && cd ~/flood_camera
python3 -m venv venv
source venv/bin/activate
pip install opencv-python requests
Run Detection
Download capture.py and run:

Bash

python3 capture.py
🛠️ Troubleshooting & Security
GCP Firewall Requirements
Ensure the following ports are open in the GCP Console:

TCP 1883: MQTT Broker

TCP 80: Web Dashboard

TCP 22: SSH

UFW (Uncomplicated Firewall)
If the bridge cannot connect, check your VM firewall:

Bash

sudo apt install ufw
sudo ufw allow ssh
sudo ufw allow 1883
sudo ufw allow 80
sudo ufw enable
PHP Upload Limits
If high-res images fail to upload, edit php.ini:

Plaintext

upload_max_filesize = 10M
post_max_size = 10M
Then run sudo systemctl restart apache2.
