<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📋 ระบบบันทึกปฏิบัติงาน</title>
    <link rel="stylesheet" href="./css/globals.css">
    <link rel="stylesheet" href="./css/navbars.css">
    <link rel="stylesheet" href="./css/forms.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-title"><a href="dashboard.php">📋 ระบบบันทึกปฏิบัติงาน</a></div>
        <ul>
            <?php if ($user_role === 'admin'): ?>
                <li><a href="admin.php">🔧 แผงควบคุมผู้ดูแล</a></li>
            <?php endif; ?>
            <li><a href="settings.php">⚙️ การตั้งค่าผู้ใช้</a></li>
            <li><a href="logout.php">🚪 ออกจากระบบ</a></li>
        </ul>
    </nav>

    <!-- Cards Section -->
    <div class="cards-container">
        <div class="card">
            <h3>จำนวนผู้ใช้ที่อัปโหลดวันนี้</h3>
            <p id="user-upload-count">กำลังโหลด...</p>
        </div>
        <div class="card">
            <h3>ข้อมูลเพิ่มเติม</h3>
            <p id="additional-data">กำลังโหลด...</p>
        </div>
    </div>

    <!-- Chart Section -->
    <div>
        <canvas id="titleChart" width="400" height="200"></canvas>
    </div>

    <script src="chartData.js"></script>
</body>
</html>
