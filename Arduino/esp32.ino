#include <WiFi.h>
#include <PubSubClient.h>
#include <DHT.h>
#include <ESP32Servo.h>

// --- Configuration ---
const char* ssid = "Armm";
const char* password = "12345678";
const char* mqtt_server = "34.124.166.142"; // e.g., "34.xx.xx.xx"
const int mqtt_port = 1883;

// --- Pin Definitions (Based on your image) ---
#define TRIG_PIN 5
#define ECHO_PIN 18
#define DHT_PIN 4
#define WATER_LEVEL_PIN 34
#define BUZZER_PIN 13
#define SERVO_PIN 12

// --- Objects ---
DHT dht(DHT_PIN, DHT11);
Servo gateServo;
WiFiClient espClient;
PubSubClient client(espClient);

void setup() {
  Serial.begin(115200);
  
  // Pin Modes
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(WATER_LEVEL_PIN, INPUT);

  dht.begin();
  gateServo.attach(SERVO_PIN);
  
  setup_wifi();
  client.setServer(mqtt_server, mqtt_port);
}

void setup_wifi() {
  delay(10);
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected. IP address: ");
  Serial.println(WiFi.localIP());
}

void reconnect() {
  while (!client.connected()) {
    Serial.print("Attempting MQTT connection...");
    // Create a random client ID
    String clientId = "ESP32FloodClient-";
    clientId += String(random(0xffff), HEX);
    
    if (client.connect(clientId.c_str())) {
      Serial.println("connected");
    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      Serial.println(" try again in 5 seconds");
      delay(5000);
    }
  }
}

long getDistance() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);
  long duration = pulseIn(ECHO_PIN, HIGH);
  return duration * 0.034 / 2; // Distance in cm
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();

  // Read Sensors
  float temp = dht.readTemperature();
  float hum = dht.readHumidity();
  int waterLevel = analogRead(WATER_LEVEL_PIN);
  long distance = getDistance();

  // Basic logic for Buzzer/Servo (Example)
  if (distance < 10 || waterLevel > 2000) {
    digitalWrite(BUZZER_PIN, HIGH);
    gateServo.write(90); // Open gate
  } else {
    digitalWrite(BUZZER_PIN, LOW);
    gateServo.write(0);  // Close gate
  }

  // Create JSON-like payload
  String payload = "{";
  payload += "\"temp\":" + String(temp) + ",";
  payload += "\"hum\":" + String(hum) + ",";
  payload += "\"water_analog\":" + String(waterLevel) + ",";
  payload += "\"distance_cm\":" + String(distance);
  payload += "}";

  // Publish to GCP
  Serial.print("Publishing: ");
  Serial.println(payload);
  client.publish("esp32/flood_system", payload.c_str());

  delay(5000); // Publish every 5 seconds
}
