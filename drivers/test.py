response = "SEMEATECH START;SEMEATECH 0x01;NO2;58;0.002;28.50;79;END;SEMEATECH 0x02;SO2;58;0.002;28.50;79;END;SEMEATECH 0x03;O3;58;0.002;28.50;79;END;SEMEATECH 0x04;CO;58;0.002;28.50;79;END;SEMEATECH FINISH;"
sematech = response.split(";END;")
for index,res in enumerate(sematech):
    final_str = res.replace("SEMEATECH START;","")
    final_str = final_str.replace("SEMEATECH FINISH;","")
    print(final_str)
# print(sematech)