<?php
if (!isset($_COOKIE['user_id'])) {
    header('Location: /form/public/login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>แดชบอร์ดงาน</title>
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./css/chart-new.css">
  <link rel="stylesheet" href="./css/navbars.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.5/index.global.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.5/index.global.min.js"></script>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar">
    <div class="navbar-title"><a href="dashboard.php">แบบบันทึกเสนองาน</a></div>
    <ul>
      <li><a href="chart.php">แสดงข้อมูล</a></li>
      <li><a href="form.php">📋 แบบฟอร์ม</a></li>
      <li><a href="logout.php" onclick="logout()">🚪 ออกจากระบบ</a></li>
    </ul>
  </nav>

  <!-- Status Message -->
  <p class="fetch-status" id="fetchStatus">กำลังดึงข้อมูล...</p>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Top Row: Recent Post and Upload Stats -->
    <div class="top-row">
      <div class="recent-post" id="recentPostCard">
        <h2>Recent Post</h2>
        <p><strong>เลขที่:</strong> <span id="recentCode">-</span></p>
        <p><strong>เรื่อง:</strong> <span id="recentSubject">-</span></p>
        <p><strong>วันที่เสนอ:</strong> <span id="recentDate">-</span></p>
        <p><strong>ผู้รับผิดชอบ:</strong> <span id="recentPerson">-</span></p>
      </div>
      
      <div class="stats-card">
        <h3>จำนวนการอัพโหลดทั้งหมด</h3>
        <div class="stats-number" id="totalUploads">0</div>
      </div>
      
      <div class="stats-card">
        <h3>จำนวนการอัพโหลดวันนี้</h3>
        <div class="stats-number" id="todayUploads">0</div>
      </div>
    </div>

    <!-- Top 5 Lists -->
    <div class="card">
      <h3>ผู้รับผิดชอบ 5 อันดับสูงสุด</h3>
      <ul id="top5PersonList"></ul>
    </div>

    <div class="card">
      <h3>หน่วยงาน 5 อันดับสูงสุด</h3>
      <ul id="top5AgencyList"></ul>
    </div>

    <!-- Pie Chart for Subject Distribution -->
    <div class="card">
      <h3>หัวข้อเรื่อง (รวมทั้งหมด: <span id="totalSubjects">0</span>)</h3>
      <div class="chart-container">
        <canvas id="pieChartCanvas"></canvas>
      </div>
    </div>

    <!-- Calendar -->
    <div class="card">
      <h3>ปฏิทิน</h3>
      <div id="calendarContainer"></div>
    </div>

    <!-- Bar Chart for Person Distribution -->
    <div class="card">
      <h3>ภาพรวมผู้รับผิดชอบ</h3>
      <div class="chart-container">
        <canvas id="personBarChart"></canvas>
      </div>
    </div>

    <!-- Pie Chart for Processing Duration -->
    <div class="card">
      <h3>ระยะเวลา (จำนวนวัน)</h3>
      <div class="chart-container">
        <canvas id="processingDurationChart"></canvas>
      </div>
    </div>

  <!-- Modal for Event Details -->
  <div id="modalOverlay">
    <div id="modalContent">
      <div class="modal-header">
        <h2>ข้อมูลกิจกรรม</h2>
      </div>
      <div class="modal-body">
        <div class="modal-section">
          <strong>รหัสงาน:</strong> <span id="modalTaskCode"></span>
        </div>
        <div class="modal-section">
          <strong>งาน:</strong> <span id="modalTitle"></span>
        </div>
        <div class="modal-section">
          <strong>หัวข้อ:</strong> <span id="modalSubject"></span>
        </div>
        <div class="modal-section">
          <strong>หน่วยงานที่รับผิดชอบ:</strong> <span id="modalResponsibleAgency"></span>
        </div>
        <div class="modal-section">
          <strong>ผู้รับผิดชอบ:</strong> <span id="modalPersonName"></span>
        </div>
        <div class="modal-section">
          <strong>คำแนะนำ:</strong> <span id="modalInstructions"></span>
        </div>
        <div class="modal-section">
          <strong>หมายเหตุ:</strong> <span id="modalRemarks"></span>
        </div>
      </div>
      <div class="modal-footer">
        <button class="close-btn" onclick="closeModal()">ปิด</button>
      </div>
    </div>
  </div>

  <!-- Main Scripts -->
  <script>
    // Global chart objects
    let pieChart, barChart, lineChart;

    // When DOM is loaded, initialize charts, calendar and fetch data
    document.addEventListener('DOMContentLoaded', () => {
      initCharts();
      initCalendar();
      fetchRealData();
    });

    // Update all dynamic UI elements with parsed data
    function updateUI(parsed) {
      // Recent Post Card
      document.getElementById('recentCode').textContent = parsed.recentTask.code;
      document.getElementById('recentSubject').textContent = parsed.recentTask.subject;
      document.getElementById('recentDate').textContent = parsed.recentTask.date;
      document.getElementById('recentPerson').textContent = parsed.recentTask.person;

      // Upload Stats
      document.getElementById('totalUploads').textContent = parsed.totalUploads;
      document.getElementById('todayUploads').textContent = parsed.todayUploads;
      document.getElementById('totalSubjects').textContent = parsed.totalSubjects;

      // Top 5 Person List
      const top5PersonList = document.getElementById('top5PersonList');
      top5PersonList.innerHTML = '';
      parsed.top5Persons.forEach((p, i) => {
        const li = document.createElement('li');
        li.textContent = `${i + 1}. ${p.name} (${p.count} งาน)`;
        top5PersonList.appendChild(li);
      });

      // Top 5 Agency List
      const top5AgencyList = document.getElementById('top5AgencyList');
      top5AgencyList.innerHTML = '';
      parsed.top5Agencies.forEach((a, i) => {
        const li = document.createElement('li');
        li.textContent = `${i + 1}. ${a.agency} (${a.count} งาน)`;
        top5AgencyList.appendChild(li);
      });

      // Update Charts
      pieChart.data.labels = parsed.subjectDist.labels;
      pieChart.data.datasets[0].data = parsed.subjectDist.values;
      pieChart.update();

      processingDurationChart.data.datasets[0].data = parsed.processingDurationDist.values;
      processingDurationChart.update();

      barChart.data.labels = parsed.personDist.labels;
      barChart.data.datasets[0].data = parsed.personDist.values;
      barChart.update();

      lineChart.data.labels = parsed.dateDist.labels;
      lineChart.data.datasets[0].data = parsed.dateDist.values;
      lineChart.update();
    }

    // Initialize Charts with common options
    function initCharts() {
      const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'bottom',
            labels: { boxWidth: 12, font: { size: 11 } }
          }
        }
      };

      // Pie Chart: Subject Distribution
      const ctxPie = document.getElementById('pieChartCanvas').getContext('2d');
      pieChart = new Chart(ctxPie, {
        type: 'pie',
        data: {
          labels: [],
          datasets: [{
            data: [],
            backgroundColor: [
              'rgba(255, 99, 132, 0.7)',
              'rgba(54, 162, 235, 0.7)',
              'rgba(255, 206, 86, 0.7)'
            ],
            borderWidth: 1
          }]
        },
        options: chartOptions
      });

      // Pie Chart: Processing Duration Distribution
      const ctxProcessing = document.getElementById('processingDurationChart').getContext('2d');
      processingDurationChart = new Chart(ctxProcessing, {
        type: 'pie',
        data: {
          labels: ['มากกว่า 30 วัน', '16-29 วัน', '7-15 วัน', 'น้อยกว่า 7 วัน'],
          datasets: [{
            data: [0, 0, 0, 0], // initial data, will be updated later
            backgroundColor: [
              'rgba(255, 99, 132, 0.7)',   // มากกว่า 30 วัน
              'rgba(54, 162, 235, 0.7)',   // 16-29 วัน
              'rgba(255, 206, 86, 0.7)',   // 7-15 วัน
              'rgba(75, 192, 192, 0.7)'    // 1-6 วัน
            ],
            borderWidth: 1
          }]
        },
        options: chartOptions
      });

      // Bar Chart: Person Distribution
      const ctxBar = document.getElementById('personBarChart').getContext('2d');
      barChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
          labels: [],
          datasets: [{
            label: 'จำนวนงาน',
            data: [],
            backgroundColor: 'rgba(75,192,192,0.7)',
            borderColor: 'rgba(75,192,192,1)',
            borderWidth: 1
          }]
        },
        options: {
          ...chartOptions,
          scales: {
            y: { beginAtZero: true, ticks: { font: { size: 10 } } },
            x: { ticks: { font: { size: 10 } } }
          }
        }
      });
    }

    // Initialize FullCalendar with dynamic events
    function initCalendar() {
      const calendarEl = document.getElementById('calendarContainer');
      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'th',
        events: [],
        eventClick(info) {
          showModal(
            info.event.extendedProps.task_code,
            info.event.title,
            info.event.extendedProps.subject,
            info.event.extendedProps.responsible_agency,
            info.event.extendedProps.person_name,
            info.event.extendedProps.instructions,
            info.event.extendedProps.remarks
          );
        }
      });
      calendar.render();
      window.calendar = calendar; // Expose calendar to update events later
    }

    // Update Calendar events from parsed data
    function updateCalendarWithEvents(parsed) {
      window.calendar.removeAllEvents();
      window.calendar.addEventSource(parsed.events);
    }

    // Modal Display for Event Details
    function showModal(taskCode, taskTitle, subject, responsibleAgency, personName, instructions, remarks) {
      document.getElementById('modalTaskCode').textContent = taskCode || 'ไม่พบเจอ';
      document.getElementById('modalTitle').textContent = taskTitle || 'ไม่พบเจอ';
      document.getElementById('modalSubject').textContent = subject || 'ไม่พบเจอ';
      document.getElementById('modalResponsibleAgency').textContent = responsibleAgency || 'ไม่พบเจอ';
      document.getElementById('modalPersonName').textContent = personName || 'ไม่พบเจอ';
      document.getElementById('modalInstructions').textContent = instructions || 'ไม่พบเจอ';
      document.getElementById('modalRemarks').textContent = remarks || 'ไม่พบเจอ';
      document.getElementById('modalOverlay').style.display = 'block';
    }

    function closeModal() {
      document.getElementById('modalOverlay').style.display = 'none';
    }

    // Fetch real data from the server and update the UI
    function fetchRealData() {
    const fetchStatusEl = document.getElementById('fetchStatus');
    fetchStatusEl.textContent = 'กำลังดึงข้อมูลจริง...';

    // Note the addition of &all=1 to get all data for dashboard stats
      fetch('./utils/fetchTasks.php?all=1')
          .then(res => res.json())
          .then(data => {
              if (!data.success) throw new Error('Server returned success=false');
              const tasks = data.tasks || [];
              const parsed = parseTasks(tasks);
              parsed.totalUploads = data.total;
              updateUI(parsed);
              updateCalendarWithEvents(parsed);
              const now = new Date();
              fetchStatusEl.textContent = 'ดึงข้อมูลล่าสุดเมื่อ: ' + now.toLocaleString('th-TH');
          })
          .catch(err => {
              console.error('Fetch error:', err);
              fetchStatusEl.textContent = '❗ ไม่สามารถดึงข้อมูลจริงได้: ' + new Date().toLocaleString('th-TH');
          });
  }

    // Parse tasks and calculate distributions and top lists
    function parseTasks(tasks) {
      const personCountMap = {};
      const agencyCountMap = {};
      const subjectCountMap = {};
      const dateCountMap = {};
      const calendarEvents = [];
      
      let durationMoreThan30 = 0;
      let duration16to29 = 0;
      let duration7to15 = 0;
      let duration1to6 = 0;

      tasks.forEach(t => {
        // Use fallback values if properties are missing
        const taskName = t.task || 'ไม่ระบุ';
        const personName = t.person_name || 'ไม่ระบุ';
        const agency = t.responsible_agency || 'ไม่ระบุ';
        const subject = t.subject || 'ไม่ระบุ';
        const dateProposal = t.date_proposal ? new Date(t.date_proposal).toISOString().split('T')[0] : null;
        const dateLegal = t.date_received_legal_office ? new Date(t.date_received_legal_office).toISOString().split('T')[0] : null;
        const dateOfficer = t.date_received_responsible_officer ? new Date(t.date_received_responsible_officer).toISOString().split('T')[0] : null;

         // Calculate processing duration distribution
        const days = parseInt(t.processing_duration_days, 10);
        if (!isNaN(days)) {
          if (days > 30) {
            durationMoreThan30++;
          } else if (days >= 16 && days <= 29) {
            duration16to29++;
          } else if (days >= 7 && days <= 15) {
            duration7to15++;
          } else if (days >= 1 && days <= 6) {
            duration1to6++;
          }
        }
        
        // Count by person, agency, and subject
        personCountMap[personName] = (personCountMap[personName] || 0) + 1;
        agencyCountMap[agency] = (agencyCountMap[agency] || 0) + 1;
        subjectCountMap[subject] = (subjectCountMap[subject] || 0) + 1;

        // Add events to the calendar using available dates
        if (dateProposal) {
          dateCountMap[dateProposal] = (dateCountMap[dateProposal] || 0) + 1;
          calendarEvents.push({
            title: taskName,
            start: dateProposal,
            color: 'rgba(54, 162, 235, 0.7)',
            extendedProps: { 
              task_code: t.task_code,
              subject: subject,
              responsible_agency: agency,
              person_name: personName,
              instructions: t.instructions,
              remarks: t.remarks
            }
          });
        }
        if (dateLegal) {
          calendarEvents.push({
            title: taskName,
            start: dateLegal,
            color: 'rgba(255, 99, 132, 0.7)',
            extendedProps: { 
              task_code: t.task_code,
              subject: subject,
              responsible_agency: agency,
              person_name: personName,
              instructions: t.instructions,
              remarks: t.remarks
            }
          });
        }
        if (dateOfficer) {
          calendarEvents.push({
            title: taskName,
            start: dateOfficer,
            color: 'rgba(75, 192, 192, 0.7)',
            extendedProps: { 
              task_code: t.task_code,
              subject: subject,
              responsible_agency: agency,
              person_name: personName,
              instructions: t.instructions,
              remarks: t.remarks
            }
          });
        }
      });

      // Prepare Top 5 lists (sorted by count in descending order)
      const top5Persons = Object.entries(personCountMap)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 5)
        .map(([name, count]) => ({ name, count }));

      const top5Agencies = Object.entries(agencyCountMap)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 5)
        .map(([agency, count]) => ({ agency, count }));

      // Prepare data for charts
      const subjectLabels = Object.keys(subjectCountMap);
      const subjectValues = Object.values(subjectCountMap);
      const personLabels = Object.keys(personCountMap);
      const personValues = Object.values(personCountMap);
      const sortedDates = Object.keys(dateCountMap).sort();
      const dateValues = sortedDates.map(d => dateCountMap[d]);

      // Get the most recent task (by id)
      const latest = tasks.slice().sort((a, b) => parseInt(b.id, 10) - parseInt(a.id, 10))[0] || {};
      const recentTask = {
        code: latest.task_code || '-',
        subject: latest.subject || '-',
        date: latest.date_proposal ? new Date(latest.date_proposal).toISOString().split('T')[0] : '-',
        person: latest.person_name || '-'
      };

      return {
        top5Persons,
        top5Agencies,
        subjectDist: { labels: subjectLabels, values: subjectValues },
        personDist: { labels: personLabels, values: personValues },
        dateDist: { labels: sortedDates, values: dateValues },
        processingDurationDist: {
          labels: ['มากกว่า 30 วัน', 'ใช้เวลา 16-29 วัน', 'ใช้เวลา 7-15 วัน', 'ใช้เวลา 1-6 วัน'],
          values: [durationMoreThan30, duration16to29, duration7to15, duration1to6]
        },
        recentTask,
        events: calendarEvents,
        totalSubjects: subjectLabels.length,
        totalUploads: tasks.length,
        todayUploads: dateCountMap[new Date().toISOString().split('T')[0]] || 0
      };
    }
  </script>
</body>
</html>
