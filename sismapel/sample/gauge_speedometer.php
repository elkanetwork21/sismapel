<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Gauge Speedometer</title>
  <style>
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background: #f4f4f4;
      font-family: Arial, sans-serif;
    }
    canvas {
      max-width: 450px;
      max-height: 450px;
    }
  </style>
</head>
<body>
  <canvas id="gauge" width="400" height="400"></canvas>

<script>
const canvas = document.getElementById("gauge");
const ctx = canvas.getContext("2d");

let value = 0;
const min = 0;
const max = 10;
const step = 1;

const startAngle = Math.PI * 0.66;  // 120°
const endAngle   = Math.PI * 2.34;  // 420°

function drawGauge(val) {
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  let cx = canvas.width / 2;
  let cy = canvas.height / 2;
  let radius = 160;

  // arc background abu-abu
  ctx.beginPath();
  ctx.arc(cx, cy, radius, startAngle, endAngle, false);
  ctx.lineWidth = 30;
  ctx.strokeStyle = "#ddd";
  ctx.stroke();

  // arc biru sesuai nilai
  let ratio = (val - min) / (max - min);
  let angleVal = startAngle + ratio * (endAngle - startAngle);
  ctx.beginPath();
  ctx.arc(cx, cy, radius, startAngle, angleVal, false);
  ctx.lineWidth = 30;
  ctx.strokeStyle = "#007bff";
  ctx.stroke();

  // angka skala di dalam lingkaran
  for (let v = min; v <= max; v += step) {
    let r = (v - min) / (max - min);
    let a = startAngle + r * (endAngle - startAngle);
    let tx = cx + Math.cos(a) * (radius * 0.8);
    let ty = cy + Math.sin(a) * (radius * 0.8);

    ctx.fillStyle = "#444";
    ctx.font = "16px Arial";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText(v.toString(), tx, ty);
  }

  // jarum
  let nx = cx + Math.cos(angleVal) * (radius * 0.7);
  let ny = cy + Math.sin(angleVal) * (radius * 0.7);

  ctx.beginPath();
  ctx.moveTo(cx, cy);
  ctx.lineTo(nx, ny);
  ctx.lineWidth = 6;
  ctx.strokeStyle = "red";
  ctx.stroke();

  // lingkaran kecil di pusat jarum
  ctx.beginPath();
  ctx.arc(cx, cy, 10, 0, Math.PI * 2);
  ctx.fillStyle = "red";
  ctx.fill();

  // nilai
  ctx.fillStyle = "black";
  ctx.font = "40px Arial";
  ctx.textAlign = "center";
  ctx.fillText(val.toFixed(1), cx, cy + radius * 0.9);

  // label Mbps di bawahnya
  ctx.fillStyle = "#666";
  ctx.font = "18px Arial";
  ctx.fillText("Mbps", cx, cy + radius * 1.1);
}


async function fetchMikrotik() {
  try {
    let res = await fetch("get_ppp_trafic.php");
    let data = await res.json();
    if (data.tx !== undefined) {
      return data.tx;
    }
  } catch (e) {
    console.error("Error fetch MikroTik:", e);
  }
  return 0;
}

async function animate() {
  let target = await fetchMikrotik();
  let current = value;
  let stepVal = (target - current) / 50;
  let i = 0;

  function frame() {
    if (i < 50) {
      current += stepVal;
      drawGauge(current);
      i++;
      requestAnimationFrame(frame);
    } else {
      value = target;
      setTimeout(animate, 1000);
    }
  }
  frame();
}

animate();
</script>
</body>
</html>
