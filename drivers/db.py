from mysql.connector.constants import ClientFlag
import mysql.connector
import logging

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
        cursor = cnx.cursor(dictionary=True, buffered=True)
        cursor.execute("SELECT * FROM sensor_values WHERE sensor_reader_id=%s AND pin=%s",(id,pin))
        row = cursor.fetchone()
        cursor.close()
        cnx.close()
        if row is None:
            return False
        return True
    except Exception as e: 
        return False
    

def get_configuration(name):
    try:
        cnx = connect()
        cursor = cnx.cursor(dictionary=True, buffered=True)
        cursor.execute("SELECT * FROM configurations WHERE name=%s",(name,))
        row = cursor.fetchone()
        cursor.close()
        cnx.close()
        if row is None:
            return None
        return row['content']
    except Exception as e: 
        print(e)
        logging.error("get_configuration: "+e)
        return None
def set_configuration(name,content):
    try:
        cnx = connect()
        cursor = cnx.cursor()
        cursor.execute("SELECT * FROM configurations WHERE name=%s",(name,))
        row = cursor.fetchone()
        if row is None:
            cursor.execute("INSERT INTO configurations (name,content) VALUES (%s,%s)",(name,content))
        else:
            cursor.execute("UPDATE configurations SET content=%s WHERE name=%s",(content,name))
        cnx.commit()
        cursor.close()
    except Exception as e: 
        logging.error("set_configuration: "+e)
        return None
    
def get_calibration(id):
    try:
        cnx = connect()
        cursor = cnx.cursor(dictionary=True,buffered=True)
        cursor.execute("SELECT * FROM calibrations WHERE id=%s",(id,))
        row = cursor.fetchone()
        cursor.close()
        cnx.close()
        if row is None:
            return None
        return row  
    except Exception as e: 
        print(e)
        logging.error("get_calibration: "+str(e))
        return None
    
def set_calibration_log(calibration_id,parameter_id,value,created_at):
    try:
        cnx = connect()
        cursor = cnx.cursor()
        cursor.execute("UPDATE calibrations SET is_executed = 1 WHERE id = %s",(calibration_id,))
        cnx.commit()
        cursor.execute("INSERT INTO calibration_logs (calibration_id,parameter_id,value,created_at) VALUES (%s,%s,%s,%s)",(calibration_id,parameter_id,value,created_at))
        cnx.commit()
        cursor.close()
        cnx.close()
        return True
    except Exception as e: 
        print(e)
        logging.error("set_calibration_log: "+str(e))
        return False
