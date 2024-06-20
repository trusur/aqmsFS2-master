from mysql.connector.constants import ClientFlag
import mysql.connector

def connect():
    try:
        return mysql.connector.connect(host="localhost",user="root",passwd="root",database="aqms_efs2")
    except Exception as e: 
        print('DB Connection Error :',e)
        return False
    
def update_sensor_values(id,pin,value):
    try:
        cnx = connect()
        if not is_sensor_values_exist(id,pin):
            return insert_sensor_values(id,pin,value)
        cursor = cnx.cursor()
        cursor.execute("UPDATE sensor_values SET value=%s, updated_at=NOW() WHERE sensor_reader_id=%s AND pin=%s",(value,id,pin))
        cnx.commit()
        cursor.close()
        cnx.close()
        return True
    except Exception as e: 
        print('Error: ',e)
        return False
def insert_sensor_values(id,pin,value):
    try:
        cnx = connect()
        cursor = cnx.cursor()
        cursor.execute("INSERT INTO sensor_values (sensor_reader_id,pin,value) VALUES (%s,%s,%s)",(id,pin,value))
        cnx.commit()
        cursor.close()
        cnx.close()
        return True
    except Exception as e: 
        print('Error: ',e)
        return False
def is_sensor_values_exist(id,pin):
    try:
        cnx = connect()
        cursor = cnx.cursor()
        cursor.execute("SELECT * FROM sensor_values WHERE sensor_reader_id=%s AND pin=%s",(id,pin))
        row = cursor.fetchone()
        cursor.close()
        cnx.close()
        if row is None:
            return False
        return True
    except Exception as e: 
        return False