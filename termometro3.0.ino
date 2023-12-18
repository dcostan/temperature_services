#include <Wire.h>
#include <SPI.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>

#define BME_SCK 13
#define BME_MISO 12
#define BME_MOSI 11
#define BME_CS 10

#define SEALEVELPRESSURE_HPA (1013.25)

#define delayTime 60000
#define timesInAPeriod 30

Adafruit_BME280 bme; // I2C
int incomingByte = 0;   // for incoming serial data
int noData = 0;         // counter for nodata sessions
bool outStatus = 0;     // output status variable

float c, p, h;
unsigned long lastMeasure = 0;
bool first = true;

void setup() {
  pinMode(13, OUTPUT);
  pinMode(8, OUTPUT);
  digitalWrite(13, LOW);
  digitalWrite(8, LOW);

  Serial.begin(9600);

  bool status = bme.begin();
  if (!status) {
    while (1) {
      Serial.println("{\"connected\":0}");
      delay(1000);
    }
  }
}

void changeStatus(bool state) {
  if (state)
    digitalWrite(8, HIGH);
  else
    digitalWrite(8, LOW);
  outStatus = state;
}

void loop() {

  if (Serial.available() > 0) {
    noData = 0;

    // read the incoming byte:
    incomingByte = Serial.read();

    if (incomingByte == 48)
      changeStatus(0);
    else if (incomingByte == 49)
      changeStatus(1);
  }
  else {
    noData++;
  }

  if (noData == 4)
    changeStatus(0);

  if (millis() - lastMeasure >= delayTime || first) {

    if(first) first = false;
    else lastMeasure = millis();
    
    c = bme.readTemperature();
    p = bme.readPressure() / 100.0F;
    h = bme.readHumidity();
    
  }

  Serial.print("{\"temp\": "); Serial.print(c); Serial.print(",\"status\": "); Serial.print(outStatus); Serial.print(",\"hum\": "); Serial.print(h); Serial.print(",\"pres\": "); Serial.print(p); Serial.println(",\"connected\":1}\t");

  delay(delayTime / timesInAPeriod);

}
