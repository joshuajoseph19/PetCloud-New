#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
<<<<<<< HEAD

// ====== YOUR WIFI (PHONE HOTSPOT) ======
const char* WIFI_SSID = "RCT";
const char* WIFI_PASS = "reibin123";
=======
#include <ESP32Servo.h>

// ====== YOUR WIFI (PHONE HOTSPOT) ======
const char* WIFI_SSID = "Wifi";
const char* WIFI_PASS = "9428285177";
>>>>>>> df926ef (new commit)

// ====== YOUR RENDER URL (LIVE SERVER) ======
String BASE = "https://petcloud-new.onrender.com/api";

<<<<<<< HEAD
// ====== SERVO (Native PWM Setup) ======
// This avoids using the ESP32Servo library which has compilation bugs in newer cores
const int SERVO_PIN = 13;
const int servoChannel = 0;    // LEDC Channel
const int servoFreq = 50;      // 50Hz for Servos
const int servoRes = 13;       // 13-bit resolution (0-8191)
=======
// ====== SERVO ======
Servo myservo;
const int SERVO_PIN = 13;
>>>>>>> df926ef (new commit)

// ====== LED ======
const int LED_PIN = 2;

// ====== TIMING ======
unsigned long lastPingTime = 0;
const unsigned long PING_INTERVAL = 30000; // Send heartbeat every 30 seconds

<<<<<<< HEAD
// ---- Native Servo Write (Replaces myservo.write) ----
void servoWrite(int angle) {
  // Standard servos take a pulse between 0.5ms to 2.4ms
  // At 50Hz (20ms period) and 13-bit resolution:
  // 0.5ms = ~205 units (0 degrees)
  // 2.4ms = ~983 units (180 degrees)
  int duty = map(angle, 0, 180, 205, 983);
  ledcWrite(servoChannel, duty);
}

=======
>>>>>>> df926ef (new commit)
// ---- Dispense logic (30/60/100 -> 1/2/3 drops) ----
void doFeed(int portion) {
  int drops = 1;
  if (portion <= 30) drops = 1;
  else if (portion <= 60) drops = 2;
  else drops = 3;

  Serial.print("Feeding drops: ");
  Serial.println(drops);

  // Turn LED ON while feeding starts
  digitalWrite(LED_PIN, HIGH);

  for (int i = 0; i < drops; i++) {
    Serial.print("Drop number: ");
    Serial.println(i + 1);

<<<<<<< HEAD
    servoWrite(0);
    delay(300);

    servoWrite(90);
    delay(600);

    servoWrite(0);
=======
    myservo.write(0);
    delay(300);

    myservo.write(90);
    delay(600);

    myservo.write(0);
>>>>>>> df926ef (new commit)
    delay(300);
  }

  // Turn LED OFF after feeding ends
  digitalWrite(LED_PIN, LOW);
}

// ---- Send Heartbeat to server (Ping) ----
void sendPing() {
  if (WiFi.status() != WL_CONNECTED) return;
  
  HTTPClient http;
  http.begin(BASE + "/device_ping.php");
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  // Identification for this specific feeder
  String postData = "device_id=esp32_1";
  
  Serial.print("Sending Heartbeat... ");
  int code = http.POST(postData);
  Serial.println(code);

  http.end();
}

// ---- Mark command as done ----
void markDone(int id) {
  HTTPClient http;
  http.begin(BASE + "/mark_done.php");
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  String postData = "id=" + String(id);
  int code = http.POST(postData);

  Serial.print("mark_done HTTP: ");
  Serial.println(code);

  http.end();
}

// ---- Check for pending feed commands ----
void checkCommands() {
  String url = BASE + "/get_command.php?device_id=esp32_1";
  Serial.println("Checking commands: " + url);

  HTTPClient http;
  http.begin(url);

  int httpCode = http.GET();
  if (httpCode == 200) {
    String payload = http.getString();
    Serial.println("Payload: " + payload);

    StaticJsonDocument<256> doc;
    DeserializationError err = deserializeJson(doc, payload);

    if (!err) {
      bool ok = doc["ok"] | false;
      if (ok) {
        int id = doc["id"] | 0;
        int portion = doc["portion"] | 0;

        Serial.print("Feed Command Received - id=");
        Serial.print(id);
        Serial.print(" portion=");
        Serial.println(portion);

        doFeed(portion);
        markDone(id);
      } else {
        Serial.println("No pending command.");
      }
    } else {
      Serial.print("JSON parse error: ");
      Serial.println(err.c_str());
    }
  } else {
    Serial.print("HTTP Command Check Error: ");
    Serial.println(httpCode);
  }
  http.end();
}

void setup() {
  Serial.begin(115200);
  delay(1000);
<<<<<<< HEAD
  Serial.println("PetCloud Smart Feeder (Native PWM) Booting...");
=======
  Serial.println("PetCloud Smart Feeder Booting...");
>>>>>>> df926ef (new commit)

  // LED setup
  pinMode(LED_PIN, OUTPUT);
  digitalWrite(LED_PIN, LOW);

<<<<<<< HEAD
  // Initialize Native PWM for Servo (LEDC)
  ledcSetup(servoChannel, servoFreq, servoRes);
  ledcAttachPin(SERVO_PIN, servoChannel);
  servoWrite(0); // Move to initial position
=======
  // Servo attach
  myservo.attach(SERVO_PIN);
  myservo.write(0);
>>>>>>> df926ef (new commit)

  // WiFi Setup
  WiFi.mode(WIFI_STA);
  Serial.println("Connecting to WiFi...");
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
    digitalWrite(LED_PIN, !digitalRead(LED_PIN)); // Blink while connecting
  }

  Serial.println("\nWiFi connected!");
  digitalWrite(LED_PIN, HIGH);
  delay(2000);
  digitalWrite(LED_PIN, LOW);
  
  // Send initial ping on boot
  sendPing();
  lastPingTime = millis();
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    unsigned long currentMillis = millis();

    // 1. Send Heartbeat (Ping) every 30 seconds
    if (currentMillis - lastPingTime >= PING_INTERVAL) {
      sendPing();
      lastPingTime = currentMillis;
    }

    // 2. Check for commands every 3 seconds
    checkCommands();
  } else {
    Serial.println("WiFi disconnected. Reconnecting...");
    WiFi.begin(WIFI_SSID, WIFI_PASS);
  }

  delay(3000); 
}