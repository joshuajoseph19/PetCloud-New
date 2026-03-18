#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <PubSubClient.h>
#include <ESP32Servo.h>

// ===== WIFI =====
const char* WIFI_SSID = "RCT";
const char* WIFI_PASS = "reibin123";

// ===== HIVEMQ CLOUD =====
const char* mqtt_server   = "1c596e0a7b50436a8d8a596c80265e05.s1.eu.hivemq.cloud";
const int   mqtt_port     = 8883;
const char* mqtt_user     = "joshu_a_19";
const char* mqtt_password = "jj10JMHJPhj";

// ===== TOPICS =====
const char* TOPIC_CMD    = "petcloud/feeder/esp32_1/cmd";
const char* TOPIC_STATUS = "petcloud/feeder/esp32_1/status";

// ===== OBJECTS =====
WiFiClientSecure espClient;
PubSubClient client(espClient);
Servo myservo;

// ===== PINS =====
const int SERVO_PIN = 13;
const int LED_PIN   = 2;

// ===== FEED FUNCTION =====
void doFeed(int drops) {
  Serial.print("Feeding... Drops = ");
  Serial.println(drops);

  digitalWrite(LED_PIN, HIGH);

  for (int i = 0; i < drops; i++) {
    myservo.write(0);    // closed
    delay(400);

    myservo.write(90);   // open
    delay(700);

    myservo.write(0);    // close again
    delay(400);
  }

  digitalWrite(LED_PIN, LOW);
  client.publish(TOPIC_STATUS, "Feed completed", true);
}

// ===== MQTT MESSAGE CALLBACK =====
void callback(char* topic, byte* payload, unsigned int length) {
  String msg = "";

  for (unsigned int i = 0; i < length; i++) {
    msg += (char)payload[i];
  }

  msg.trim();

  Serial.print("Message arrived on topic: ");
  Serial.println(topic);
  Serial.print("Received message: ");
  Serial.println(msg);

  // Servo works ONLY when button sends one of these commands
  if (msg == "FEED_30") {
    doFeed(1);
  }
  else if (msg == "FEED_60") {
    doFeed(2);
  }
  else if (msg == "FEED_100") {
    doFeed(3);
  }
  else {
    Serial.println("No valid button command received. Servo will not run.");
  }
}

// ===== WIFI CONNECT =====
void connectWiFi() {
  Serial.print("Connecting to WiFi");

  WiFi.begin(WIFI_SSID, WIFI_PASS);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println();
  Serial.println("WiFi connected");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
}

// ===== MQTT RECONNECT =====
void reconnectMQTT() {
  while (!client.connected()) {
    Serial.print("Connecting to HiveMQ... ");

    if (client.connect("PetCloud_ESP32", mqtt_user, mqtt_password, TOPIC_STATUS, 1, true, "Offline")) {
      Serial.println("Connected");
      client.publish(TOPIC_STATUS, "Online", true);
      client.subscribe(TOPIC_CMD);
      Serial.println("Subscribed to command topic");

      digitalWrite(LED_PIN, HIGH);
      delay(300);
      digitalWrite(LED_PIN, LOW);
    } else {
      Serial.print("Failed, rc = ");
      Serial.println(client.state());
      Serial.println("Retrying in 5 seconds...");
      delay(5000);
    }
  }
}

// ===== SETUP =====
void setup() {
  Serial.begin(115200);

  pinMode(LED_PIN, OUTPUT);
  digitalWrite(LED_PIN, LOW);

  myservo.setPeriodHertz(50);
  myservo.attach(SERVO_PIN, 500, 2400);
  myservo.write(0);   // keep closed initially

  espClient.setInsecure(); // testing only

  connectWiFi();

  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback);
}

// ===== LOOP =====
void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }

  if (!client.connected()) {
    reconnectMQTT();
  }

  client.loop();
}