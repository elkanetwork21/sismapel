<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Monitoring Bandwidth</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-gauge"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
      padding: 20px;
      background: #f7f9fc;
    }
    .gauge-container {
      display: inline-block;
      width: 300px;
      margin: 20px;
    }
    .percent-text {
      font-size: 18px;
      font-weight: bold;
      margin-top: 8px;
    }
  </style>
</head>
<body>
  <h2>Monitoring Bandwidth Customer</h2>

  <div class="gauge-container">
    <canvas id="gaugeDownload"></canvas>
    <div id="downloadPercent" class="percent-text">0%</div>
  </div>

  <div class="gauge-container">
    <canvas id="gaugeUpload"></canvas>
    <div id="uploadPercent" class="percent-text">0%</div>
  </div>

  <script>
    let maxVal = 20; // default, nanti update dari API

    function getColor(value, maxVal) {
      let ratio = value / maxVal;
      if (ratio < 0.7) return "#4caf50";   // hijau
      if (ratio < 0.9) return "#ffc107";   // kuning
      return "#f44336";                    // merah
    }

    const gaugeDownload = new Chart(document.getElementById("gaugeDownload"), {
      type: "gauge",
      data: {
        datasets: [{
          value: 0,
          data: [{ value: 0, backgroundColor: "#4caf50" }],
          minValue: 0,
          maxValue: maxVal
        }]
      },
      options: {
        responsive: true,
        title: { display: true, text: "Download (Mbps)" }
      }
    });

    const gaugeUpload = new Chart(document.getElementById("gaugeUpload"), {
      type: "gauge",
      data: {
        datasets: [{
          value: 0,
          data: [{ value: 0, backgroundColor: "#4caf50" }],
          minValue: 0,
          maxValue: maxVal
        }]
      },
      options: {
        responsive: true,
        title: { display: true, text: "Upload (Mbps)" }
      }
    });

    function updateGauges(rx, tx) {
      let rxVal = Math.min(rx, maxVal);
      gaugeDownload.data.datasets[0].value = rxVal;
      gaugeDownload.data.datasets[0].data[0].value = rxVal;
      gaugeDownload.data.datasets[0].data[0].backgroundColor = getColor(rxVal, maxVal);
      gaugeDownload.update();
      document.getElementById("downloadPercent").textContent = ((rxVal / maxVal) * 100).toFixed(0) + "%";

      let txVal = Math.min(tx, maxVal);
      gaugeUpload.data.datasets[0].value = txVal;
      gaugeUpload.data.datasets[0].data[0].value = txVal;
      gaugeUpload.data.datasets[0].data[0].backgroundColor = getColor(txVal, maxVal);
      gaugeUpload.update();
      document.getElementById("uploadPercent").textContent = ((txVal / maxVal) * 100).toFixed(0) + "%";
    }

    // 🚀 Ambil data dari API PHP
    async function fetchData() {
      try {
        let res = await fetch("get_txrx_customer.php");
        let json = await res.json();

        if (json.status === "success") {
          maxVal = json.paket || maxVal; // update maxVal sesuai paket
          updateGauges(json.download, json.upload);
        } else {
          console.error("API Error:", json.message);
        }
      } catch (e) {
        console.error("Fetch error:", e);
      }
    }

    // Jalankan fetch tiap 5 detik
    fetchData();
    setInterval(fetchData, 5000);
  </script>
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Monitoring Bandwidth</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-gauge"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
      padding: 20px;
      background: #f7f9fc;
    }
    .gauge-container {
      display: inline-block;
      width: 300px;
      margin: 20px;
    }
    .percent-text {
      font-size: 18px;
      font-weight: bold;
      margin-top: 8px;
    }
  </style>
</head>
<body>
  <h2>Monitoring Bandwidth Customer</h2>

  <div class="gauge-container">
    <canvas id="gaugeDownload"></canvas>
    <div id="downloadPercent" class="percent-text">0%</div>
  </div>

  <div class="gauge-container">
    <canvas id="gaugeUpload"></canvas>
    <div id="uploadPercent" class="percent-text">0%</div>
  </div>

  <script>
    let maxVal = 20; // default, nanti update dari API

    function getColor(value, maxVal) {
      let ratio = value / maxVal;
      if (ratio < 0.7) return "#4caf50";   // hijau
      if (ratio < 0.9) return "#ffc107";   // kuning
      return "#f44336";                    // merah
    }

    const gaugeDownload = new Chart(document.getElementById("gaugeDownload"), {
      type: "gauge",
      data: {
        datasets: [{
          value: 0,
          data: [{ value: 0, backgroundColor: "#4caf50" }],
          minValue: 0,
          maxValue: maxVal
        }]
      },
      options: {
        responsive: true,
        title: { display: true, text: "Download (Mbps)" }
      }
    });

    const gaugeUpload = new Chart(document.getElementById("gaugeUpload"), {
      type: "gauge",
      data: {
        datasets: [{
          value: 0,
          data: [{ value: 0, backgroundColor: "#4caf50" }],
          minValue: 0,
          maxValue: maxVal
        }]
      },
      options: {
        responsive: true,
        title: { display: true, text: "Upload (Mbps)" }
      }
    });

    function updateGauges(rx, tx) {
      let rxVal = Math.min(rx, maxVal);
      gaugeDownload.data.datasets[0].value = rxVal;
      gaugeDownload.data.datasets[0].data[0].value = rxVal;
      gaugeDownload.data.datasets[0].data[0].backgroundColor = getColor(rxVal, maxVal);
      gaugeDownload.update();
      document.getElementById("downloadPercent").textContent = ((rxVal / maxVal) * 100).toFixed(0) + "%";

      let txVal = Math.min(tx, maxVal);
      gaugeUpload.data.datasets[0].value = txVal;
      gaugeUpload.data.datasets[0].data[0].value = txVal;
      gaugeUpload.data.datasets[0].data[0].backgroundColor = getColor(txVal, maxVal);
      gaugeUpload.update();
      document.getElementById("uploadPercent").textContent = ((txVal / maxVal) * 100).toFixed(0) + "%";
    }

    // 🚀 Ambil data dari API PHP
    async function fetchData() {
      try {
        let res = await fetch("get_txrx_customer.php");
        let json = await res.json();

        if (json.status === "success") {
          maxVal = json.paket || maxVal; // update maxVal sesuai paket
          updateGauges(json.download, json.upload);
        } else {
          console.error("API Error:", json.message);
        }
      } catch (e) {
        console.error("Fetch error:", e);
      }
    }

    // Jalankan fetch tiap 5 detik
    fetchData();
    setInterval(fetchData, 5000);
  </script>
</body>
</html>
