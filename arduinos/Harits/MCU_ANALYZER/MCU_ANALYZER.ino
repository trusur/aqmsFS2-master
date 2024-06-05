/*
 Name:		MCU_PUMP.ino
 Created:	4/25/2022 16:47:23
 Author:	harits
*/

/*SYSTEM CONFIGURATION
MCU: ARDUINO MEGA ATmega 2560
Baud rate: 115200
*/

#define device_ID F("MCU_ANZ")
#define software_version F("VER.1.0")
#define devMode_default false
boolean devMode = devMode_default;

#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BMP280.h>
#include <Adafruit_BME280.h>
#include <Adafruit_SHT31.h>


/**********************************************************************************************************************
Command LIST
***********************************************************************************************************************

Command: $ID# return $MCU_ANZ,VER.1.0#

Command: $DEVMODE,ON# return $MCU_ANZ,DEVMODE,ON#
Command: $DEVMODE,OFF# return $MCU_ANZ,DEVMODE,OFF#

//FAN COMMAND LIST ====================================================================================================
Command: $FAN,100# return $MCU_ANZ,FAN,100#
Command: $FAN,IN,200# return $MCU_ANZ,FAN,IN,200#
Command: $FAN,IN,STATUS# return $MCU_ANZ,FAN,IN,200#
Command: $FAN,OUT,10# return $MCU_ANZ,FAN,OUT,10#
Command: $FAN,OUT,STATUS# return $MCU_ANZ,FAN,OUT,70#

//BMP280 COMMAND LIST =================================================================================================
Command: $BMP280,BEGIN#
return:
Response: $MCU_ANZ,BMP280,BEGIN#
Response: $MCU_ANZ,BMP280,STATUS,SENSOR_FOUND# or $MCU_ANZ,BMP280,STATUS,SENSOR_NOT_FOUND#

Command: $BMP280,SET,AUTO# return $MCU_ANZ,BMP280,STATUS,AUTO,ON#
Command: $BMP280,SET,AUTO,5000# return $MCU_ANZ,BMP280,STATUS,AUTO,5000#
Command: $BMP280,SET,AUTO,ON# return $MCU_ANZ,BMP280,STATUS,AUTO,ON#
Command: $BMP280,SET,AUTO,OFF# return $MCU_ANZ,BMP280,STATUS,AUTO,OFF#

Command: $BMP280,READ# return $MCU_ANZ,BMP280,VAL,23.32,98697.66#

Command: $BMP280,STATUS# return:
Response: $MCU_ANZ,BMP280,STATUS,AUTO,ON#
Response: $MCU_ANZ,BMP280,STATUS,AUTO,OFF#
Response: $MCU_ANZ,BMP280,STATUS,AUTO,5000#

//BME280 COMMAND LIST =================================================================================================
Command: $BME280,BEGIN#
return:
Response: $MCU_ANZ,BME280,BEGIN#
Response: $MCU_ANZ,BME280,STATUS,SENSOR_FOUND# or $MCU_ANZ,BME280,STATUS,SENSOR_NOT_FOUND#

Command: $BME280,SET,AUTO# return $MCU_ANZ,BME280,STATUS,AUTO,ON#
Command: $BME280,SET,AUTO,5000# return $MCU_ANZ,BME280,STATUS,AUTO,5000#
Command: $BME280,SET,AUTO,ON# return $MCU_ANZ,BME280,STATUS,AUTO,ON#
Command: $BME280,SET,AUTO,OFF# return $MCU_ANZ,BME280,STATUS,AUTO,OFF#

Command: $BME280,READ# return $MCU_ANZ,BME280,VAL,23.32,98697.66#

Command: $BME280,STATUS# return:
Response: $MCU_ANZ,BME280,STATUS,AUTO,ON#
Response: $MCU_ANZ,BME280,STATUS,AUTO,OFF#
Response: $MCU_ANZ,BME280,STATUS,AUTO,5000#

//SHT31 COMMAND LIST ==================================================================================================
Command: $SHT31,BEGIN#
return:
Response: $MCU_ANZ,SHT31,BEGIN#
Response: $MCU_ANZ,SHT31,STATUS,SENSOR_FOUND# or $MCU_ANZ,SHT31,STATUS,SENSOR_NOT_FOUND#

Command: $SHT31,SET,AUTO# return $MCU_ANZ,SHT31,STATUS,AUTO,ON#
Command: $SHT31,SET,AUTO,5000# return $MCU_ANZ,SHT31,STATUS,AUTO,5000#
Command: $SHT31,SET,AUTO,ON# return $MCU_ANZ,SHT31,STATUS,AUTO,ON#
Command: $SHT31,SET,AUTO,OFF# return $MCU_ANZ,SHT31,STATUS,AUTO,OFF#

Command: $SHT31,READ# return $MCU_ANZ,SHT31,VAL,23.32,98697.66#

Command: $SHT31,STATUS# return:
Response: $MCU_ANZ,SHT31,STATUS,AUTO,ON#
Response: $MCU_ANZ,SHT31,STATUS,AUTO,OFF#
Response: $MCU_ANZ,SHT31,STATUS,AUTO,5000#

//VACUUM IN SENSOR COMMAND LIST =======================================================================================
Command: $VAC_IN,SET,AUTO# return $MCU_ANZ,VAC_IN,STATUS,AUTO,ON,500,1#
Command: $VAC_IN,SET,AUTO,1000# return $MCU_ANZ,VAC_IN,STATUS,AUTO,1000,1#
Command: $VAC_IN,SET,AUTO,ON# return $MCU_ANZ,VAC_IN,STATUS,AUTO,ON,500,1#
Command: $VAC_IN,SET,AUTO,OFF# return $MCU_ANZ,VAC_IN,STATUS,AUTO,OFF#

Command: $VAC_IN,READ# return $MCU_ANZ,VAC_IN,RAW,607,247#

Command: $VAC_IN,STATUS# return:
Response: $MCU_ANZ,VAC_IN,STATUS,AUTO,ON,10000,10#
Response: $MCU_ANZ,VAC_IN,STATUS,AUTO,OFF#

//VACUUM OUT SENSOR COMMAND LIST ======================================================================================
Command: $VAC_OUT,SET,AUTO# return $MCU_ANZ,VAC_OUT,STATUS,AUTO,ON,500,1#
Command: $VAC_OUT,SET,AUTO,1000# return $MCU_ANZ,VAC_OUT,STATUS,AUTO,1000,1#
Command: $VAC_OUT,SET,AUTO,ON# return $MCU_ANZ,VAC_OUT,STATUS,AUTO,ON,500,1#
Command: $VAC_OUT,SET,AUTO,OFF# return $MCU_ANZ,VAC_OUT,STATUS,AUTO,OFF#

Command: $VAC_OUT,READ# return $MCU_ANZ,VAC_OUT,RAW,607,247#

Command: $VAC_OUT,STATUS# return:
Response: $MCU_ANZ,VAC_OUT,STATUS,AUTO,ON,10000,10#
Response: $MCU_ANZ,VAC_OUT,STATUS,AUTO,OFF#

//PARTICULATE MATTER COMMAND LIST ======================================================================================

Command: $PM,RESTART#
Response: $MCU_ANZ,PM,STATUS,RESTART,3000#
Response: $MCU_ANZ,PM,STATUS,ON#

Command: $PM,RESTART,2999#
Response: $MCU_ANZ,PM,STATUS,RESTART,3000#
Response: $MCU_ANZ,PM,STATUS,ON#

Command: $PM,RESTART,10000#
Response: $MCU_ANZ,PM,STATUS,RESTART,10000#
Response: $MCU_ANZ,PM,STATUS,ON#

Command: $PM,RESTART,1000000000#
Response: $MCU_ANZ,PM,STATUS,RESTART,1000000000#
Response: $MCU_ANZ,PM,STATUS,ON#

Command: $PM,RESTART,10000000000#
Response: $MCU_ANZ,GET_INVALID_COMMAND#

Command: $PM,OFF#
Response: $MCU_ANZ,PM,STATUS,OFF#

Command: $PM,ON#
Response: $MCU_ANZ,PM,STATUS,ON#

Command: $PM,STATUS#
Response: $MCU_ANZ,PM,STATUS,ON#
Response: $MCU_ANZ,PM,STATUS,OFF#



Command: $PM,2.5,SEND,"H,E,L,L,O, ,W,O,R,L,D,"#
Response: $MCU_ANZ,PM,2.5,SEND,"H,E,L,L,O, ,W,O,R,L,D,"#
Data: H,E,L,L,O, ,W,O,R,L,D,

Command: $PM,2.5,SEND,"HELLO WORLD"#
Response: $MCU_ANZ,PM,2.5,SEND,"HELLO WORLD"#
Data: HELLO WORLD

Command: $PM,2.5,STOP#
Response: $MCU_ANZ,PM,2.5,STATUS,STOP#

Command: $PM,2.5,RUN#
Response: $MCU_ANZ,PM,2.5,DATA,"000.566,1.8,+27.3,075,0964.5,08,*0110"#
Data: 000.566,1.8,+27.3,075,0964.5,08,*0110

Command: $PM,2.5,STATUS#
Response: $MCU_ANZ,PM,2.5,STATUS,RUN#
Response: $MCU_ANZ,PM,2.5,STATUS,STOP#


Command: $PM,10,SEND,"H,E,L,L,O, ,W,O,R,L,D,"#
$MCU_ANZ,PM,10,SEND,"H,E,L,L,O, ,W,O,R,L,D,"#
Data: H,E,L,L,O, ,W,O,R,L,D,

Command: $PM,10,SEND,"HELLO WORLD"#
Response: $MCU_ANZ,PM,10,SEND,"HELLO WORLD"#
Data: HELLO WORLD

Command: $PM,10,STOP#
Response: $MCU_ANZ,PM,10,STATUS,STOP#

Command: $PM,10,RUN#
Response: $MCU_ANZ,PM,10,DATA,"000.566,1.8,+27.3,075,0964.5,08,*0110"#
Data: 000.566,1.8,+27.3,075,0964.5,08,*0110

Command: $PM,10,STATUS#
Response: $MCU_ANZ,PM,10,STATUS,RUN#
Response: $MCU_ANZ,PM,10,STATUS,STOP#

***********************************************************************************************************************/



