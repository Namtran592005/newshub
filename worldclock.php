<!DOCTYPE html>
<html lang="vi" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NewsHub - Đồng hồ thế giới</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="page-clock">
<div class="wc-container">
    <div class="wc-header">
        <div class="wc-title"><i class="fa-regular fa-clock"></i> Đồng hồ thế giới</div>
        <a href="index.php" class="wc-back"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
    </div>
    <div class="wc-grid" id="wc-grid"></div>
</div>
<script>
const ZONES = [
    {city:'Hà Nội',tz:'Asia/Ho_Chi_Minh',flag:'🇻🇳',offset:7},
    {city:'Tokyo',tz:'Asia/Tokyo',flag:'🇯🇵',offset:9},
    {city:'Seoul',tz:'Asia/Seoul',flag:'🇰🇷',offset:9},
    {city:'Bắc Kinh',tz:'Asia/Shanghai',flag:'🇨🇳',offset:8},
    {city:'Singapore',tz:'Asia/Singapore',flag:'🇸🇬',offset:8},
    {city:'Dubai',tz:'Asia/Dubai',flag:'🇦🇪',offset:4},
    {city:'Moscow',tz:'Europe/Moscow',flag:'🇷🇺',offset:3},
    {city:'Berlin',tz:'Europe/Berlin',flag:'🇩🇪',offset:2},
    {city:'Paris',tz:'Europe/Paris',flag:'🇫🇷',offset:2},
    {city:'London',tz:'Europe/London',flag:'🇬🇧',offset:1},
    {city:'New York',tz:'America/New_York',flag:'🇺🇸',offset:-4},
    {city:'Chicago',tz:'America/Chicago',flag:'🇺🇸',offset:-5},
    {city:'Denver',tz:'America/Denver',flag:'🇺🇸',offset:-6},
    {city:'Los Angeles',tz:'America/Los_Angeles',flag:'🇺🇸',offset:-7},
    {city:'Sydney',tz:'Australia/Sydney',flag:'🇦🇺',offset:10},
    {city:'Auckland',tz:'Pacific/Auckland',flag:'🇳🇿',offset:12},
    {city:'São Paulo',tz:'America/Sao_Paulo',flag:'🇧🇷',offset:-3},
    {city:'Bangkok',tz:'Asia/Bangkok',flag:'🇹🇭',offset:7},
    {city:'Mumbai',tz:'Asia/Kolkata',flag:'🇮🇳',offset:5.5},
    {city:'Jakarta',tz:'Asia/Jakarta',flag:'🇮🇩',offset:7},
];

function formatNum(n){return n.toString().padStart(2,'0')}

function updateClocks(){
    const grid=document.getElementById('wc-grid');
    grid.innerHTML=ZONES.map(z=>{
        const now=new Date();
        const utc=now.getTime()+now.getTimezoneOffset()*60000;
        const local=new Date(utc+z.offset*3600000);
        const h=local.getHours(), m=local.getMinutes(), s=local.getSeconds();
        const isDay=h>=6&&h<18;
        return `<div class="wc-card">
            <div class="wc-flag">${z.flag}</div>
            <div class="wc-city">${z.city}</div>
            <div class="wc-time">${formatNum(h)}:${formatNum(m)}:${formatNum(s)}</div>
            <div class="wc-date">${local.toLocaleDateString('vi-VN')}</div>
            <div class="wc-offset"><span class="wc-indicator ${isDay?'day':'night'}"></span> ${isDay?'Ban ngày':'Ban đêm'} • UTC${z.offset>=0?'+':''}${z.offset}</div>
        </div>`;
    }).join('');
}
updateClocks();
setInterval(updateClocks,1000);
</script>
</body>
</html>
