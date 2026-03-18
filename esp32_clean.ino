#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <ESP32Servo.h>

// ====== YOUR WIFI (PHONE HOTSPOT) ======
const char* WIFI_SSID = "Wifi";
const char* WIFI_PASS = "9428285177";

// ====== YOUR PC IP (XAMPP SERVER) ======
// PC must be connected to SAME hotspot, and IP may change.
String BASE = "http://192.168.1.8/PetCloud/api";

// ====== SERVO ======
Servo myservo;
const int SERVO_PIN = 13;

// ====== LED ======
const int LED_PIN = 2;

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

    myservo.write(0);
    delay(300);

    myservo.write(90);
    delay(600);

    myservo.write(0);
    delay(300);
  }

  // Turn LED OFF after feeding ends
  digitalWrite(LED_PIN, LOW);
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

void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("Booting...");

  // LED setup
  pinMode(LED_PIN, OUTPUT);
  digitalWrite(LED_PIN, LOW);

  // Servo attach
  myservo.attach(SERVO_PIN);
  myservo.write(0);

  // Clean WiFi reset
  WiFi.mode(WIFI_MODE_NULL);
  delay(500);
  WiFi.disconnect(true, true);
  delay(1000);

  WiFi.mode(WIFI_STA);
  delay(500);

  Serial.println("Connecting to WiFi...");
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  unsigned long start = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - start < 20000) {
    delay(500);
    Serial.print(".");
    
    // Optional blink while connecting
    digitalWrite(LED_PIN, !digitalRead(LED_PIN));
  }

  Serial.println();
  Serial.print("WiFi status: ");
  Serial.println(WiFi.status());

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("WiFi connected!");
    Serial.print("ESP32 IP: ");
    Serial.println(WiFi.localIP());

    // LED ON for 1 second when WiFi connected
    digitalWrite(LED_PIN, HIGH);
    delay(1000);
    digitalWrite(LED_PIN, LOW);
  } else {
    Serial.println("WiFi FAILED (check SSID/PASS or 2.4GHz hotspot)");

    // Blink LED 5 times if WiFi fails
    for (int i = 0; i < 5; i++) {
      digitalWrite(LED_PIN, HIGH);
      delay(200);
      digitalWrite(LED_PIN, LOW);
      delay(200);
    }
  }
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    String url = BASE + "/get_command.php?device_id=esp32_1";
    Serial.println("Checking: " + url);

    HTTPClient http;
    http.begin(url);

    int httpCode = http.GET();
    Serial.print("HTTP code: ");
    Serial.println(httpCode);

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
      Serial.println("HTTP request failed.");
    }

    http.end();
  } else {
    Serial.println("WiFi disconnected.");
  }

  delay(3000); // poll every 3 seconds
}