//=====================================================================================================================
//DIGITAL PIN DECALRATION & TOOLS
//=====================================================================================================================
void ledBlink(byte type = 0) {
	switch (type)
	{
	case 0:
		digitalWrite(LED_BUILTIN, HIGH);
		delay(50);
		digitalWrite(LED_BUILTIN, LOW);
		break;
	case 1:
		digitalWrite(LED_BUILTIN, HIGH);
		delay(50);
		digitalWrite(LED_BUILTIN, LOW);
		delay(50);
		break;
	default:
		digitalWrite(LED_BUILTIN, HIGH);
		delay(50);
		digitalWrite(LED_BUILTIN, LOW);
		break;
	}
}

//=====================================================================================================================
//SERIAL COMMUNICATION
//=====================================================================================================================

boolean check_isDigit(String text, boolean sign = false) {
	char anz = 0;
	for (uint16_t i = 0; i < text.length(); i++) {
		anz = text.charAt(i);
		if (anz == ' ') return false;
		if (!sign) {
			if (isalpha(anz) || anz == '-') return false;
		}
		else if (sign) if (isalpha(anz)) return false;
	}
	return true;
}

void print_invalidcmd() {
	Serial.print('$' + (String)device_ID + ",GET_INVALID_COMMAND#");
}

//FLushing Serial Buffer
void flush_serialPort(HardwareSerial* SerialPort) {
	if (SerialPort->available()) {
		for (byte i = 0; i < SerialPort->available(); i++)
		{
			SerialPort->read();
			ledBlink(1);
		}
	}
}

//=====================================================================================================================
//VACUUM SENSOR AT INLET PORT PARAMETER & CONTROL
//=====================================================================================================================
#define vacuum_in_sensor_signal_pin A0
boolean vacuum_in_status_auto = false;
const uint32_t vacuum_in_sendInterval_default = 500;
const uint32_t vacuum_in_readInterval_default = 1;
uint32_t vacuum_in_sendInterval = vacuum_in_sendInterval_default;
uint32_t vacuum_in_readInterval = 500;
uint32_t vacuum_in_send_tc = millis();
uint32_t vacuum_in_read_tc = millis();
int vacuum_in_raw = 0;
int32_t vacuum_in_buffer = 0;
uint16_t vacuum_in_dataCount = 0;

void vacuum_in_setup() {
	pinMode(vacuum_in_sensor_signal_pin, INPUT);
}

void vacuum_in_read(boolean send = false) {
	Serial.print('$');
	Serial.print(device_ID);
	Serial.print(",VAC_IN,RAW,");
	Serial.print(analogRead(vacuum_in_sensor_signal_pin));
	Serial.print(",1");
	Serial.print('#');
	if (devMode) Serial.println();
}

void vacuum_in_sendData() {
	Serial.print('$');
	Serial.print(device_ID);
	Serial.print(",VAC_IN,RAW,");
	Serial.print(vacuum_in_raw);
	Serial.print(',');
	Serial.print(vacuum_in_dataCount);
	Serial.print('#');
	if (devMode) Serial.println();
}

void vacuum_in_defineReadInterval() { //strecthing sampling rate
	if ((vacuum_in_sendInterval / vacuum_in_readInterval_default) > 1000) {
		vacuum_in_readInterval = vacuum_in_sendInterval / 1000;
	}
	else vacuum_in_readInterval = vacuum_in_readInterval_default;
}


void vacuum_in_autoService(boolean enable) {
	if (enable) {
		if (millis() - vacuum_in_read_tc > vacuum_in_readInterval) {
			vacuum_in_buffer += analogRead(vacuum_in_sensor_signal_pin);
			vacuum_in_dataCount++;
			vacuum_in_read_tc = millis();
		}
		if (millis() - vacuum_in_send_tc > vacuum_in_sendInterval) {
			vacuum_in_raw = vacuum_in_buffer / vacuum_in_dataCount;
			vacuum_in_sendData();
			vacuum_in_buffer = 0;
			vacuum_in_dataCount = 0;
			vacuum_in_send_tc = millis();
		}
	}
}

//=====================================================================================================================
//VACUUM SENSOR AT OUTLET PORT PARAMETER & CONTROL
//=====================================================================================================================
#define vacuum_out_sensor_signal_pin A1
boolean vacuum_out_status_auto = false;
const uint32_t vacuum_out_sendInterval_default = 500;
const uint32_t vacuum_out_readInterval_default = 1;
uint32_t vacuum_out_sendInterval = vacuum_out_sendInterval_default;
uint32_t vacuum_out_readInterval = 500;
uint32_t vacuum_out_send_tc = millis();
uint32_t vacuum_out_read_tc = millis();
int vacuum_out_raw = 0;
int32_t vacuum_out_buffer = 0;
uint16_t vacuum_out_dataCount = 0;

void vacuum_out_setup() {
	pinMode(vacuum_out_sensor_signal_pin, INPUT);
}

void vacuum_out_read(boolean send = false) {
	Serial.print('$');
	Serial.print(device_ID);
	Serial.print(",VAC_OUT,RAW,");
	Serial.print(analogRead(vacuum_out_sensor_signal_pin));
	Serial.print(",1");
	Serial.print('#');
	if (devMode) Serial.println();
}

void vacuum_out_sendData() {
	Serial.print('$');
	Serial.print(device_ID);
	Serial.print(",VAC_OUT,RAW,");
	Serial.print(vacuum_out_raw);
	Serial.print(',');
	Serial.print(vacuum_out_dataCount);
	Serial.print('#');
	if (devMode) Serial.println();
}

