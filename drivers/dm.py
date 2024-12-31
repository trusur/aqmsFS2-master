import random
import db
import time
from datetime import datetime


print("Start Reading Sensor ......")
time.sleep(2)
while True:
    try:
        current_time = datetime.now()
        hour = current_time.hour

        minute = current_time.minute
        changes = minute % 3 == 0

        if changes:
            # insert data PM
            pm10 = random.uniform(45, 70)
            pm25 = random.uniform(12, 18) if 0 <= hour < 18 else random.uniform(15, 16)

            data_pm = f"PM_OPC;0;1;{round(pm25,2)};{round(pm10,2)};{round(random.uniform(27, 33), 1)};{round(random.uniform(1, 1.8), 1)};{round(random.uniform(0.5, 2.0), 2)};HEATER_OFF;0;0;END_PM_OPC;"
            db.update_sensor_values(1, 10, data_pm)
            print("Read Pin 10")
            time.sleep(random.uniform(0.1, 0.5))

            # insert HC
            hc = random.uniform(2, 40)
            ppb_hc = hc * 1000 / (0.0409 * 44)
            db.update_sensor_values(
                2, 20, f"HC;{round(ppb_hc,2)};{round(hc,2)};END_HC;"
            )
            print("Read Pin 20")
            time.sleep(random.uniform(0.7, 1))

            no2 = int(random.uniform(80, 100))
            ppb_no2 = int(no2 * 0.5257731958762887)
            db.update_sensor_values(
                1,
                31,
                f"SEMEATECH_DATA;1;NO2;{no2};{ppb_no2};{round(random.uniform(22, 25), 2)};{round(random.uniform(63, 65), 2)}",
            )

            so2 = int(random.uniform(20, 30))  # 2
            ppb_so2 = int(so2 * 0.376344086021505)
            db.update_sensor_values(
                1,
                32,
                f"SEMEATECH_DATA;2;SO2;{so2};{ppb_so2};{round(random.uniform(22, 25), 2)};{round(random.uniform(63, 65), 2)}",
            )
            ppb_so2 = so2 * 2.475122349102773

            o3 = int(random.uniform(6, 10))  # 3
            ppb_o3 = int(o3 * 0.506849315068493)
            db.update_sensor_values(
                1,
                33,
                f"SEMEATECH_DATA;2;O3;{o3};{ppb_o3};{round(random.uniform(22, 25), 2)};{round(random.uniform(63, 65), 2)}",
            )

            co = int(random.uniform(500, 2000))  # 4
            ppb_co = int(co * 0.872093023255814)
            db.update_sensor_values(
                1,
                34,
                f"SEMEATECH_DATA;2;CO;{co};{ppb_co};{round(random.uniform(22, 25), 2)};{round(random.uniform(63, 65), 2)}",
            )

            db.update_sensor_values(1, 35, "END_SEMEATECH_BATCH;")
            print("Read Pin 31-35")
            time.sleep(random.uniform(0.7, 1))
            db.update_sensor_values(1, 60, -999)
            print("Read Pin 60")
        else:
            pm10 = random.uniform(20, 50)
            pm25 = random.uniform(9, 12) if 0 <= hour < 18 else random.uniform(15, 16)

            data_pm = f"PM_OPC;0;1;{round(pm25,2)};{round(pm10,2)};{round(random.uniform(27, 33), 1)};{round(random.uniform(1, 1.8), 1)};{round(random.uniform(0.5, 2.0), 2)};HEATER_OFF;0;0;END_PM_OPC;"
            db.update_sensor_values(1, 10, data_pm)
            print("Read Pin 10")
            time.sleep(random.uniform(0.1, 0.5))

            # insert HC
            hc = random.uniform(random.uniform(5, 19), random.uniform(20, 30))
            ppb_hc = hc * 1000 / (0.0409 * 44)
            db.update_sensor_values(
                2, 20, f"HC;{round(ppb_hc,2)};{round(hc,2)};END_HC;"
            )
            print("Read Pin 20")
            time.sleep(random.uniform(0.7, 1))

            no2 = int(random.uniform(80, 200))
            ppb_no2 = int(no2 * 0.5257731958762887)
            db.update_sensor_values(
                1,
                31,
                f"SEMEATECH_DATA;1;NO2;{no2};{ppb_no2};{round(random.uniform(22, 25), 2)};{round(random.uniform(63, 65), 2)}",
            )

            so2 = int(random.uniform(30, 70))  # 2
            ppb_so2 = int(so2 * 0.376344086021505)
            db.update_sensor_values(
                1,
                32,
                f"SEMEATECH_DATA;2;SO2;{so2};{ppb_so2};{round(random.uniform(22, 25), 2)};{round(random.uniform(63, 65), 2)}",
            )
            ppb_so2 = so2 * 2.475122349102773

            o3 = int(random.uniform(6, 30))  # 3
            ppb_o3 = int(o3 * 0.506849315068493)
            db.update_sensor_values(
                1,
                33,
                f"SEMEATECH_DATA;2;SO2;{o3};{ppb_o3};{round(random.uniform(22, 25), 2)};{round(random.uniform(63, 65), 2)}",
            )

            co = int(random.uniform(500, 2000))  # 4
            ppb_co = int(co * 0.872093023255814)
            db.update_sensor_values(
                1,
                34,
                f"SEMEATECH_DATA;2;SO2;{co};{ppb_co};{round(random.uniform(22, 25), 2)};{round(random.uniform(63, 65), 2)}",
            )

            db.update_sensor_values(1, 35, "END_SEMEATECH_BATCH;")
            print("Read Pin 31-35")
            time.sleep(random.uniform(0.7, 1))
            db.update_sensor_values(1, 60, -999)
            print("Read Pin 60")

    except Exception as e:
        print(
            "Error reading sensor ...",
        )
        time.sleep(1)

    time.sleep(random.uniform(0.1, 0.5))
