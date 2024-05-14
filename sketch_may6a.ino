//RFID-----------------------------
#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
//NodeMCU--------------------------
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiManager.h>

#include<Servo.h>
//************************************************************************
#define SS_PIN  D2  //D2
#define RST_PIN D1  //D1
//************************************************************************

Servo servo;

LiquidCrystal_I2C lcd(0x27,16,2);

MFRC522 mfrc522(SS_PIN, RST_PIN); // Create MFRC522 instance.
//************************************************************************
/* Set these to your desired credentials. */
const char* device_token  = "6860c1ef000822bf"; // copy token when create new devices in web
//************************************************************************
String URL = "http://192.168.211.186:8080/rfidattendance/getdata.php"; //computer IP or the server domain
String getData, Link;
String OldCardID = "";
unsigned long previousMillis = 0;
//************************************************************************
void setup() {
  pinMode(D0, OUTPUT);
  Wire.begin(D4, D3);
  lcd.init();
  lcd.clear();
  lcd.backlight();
  lcd.setCursor(0,0);
  lcd.print("Welcome !");
  delay(1000);
  Serial.begin(115200);
  SPI.begin();  // Init SPI bus
  mfrc522.PCD_Init(); // Init MFRC522 card

  servo.attach(D8); // Attach servo to pin D8
  //---------------------------------------------
  connectToWiFi();
}
//************************************************************************
void loop() {
  //check if there's a connection to Wi-Fi or not
  if(!WiFi.isConnected()){
    connectToWiFi();    //Retry to connect to Wi-Fi
  }
  //---------------------------------------------
  if (millis() - previousMillis >= 15000) {
    previousMillis = millis();
    OldCardID="";
  }
  delay(50);
  //---------------------------------------------
  //look for new card
  if ( ! mfrc522.PICC_IsNewCardPresent()) {
    return;//got to start of loop if there is no card present
  }
  // Select one of the cards
  if ( ! mfrc522.PICC_ReadCardSerial()) {
    return;//if read card serial(0) returns 1, the uid struct contians the ID of the read card.
  }
  String CardID ="";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    CardID += mfrc522.uid.uidByte[i];
  }
  //---------------------------------------------
  if( CardID == OldCardID ){
    return;
  }
  else{
    OldCardID = CardID;
  }
  //---------------------------------------------
  Serial.println(CardID);
  SendCardID(CardID);
  delay(1000);
}
//************send the Card UID to the website*************
void SendCardID(String Card_uid) {
  Serial.println("Sending the Card ID");
  
  if (WiFi.isConnected()) {
    WiFiClient client; // Tạo đối tượng WiFiClient
    HTTPClient http;   // Khai báo đối tượng HTTPClient
    
    //GET Data
    getData = "?card_uid=" + String(Card_uid) + "&device_token=" + String(device_token); // Thêm Card ID vào URL
    //GET method
    Link = URL + getData;
    
    http.begin(client, Link); // Khởi tạo yêu cầu HTTP
    
    int httpCode = http.GET();   // Gửi yêu cầu
    String payload = http.getString();    // Nhận dữ liệu trả về

    Serial.println(httpCode);   // In mã trả về
    Serial.println(Card_uid);   // In Card ID
    Serial.println(payload);    // In dữ liệu nhận được

    if (httpCode == 200) {
      if (payload.substring(0, 5) == "login") {
        digitalWrite(D0, HIGH); 
        delay(1500);                  
        digitalWrite(D0, LOW);
        //String user_name = payload.substring(5);
        // Serial.println(user_name);
        // Lấy 10 ký tự từ cuối chuỗi
        String nameAndTime = payload.substring(5);
        String time = payload.substring(payload.length() - 10);//lastTenChars
        String user_name = payload.substring(5, payload.length() - 10);
        for(int i = 0; i<= 180; i+= 1)
        {
          servo.write(i);
          delay(20);
        }
        delay(1000);
        for(int i = 180; i >= 0; i-= 1)
        {
          servo.write(i);
          delay(20);
        }
        lcd.clear();
        lcd.setCursor(0,0);
        lcd.print(user_name);
        lcd.print(" Checkin");
        lcd.setCursor(0,1);
        lcd.print(time);
      }
      else if (payload.substring(0, 6) == "logout") {
        digitalWrite(D0, HIGH); 
        delay(1500);                  
        digitalWrite(D0, LOW);
        //String user_name = payload.substring(6);
        // Serial.println(user_name);
        //Serial.print(payload.substring(0, 11));
        String nameAndTime = payload.substring(6);
        String time = payload.substring(payload.length() - 10);
        String user_name = payload.substring(6, payload.length() - 10);
        for(int i = 0; i<= 180; i+= 1)
        {
          servo.write(i);
          delay(20);
        }
        delay(1000);
        for(int i = 180; i >= 0; i-= 1)
        {
          servo.write(i);
          delay(20);
        }
        lcd.clear();
        lcd.setCursor(0,0);
        lcd.print(user_name);
        lcd.print(" Checkout");
        lcd.setCursor(0,1);
        lcd.print(time);
      }
      else if (payload == "succesful") {
        digitalWrite(D0, HIGH); 
        delay(1500);                  
        digitalWrite(D0, LOW);
        lcd.clear();
        lcd.setCursor(0,0);
        lcd.print(Card_uid);
        lcd.setCursor(0,1);
        lcd.print("Register Succesful");
      }
      else if (payload == "available") {
        digitalWrite(D0, HIGH); 
        delay(1500);                  
        digitalWrite(D0, LOW);
        lcd.clear();
        lcd.setCursor(0,0);
        lcd.print(Card_uid);
        lcd.setCursor(0,1);
        lcd.print("Available");
      }
      else if(payload == "Not found!"){
        digitalWrite(D0, HIGH); 
        delay(1500);                  
        digitalWrite(D0, LOW);
        lcd.clear();
        lcd.setCursor(0,0);
        lcd.print("Not found User !");
      }
      delay(100);
      http.end();  // Đóng kết nối
    }
  }
}
//********************connect to the WiFi******************
void connectToWiFi(){

  WiFiManager wifiManager;
  if (!wifiManager.autoConnect("AutoConnectAP")) {
    Serial.println("Failed to connect and hit timeout");
    ESP.restart();
  }
  else{
    Serial.println("Connected to WiFi");
    Serial.print("IP address: ");
    Serial.println(WiFi.localIP());
  }
}
