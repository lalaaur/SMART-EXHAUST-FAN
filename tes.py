import sqlite3

conn = sqlite3.connect('data.db')
c = conn.cursor()
c.execute("SELECT * FROM sensor_data")
rows = c.fetchall()
print(rows)  # Memastikan data ada
conn.close()
