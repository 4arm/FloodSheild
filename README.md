🌊 FloodShield – IoT Flood Monitoring System

FloodShield is a full-stack IoT-based flood monitoring and alert system that integrates ESP32 sensors, MQTT messaging, a Google Cloud Platform (GCP) VM, MariaDB, a web dashboard, and a Raspberry Pi camera module for visual monitoring.

This project is designed for real-time environmental sensing, cloud-based data storage, and web-based visualization, making it ideal for flood detection, monitoring, and research applications.

🧩 System Architecture Overview
ESP32 Sensors
   ↓ (MQTT)
Mosquitto Broker (GCP VM)
   ↓
Python MQTT Bridge
   ↓
MariaDB Database
   ↓
Apache + PHP Dashboard


📷 Raspberry Pi Camera uploads images to the server for visual flood confirmation.

🛠️ Hardware Components

ESP32 (NodeMCU-32S)

DHT11 Temperature & Humidity Sensor

Water Level Sensor (Analog)

Ultrasonic Distance Sensor

Servo Motor

Raspberry Pi with Camera Module

📦 Arduino Setup (ESP32)
1️⃣ Required Libraries

Install via Arduino IDE → Sketch > Include Library > Manage Libraries...

Library Name	Author	Purpose
PubSubClient	Nick O'Leary	MQTT communication with GCP VM
DHT sensor library	Adafruit	Reads DHT11 temperature & humidity
Adafruit Unified Sensor	Adafruit	Dependency for DHT
ESP32Servo	Kevin Harrington	Servo PWM control for ESP32
2️⃣ ESP32 Code

MQTT topic: flood/sensor

Database key fixed: water_analog

📄 ESP32 Sketch:
👉 https://raw.githubusercontent.com/4arm/FloodSheild/refs/heads/main/Arduino/esp32.ino

3️⃣ Critical Arduino Configuration

Board: NodeMCU-32S

Upload Speed: 115200

GCP VM External IP: 34.124.166.142 (verify if VM restarted)

MQTT Topic: flood/sensor

☁️ GCP VM Setup
Required Services

MariaDB

Mosquitto MQTT Broker

Apache2 + PHP

🗄️ MariaDB Database Setup
Create User
CREATE USER 'flood_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON flood_system.* TO 'flood_user'@'localhost';
FLUSH PRIVILEGES;

Create Database & Table
CREATE DATABASE IF NOT EXISTS flood_system;
USE flood_system;

CREATE TABLE sensor_data (
  id INT AUTO_INCREMENT PRIMARY KEY,
  temp FLOAT,
  hum FLOAT,
  water_analog INT,
  distance_cm INT,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);

📡 Mosquitto MQTT Broker Configuration
Install
sudo apt update && sudo apt upgrade -y
sudo apt install mosquitto mosquitto-clients -y
sudo systemctl enable mosquitto

Configuration
sudo nano /etc/mosquitto/conf.d/default.conf

listener 1883 0.0.0.0
allow_anonymous true

sudo systemctl restart mosquitto

Firewall Rule (GCP)

Port: 1883

Protocol: TCP

Source: 0.0.0.0/0

MQTT Test
mosquitto_sub -h localhost -t "flood/sensor"
mosquitto_pub -h localhost -t "flood/sensor" -m "Flood data test"

🐍 Python MQTT → MariaDB Bridge
Setup Virtual Environment
sudo apt install python3-venv -y
mkdir ~/flood_bridge && cd ~/flood_bridge
python3 -m venv venv
source venv/bin/activate
pip install paho-mqtt mysql-connector-python


📄 Bridge Script:
👉 https://raw.githubusercontent.com/4arm/FloodSheild/refs/heads/main/GCP%20VM/mqtt_bridge.py

python3 mqtt_bridge.py
deactivate

🌐 Web Dashboard (Apache + PHP)
Install Apache & PHP
sudo apt install apache2 php libapache2-mod-php php-mysql -y
sudo systemctl enable apache2

Firewall

Port: 80

Dashboard File
sudo nano /var/www/html/index.php


📄 Dashboard Code:
👉 https://raw.githubusercontent.com/4arm/FloodSheild/refs/heads/main/GCP%20VM/index.php

sudo chown www-data:www-data /var/www/html/index.php
sudo chmod 644 /var/www/html/index.php
sudo rm /var/www/html/index.html


🌍 Access at:

http://[GCP_EXTERNAL_IP]

📤 Image Upload Configuration
sudo mkdir -p /var/www/html/uploads
sudo chown www-data:www-data /var/www/html/uploads
sudo chmod 755 /var/www/html/uploads


📄 Upload Handler:
👉 https://raw.githubusercontent.com/4arm/FloodSheild/refs/heads/main/GCP%20VM/upload.php

Update PHP limits:

upload_max_filesize = 10M
post_max_size = 10M

sudo systemctl restart apache2

📷 Raspberry Pi Camera Setup
Install Dependencies
sudo apt install -y python3-venv python3-pip \
libglib2.0-0 libsm6 libxext6 libxrender1 \
libgl1-mesa-glx libgtk-3-dev \
libavcodec-dev libavformat-dev libswscale-dev

Virtual Environment
mkdir ~/flood_camera && cd ~/flood_camera
python3 -m venv venv
source venv/bin/activate
pip install opencv-python requests


📄 Camera Script:
👉 https://raw.githubusercontent.com/4arm/FloodSheild/refs/heads/main/RaspberryPi/capture.py

python3 capture.py

🛡️ Troubleshooting (UFW Firewall)
sudo apt install ufw
sudo ufw allow ssh
sudo ufw allow 1883
sudo ufw enable
sudo ufw status

✅ Features Summary

📡 Real-time MQTT sensor data

🗄️ Cloud-based MariaDB storage

🌐 Web dashboard visualization

📷 Image uploads from Raspberry Pi

☁️ Fully hosted on GCP VM

🚀 Future Enhancements

Telegram / Email alerts

Authentication for MQTT & Dashboard

HTTPS (SSL)

Historical charts & analytics

AI-based flood detection

👨‍💻 Author

FloodShield Project
GitHub: https://github.com/4arm/FloodSheild
