<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Gauge Traffic</title>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body>
  <h2 style="text-align:center">Traffic Gauge - ether1</h2>
  <div id="gaugeChart" style="max-width:400px;margin:auto;"></div>

  <script>
    var options = {
      chart: {
        type: 'radialBar',
        offsetY: -10
      },
      plotOptions: {
        radialBar: {
          startAngle: -135,
          endAngle: 135,
          hollow: { size: "60%" },
          dataLabels: {
            name: { show: true, offsetY: 60 },
            value: {
              fontSize: "22px",
              formatter: val => val + " Mbps"
            }
          }
        }
      },
      fill: {
        type: "gradient",
        gradient: {
          shade: "dark",
          gradientToColors: ["#00ff00"],
          stops: [0, 100]
        }
      },
      stroke: { lineCap: "round" },
      labels: ["Speed"],
      series: [0] // awalnya 0
    };

    var chart = new ApexCharts(document.querySelector("#gaugeChart"), options);
    chart.render();

    function fetchTraffic() {
      fetch("get_traffic.php?iface=ether1")
        .then(res => res.json())
        .then(data => {
          if (!data.error) {
            chart.updateSeries([data.speed]);
          }
        })
        .catch(err => console.error(err));
    }

    // update tiap 3 detik
    setInterval(fetchTraffic, 3000);
    fetchTraffic();
  </script>
</body>
</html>



