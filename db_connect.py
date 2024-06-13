import mysql.connector

def connecting():
    try:
        return mysql.connector.connect(host="localhost",user="root",passwd="R2h2s12*",database="aqms_efs1")
    except Exception as e: 
        return False