void vacuum_out_defineReadInterval() { //strecthing sampling rate
	if ((vacuum_out_sendInterval / vacuum_out_readInterval_default) > 1000) {
		vacuum_out_readInterval = vacuum_out_sendInterval / 1000;
	}
	else vacuum_out_readInterval = vacuum_out_readInterval_default;
}


void vacuum_out_autoService(boolean enable) {
	if (enable) {
		if (millis() - vacuum_out_read_tc > vacuum_out_readInterval) {
			vacuum_out_buffer += analogRead(vacuum_out_sensor_signal_pin);
			vacuum_out_dataCount++;
			vacuum_out_read_tc = millis();
		}
		if (millis() - vacuum_out_send_tc > vacuum_out_sendInterval) {
			vacuum_out_raw = vacuum_out_buffer / vacuum_out_dataCount;
			vacuum_out_sendData();
			vacuum_out_buffer = 0;
			vacuum_out_dataCount = 0;
			vacuum_out_send_tc = millis();
		}
	}
}

//=====================================================================================================================
//EXHAUST FAN PARAMETER & CONTROL
//=====================================================================================================================
#define fan_IN_PWM_pin 9
#define fan_IN_DIR 8
#define fan_OUT_PWM 10
#define fan_OUT_DIR 11
#define fan_IN 9
#define fan_OUT 10
int fan_IN_speed = 0;
int fan_OUT_speed = 0;

void fan_begin(boolean testFan = true) {
	pinMode(fan_IN_PWM_pin, OUTPUT);
	pinMode(fan_IN_DIR, OUTPUT);
	pinMode(fan_OUT_PWM, OUTPUT);
	pinMode(fan_OUT_DIR, OUTPUT);
	digitalWrite(fan_IN_DIR, HIGH);
	digitalWrite(fan_OUT_DIR, LOW);

	if (testFan) {
		for (int i = 0; i <= 255; i++)
		{
			analogWrite(fan_IN_PWM_pin, i);
			analogWrite(fan_OUT_PWM, i);
			delay(3);
		}
		delay(1000);
		for (int i = 255; i >= 0; i--)
		{
			analogWrite(fan_IN_PWM_pin, i);
			analogWrite(fan_OUT_PWM, i);
			delay(3);
		}
	}
}

int fan_speed(int fan, int pwm) {

	if (pwm < 80 && pwm > 0) pwm = 80;
	if (fan == fan_IN) {
		analogWrite(fan_IN, pwm);
		fan_IN_speed = pwm;
	}
	else if (fan == fan_OUT) {
		analogWrite(fan_OUT, pwm);
		fan_OUT_speed = pwm;
	}
	return pwm;
}

//=====================================================================================================================
//BMP280 PARAMETER & CONTROL
//=====================================================================================================================
/***************************************************************************
  This is a library for the BMP280 humidity, temperature & pressure sensor

  Designed specifically to work with the Adafruit BMP280 Breakout
  ----> http://www.adafruit.com/products/2651

  These sensors use I2C or SPI to communicate, 2 or 4 pins are required
  to interface.

  Adafruit invests time and resources providing this open source code,
  please support Adafruit andopen-source hardware by purchasing products
  from Adafruit!

  Written by Limor Fried & Kevin Townsend for Adafruit Industries.
  BSD license, all text above must be included in any redistribution
 ***************************************************************************/

 //#include <Wire.h>
 //#include <Adafruit_BMP280.h>

Adafruit_BMP280 bmp; // I2C

boolean BMP280_status_exist = true;
boolean BMP280_status_auto = false;
uint32_t BMP280_readInterval = 500;
const uint32_t BMP280_readInterval_default = 500;
uint32_t BMP280_tc = millis();
int BMP280_decimalPlace = 2;
float BMP280_temperature = 0.0F; //unit: °C
float BMP280_pressure = 0.0F; //unit: pascal

void BMP280_setup() {

	BMP280_status_exist = false;
	BMP280_status_auto = false;
	BMP280_readInterval = 1000;
	BMP280_tc = millis();
	BMP280_decimalPlace = 2;
	BMP280_temperature = 0.0F; //unit: °C
	BMP280_pressure = 0.0F; //unit: pascal

	Wire.begin();
	if (devMode) Serial.println(F("BMP280 Begin"));
	unsigned status;
	status = bmp.begin(BMP280_ADDRESS_ALT, BMP280_CHIPID);
	if (!status) {
		Serial.print('$' + (String)device_ID + ",BMP280,STATUS,SENSOR_NOT_FOUND#");
		if (devMode) Serial.println();
		BMP280_status_exist = false;
	}
	if (status) {
		Serial.print('$' + (String)device_ID + ",BMP280,STATUS,SENSOR_FOUND#");
		if (devMode) Serial.println();
		BMP280_status_exist = true;
		bmp.setSampling(Adafruit_BMP280::MODE_NORMAL,   /* Operating Mode. */
			Adafruit_BMP280::SAMPLING_X2,				/* Temp. oversampling */
			Adafruit_BMP280::SAMPLING_X16,				/* Pressure oversampling */
			Adafruit_BMP280::FILTER_X16,				/* Filtering. */
			Adafruit_BMP280::STANDBY_MS_500);			/* Standby time. */
	}
}

void BMP280_read(boolean send = false) {

	BMP280_temperature = bmp.readTemperature();
	BMP280_pressure = bmp.readPressure();

	if (BMP280_temperature == 0 && BMP280_pressure == 0) {
		BMP280_status_exist = false;
	}
	if (send) {
		if (BMP280_status_exist) BMP280_sendData();
		else if (!BMP280_status_exist) {
			Serial.print('$');
			Serial.print(device_ID);
			Serial.print(",BMP280,STATUS,NOT_FOUND#");
			if (devMode) Serial.println();
		}
	}
}

void BMP280_sendData() {
	Serial.print('$');
	Serial.print(device_ID);
	Serial.print(",BMP280,VAL,");
	Serial.print(BMP280_temperature, BMP280_decimalPlace);
	Serial.print(',');
	Serial.print(BMP280_pressure, BMP280_decimalPlace);
	Serial.print('#');
	if (devMode) Serial.println();
}


void BMP280_autoService(boolean enable) {
	if (enable) {
		if (millis() - BMP280_tc > BMP280_readInterval) {
			BMP280_read(true);
			BMP280_tc = millis();
		}
	}
}

//=====================================================================================================================
//BME280 PARAMETER & CONTROL
//=====================================================================================================================
/***************************************************************************
  This is a library for the BME280 humidity, temperature & pressure sensor

  Designed specifically to work with the Adafruit BME280 Breakout
  ----> http://www.adafruit.com/products/2650

  These sensors use I2C or SPI to communicate, 2 or 4 pins are required
  to interface. The device's I2C address is either 0x76 or 0x77.

  Adafruit invests time and resources providing this open source code,
  please support Adafruit andopen-source hardware by purchasing products
  from Adafruit!

  Written by Limor Fried & Kevin Townsend for Adafruit Industries.
  BSD license, all text above must be included in any redistribution
  See the LICENSE file for details.
 ***************************************************************************/

 //#include <Wire.h>
 //#include <Adafruit_Sensor.h>
 //#include <Adafruit_BME280.h>
 //#define SEALEVELPRESSURE_HPA (1013.25)
Adafruit_BME280 BME280; // I2C

boolean BME280_status_exist = false;
boolean BME280_status_auto = false;
uint32_t BME280_readInterval = 500;
const uint32_t BME280_readInterval_default = 500;
uint32_t BME280_tc = millis();
int BME280_decimalPlace = 2;
float BME280_temperature = 0.0F; //unit: °C
float BME280_pressure = 0.0F; //unit: pascal
float BME280_humidity = 0.0F; //unit: %RH

