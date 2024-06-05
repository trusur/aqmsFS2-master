#include <Wire.h>
#include <Ard2499.h>
Ard2499 ard2499board;

void setup() {
  Serial.begin(9600);
  Wire1.begin();
  ard2499board.begin(ARD2499_ADC_ADDR_ZZZ, ARD2499_EEP_ADDR_ZZ);
  ard2499board.ltc2499ChangeConfiguration(LTC2499_CONFIG2_60_50HZ_REJ);
  Serial.println("CEMS_ARD2499_START");
}

int32_t AIN0, AIN1, AIN2, AIN3, AIN4, AIN5, AIN6, AIN7,AIN8, AIN9, AIN10, AIN11, AIN12, AIN13, AIN14, AIN15;
int32_t AVG_AIN0, AVG_AIN1, AVG_AIN2, AVG_AIN3, AVG_AIN4, AVG_AIN5, AVG_AIN6, AVG_AIN7, AVG_AIN8, AVG_AIN9, AVG_AIN10, AVG_AIN11, AVG_AIN12, AVG_AIN13, AVG_AIN14, AVG_AIN15;
int counter = 0;

void loop() {

  AIN0 = AIN0 + ard2499board.ltc2499ReadAndChangeChannel(LTC2499_CHAN_SINGLE_0P);
  AIN1 = AIN1 + ard2499board.ltc2499ReadAndChangeChannel(LTC2499_CHAN_SINGLE_1P);
  AIN2 = AIN2 + ard2499board.ltc2499ReadAndChangeChannel(LTC2499_CHAN_SINGLE_2P);
  AIN3 = AIN3 + ard2499board.ltc2499ReadAndChangeChannel(LTC2499_CHAN_SINGLE_3P);
  AIN4 = AIN4 + ard2499board.ltc2499ReadAndChangeChannel(LTC2499_CHAN_SINGLE_4P);
  AIN5 = AIN5 + ard2499board.ltc2499ReadAndChangeChannel(LTC2499_CHAN_SINGLE_5P);
  AIN6 = AIN6 + ard2499board.ltc2499ReadAndChangeChannel(LTC2499_CHAN_SINGLE_6P);
  AIN7 = AIN7 + ard2499board.ltc2499ReadAndChangeChannel(LTC2499_CHAN_SINGLE_7P);
  /*AIN8 = AIN8 + ard2499board.ltc2499ReadAndChangeChannel(LTC2499_CHAN_SINGLE_8P);
  AIN9 = AIN9 + ard2499board.ltc2499ReadAndChangeChannel(LTC2499_CHAN_SINGLE_9P);
  AIN11 = AIN11 + ard2499board.ltc2499ReadAndChangeChannel(LTC2499_CHAN_SINGLE_11P);
  AIN12 = AIN12 + ard2499board.ltc2499ReadAndChangeChannel(LTC2499_CHAN_SINGLE_12P);
  AIN13 = AIN13 + ard2499board.ltc2499ReadAndChangeChannel(LTC2499_CHAN_SINGLE_13P);
  AIN14 = AIN14 + ard2499board.ltc2499ReadAndChangeChannel(LTC2499_CHAN_SINGLE_14P);
  AIN15 = AIN15 + ard2499board.ltc2499ReadAndChangeChannel(LTC2499_CHAN_SINGLE_15P);*/

  counter++;
  if(counter >= 1){
    AVG_AIN0 = AIN0 / counter;
    AVG_AIN1 = AIN1 / counter;
    AVG_AIN2 = AIN2 / counter;
    AVG_AIN3 = AIN3 / counter;
    AVG_AIN4 = AIN4 / counter;
    AVG_AIN5 = AIN5 / counter;
    AVG_AIN6 = AIN6 / counter;
    AVG_AIN7 = AIN7 / counter;
    /*AVG_AIN8 = AIN8 / counter;
    AVG_AIN9 = AIN9 / counter;
    AVG_AIN10 = AIN10 / counter;
    AVG_AIN11 = AIN11 / counter;
    AVG_AIN12 = AIN12 / counter;
    AVG_AIN13 = AIN13 / counter;
    AVG_AIN14 = AIN14 / counter;
    AVG_AIN15 = AIN15 / counter;*/
    AIN0 = 0;
    AIN1 = 0;
    AIN2 = 0;
    AIN3 = 0;
    AIN4 = 0;
    AIN5 = 0;
    AIN6 = 0;
    AIN7 = 0;
    /*AIN8 = 0;
    AIN9 = 0;
    AIN10 = 0;
    AIN11 = 0;
    AIN12 = 0;
    AIN13 = 0;
    AIN14 = 0;
    AIN15 = 0;*/
    counter = 0;
  }
  //Serial.println("CEMS_ARD2499;" + String((AVG_AIN0*2.048/16777216), 6) + ";" + String((AVG_AIN1*2.048/16777216), 6) + ";" + String((AVG_AIN2*2.048/16777216), 6) + ";" + String((AVG_AIN3*2.048/16777216), 6) + ";" + String((AVG_AIN4*2.048/16777216), 6) + ";" + String((AVG_AIN5*2.048/16777216), 6) + ";" + String((AVG_AIN6*2.048/16777216), 6) + ";" + String((AVG_AIN7*2.048/16777216), 6) + ";" + String((AVG_AIN8*2.048/16777216), 6) + ";" + String((AVG_AIN9*2.048/16777216), 6) + ";" + String((AVG_AIN10*2.048/16777216), 6) + ";" + String((AVG_AIN11*2.048/16777216), 6) + ";" + String((AVG_AIN12*2.048/16777216), 6) + ";" + String((AVG_AIN13*2.048/16777216), 6) + ";" + String((AVG_AIN14*2.048/16777216), 6) + ";" + String((AVG_AIN15*2.048/16777216), 6) + ";");
  Serial.println("CEMS_ARD2499;" + String((AVG_AIN0*2.048/16777216), 6) + ";" + String((AVG_AIN1*2.048/16777216), 6) + ";" + String((AVG_AIN2*2.048/16777216), 6) + ";" + String((AVG_AIN3*2.048/16777216), 6) + ";" + String((AVG_AIN4*2.048/16777216), 6) + ";" + String((AVG_AIN5*2.048/16777216), 6) + ";" + String((AVG_AIN6*2.048/16777216), 6) + ";" + String((AVG_AIN7*2.048/16777216), 6) + ";");
    
}
