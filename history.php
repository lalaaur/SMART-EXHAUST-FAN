<?php
// URL Flask API untuk mengambil sejarah data
$api_url = 'http://192.168.1.4:5000/api/history';

// Menggunakan file_get_contents untuk mengambil data JSON dari API
$response = file_get_contents($api_url);

// Mengubah response JSON menjadi array
$data = json_decode($response, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="static/css/style.css">
  <title>Smart Exhaust Fan - History</title>
</head>
<body>
  <nav class="navbar">
    <a href="index.php" class="nav-link">HOME</a>
    <a href="history.php" class="nav-link">HISTORY</a>
  </nav>
  <div class="container">
    <h2>Sensor Data History</h2>
    <table class="history-table">
      <thead>
        <tr>
          <th>Timestamp</th>
          <th>Temperature (°C)</th>
          <th>Gas Level</th>
          <th>Fan Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($data) {
          foreach ($data as $row) {
            echo "<tr>
                    <td>{$row['timestamp']}</td>
                    <td>{$row['temperature']}</td>
                    <td>{$row['gas']}</td>
                    <td>{$row['fan_status']}</td>
                  </tr>";
          }
        } else {
          echo "<tr><td colspan='4'>No data available</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</body>
<script src="static/script.js"></script>
</html>

<script>
  async function loadData() {
    try {
      const response = await fetch('http://192.168.1.4:5000/api/latest');
      const data = await response.json();

      document.getElementById("temp").innerText = data.temperature + " °C";
      document.getElementById("gas").innerText = data.gas;
      document.getElementById("fan-status").innerText = data.fan_status;
      document.getElementById("fan-status").style.display = "block";
    } catch (error) {
      console.error("Gagal mengambil data:", error);
    }
  }

  loadData(); // jalankan saat halaman dibuka
</script>
