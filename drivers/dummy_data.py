import random
import db
import time

while True:
    try:
        #insert data PM
        data_pm = f"PM_OPC;0;{round(random.uniform(1, 2.0), 2)};{round(random.uniform(1, 5.0), 2)};{round(random.uniform(27, 33), 1)};{round(random.uniform(1, 1.8), 1)};{round(random.uniform(1, 2.0), 2)};[HeaterStatus];[HeaterONTime];[HeaterOFFTimer];END_PM_OPC;"
        db.update_sensor_values(1, 10, data_pm)

        # insert HC
        mg_hc = round(random.uniform(1.00, 2.50), 2)
        hc = round(mg_hc * 1000 / ( 0.0409 * 44),2)
        value_hc = f"HC;0;{hc};{mg_hc};END_HC;"
        db.update_sensor_values(2, 20, value_hc)
        print("Done insert data")
        
    except Exception as e:
        print("Error in script:", e)
    
    time.sleep(3)
