<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Smart Exhaust Fan</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f5f7fe;
    }

    .navbar {
      display: flex;
      justify-content: center;
      gap: 40px;
      padding: 20px 0;
      font-weight: bold;
    }

    .nav-link {
      text-decoration: none;
      color: black;
    }

    .container {
      display: grid;
      grid-template-areas:
        "temp fan"
        "gas fan"
        "threshold threshold";
      grid-gap: 20px;
      padding: 30px;
      max-width: 1000px;
      margin: auto;
    }

    .temp-card { grid-area: temp; }
    .gas-card { grid-area: gas; }
    .fan-card { grid-area: fan; }
    .threshold-card { grid-area: threshold; }

    .card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      padding: 30px 20px;
      min-height: 140px;
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .icon img {
      width: 70px;
      height: 70px;
    }

    .text h4 {
      margin: 0;
      font-size: 16px;
      font-weight: bold;
    }

    .text p {
      margin: 4px 0 0;
      font-size: 20px;
    }

.fan-card {
  justify-content: center; /* posisi tengah horizontal */
  gap: 40px;
}

.fan-card .icon {
  display: flex;
  align-items: center;
  justify-content: center;
}

.fan-card .icon img {
  width: 130px;
  height: 130px;
}

.fan-content {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
  gap: 10px;
}

.fan-content h4 {
  font-size: 18px;
  margin: 0;
}

    .fan-buttons {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }

    .fan-buttons button {
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
    }

    .btn-on {
      background-color: #4a90e2;
      color: white;
    }

    .btn-off {
      background-color: #333;
      color: white;
    }

    .threshold-card {
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      min-height: 160px;
    }

    .threshold-card h4 {
      margin-bottom: 10px;
    }

    .threshold-values {
      display: flex;
      gap: 50px;
      align-items: center;
      justify-content: center;
    }

    .threshold-item {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .threshold-item img {
      width: 50px;
      height: 50px;
    }

    .threshold-item p {
      margin: 0;
      font-size: 16px;
    }
  </style>
</head>
<body>
  <nav class="navbar">
    <a href="index.php" class="nav-link">HOME</a>
    <a href="history.php" class="nav-link">HISTORY</a>
  </nav>

  <div class="container">
    <div class="card temp-card">
      <div class="icon">
        <img src="static/icons/Temperature.png" alt="Temperature Icon">
      </div>
      <div class="text">
        <h4>Room Temperature</h4>
        <p id="temp">-- °C</p>
      </div>
    </div>

    <div class="card gas-card">
      <div class="icon">
        <img src="static/icons/Aerodynamic.png" alt="Gas Icon">
      </div>
      <div class="text">
        <h4>Gas Level</h4>
        <p id="gas">--</p>
      </div>
    </div>

    <div class="card fan-card">
      <div class="icon">
        <img src="static/icons/Fan Speed.png" alt="Fan Icon">
      </div>
      <div class="fan-content">
        <h4>Fan Status</h4>
        <div id="fan-status" class="fan-buttons">
          <button class="btn-on">ON</button>
          <button class="btn-off">OFF</button>
        </div>
      </div>
    </div>

    <div class="card threshold-card">
      <h4>Threshold</h4>
      <div class="threshold-values">
        <div class="threshold-item">
          <img src="static/icons/Temperature.png" alt="Temp Icon">
          <p><strong>Temperature:</strong> 40 °C</p>
        </div>
        <div class="threshold-item">
          <img src="static/icons/Aerodynamic.png" alt="Gas Icon">
          <p><strong>Gas Level:</strong> 1500</p>
        </div>
      </div>
    </div>
  </div>

  <script>
    function fetchData() {
      fetch('http://192.168.1.4:5000/api/latest')
        .then(res => res.json())
        .then(data => {
          document.getElementById("temp").textContent = data.temperature + " °C";
          document.getElementById("gas").textContent = data.gas;
          
          const status = data.fan_status.toLowerCase();
          const onBtn = document.querySelector('.btn-on');
          const offBtn = document.querySelector('.btn-off');

          if (status === "on") {
            onBtn.style.backgroundColor = "#4a90e2";
            offBtn.style.backgroundColor = "#ccc";
          } else if (status === "medium") {
            onBtn.style.backgroundColor = "#FFD700"; // Kuning untuk status sedang
            offBtn.style.backgroundColor = "#ccc";
          } else {
            onBtn.style.backgroundColor = "#ccc";
            offBtn.style.backgroundColor = "#333";
          }

        })
        .catch(error => {
          console.error("Error fetching data:", error);
        });
    }

    fetchData();
    setInterval(fetchData, 3000);
  </script>
</body>
</html>
