#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClient.h>
#include <DHT.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// --- Konfigurasi Pin ---
#define MQ2_PIN     34          // Pin analog sensor MQ2
#define DHT_PIN     14          // Pin data sensor DHT11
#define FAN_RELAY   27          // Pin kendali relay kipas
#define DHT_TYPE    DHT11       // Tipe sensor DHT

DHT dht(DHT_PIN, DHT_TYPE);

// --- LCD I2C ---
LiquidCrystal_I2C lcd(0x27, 16, 2); // Alamat I2C, kolom, baris

// --- WiFi & Server ---
const char* ssid = "hey";
const char* password = "wij4y425Juni";
const char* serverName = "http://192.168.1.4:5000/api/data"; // Ganti sesuai IP Flask kamu

void setup() {
  Serial.begin(115200);

  pinMode(FAN_RELAY, OUTPUT);
  digitalWrite(FAN_RELAY, LOW);
  dht.begin();

  lcd.init();
  lcd.backlight();
  lcd.setCursor(0, 0);
  lcd.print("Menghubungkan...");

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\nWiFi Connected!");
  Serial.print("ESP32 IP Address: ");
  Serial.println(WiFi.localIP());
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("WiFi Terhubung!");
  

}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    float suhu = dht.readTemperature();
    float kelembaban = dht.readHumidity();
    int gasValue = analogRead(MQ2_PIN);

    if (isnan(suhu) || isnan(kelembaban)) {
      Serial.println("Gagal membaca dari DHT11!");
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Error DHT11!");
      delay(2000);
      return;
    }

    String fanStatus = "OFF";
    if (gasValue > 1500) {
      digitalWrite(FAN_RELAY, HIGH);
      fanStatus = "ON";
    } else {
      digitalWrite(FAN_RELAY, LOW);
    }

    // Serial monitor
    Serial.println("Suhu: " + String(suhu, 1) + " °C");
    Serial.println("Kelembaban: " + String(kelembaban, 1) + " %");
    Serial.println("MQ2: " + String(gasValue));
    Serial.println("Kipas: " + fanStatus);

    // LCD
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Suhu:" + String(suhu, 1) + "C ");
    lcd.print(fanStatus);
    lcd.setCursor(0, 1);
    lcd.print("Gas:" + String(gasValue));

    // Kirim JSON ke server Flask
    WiFiClient client;
    HTTPClient http;

    http.begin(client, serverName);
    http.addHeader("Content-Type", "application/json");

    String jsonData = "{\"temperature\":" + String(suhu, 1) +
                      ",\"humidity\":" + String(kelembaban, 1) +
                      ",\"gas\":" + String(gasValue) +
                      ",\"fan_status\":\"" + fanStatus + "\"}";

    Serial.println("Sending JSON: " + jsonData);
    int httpResponseCode = http.POST(jsonData);

    if (httpResponseCode > 0) {
      Serial.print("HTTP Response: ");
      Serial.println(httpResponseCode);
      String response = http.getString();
      Serial.println("Server response: " + response);
    } else {
      Serial.print("HTTP POST Failed. Code: ");
      Serial.println(httpResponseCode);
      Serial.println("Error: " + http.errorToString(httpResponseCode));
    }

    http.end();
  } else {
    Serial.println("WiFi disconnected!");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi Putus!");
  }

  delay(5000);
}