void BME280_setup() {

	BME280_status_exist = true;
	BME280_status_auto = false;
	BME280_readInterval = 1000;
	BME280_tc = millis();
	BME280_decimalPlace = 2;
	BME280_temperature = 0.0F; //unit: °C
	BME280_pressure = 0.0F; //unit: pascal
	BME280_humidity = 0.0F; //unit: %RH
	Wire.begin();
	if (devMode) Serial.println(F("BME280 Begin"));
	unsigned int status;
	status = BME280.begin(0x76, &Wire);
	if (!status) {
		Serial.print('$' + (String)device_ID + ",BME280,STATUS,SENSOR_NOT_FOUND#");
		if (devMode) Serial.println();
		BME280_status_exist = false;

	}

	else if (status) {
		BME280_status_exist = true;
		Serial.print('$' + (String)device_ID + ",BME280,STATUS,SENSOR_FOUND#");
		if (devMode) Serial.println();
	}

}

void BME280_read(boolean send = false) {

	BME280_temperature = BME280.readTemperature();
	BME280_pressure = BME280.readPressure();
	BME280_humidity = BME280.readHumidity();

	if (BME280_temperature == 0 && BME280_pressure == 0) {
		BME280_status_exist = false;
	}
	if (send) {
		if (BME280_status_exist) BME280_sendData();
		else if (!BME280_status_exist) {
			Serial.print('$');
			Serial.print(device_ID);
			Serial.print(",BME280,STATUS,NOT_FOUND#");
			if (devMode) Serial.println();
		}
	}
}

void BME280_sendData() {
	Serial.print('$');
	Serial.print(device_ID);
	Serial.print(",BME280,VAL,");
	Serial.print(BME280_temperature, BME280_decimalPlace);
	Serial.print(',');
	Serial.print(BME280_pressure, BME280_decimalPlace);
	Serial.print(',');
	Serial.print(BME280_humidity, BME280_decimalPlace);
	Serial.print('#');
	if (devMode) Serial.println();
}


void BME280_autoService(boolean enable) {
	if (enable) {
		if (millis() - BME280_tc > BME280_readInterval) {
			BME280_read(true);
			BME280_tc = millis();
		}
	}
}

//=====================================================================================================================
//SHT31 PARAMETER & CONTROL
//=====================================================================================================================
//#include <Wire.h>
//#include "Adafruit_SHT31.h"
Adafruit_SHT31 SHT31 = Adafruit_SHT31();
boolean SHT31_status_exist = false;
boolean SHT31_status_auto = false;
uint32_t SHT31_readInterval = 500;
const uint32_t SHT31_readInterval_default = 500;
uint32_t SHT31_tc = millis();
int SHT31_decimalPlace = 2;
float SHT31_temperature = 0.0F; //unit: °C
float SHT31_humidity = 0.0F; //unit: %RH

void SHT31_setup() {
	SHT31_status_exist = true;
	SHT31_status_auto = false;
	SHT31_readInterval = 1000;
	SHT31_tc = millis();
	SHT31_decimalPlace = 2;
	SHT31_temperature = 0.0F; //unit: °C
	SHT31_humidity = 0.0F; //unit: %RH
	Wire.begin();
	if (devMode) Serial.println(F("SHT31 Begin"));
	bool status;
	status = SHT31.begin(0x44);
	if (!status) {
		Serial.print('$' + (String)device_ID + ",SHT31,STATUS,SENSOR_NOT_FOUND#");
		if (devMode) Serial.println();
		SHT31_status_exist = false;
	}
	else if (status) {
		SHT31_status_exist = true;
		Serial.print('$' + (String)device_ID + ",SHT31,STATUS,SENSOR_FOUND#");
		if (devMode) Serial.println();
	}

	if (SHT31.isHeaterEnabled()) {
		SHT31.heater(false);
	}
}

void SHT31_read(boolean send = false) {
	SHT31_temperature = SHT31.readTemperature();
	SHT31_humidity = SHT31.readHumidity();
	if (SHT31_temperature == 0 && SHT31_humidity == 0) {
		SHT31_status_exist = false;
	}
	if (send) {
		if (SHT31_status_exist) SHT31_sendData();
		else if (!SHT31_status_exist) {
			Serial.print('$');
			Serial.print(device_ID);
			Serial.print(",SHT31,STATUS,NOT_FOUND#");
			if (devMode) Serial.println();
		}
	}
}

void SHT31_sendData() {
	Serial.print('$');
	Serial.print(device_ID);
	Serial.print(",SHT31,VAL,");
	Serial.print(SHT31_temperature, SHT31_decimalPlace);
	Serial.print(',');
	Serial.print(SHT31_humidity, SHT31_decimalPlace);
	Serial.print('#');
	if (devMode) Serial.println();
}


void SHT31_autoService(boolean enable) {
	if (enable) {
		if (millis() - SHT31_tc > SHT31_readInterval) {
			SHT31_read(true);
			SHT31_tc = millis();
		}
	}
}
//=====================================================================================================================
//PARTICULATE MATTER PARAMETER & FUNCTION
//=====================================================================================================================
#define PM_power_trigger_pin 12
#define pm_restartDuration_default 3000UL

volatile boolean pm_power_status = true;
volatile boolean pm_restart_status = false;
uint32_t pm_restart_tc = 0UL;
uint32_t pm_restartDuration = 0UL;

void PM2p5_begin() {
	pinMode(PM_power_trigger_pin, OUTPUT);
	digitalWrite(PM_power_trigger_pin, LOW);
	pm_power_status = true;
	Serial1.begin(9600);
	Serial.print('$' + (String)device_ID + ',' + "PM,2.5,BEGIN#");
	if (devMode) Serial.println();
}

volatile boolean PM2p5_data_enable = true;

void PM2p5_serial(boolean enable = true) {
	if (enable) {
		if (Serial1.available() > 0) {

			//Serial.println("incoming data");
			//Serial.print("serial available: ");
			//Serial.println(Serial1.available());
			//Serial.print("pm 2.5 get data");
			//if (devMode) Serial.println();

			//delay(200);

			char buff[256] = { 0 };
			int index = 0;

			boolean valid = false;
			char read;
			uint32_t timeout = millis();
			while (millis() - timeout < 500) {
				if (Serial1.available()) {
					read = Serial1.read();
					if (read == '\n') {
						valid = true;
						break;
					}
					//if (Serial1.available()) buff[index++] = read;
					else buff[index++] = read;
				}
			}

			if (valid) {

				//Serial.println(":::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::");
				//String snd = '$' + (String)device_ID + ",PM,2.5,DATA," + '"' + buff + '"' + '#';
				//Serial.print("pm 2.5 snd length: ");
				//Serial.println(snd.length());
				//Serial.print("pm 2.5 get data length: ");
				//Serial.print(strlen(buff));
				//if (devMode) Serial.println();

				Serial.print('$' + (String)device_ID + ",PM,2.5,DATA," + '"');
				for (int i = 0; i < index - 1; i++)
				{
					Serial.write(buff[i]);
				}
				Serial.write('"');
				Serial.write('#');
				if (devMode) Serial.println();
			}
			else {
				if (devMode)Serial.println("< PM 2.5 receive timeout >");
			}
		}
	}
}

void PM10_begin() {
	pinMode(PM_power_trigger_pin, OUTPUT);
	digitalWrite(PM_power_trigger_pin, LOW);
	pm_power_status = true;
	Serial2.begin(9600);
	Serial.print('$' + (String)device_ID + ',' + "PM,10,BEGIN#");
	if (devMode) Serial.println();
}

volatile boolean PM10_data_enable = true;

void PM10_serial(boolean enable = true) {
	if (enable) {
		if (Serial2.available() > 0) {
			char buff[256] = { 0 };
			int index = 0;
			boolean valid = false;
			char read;
			uint32_t timeout = millis();
			while (millis() - timeout < 500) {
				if (Serial2.available()) {
					read = Serial2.read();
					if (read == '\n') {
						valid = true;
						break;
					}
					else buff[index++] = read;
				}
			}

			if (valid) {
				Serial.print('$' + (String)device_ID + ",PM,10,DATA," + '"');
				for (int i = 0; i < index - 1; i++)
				{
					Serial.write(buff[i]);
				}
				Serial.write('"');
				Serial.write('#');
				if (devMode) Serial.println();
			}

			else {
				if (devMode)Serial.println("< PM 10 receive timeout >");
			}

		}
	}
}

