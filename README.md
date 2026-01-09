# 🌊 FloodShield Project Documentation

FloodShield is an integrated IoT ecosystem designed to monitor water levels, temperature, and humidity in real-time. It leverages an ESP32 for sensor data collection, a GCP Cloud VM for data storage and web visualization, and a Raspberry Pi for AI-driven visual monitoring and instant Telegram alerts.

## 🛠️ Section 1: ESP32 Setup (Arduino IDE)

### 1. Required Libraries
Open your Arduino IDE, go to **Sketch > Include Library > Manage Libraries...**, and install:

| Library Name | Author | Purpose |
| :--- | :--- | :--- |
| **PubSubClient** | Nick O'Leary | Handles MQTT connection to GCP. |
| **DHT sensor library** | Adafruit | Reads temperature and humidity from DHT11. |
| **Adafruit Unified Sensor** | Adafruit | Required dependency for the DHT library. |
| **ESP32Servo** | Kevin Harrington | PWM servo control specifically for ESP32. |

### 2. Complete Refined Code
Download or copy the full source code from the repository:
> 📄 **Source:** [esp32.ino (GitHub)](https://raw.githubusercontent.com/4arm/FloodSheild/refs/heads/main/Arduino/esp32.ino)

### 3. Critical Setup Steps
* **Board Selection:** Tools > Board > ESP32 Arduino > **NodeMCU-32S**.
* **Upload Speed:** Set to **115200**.
* **GCP IP:** Verify `34.124.166.142` is still your VM's External IP.
* **MQTT Topic:** Ensure the Python Bridge subscribes to `flood/sensor`.

---

## ☁️ Section 2: GCP VM Configuration

### 1. Infrastructure Installation
Run these commands to install the core services:
```
sudo apt update && sudo apt upgrade -y
sudo apt install mariadb-server apache2 php libapache2-mod-php php-mysql mosquitto mosquitto-clients -y
sudo systemctl enable mosquitto apache2 mariadb
```

2. MariaDB Database Setup
Log in to MariaDB by running```sudo mariadb```, then execute the following SQL commands:

```
-- 1. User and Database Creation
CREATE DATABASE IF NOT EXISTS flood_system;
CREATE USER 'flood_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON flood_system.* TO 'flood_user'@'localhost';
FLUSH PRIVILEGES;

-- 2. Schema Creation
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
```
3. Mosquitto MQTT Configuration
Open the configuration file:
```
sudo nano /etc/mosquitto/conf.d/default.conf
```
Paste the following content:

```
listener 1883 0.0.0.0
allow_anonymous true
```
Restart the service to apply changes:


```sudo systemctl restart mosquitto```

4. Python Bridge (MQTT to Database)
Run this inside a virtual environment to start syncing data:

```
mkdir ~/flood_bridge && cd ~/flood_bridge
python3 -m venv venv
source venv/bin/activate
pip install paho-mqtt mysql-connector-python
```
# Download and run bridge
```
wget [https://raw.githubusercontent.com/4arm/FloodSheild/refs/heads/main/GCP%20VM/mqtt_bridge.py (https://raw.githubusercontent.com/4arm/FloodSheild/refs/heads/main/GCP%20VM/mqtt_bridge.py)
```

```
python3 mqtt_bridge.py
```

5. Web Dashboard & File Uploads
Set up the Apache web root and the upload script:

```
# Create uploads folder with permissions
sudo mkdir -p /var/www/html/uploads
sudo chown www-data:www-data /var/www/html/uploads
sudo chmod 755 /var/www/html/uploads
```

# Download Dashboard and Upload Script
```
sudo wget -O /var/www/html/index.php [https://raw.githubusercontent.com/4arm/FloodSheild/refs/heads/main/GCP%20VM/index.php](https://raw.githubusercontent.com/4arm/FloodSheild/refs/heads/main/GCP%20VM/index.php)
sudo wget -O /var/www/html/upload.php [https://raw.githubusercontent.com/4arm/FloodSheild/refs/heads/main/GCP%20VM/upload.php](https://raw.githubusercontent.com/4arm/FloodSheild/refs/heads/main/GCP%20VM/upload.php)
sudo rm /var/www/html/index.html
```
📸 Section 3: Raspberry Pi (Camera & AI)
1. System Dependencies
Install necessary image processing libraries:

```
sudo apt update
sudo apt install -y python3-venv python3-pip libglib2.0-0 libsm6 libxext6 \
libxrender1 libgl1-mesa-glx libgtk-3-dev libavcodec-dev libavformat-dev libswscale-dev
```
2. Python Environment Setup

```
mkdir ~/flood_camera && cd ~/flood_camera
python3 -m venv venv
source venv/bin/activate
pip install --upgrade pip
pip install opencv-python requests
```
3. Execution
Download your capture script and run it:

```
wget [https://raw.githubusercontent.com/4arm/FloodSheild/refs/heads/main/RaspberryPi/capture.py](https://raw.githubusercontent.com/4arm/FloodSheild/refs/heads/main/RaspberryPi/capture.py)
```
# Edit the script to add your VM IP and Telegram Token
```nano capture.py ```

# Run the script
```
python3 capture.py
```
🛡️ Troubleshooting & Security
GCP Firewall Settings
Ensure these ports are open in the GCP Console > VPC Network > Firewall:

TCP 1883: MQTT

TCP 80: HTTP Dashboard

TCP 22: SSH Access

Local Firewall (UFW)
If you encounter connection blocks on the VM, configure UFW:

Bash
```
sudo apt install ufw
sudo ufw allow ssh
sudo ufw allow 1883
sudo ufw allow 80
sudo ufw enable
```
