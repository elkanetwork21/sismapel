<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Gauge Customer</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
  <style>
    #gaugeContainer {
      width: 300px;
      margin: auto;
    }
  </style>
</head>
<body>
  <h2 style="text-align:center">Customer Bandwidth Usage</h2>
  <div id="gaugeContainer">
    <canvas id="gaugeChart"></canvas>
  </div>

  <script>
    const ctx = document.getElementById('gaugeChart').getContext('2d');

    const gaugeChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        datasets: [{
          data: [70, 30], // 70% terpakai, 30% sisa
          backgroundColor: ['#4caf50', '#e0e0e0'],
          borderWidth: 0
        }]
      },
      options: {
        rotation: -90,
        circumference: 180, // setengah lingkaran
        cutout: '70%',
        plugins: {
          legend: { display: false },
          datalabels: {
            display: true,
            formatter: (value, context) => {
              if (context.dataIndex === 0) {
                return value + '%';
              } else {
                return '';
              }
            },
            color: '#000',
            font: { size: 18, weight: 'bold' },
            anchor: 'center',
            align: 'center'
          }
        }
      },
      plugins: [ChartDataLabels]
    });
  </script>
</body>
</html>
