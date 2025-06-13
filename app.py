from flask import Flask, request, jsonify
from flask_cors import CORS
from flask_mysqldb import MySQL

app = Flask(__name__)
CORS(app)

# === MySQL Configuration ===
app.config['MYSQL_HOST'] = 'localhost'
app.config['MYSQL_USER'] = 'root'
app.config['MYSQL_PASSWORD'] = ''
app.config['MYSQL_DB'] = 'sensor_data'

mysql = MySQL(app)

# === Fuzzy Logic ===
def fuzzy_decision(temp, gas):
    if temp >= 40 or gas >= 1500:
        return "ON"
    elif 30 <= temp < 40 or 1000 <= gas < 1500:
        return "MEDIUM"
    else:
        return "OFF"

# === Endpoint: POST data from ESP32 ===
@app.route('/api/data', methods=['POST'])
def post_data():
    data = request.get_json()

    if not isinstance(data, dict):
        return jsonify({"error": "Invalid JSON data"}), 400

    temp = data.get('temperature')
    gas = data.get('gas')
    fan_status = data.get('fan_status')  # Ambil dari ESP32

    if temp is None or gas is None or fan_status is None:
        return jsonify({"error": "Missing temperature, gas, or fan_status"}), 400

    try:
        temp = float(temp)
        gas = int(gas)
    except ValueError:
        return jsonify({"error": "Invalid data type"}), 400

    try:
        with mysql.connection.cursor() as cursor:
            cursor.execute(
                "INSERT INTO sensor_data (temperature, gas, fan_status) VALUES (%s, %s, %s)",
                (temp, gas, fan_status)
            )
            mysql.connection.commit()
            print(f"Data inserted: Temp={temp}, Gas={gas}, Status={fan_status}")
    except Exception as e:
        return jsonify({"error": str(e)}), 500

    return jsonify({"status": "success"}), 200

# === Endpoint: GET latest data ===
@app.route('/api/latest', methods=['GET'])
def get_latest():
    try:
        with mysql.connection.cursor() as cursor:
            cursor.execute("SELECT temperature, gas, fan_status, timestamp FROM sensor_data ORDER BY id DESC LIMIT 1")
            row = cursor.fetchone()
    except Exception as e:
        return jsonify({"error": str(e)}), 500

    if row:
        return jsonify({
            "temperature": row[0],
            "gas": row[1],
            "fan_status": row[2],
            "timestamp": row[3]
        })
    else:
        return jsonify({"message": "No data available"}), 404

# === Endpoint: GET full history ===
@app.route('/api/history', methods=['GET'])
def get_history():
    try:
        with mysql.connection.cursor() as cursor:
            cursor.execute("SELECT timestamp, temperature, gas, fan_status FROM sensor_data ORDER BY id DESC LIMIT 100")
            rows = cursor.fetchall()
    except Exception as e:
        return jsonify({"error": str(e)}), 500

    return jsonify([{
        "timestamp": row[0],
        "temperature": row[1],
        "gas": row[2],
        "fan_status": row[3]
    } for row in rows])

# === Start API-only Server ===
if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000) # Add port explicitly
