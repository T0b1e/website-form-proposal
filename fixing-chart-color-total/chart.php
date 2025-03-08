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
  <nav class="navbar">
    <div class="navbar-title"><a href="dashboard.php">แบบบันทึกเสนองาน</a></div>
    <ul>
      <li><a href="chart.php">แสดงข้อมูล</a></li>
      <li><a href="form.php">📋 แบบฟอร์ม</a></li>
      <li><a href="logout.php" onclick="logout()">🚪 ออกจากระบบ</a></li>
    </ul>
  </nav>

  <p class="fetch-status" id="fetchStatus">กำลังดึงข้อมูล...</p>

  <div class="main-content">
    <div class="top-row">
      <div class="recent-post" id="recentPostCard">
        <h2>Recent Post</h2>
        <p><strong>เลขที่:</strong> <span id="recentCode">1/7/2568</span></p>
        <p><strong>เรื่อง:</strong> <span id="recentSubject">คดีความ</span></p>
        <p><strong>วันที่เสนอ:</strong> <span id="recentDate">2025-01-25</span></p>
        <p><strong>ผู้รับผิดชอบ:</strong> <span id="recentPerson">นางสาวชลธิชา ปัญญาประดิษฐ์</span></p>
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

    <div class="card">
      <h3>ผู้รับผิดชอบ 5 อันดับสูงสุด</h3>
      <ul id="top5PersonList"></ul>
    </div>

    <div class="card">
      <h3>หน่วยงาน 5 อันดับสูงสุด</h3>
      <ul id="top5AgencyList"></ul>
    </div>

    <div class="card">
      <h3>หัวข้อเรื่อง (รวมทั้งหมด: <span id="totalSubjects">0</span>)</h3>
      <div class="chart-container">
        <canvas id="pieChartCanvas"></canvas>
      </div>
    </div>

    <div class="card">
      <h3>ปฏิทิน</h3>
      <div id="calendarContainer"></div>
    </div>

    <div class="card">
      <h3>ภาพรวมผู้รับผิดชอบ</h3>
      <div class="chart-container">
        <canvas id="personBarChart"></canvas>
      </div>
    </div>

    <div class="card">
      <h3>จำนวนงานตามช่วงเวลา</h3>
      <div class="chart-container">
        <canvas id="timeLineChart"></canvas>
      </div>
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


  <script>
  let pieChart, barChart, lineChart;

  const fallbackData = {
    top5Persons: [],
    top5Agencies: [],
    subjectDist: { labels: [], values: [] },
    personDist:  { labels: [], values: [] },
    dateDist:    { labels: [], values: [] },
    recentTask:  { code: '1/7/2568', subject: 'คดีความ' },
    totalSubjects: 0,
    totalUploads: 0,
    todayUploads: 0
  };

  document.addEventListener('DOMContentLoaded', () => {
    initCharts();
    initCalendar(); // Initialize FullCalendar once
    fetchRealData();
  });

  function updateUI(parsed) {
    // Update the recent task info
    document.getElementById('recentCode').textContent = parsed.recentTask.code;
    document.getElementById('recentSubject').textContent = parsed.recentTask.subject;

    // Update upload stats
    // You can update the labels to make it clear: total uploads vs. today's uploads.
    document.getElementById('totalUploads').textContent = parsed.totalUploads;
    document.getElementById('todayUploads').textContent = parsed.todayUploads;

    // For total subjects, if needed, update it as well.
    document.getElementById('totalSubjects').textContent = parsed.totalSubjects;

    // Update the top 5 lists
    const top5PersonList = document.getElementById('top5PersonList');
    top5PersonList.innerHTML = '';
    parsed.top5Persons.forEach((p, i) => {
        const li = document.createElement('li');
        li.textContent = `${i+1}. ${p.name} (${p.count} งาน)`;
        top5PersonList.appendChild(li);
    });

    const top5AgencyList = document.getElementById('top5AgencyList');
    top5AgencyList.innerHTML = '';
    parsed.top5Agencies.forEach((a, i) => {
        const li = document.createElement('li');
        li.textContent = `${i+1}. ${a.agency} (${a.count} งาน)`;
        top5AgencyList.appendChild(li);
    });

    // Update charts with parsed data
    pieChart.data.labels = parsed.subjectDist.labels;
    pieChart.data.datasets[0].data = parsed.subjectDist.values;
    pieChart.update();

    barChart.data.labels = parsed.personDist.labels;
    barChart.data.datasets[0].data = parsed.personDist.values;
    barChart.update();

    lineChart.data.labels = parsed.dateDist.labels;
    lineChart.data.datasets[0].data = parsed.dateDist.values;
    lineChart.update();
}


    function initCharts() {
      const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'bottom',
            labels: {
              boxWidth: 12,
              font: {
                size: 11
              }
            }
          }
        }
      };

      const ctxPie = document.getElementById('pieChartCanvas').getContext('2d');
      pieChart = new Chart(ctxPie, {
        type: 'pie',
        data: {
          labels: fallbackData.subjectDist.labels,
          datasets: [{
            data: fallbackData.subjectDist.values,
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

      const ctxBar = document.getElementById('personBarChart').getContext('2d');
      barChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
          labels: fallbackData.personDist.labels,
          datasets: [{
            label: 'จำนวนงาน',
            data: fallbackData.personDist.values,
            backgroundColor: 'rgba(75,192,192,0.7)',
            borderColor: 'rgba(75,192,192,1)',
            borderWidth: 1
          }]
        },
        options: {
          ...chartOptions,
          scales: {
            y: {
              beginAtZero: true,
              ticks: { font: { size: 10 } }
            },
            x: {
              ticks: { font: { size: 10 } }
            }
          }
        }
      });

      const ctxLine = document.getElementById('timeLineChart').getContext('2d');
      lineChart = new Chart(ctxLine, {
        type: 'line',
        data: {
          labels: fallbackData.dateDist.labels,
          datasets: [{
            label: 'จำนวนงาน',
            data: fallbackData.dateDist.values,
            fill: false,
            borderColor: 'rgba(153,102,255,1)',
            tension: 0.1
          }]
        },
        options: {
          ...chartOptions,
          scales: {
            y: {
              beginAtZero: true,
              ticks: { font: { size: 10 } }
            },
            x: {
              ticks: { font: { size: 10 } }
            }
          }
        }
      });
    }

    function initCalendar() {
      const calendarEl = document.getElementById('calendarContainer');
      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'th', // Thai locale
        events: [], // Start with no events, we will update it later
        eventClick: function(info) {
          console.log('Event clicked:', info.event); // Output to console
          showModal(info.event.extendedProps.task_code, info.event.title, info.event.extendedProps.subject, 
          info.event.extendedProps.responsible_agency, info.event.extendedProps.person_name, 
          info.event.extendedProps.instructions, info.event.extendedProps.remarks);
        }
      });

      calendar.render(); // Render the calendar
      window.calendar = calendar; // Store the calendar object to update it later
    }

    function updateCalendarWithEvents(parsed) {
      window.calendar.removeAllEvents(); // Clear existing events
      window.calendar.addEventSource(parsed.events); // Add new events
    }


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

    function fetchRealData() {
      const fetchStatusEl = document.getElementById('fetchStatus');
      fetchStatusEl.textContent = 'กำลังดึงข้อมูลจริง...';

      fetch('./utils/fetchTasks.php')
        .then(res => res.json())
        .then(data => {
          console.log(data);
          if (!data.success) throw new Error('Server returned success=false');
          const tasks = data.tasks || [];
          const parsed = parseTasks(tasks);
          // Override totalUploads with the full total from the server
          parsed.totalUploads = data.total;
          updateUI(parsed);
          const now = new Date();
          fetchStatusEl.textContent = 'ดึงข้อมูลล่าสุดเมื่อ: ' + now.toLocaleString('th-TH');
          updateCalendarWithEvents(parsed);
        })
        .catch(err => {
          console.error('Fetch error:', err);
          updateUI(fallbackData);
          const now = new Date();
          fetchStatusEl.textContent =
            '❗ ไม่สามารถดึงข้อมูลจริงได้ (ใช้ข้อมูลเดิมแทน): ' + now.toLocaleString('th-TH');
        });
    }


    function parseTasks(tasks) {
      const personCountMap = {};
      const agencyCountMap = {};
      const subjectCountMap = {};
      const dateCountMap = {};
      const calendarEvents = [];

      tasks.forEach(t => {
        const tc = t.task || 'ไม่ระบุ';
        const p = t.person_name || 'ไม่ระบุ';
        const ag = t.responsible_agency || 'ไม่ระบุ';
        const sj = t.subject || 'ไม่ระบุ';

        // Use only the date portion (YYYY-MM-DD) for comparisons
        const dp = t.date_proposal ? new Date(t.date_proposal).toISOString().split('T')[0] : null;
        const dl = t.date_received_legal_office ? new Date(t.date_received_legal_office).toISOString().split('T')[0] : null;
        const dr = t.date_received_responsible_officer ? new Date(t.date_received_responsible_officer).toISOString().split('T')[0] : null;
        
        personCountMap[p] = (personCountMap[p] || 0) + 1;
        agencyCountMap[ag] = (agencyCountMap[ag] || 0) + 1;
        subjectCountMap[sj] = (subjectCountMap[sj] || 0) + 1;
        
        // Now count using the date portion only
        if (dp) {
          dateCountMap[dp] = (dateCountMap[dp] || 0) + 1;
          calendarEvents.push({
            title: `${tc}`,
            start: dp,  // if your calendar accepts YYYY-MM-DD
            color: 'rgba(54, 162, 235, 0.7)',  // Blue for date_proposal
            extendedProps: { 
              details: `กิจกรรมที่เกี่ยวข้องกับ: ${sj}`, 
              task_code: t.task_code,
              subject: sj,
              responsible_agency: ag,
              person_name: p,
              instructions: t.instructions,
              remarks: t.remarks
            }
          });
        }
        
        if (dl) {
          // Optionally count or handle separately if needed
          calendarEvents.push({
            title: `${tc}`,
            start: dl,
            color: 'rgba(255, 99, 132, 0.7)',  // Red for date_received_legal_office
            extendedProps: { 
              details: `กิจกรรมที่เกี่ยวข้องกับ: ${sj}`,
              task_code: t.task_code,
              subject: sj,
              responsible_agency: ag,
              person_name: p,
              instructions: t.instructions,
              remarks: t.remarks
            }
          });
        }
        
        if (dr) {
          calendarEvents.push({
            title: `${tc}`,
            start: dr,
            color: 'rgba(75, 192, 192, 0.7)',  // Green for date_received_responsible_officer
            extendedProps: { 
              details: `กิจกรรมที่เกี่ยวข้องกับ: ${sj}`,
              task_code: t.task_code,
              subject: sj,
              responsible_agency: ag,
              person_name: p,
              instructions: t.instructions,
              remarks: t.remarks
            }
          });
        }
      });

      // Calculate totals and other distributions as before
      const top5Persons = Object.entries(personCountMap)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 5)
        .map(([name, count]) => ({ name, count }));

      const top5Agencies = Object.entries(agencyCountMap)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 5)
        .map(([agency, count]) => ({ agency, count }));

      const subjectLabels = Object.keys(subjectCountMap);
      const subjectValues = Object.values(subjectCountMap);

      const personLabels = Object.keys(personCountMap);
      const personValues = Object.values(personCountMap);

      const sortedDates = Object.keys(dateCountMap).sort();
      const dateValues = sortedDates.map(d => dateCountMap[d]);

      const latest = tasks.slice().sort((a, b) => parseInt(b.id, 10) - parseInt(a.id, 10))[0] || {};
      const recentTask = {
        code: latest.task_code || '-',
        subject: latest.subject || '-'
      };

      return {
        top5Persons,
        top5Agencies,
        subjectDist: { labels: subjectLabels, values: subjectValues },
        personDist: { labels: personLabels, values: personValues },
        dateDist: { labels: sortedDates, values: dateValues },
        recentTask,
        events: calendarEvents, // Include the events with different colors
        totalSubjects: subjectLabels.length,
        totalUploads: tasks.length,
        // Now todayUploads will work because the keys are in the YYYY-MM-DD format:
        todayUploads: dateCountMap[new Date().toISOString().split('T')[0]] || 0
      };
    }


  </script>
</body>
</html>

