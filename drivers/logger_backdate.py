import random
import db
from datetime import datetime, timedelta
import json
import mysql.connector

# read JSON file
def read_json_file(file_path):
    try:
        with open(file_path, "r") as file:
            data = json.load(file)
            return data
    except Exception as e:
        print("Error membaca file JSON:", e)
        return None

# generate random value for each parameter
def random_value(key):
    value_map = {
        "no2": (80, 200),
        "o3": (6, 30),
        "co": (500, 2000),
        "so2": (30, 70),
        "hc": (2, 40),
        "pm25": (45, 70),
        "pm25_flow": (0.25, 0.32),
        "pm10_flow": (0.25, 0.32),
        "pm10": (12, 18),
        "pressure": (999, 1020),
        "wd": (270, 300),
        "ws": (0, 5),
        "temperature": (24, 31),
        "humidity": (67, 98),
        "sr": (0, 5),
        "rain_intensity": (0, 3),
    }
    if key in value_map:
        value = random.uniform(*value_map[key])
        return [round(value, 2), round(value, 3)]
    return []

# generaten datetime list
def generate_datetime_list(start_str, end_str, interval_minutes=30):
    # 22/12/2024 12:00:00 ( contoh format datetime)
    start_time = datetime.strptime(start_str, "%d/%m/%Y %H:%M:%S")
    end_time = datetime.strptime(end_str, "%d/%m/%Y %H:%M:%S")
    interval = timedelta(minutes=interval_minutes)

    current_time = start_time
    while current_time <= end_time:
        yield current_time.strftime("%Y-%m-%d %H:%M:%S")
        current_time += interval


# insert bacth data to database
def batch_insert_measurement(cnx,data, batch_size=5000):
    if cnx is None:
        return
    try:
        cursor = cnx.cursor()
        sql = """
            INSERT INTO measurements (parameter_id, value, sensor_value, is_valid, total_valid, total_data, avg_id, time_group)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s )
            ON DUPLICATE KEY UPDATE
                avg_id = VALUES(avg_id);
            """

        # Memecah data ke dalam batch
        for i in range(0, len(data), batch_size):
            batch = data[i : i + batch_size]
            cursor.executemany(sql, batch)

            cnx.commit()
            print(f"Batch {i // batch_size + 1} inserted/updated: {len(batch)} rows.")
    except Exception as err:
        print(f"Error: {err}")
    finally:
        if cnx.is_connected():
            cursor.close()
            cnx.close()


def main():
    try:
        cnx = db.connect()
        cur = cnx.cursor(dictionary=True, buffered=True)

        file_path = "data.json"
        logger = read_json_file(file_path)

        if not logger:
            raise Exception(f"No file {file_path} ")

        cur.execute(
            """
                    SELECT code, id,p_type from parameters where is_view = 1
                """,
        )
        rows = cur.fetchall()
        parameters = {
            item["code"]: {"id": item["id"], "p_type": item["p_type"]} for item in rows
        }

        cur.execute(
            """
                    SELECT content from configurations where name = 'id_stasiun'
                    """
        )
        id_stasiun = cur.fetchone()["content"]

        if not id_stasiun in logger:
            raise Exception(f"No id_stasiun {id_stasiun} in file {file_path} ")

        data = logger[id_stasiun]
        result = []

        for x, y in data.items():
            times = []
            for k in y:
                z = k.split("-")
                if len(z) == 2:
                    k = generate_datetime_list(z[0], z[1])
                    times.extend(k)
                else:
                    format_time = (datetime.strptime(k, "%d/%m/%Y %H:%M:%S")).strftime("%Y-%m-%d %H:%M:%S")
                    times.append(format_time)
            for t in times:
                value = random_value(x)
                is_valid = (
                    11 if parameters[x]["p_type"] in ["gas", "particulate"] else 1
                )
                total_data = total_valid = (
                    random.randint(29, 30)
                    if parameters[x]["p_type"] in ["gas", "particulate"]
                    else 1
                )
                avg_id = (datetime.strptime(t, "%Y-%m-%d %H:%M:%S")).strftime("%y%m%d%H%M01")
                result.append(
                    (
                        parameters[x]["id"],  # parameter_id
                        value[0],  # value
                        value[1],  # sensor_value
                        is_valid,  # is_valid
                        total_valid,  # total_valid
                        total_data,  # total_data
                        avg_id,  # avg_id
                        t,  # time_group
                    )
                )
        
        batch_insert_measurement(cnx,result, batch_size=1000)
        result = []
        data = []

    except Exception as e:
        print("Error ...", e)
    finally:
        cur.close()
        cnx.close()

if __name__ == "__main__":
    main()