void pm_serviceRoutine() {
	PM2p5_serial(PM2p5_data_enable);
	PM10_serial(PM10_data_enable);

	if (pm_restart_status) {
		if (millis() - pm_restart_tc > pm_restartDuration) {
			digitalWrite(PM_power_trigger_pin, LOW);
			pm_power_status = true;
			PM2p5_data_enable = true;
			PM10_data_enable = true;
			pm_restart_status = false;
			pm_restartDuration = pm_restartDuration_default;
			Serial.print('$' + (String)device_ID + ",PM,STATUS,ON#");
			if (devMode) Serial.println();
			Serial1.begin(9600);
			Serial2.begin(9600);
			Serial.print('$' + (String)device_ID + ",PM,2.5,STATUS,RUN#");
			if (devMode) Serial.println();
			Serial.print('$' + (String)device_ID + ",PM,10,STATUS,RUN#");
			if (devMode) Serial.println();
			ledBlink();
		}
	}
}

//=====================================================================================================================
//COMMAND CONTROL (SERIAL COMMUNICATION)
//=====================================================================================================================
void serialEvent() {

	//=================================================================================================================
	//PROCESSING PACKET
	//=================================================================================================================
	if (Serial.read() == '$') {

		//SPLITTING STRING=============================================================================================
		const int max_token = 10;
		String token[max_token] = { "" };
		byte token_count = 0;
		char buff[64] = { 0 };
		char read;
		byte index = 0;
		int valid = 0;

		delay(20);

		uint32_t timeout = millis();
		while (millis() - timeout < 500) {
			if (Serial.available()) {
				if (index < SERIAL_RX_BUFFER_SIZE) {
					read = Serial.read();
					switch (read)
					{
					case '"':
					{
						boolean v = false;
						uint32_t timeout = millis();
						while (millis() - timeout < 500) {
							if (!Serial.available()) break;
							read = Serial.read();
							if (read == '"') {
								v = true;
								read = '\0';
								break;
							}
							else buff[index++] = read;
						}
						if (!v) {
							memset(buff, 0, strlen(buff));
							index = 0;
							valid--;
							break;
						}
					}
					break;
					case ',':
						//token[token_count++] = buff;
						if ((token[token_count++] = buff) == NULL) valid--;
						memset(buff, 0, strlen(buff));
						index = 0;
						break;
					case '#':
						//token[token_count++] = buff;
						if ((token[token_count++] = buff) == NULL) valid--;
						else valid++;
						break;
					default:
						buff[index++] = read;
						break;
					}
					if (token_count > max_token) {
						if (devMode) Serial.println("token overflow!");
						valid--;
						break;
					}
					if (valid != 0) break;
				}
			}
			else break;
		}

		//=============================================================================================================
		if (valid > 0) {
			if ((String)token[0] == "ID" && token_count == 1) { //SEND MCU ID & SOFTWARE VERSION
				Serial.print('$' + (String)device_ID + ',' + (String)software_version + '#');
				ledBlink();
			}
			else if ((String)token[0] == "DEVMODE" && token_count == 2) { //SEND MCU ID & SOFTWARE VERSION
				if ((String)token[1] == "ON") {
					devMode = true;
					Serial.println('$' + (String)device_ID + ",DEVMODE,ON#");
					ledBlink();
				}
				else if ((String)token[1] == "OFF") {
					devMode = false;
					Serial.print('$' + (String)device_ID + ",DEVMODE,OFF#");
					ledBlink();
				}
				else print_invalidcmd();
			}
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			//FAN COMMANDS
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else if ((String)token[0] == "FAN") { //FAN CONTROL
				if (token_count <= 2) {
					if (token[1] != NULL) {
						if (check_isDigit((String)token[1])) {
							//int fan_pwm = atoi(token[1]);
							int fan_pwm = token[1].toInt();

							String check = (String)token[1];
							if (!(check.charAt(0) == '0' && check.length() > 1)) {
								if (fan_pwm >= 0 && fan_pwm <= 255) {
									if (fan_pwm > 0 && fan_pwm < 80) fan_pwm = 80;
									fan_speed(fan_IN, fan_pwm);
									fan_speed(fan_OUT, fan_pwm);
									Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)fan_pwm + '#');
									ledBlink();
								}
								else print_invalidcmd();
							}
							else print_invalidcmd();
						}
						else print_invalidcmd();
					}
					else print_invalidcmd();
				}
				else if (token_count > 2) {
					if ((String)token[1] == "IN") {
						if (token[2] != NULL) {
							if (check_isDigit((String)token[2])) {
								int fan_pwm = token[2].toInt();
								String check = (String)token[2];
								if (!(check.charAt(0) == '0' && check.length() > 1)) {
									if (fan_pwm >= 0 && fan_pwm <= 255) {
										if (fan_pwm > 0 && fan_pwm < 70) fan_pwm = 70;
										fan_speed(fan_IN, fan_pwm);
										Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)token[1] + ',' + (String)fan_pwm + '#');
										ledBlink();
									}
									else print_invalidcmd();
								}
								else print_invalidcmd();
							}
							else if ((String)token[2] == "STATUS") {
								Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)token[1] + ',' + (String)fan_IN_speed + '#');
								ledBlink();
							}
							else print_invalidcmd();
						}
						else print_invalidcmd();
					}
					else if ((String)token[1] == "OUT") {
						if (token[2] != NULL) {
							if (check_isDigit((String)token[2])) {
								int fan_pwm = token[2].toInt();
								String check = (String)token[2];
								if (!(check.charAt(0) == '0' && check.length() > 1)) {
									if (fan_pwm >= 0 && fan_pwm <= 255) {
										if (fan_pwm > 0 && fan_pwm < 80) fan_pwm = 80;
										fan_speed(fan_OUT, fan_pwm);
										Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)token[1] + ',' + (String)fan_pwm + '#');
										ledBlink();
									}
									else print_invalidcmd();
								}
								else print_invalidcmd();
							}
							else if ((String)token[2] == "STATUS") {
								Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)token[1] + ',' + (String)fan_OUT_speed + '#');
								ledBlink();
							}
							else print_invalidcmd();
						}
						else print_invalidcmd();
					}
					else print_invalidcmd();
				}
				else print_invalidcmd();
			}
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			//VAC_IN COMMANDS
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else if ((String)token[0] == "VAC_IN") { //VAC_IN SENSOR CONTROL
				if ((String)token[1] == "SET") {
					if (token_count <= 3) {
						if ((String)token[2] == "AUTO") {
							vacuum_in_status_auto = true;
							vacuum_in_sendInterval = vacuum_in_sendInterval_default;
							vacuum_in_defineReadInterval();
							Serial.print('$' + (String)device_ID + ",VAC_IN,STATUS,AUTO,ON," + (String)vacuum_in_sendInterval + ',' + (String)vacuum_in_readInterval + '#');
							ledBlink();
						}
					}
					else if (token_count == 4) {
						if ((String)token[2] == "AUTO") {
							if ((String)token[3] == "ON") {
								vacuum_in_status_auto = true;
								vacuum_in_sendInterval = vacuum_in_sendInterval_default;
								vacuum_in_defineReadInterval();
								Serial.print('$' + (String)device_ID + ",VAC_IN,STATUS,AUTO,ON," + (String)vacuum_in_sendInterval + ',' + (String)vacuum_in_readInterval + '#');
								ledBlink();
							}
							else if ((String)token[3] == "OFF") {
								vacuum_in_status_auto = false;
								Serial.print('$' + (String)device_ID + ",VAC_IN,STATUS,AUTO,OFF#");
								ledBlink();
							}
							else if (check_isDigit((String)token[3]) && token[3] != NULL) {
								long send_interval = token[3].toInt();
								if (send_interval >= 50 && send_interval <= 1000000000L) {
									String check = (String)token[3];
									if (check.charAt(0) != '0' && check.length() > 1) {
										vacuum_in_sendInterval = send_interval;
										vacuum_in_defineReadInterval();
										vacuum_in_status_auto = true;
										Serial.print('$' + (String)device_ID + ",VAC_IN,STATUS,AUTO,ON," + (String)vacuum_in_sendInterval + ',' + (String)vacuum_in_readInterval + '#');
										ledBlink();
									}
									else print_invalidcmd();
								}
								else print_invalidcmd();
							}
							else print_invalidcmd();
						}
						else print_invalidcmd();
					}
					else print_invalidcmd();
				}
				else if ((String)token[1] == "READ") {
					if (token_count <= 2) {
						vacuum_in_read(true);
						vacuum_in_status_auto = false;
						ledBlink();
					}
					else print_invalidcmd();
				}
				else if ((String)token[1] == "STATUS") {
					if (vacuum_in_status_auto) {
						Serial.print('$' + (String)device_ID + ",VAC_IN,STATUS,AUTO,ON," + (String)vacuum_in_sendInterval + ',' + (String)vacuum_in_readInterval + '#');
						ledBlink();
					}
					else if (!vacuum_in_status_auto) {
						Serial.print('$' + (String)device_ID + ",VAC_IN,STATUS,AUTO,OFF#");
						ledBlink();
					}
					if (devMode) Serial.println();
				}
				else print_invalidcmd();
			}
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			//VAC_OUT COMMANDS
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else if ((String)token[0] == "VAC_OUT") { //VAC_OUT SENSOR CONTROL
				if ((String)token[1] == "SET") {
					if (token_count <= 3) {
						if ((String)token[2] == "AUTO") {
							vacuum_out_status_auto = true;
							vacuum_out_sendInterval = vacuum_out_sendInterval_default;
							vacuum_out_defineReadInterval();
							Serial.print('$' + (String)device_ID + ",VAC_OUT,STATUS,AUTO,ON," + (String)vacuum_out_sendInterval + ',' + (String)vacuum_out_readInterval + '#');
							ledBlink();
						}
					}
					else if (token_count == 4) {
						if ((String)token[2] == "AUTO") {
							if ((String)token[3] == "ON") {
								vacuum_out_status_auto = true;
								vacuum_out_sendInterval = vacuum_out_sendInterval_default;
								vacuum_out_defineReadInterval();
								Serial.print('$' + (String)device_ID + ",VAC_OUT,STATUS,AUTO,ON," + (String)vacuum_out_sendInterval + ',' + (String)vacuum_out_readInterval + '#');
								ledBlink();
							}
							else if ((String)token[3] == "OFF") {
								vacuum_out_status_auto = false;
								Serial.print('$' + (String)device_ID + ",VAC_OUT,STATUS,AUTO,OFF#");
								ledBlink();
							}
							else if (check_isDigit((String)token[3]) && token[3] != NULL) {
								long send_interval = token[3].toInt();
								if (send_interval >= 50 && send_interval <= 1000000000L) {
									String check = (String)token[3];
									if (check.charAt(0) != '0' && check.length() > 1) {
										vacuum_out_sendInterval = send_interval;
										vacuum_out_defineReadInterval();
										vacuum_out_status_auto = true;
										Serial.print('$' + (String)device_ID + ",VAC_OUT,STATUS,AUTO,ON," + (String)vacuum_out_sendInterval + ',' + (String)vacuum_out_readInterval + '#');
										ledBlink();
									}
									else print_invalidcmd();
								}
								else print_invalidcmd();
							}
							else print_invalidcmd();
						}
						else print_invalidcmd();
					}
					else print_invalidcmd();
				}
				else if ((String)token[1] == "READ") {
					if (token_count <= 2) {
						vacuum_out_read(true);
						vacuum_out_status_auto = false;
						ledBlink();
					}
					else print_invalidcmd();
				}
				else if ((String)token[1] == "STATUS") {
					if (vacuum_out_status_auto) {
						Serial.print('$' + (String)device_ID + ",VAC_OUT,STATUS,AUTO,ON," + (String)vacuum_out_sendInterval + ',' + (String)vacuum_out_readInterval + '#');
						ledBlink();
					}
					else if (!vacuum_out_status_auto) {
						Serial.print('$' + (String)device_ID + ",VAC_OUT,STATUS,AUTO,OFF#");
						ledBlink();
					}
					if (devMode) Serial.println();
				}
				else print_invalidcmd();
			}
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			//BMP280 COMMANDS
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else if ((String)token[0] == "BMP280") { //BMP280 CONTROL
				if (BMP280_status_exist) {
					if ((String)token[1] == "BEGIN") {
						Serial.print('$' + (String)device_ID + ",BMP280,BEGIN#");
						BMP280_setup();
						ledBlink();
					}
					else if ((String)token[1] == "SET") {
						if (token_count <= 3) {
							if ((String)token[2] == "AUTO") {
								BMP280_status_auto = true;
								BMP280_readInterval = BMP280_readInterval_default;
								Serial.print('$' + (String)device_ID + ",BMP280,STATUS,AUTO,ON#");
								ledBlink();
							}
						}
						else if (token_count == 4) {
							if ((String)token[2] == "AUTO") {
								if ((String)token[3] == "ON") {
									BMP280_status_auto = true;
									BMP280_readInterval = BMP280_readInterval_default;
									Serial.print('$' + (String)device_ID + ",BMP280,STATUS,AUTO,ON#");
									ledBlink();
								}
								else if ((String)token[3] == "OFF") {
									BMP280_status_auto = false;
									Serial.print('$' + (String)device_ID + ",BMP280,STATUS,AUTO,OFF#");
									ledBlink();
								}
								else if (check_isDigit((String)token[3]) && token[3] != NULL) {
									long read_interval = token[3].toInt();
									if (read_interval <= 1000000000L) {
										String check = (String)token[3];
										if (!(check.charAt(0) == '0' && check.length() > 1)) {
											if (read_interval < 1000) {
												BMP280_readInterval = BMP280_readInterval_default;
												BMP280_status_auto = true;
												Serial.print('$' + (String)device_ID + ",BMP280,STATUS,AUTO," + (String)BMP280_readInterval + '#');
												ledBlink();
											}
											else {
												BMP280_readInterval = read_interval;
												BMP280_status_auto = true;
												Serial.print('$' + (String)device_ID + ",BMP280,STATUS,AUTO," + (String)BMP280_readInterval + '#');
												ledBlink();
											}
										}
										else print_invalidcmd();
									}
									else print_invalidcmd();
								}
								else print_invalidcmd();
							}
							else print_invalidcmd();
						}
						else print_invalidcmd();
					}
					else if ((String)token[1] == "READ") {
						if (token_count <= 2) {
							BMP280_read(true);
							BMP280_status_auto = false;
							ledBlink();
						}
						else print_invalidcmd();
					}
					else if ((String)token[1] == "STATUS") {
						if (BMP280_status_auto) {
							if (BMP280_readInterval == BMP280_readInterval_default) {
								Serial.print('$' + (String)device_ID + ",BMP280,STATUS,AUTO,ON#");
								ledBlink();
							}
							else if (BMP280_readInterval != BMP280_readInterval_default) {
								Serial.print('$' + (String)device_ID + ",BMP280,STATUS,AUTO," + (String)BMP280_readInterval + '#');
								ledBlink();
							}
						}
						else if (!BMP280_status_auto) {
							Serial.print('$' + (String)device_ID + ",BMP280,STATUS,AUTO,OFF#");
							ledBlink();
						}
						if (devMode) Serial.println();
					}
					else print_invalidcmd();
				}
				else Serial.print('$' + (String)device_ID + ",BMP280,STATUS,SENSOR_NOT_FOUND#");
			}
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			//BME280 COMMANDS
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else if ((String)token[0] == "BME280") { //BME280 CONTROL
				if (BME280_status_exist) {
					if ((String)token[1] == "BEGIN") {
						Serial.print('$' + (String)device_ID + ",BME280,BEGIN#");
						BME280_setup();
						ledBlink();
					}
					else if ((String)token[1] == "SET") {
						if (token_count <= 3) {
							if ((String)token[2] == "AUTO") {
								BME280_status_auto = true;
								BME280_readInterval = BME280_readInterval_default;
								Serial.print('$' + (String)device_ID + ",BME280,STATUS,AUTO,ON#");
								ledBlink();
							}
						}
						else if (token_count == 4) {
							if ((String)token[2] == "AUTO") {
								if ((String)token[3] == "ON") {
									BME280_status_auto = true;
									BME280_readInterval = BME280_readInterval_default;
									Serial.print('$' + (String)device_ID + ",BME280,STATUS,AUTO,ON#");
									ledBlink();
								}
								else if ((String)token[3] == "OFF") {
									BME280_status_auto = false;
									Serial.print('$' + (String)device_ID + ",BME280,STATUS,AUTO,OFF#");
									ledBlink();
								}
								else if (check_isDigit((String)token[3]) && token[3] != NULL) {
									long read_interval = token[3].toInt();
									if (read_interval <= 1000000000L) {
										String check = (String)token[3];
										if (!(check.charAt(0) == '0' && check.length() > 1)) {
											if (read_interval < 1000) {
												BME280_readInterval = BME280_readInterval_default;
												BME280_status_auto = true;
												Serial.print('$' + (String)device_ID + ",BME280,STATUS,AUTO," + (String)BME280_readInterval + '#');
												ledBlink();
											}
											else {
												BME280_readInterval = read_interval;
												BME280_status_auto = true;
												Serial.print('$' + (String)device_ID + ",BME280,STATUS,AUTO," + (String)BME280_readInterval + '#');
												ledBlink();
											}
										}
										else print_invalidcmd();
									}
									else print_invalidcmd();
								}
								else print_invalidcmd();
							}
							else print_invalidcmd();
						}
						else print_invalidcmd();
					}
					else if ((String)token[1] == "READ") {
						if (token_count <= 2) {
							BME280_read(true);
							BME280_status_auto = false;
							ledBlink();
						}
						else print_invalidcmd();
					}
					else if ((String)token[1] == "STATUS") {
						if (BME280_status_auto) {
							if (BME280_readInterval == BME280_readInterval_default) {
								Serial.print('$' + (String)device_ID + ",BME280,STATUS,AUTO,ON#");
								ledBlink();
							}
							else if (BME280_readInterval != BME280_readInterval_default) {
								Serial.print('$' + (String)device_ID + ",BME280,STATUS,AUTO," + (String)BME280_readInterval + '#');
								ledBlink();
							}
						}
						else if (!BME280_status_auto) {
							Serial.print('$' + (String)device_ID + ",BME280,STATUS,AUTO,OFF#");
							ledBlink();
						}
						if (devMode) Serial.println();
					}
					else print_invalidcmd();
				}
				else Serial.print('$' + (String)device_ID + ",BME280,STATUS,SENSOR_NOT_FOUND#");
			}

			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			//SHT31 COMMANDS
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else if ((String)token[0] == "SHT31") { //SHT31 CONTROL
				if (SHT31_status_exist) {
					if ((String)token[1] == "BEGIN") {
						Serial.print('$' + (String)device_ID + ",SHT31,BEGIN#");
						SHT31_setup();
						ledBlink();
					}
					else if ((String)token[1] == "SET") {
						if (token_count <= 3) {
							if ((String)token[2] == "AUTO") {
								SHT31_status_auto = true;
								SHT31_readInterval = SHT31_readInterval_default;
								Serial.print('$' + (String)device_ID + ",SHT31,STATUS,AUTO,ON#");
								ledBlink();
							}
						}
						else if (token_count == 4) {
							if ((String)token[2] == "AUTO") {
								if ((String)token[3] == "ON") {
									SHT31_status_auto = true;
									SHT31_readInterval = SHT31_readInterval_default;
									Serial.print('$' + (String)device_ID + ",SHT31,STATUS,AUTO,ON#");
									ledBlink();
								}
								else if ((String)token[3] == "OFF") {
									SHT31_status_auto = false;
									Serial.print('$' + (String)device_ID + ",SHT31,STATUS,AUTO,OFF#");
									ledBlink();
								}
								else if (check_isDigit((String)token[3]) && token[3] != NULL) {
									long read_interval = token[3].toInt();
									if (read_interval <= 1000000000L) {
										String check = (String)token[3];
										if (!(check.charAt(0) == '0' && check.length() > 1)) {
											if (read_interval < 1000) {
												SHT31_readInterval = SHT31_readInterval_default;
												SHT31_status_auto = true;
												Serial.print('$' + (String)device_ID + ",SHT31,STATUS,AUTO," + (String)SHT31_readInterval + '#');
												ledBlink();
											}
											else {
												SHT31_readInterval = read_interval;
												SHT31_status_auto = true;
												Serial.print('$' + (String)device_ID + ",SHT31,STATUS,AUTO," + (String)SHT31_readInterval + '#');
												ledBlink();
											}
										}
										else print_invalidcmd();
									}
									else print_invalidcmd();
								}
								else print_invalidcmd();
							}
							else print_invalidcmd();
						}
						else print_invalidcmd();
					}
					else if ((String)token[1] == "READ") {
						if (token_count <= 2) {
							SHT31_read(true);
							SHT31_status_auto = false;
							ledBlink();
						}
						else print_invalidcmd();
					}
					else if ((String)token[1] == "STATUS") {
						if (SHT31_status_auto) {
							if (SHT31_readInterval == SHT31_readInterval_default) {
								Serial.print('$' + (String)device_ID + ",SHT31,STATUS,AUTO,ON#");
								ledBlink();
							}
							else if (SHT31_readInterval != SHT31_readInterval_default) {
								Serial.print('$' + (String)device_ID + ",SHT31,STATUS,AUTO," + (String)SHT31_readInterval + '#');
								ledBlink();
							}
						}
						else if (!SHT31_status_auto) {
							Serial.print('$' + (String)device_ID + ",SHT31,STATUS,AUTO,OFF#");
							ledBlink();
						}
						if (devMode) Serial.println();
					}
					else print_invalidcmd();
				}
				else Serial.print('$' + (String)device_ID + ",SHT31,STATUS,SENSOR_NOT_FOUND#");
			}
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			//PARTICULATE MATTER COMMANDS
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else if ((String)token[0] == "PM") { //PARTICULATE MATTER CONTROL
				if ((String)token[1] == "RESTART") {
					if (token_count == 2) {
						digitalWrite(PM_power_trigger_pin, HIGH);
						pm_power_status = false;
						PM2p5_data_enable = false;
						PM10_data_enable = false;
						pm_restart_status = true;
						pm_restart_tc = millis();
						pm_restartDuration = pm_restartDuration_default;
						Serial.print('$' + (String)device_ID + ",PM,STATUS,RESTART,3000#");
						if (devMode) Serial.println();
						Serial1.end();
						Serial2.end();
						Serial.print('$' + (String)device_ID + ",PM,2.5,STATUS,STOP#");
						if (devMode) Serial.println();
						Serial.print('$' + (String)device_ID + ",PM,10,STATUS,STOP#");
						if (devMode) Serial.println();
						ledBlink();
					}
					else if (token_count == 3 && token[2] != NULL) {
						if (check_isDigit((String)token[2])) {
							long halt = token[2].toInt();
							if (halt <= 1000000000L) {
								if ((uint32_t)halt < pm_restartDuration_default) {
									digitalWrite(PM_power_trigger_pin, HIGH);
									pm_power_status = false;
									PM2p5_data_enable = false;
									PM10_data_enable = false;
									pm_restart_status = true;
									pm_restart_tc = millis();
									pm_restartDuration = pm_restartDuration_default;
									Serial.print('$' + (String)device_ID + ",PM,STATUS,RESTART,3000#");
									if (devMode) Serial.println();
									Serial1.end();
									Serial2.end();
									Serial.print('$' + (String)device_ID + ",PM,2.5,STATUS,STOP#");
									if (devMode) Serial.println();
									Serial.print('$' + (String)device_ID + ",PM,10,STATUS,STOP#");
									if (devMode) Serial.println();
									ledBlink();
								}
								else {
									String check = (String)token[2];
									if (check.charAt(0) != '0' && check.length() > 1) {
										digitalWrite(PM_power_trigger_pin, HIGH);
										pm_power_status = false;
										PM2p5_data_enable = false;
										PM10_data_enable = false;
										pm_restart_status = true;
										pm_restart_tc = millis();
										pm_restartDuration = halt;
										Serial.print('$' + (String)device_ID + ",PM,STATUS,RESTART," + (String)halt + '#');
										if (devMode) Serial.println();
										Serial1.end();
										Serial2.end();
										Serial.print('$' + (String)device_ID + ",PM,2.5,STATUS,STOP#");
										if (devMode) Serial.println();
										Serial.print('$' + (String)device_ID + ",PM,10,STATUS,STOP#");
										if (devMode) Serial.println();
										ledBlink();
									}
									else print_invalidcmd();
								}
							}
							else print_invalidcmd();
						}
						else print_invalidcmd();
					}
					else print_invalidcmd();
				}
				else if ((String)token[1] == "OFF") {
					if (token_count == 2) {
						digitalWrite(PM_power_trigger_pin, HIGH);
						pm_power_status = false;
						PM2p5_data_enable = false;
						PM10_data_enable = false;
						Serial.print('$' + (String)device_ID + ",PM,STATUS,OFF#");
						if (devMode) Serial.println();
						Serial1.end();
						Serial2.end();
						Serial.print('$' + (String)device_ID + ",PM,2.5,STATUS,STOP#");
						if (devMode) Serial.println();
						Serial.print('$' + (String)device_ID + ",PM,10,STATUS,STOP#");
						if (devMode) Serial.println();
						ledBlink();
					}
					else print_invalidcmd();
				}
				else if ((String)token[1] == "ON") {
					if (token_count == 2) {
						digitalWrite(PM_power_trigger_pin, LOW);
						pm_power_status = true;
						PM2p5_data_enable = true;
						PM10_data_enable = true;
						Serial.print('$' + (String)device_ID + ",PM,STATUS,ON#");
						if (devMode) Serial.println();
						Serial1.begin(9600);
						Serial2.begin(9600);
						Serial.print('$' + (String)device_ID + ",PM,2.5,STATUS,RUN#");
						if (devMode) Serial.println();
						Serial.print('$' + (String)device_ID + ",PM,10,STATUS,RUN#");
						if (devMode) Serial.println();
						ledBlink();
					}
					else print_invalidcmd();
				}
				else if ((String)token[1] == "STATUS") {
					if (token_count == 2) {
						if (pm_power_status) Serial.print('$' + (String)device_ID + ",PM,STATUS,ON#");
						else if (!pm_power_status) Serial.print('$' + (String)device_ID + ",PM,STATUS,OFF#");
						ledBlink();
					}
					else print_invalidcmd();
				}
				else if (token[1] == "2.5") {
					if ((String)token[2] == "SEND") {
						if (token_count == 4) {
							for (byte i = 0; i < token[3].length(); i++)
							{
								Serial1.write(token[3].charAt(i));
							}
							Serial.print('$' + (String)device_ID + ",PM,2.5,SEND," + '"' + token[3] + '"' + '#');
							if (devMode) Serial.println();
							ledBlink();
						}
						else print_invalidcmd();
					}
					else if (token[2] == "STOP") {
						PM2p5_data_enable = false;
						Serial.print('$' + (String)device_ID + ",PM,2.5,STATUS,STOP#");
						if (devMode) Serial.println();
						ledBlink();
					}
					else if (token[2] == "RUN") {
						if (pm_power_status) {
							PM2p5_data_enable = true;
							Serial.print('$' + (String)device_ID + ",PM,2.5,STATUS,RUN#");
							if (devMode) Serial.println();
							ledBlink();
						}
						if (!pm_power_status) {
							Serial.print('$' + (String)device_ID + ",PM,STATUS,OFF#");
							if (devMode) Serial.println();
							Serial.print('$' + (String)device_ID + ",PM,2.5,STATUS,STOP#");
							if (devMode) Serial.println();
							ledBlink();
						}


					}
					else if (token[2] == "STATUS") {
						if (PM2p5_data_enable) Serial.print('$' + (String)device_ID + ",PM,2.5,STATUS,RUN#");
						else if (!PM2p5_data_enable) Serial.print('$' + (String)device_ID + ",PM,2.5,STATUS,STOP#");
						if (devMode) Serial.println();
						ledBlink();
					}
					else print_invalidcmd();
				}
				else if (token[1] == "10") {
					if ((String)token[2] == "SEND") {
						if (token_count == 4) {
							for (byte i = 0; i < token[3].length(); i++)
							{
								Serial2.write(token[3].charAt(i));
							}
							Serial.print('$' + (String)device_ID + ",PM,10,SEND," + '"' + token[3] + '"' + '#');
							if (devMode) Serial.println();
							ledBlink();
						}
						else print_invalidcmd();
					}
					else if (token[2] == "STOP") {
						PM10_data_enable = false;
						Serial.print('$' + (String)device_ID + ",PM,10,STATUS,STOP#");
						if (devMode) Serial.println();
						ledBlink();
					}
					else if (token[2] == "RUN") {
						if (pm_power_status) {
							PM10_data_enable = true;
							Serial.print('$' + (String)device_ID + ",PM,10,STATUS,RUN#");
							if (devMode) Serial.println();
							ledBlink();
						}
						if (!pm_power_status) {
							Serial.print('$' + (String)device_ID + ",PM,STATUS,OFF#");
							if (devMode) Serial.println();
							Serial.print('$' + (String)device_ID + ",PM,10,STATUS,STOP#");
							if (devMode) Serial.println();
							ledBlink();
						}

					}
					else if (token[2] == "STATUS") {
						if (PM10_data_enable) Serial.print('$' + (String)device_ID + ",PM,10,STATUS,RUN#");
						else if (!PM10_data_enable) Serial.print('$' + (String)device_ID + ",PM,10,STATUS,STOP#");
						if (devMode) Serial.println();
						ledBlink();
					}
					else print_invalidcmd();
				}
				else print_invalidcmd();
			}
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else print_invalidcmd();
		} //end of valid statement
		else {
			print_invalidcmd();
		}
	}
}
//=====================================================================================================================
//=====================================================================================================================

void setup() {
	Serial.begin(115200);
	delay(500);

	pinMode(LED_BUILTIN, OUTPUT);

	Serial.print('$' + (String)device_ID + ",SETUP#");
	if (devMode) Serial.println();

	ledBlink(1);
	ledBlink(1);
	ledBlink(1);

	fan_begin();
	BMP280_setup();
	BME280_setup();
	SHT31_setup();
	vacuum_in_setup();
	vacuum_out_setup();
	PM2p5_begin();
	PM10_begin();

	Serial.print('$' + (String)device_ID + ',' + (String)software_version + '#');
	if (devMode) Serial.println();
	Serial.print('$' + (String)device_ID + ",READY#");
	if (devMode) Serial.println();
	ledBlink();
}

void loop() {
	serialEvent();
	BMP280_autoService(BMP280_status_auto);
	BME280_autoService(BME280_status_auto);
	SHT31_autoService(SHT31_status_auto);
	vacuum_in_autoService(vacuum_in_status_auto);
	vacuum_out_autoService(vacuum_out_status_auto);
	pm_serviceRoutine();
}

