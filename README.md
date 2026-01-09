# Flood Shield: IoT Flood Monitoring & AI Security System

Flood Shield is an integrated Edge-to-Cloud IoT solution designed for real-time flood monitoring and site security. The system utilizes a Raspberry Pi to collect sensor data and perform on-device Human Detection using Computer Vision, syncing all data to a Google Cloud Platform (GCP) VM for visualization.

---

## 🚀 System Architecture

The project follows a three-tier architecture:

1.  **Edge Layer (Raspberry Pi):** Collects ultrasonic/water sensor data and captures images. Runs a **HOG (Histogram of Oriented Gradients)** model to detect human presence locally.
2.  **Transmission Layer:** Data is pushed via **HTTP POST** requests to a cloud-hosted API.
3.  **Cloud Layer (GCP VM):** A LAMP stack server that stores sensor history in **MariaDB** and serves a real-time **Web Dashboard** with live trend charts and camera feeds.



---

## ✨ Features

- **Real-time Monitoring:** 10-second refresh rate for sensor data (Temp, Humidity, Water Level, Distance).
- **Edge AI:** On-device human detection to reduce cloud processing load.
- **Instant Alerts:** Telegram Bot integration for immediate photo notifications when a human is detected.
- **Data Visualization:** Interactive dual-axis line charts (Chart.js) to track water level trends.
- **Automated Logging:** 10-point activity history displayed on the dashboard.

---

## 🛠️ Technical Stack

| Component | Technology |
| :--- | :--- |
| **Edge Hardware** | Raspberry Pi 4, HC-SR04 Ultrasonic, Analog Water Sensor, USB Camera |
| **AI / Vision** | Python 3, OpenCV (HOG + Linear SVM) |
| **Cloud Host** | Google Cloud Platform (Compute Engine - Debian/Ubuntu) |
| **Web Server** | Apache2, PHP 8.x |
| **Database** | MariaDB (MySQL) |
| **Frontend** | Bootstrap 5, FontAwesome 6, Chart.js |

---

## 📦 Setup & Installation

### 1. GCP VM (Cloud Server)
- Install Apache, PHP, and MariaDB.
- Configure the `flood_system` database using the provided schema.
- Set directory permissions: `sudo chown -R www-data:www-data /var/www/html/uploads`.

### 2. Raspberry Pi (Edge)
- Enable Legacy Camera Support via `raspi-config`.
- Create a Python virtual environment: `python3 -m venv venv`.
- Install dependencies:
  ```bash
  pip install opencv-python numpy requests
