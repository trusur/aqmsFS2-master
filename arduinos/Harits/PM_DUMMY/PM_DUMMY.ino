/*
 Name:		PM_DUMMY.ino
 Created:	5/5/2022 15:15:08
 Author:	harits
*/

/*SYSTEM CONFIGURATION
MCU: ARDUINO NANO EVERY ATmega 4809
Registers emulation: None (ATmega4809)
Baud rate: 9600
*/

#include <SoftwareSerial.h>
SoftwareSerial mySerial(2, 3); // RX, TX

uint32_t tc = millis();
volatile boolean ledState = false;
byte counter = 0;

//char buff[];
//int index = 0;

// the setup function runs once when you press reset or power the board
void setup() {
	pinMode(LED_BUILTIN, OUTPUT);
	Serial.begin(9600);
	mySerial.begin(9600);
	Serial.println("START");
}

float data0;
float data1;
float data2;
int data3;
float data4;
int data5;
byte data6;



// the loop function runs over and over again until power down or reset
void loop() {
	if (millis() - tc > 1000) {
		ledState = !ledState;
		digitalWrite(LED_BUILTIN, ledState);



		data0 = (float)random(10,800)/1000;
		data1 = (float)random(1800,2200)/1000;
		data2 = (float)random(25000,30000)/1000;
		data3 = random(50,80);
		data4 = (float)random(950000,999999)/1000;
		data5 = random(1,9);
		data6++;

		mySerial.print("00");
		mySerial.print(data0,3);

		mySerial.write(',');

		mySerial.print(data1, 1);

		mySerial.write(',');
		
		
		mySerial.write('+');

		mySerial.print(data2, 1);

		mySerial.write(',');

		mySerial.write('0');
		mySerial.print(data3);

		mySerial.write(',');

		mySerial.write('0');
		mySerial.print(data4, 1);

		mySerial.write(',');

		mySerial.write('0');
		mySerial.print(data5);

		mySerial.write(',');

		mySerial.print("*01");
		mySerial.print(data6);
		
		mySerial.write('\n');

		tc = millis();

	}
	if (mySerial.available()) {
		delay(50);
		char buff[SERIAL_RX_BUFFER_SIZE] = {0};
		while (mySerial.available() > 0) buff[index++] = mySerial.read();
		Serial.print("Get: ");
		Serial.println(buff);
	}

	if (Serial.available()) {
		delay(20);

		String buff = "";

		buff = Serial.readStringUntil('\n');

		for (byte i = 0; i <= buff.length(); i++)
		{
			mySerial.write(buff[i]);
		}
		mySerial.write('\n');
		Serial.print("send: ");
		Serial.println(buff);

	}
}


/*
000.026,2.0,+29.0,069,0984.3,00,*01559
000.028,2.0,+28.9,069,0984.3,00,*01569
000.027,2.0,+28.9,069,0984.3,00,*01568
000.026,2.0,+28.9,069,0984.2,00,*01566
000.029,2.0,+29.0,069,0984.3,00,*01562
000.028,2.0,+29.0,069,0984.3,00,*01561
000.027,2.0,+28.9,069,0984.3,00,*01568
000.027,2.0,+29.0,069,0984.2,00,*01559
000.026,2.0,+29.0,069,0984.3,00,*01559
000.025,2.0,+28.9,069,0984.3,00,*01566
000.027,2.0,+29.0,069,0984.2,00,*01559
000.027,2.0,+28.9,069,0984.2,00,*01567
000.031,2.0,+28.9,069,0984.2,00,*01562
000.031,2.0,+28.9,069,0984.2,00,*01562
000.030,2.0,+28.9,069,0984.2,00,*01561
000.030,2.0,+28.9,069,0984.3,00,*01562
000.027,2.0,+29.0,069,0984.3,00,*01560
000.025,2.0,+29.0,069,0984.3,00,*01558
000.024,2.0,+29.0,069,0984.2,00,*01556
000.026,2.0,+28.9,069,0984.3,00,*01567
000.024,2.0,+29.0,069,0984.2,00,*01556
000.025,2.0,+28.9,069,0984.2,00,*01565
000.024,2.0,+29.0,069,0984.2,00,*01556
000.024,2.0,+29.0,069,0984.2,00,*01556
000.023,2.0,+29.0,069,0984.3,00,*01556
000.024,2.0,+28.9,069,0984.2,00,*01564
000.031,2.0,+28.9,069,0984.3,00,*01563
000.028,2.0,+29.0,069,0984.3,00,*01561
000.026,2.0,+29.0,069,0984.2,00,*01558
000.027,2.0,+29.0,069,0984.3,00,*01560
000.026,2.0,+28.9,069,0984.3,00,*01567
000.026,2.0,+29.0,069,0984.2,00,*01558
000.026,2.0,+28.9,069,0984.2,00,*01566
000.025,2.0,+29.0,069,0984.3,00,*01558
000.029,2.0,+29.0,069,0984.2,00,*01561
000.028,2.0,+28.9,069,0984.2,00,*01568
000.028,2.0,+29.0,069,0984.3,00,*01561
000.035,2.0,+29.0,069,0984.2,00,*01558
000.033,2.0,+29.0,069,0984.3,00,*01557
000.033,2.0,+29.0,069,0984.3,00,*01557
000.031,2.0,+29.0,069,0984.3,00,*01555
000.031,2.0,+29.0,069,0984.2,00,*01554
000.029,2.0,+29.0,069,0984.3,00,*01562
000.029,2.0,+29.0,069,0984.3,00,*01562
000.027,2.0,+29.0,069,0984.3,00,*01560
000.026,2.0,+29.0,069,0984.2,00,*01558
000.024,2.0,+29.0,069,0984.2,00,*01556
000.024,2.0,+29.0,069,0984.2,00,*01556
000.023,2.0,+29.0,069,0984.3,00,*01556
000.023,2.0,+29.0,069,0984.3,00,*01556
000.023,2.0,+29.0,069,0984.3,00,*01556
000.022,2.0,+29.0,069,0984.3,00,*01555
000.024,2.0,+29.0,069,0984.3,00,*01557
000.024,2.0,+29.0,069,0984.3,00,*01557
000.024,2.0,+29.0,069,0984.2,00,*01556
000.025,2.0,+28.9,069,0984.3,00,*01566
000.024,2.0,+28.9,069,0984.3,00,*01565
000.022,2.0,+29.0,069,0984.3,00,*01555


*/