<?php
session_start();
require_once '../src/models/User.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📋 แบบบันทึกเสนองาน</title>
    <link rel="stylesheet" href="./css/navbars.css">
    <link rel="stylesheet" href="./css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <nav class="navbar">
    <div class="navbar-title"><a href="dashboard.php">แบบบันทึกเสนองาน</a></div>
    <ul>
      <li><a href="chart.php">แสดงข้อมูล</a></li>
      <li><a href="form.php">📋 แบบฟอร์ม</a></li>
      <li class="notification">
        <!-- Notification Icon with Badge -->
        <a href="#" id="notificationIcon">
          <i class="fas fa-bell"></i>
          <span class="badge" id="notificationCount">3</span>
        </a>
        <!-- Dropdown for notifications -->
        <div class="notification-dropdown" id="notificationDropdown">
          <ul>
            <li class="notification-item">
              <span>New approval request</span>
              <span class="notification-buttons">
                <button class="approve-btn">Approve</button>
                <button class="decline-btn">Decline</button>
              </span>
            </li>
            <!-- Additional notifications can be added here -->
          </ul>
        </div>
      </li>
      <li><a href="logout.php" onclick="logout()">🚪 ออกจากระบบ</a></li>
    </ul>
  </nav>

  <!-- Dialog Modal -->
    <div id="dialogModal" class="modal" style="display:none; position: fixed; z-index: 2000; left:0; top:0; width: 100%; height: 100%; overflow:auto; background: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background: #fff; margin: 15% auto; padding: 20px; border-radius: 5px; width: 80%; max-width: 400px; position: relative;">
        <span id="closeDialogModal" class="close-btn" style="position: absolute; right: 10px; top: 10px; font-size: 20px; cursor: pointer;">&times;</span>
        <p id="dialogMessage" style="font-size: 16px;"></p>
    </div>
    </div>

    <div class="split-container">
        <div class="left-side">
            <div class="search-row">
                <div class="search-group">
                    <label for="searchCriteria">🔍 ค้นหาตาม:</label>
                    <select id="searchCriteria" class="form-input">
                        <option value="task_code">เลขที่</option>
                        <option value="task">งาน</option>
                        <option value="subject">คดี</option>
                        <option value="agency">หน่วยงาน</option>
                        <option value="person_in_charge">ผู้รับผิดชอบ</option>
                        <option value="instructions">การสั่งการ</option>
                        <option value="remarks">หมายเหตุ</option>
                    </select>
                </div>

                <div class="search-group">
                    <label for="searchTerm">🔎 คำค้นหา:</label>
                    <input type="text" id="searchTerm" placeholder="กรอกข้อมูลค้นหา..." class="form-input" />
                </div>
            </div>

            <!-- Search and Clear Buttons -->
            <div class="search-group buttons-group">
                <button id="searchButton">ค้นหา</button>
                <button id="clearSearchButton">ล้างการค้นหา</button>
            </div>
            
            <!-- Record Count Section -->
            <div class="record-count-container">
                <span id="recordCount" class="record-count-label">📊 จำนวนบันทึกทั้งสิ้น: 0</span>
            </div>

            <!-- Tasks Table -->
            <table id="taskTable">
                <thead>
                    <tr>
                        <th>เลขที่</th> 
                        <th>🔖 เรื่อง</th>
                        <th>📚 งาน</th>
                        <th>🏢 หน่วยงานเจ้าของเรื่อง</th>
                        <th>👤 ผู้รับผิดชอบ</th>
                        <th class="mobile-hide">📅 วันที่เสนอเรื่อง</th>
                        <th class="mobile-hide">📅 วันที่รับเรื่องจากสำนักงานกฎหมาย</th>
                        <th class="mobile-hide">📅 วันที่รับเรื่องจากผู้รับผิดชอบ</th>
                        <th class="mobile-hide">📝 การสั่งการ</th>
                        <th class="mobile-hide">💬 หมายเหตุ</th>
                        <th class="mobile-hide">📋 ข้อมูลเพิ่มเติม</th>
                        <th class="mobile-hide">✏️ แก้ไข</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <!-- Record Count Selection -->
            <div class="recordCountSelect-class" style="margin-top: 20px;">
                <label for="recordCountSelect">📊 จำนวนรายการที่จะแสดง:</label>
                <select id="recordCountSelect">
                    <option value="10" selected>10</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="1000">ทั้งหมด</option>
                </select>
            </div>

            <!-- Pagination Container -->
            <div id="pagination-container" class="pagination" style="margin-top: 20px;"></div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close-modal close-btn" aria-label="Close Detail Modal">
                <!-- SVG Icon for Close Button -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M18.3 5.71a1 1 0 00-1.42 0L12 10.59 7.12 5.7a1 1 0 00-1.41 1.42L10.59 12l-4.88 4.88a1 1 0 101.41 1.42L12 13.41l4.88 4.88a1 1 0 001.42-1.42L13.41 12l4.88-4.88a1 1 0 000-1.41z"/>
                </svg>
            </span>
            <div id="modalDetails"></div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
        <div class="modal-content">
            <span class="close-modal close-btn" aria-label="Close Edit Modal">
                <!-- SVG Icon for Close Button -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M18.3 5.71a1 1 0 00-1.42 0L12 10.59 7.12 5.7a1 1 0 00-1.41 1.42L10.59 12l-4.88 4.88a1 1 0 101.41 1.42L12 13.41l4.88 4.88a1 1 0 001.42-1.42L13.41 12l4.88-4.88a1 1 0 000-1.41z"/>
                </svg>
            </span>
            <h2 id="editModalTitle">แก้ไขงาน</h2>
            <form id="editForm">
                <!-- Hidden Task ID -->
                <input type="hidden" id="editTaskId" name="task_id" />

                <!-- Task Title -->
                <div class="form-group">
                    <label for="editTaskTitle">📄 หัวข้อ</label>
                    <input type="text" id="editTaskTitle" name="task_title" required />
                </div>

                <!-- Subject -->
                <div class="form-group">
                    <label for="editSubject">📚 งาน</label>
                    <select id="editSubject" name="subject_id" required>
                        <!-- Options will be populated dynamically -->
                    </select>
                </div>

                <!-- Responsible Agency -->
                <div class="form-group">
                    <label for="editAgency">🏢 หน่วยงานเจ้าของเรื่อง</label>
                    <input type="text" id="editAgency" name="responsible_agency" required />
                </div>

                <!-- Person in Charge -->
                <div class="form-group">
                    <label for="editPerson">👤 ผู้รับผิดชอบ</label>
                    <select id="editPerson" name="person_in_charge_id" required>
                        <!-- Options will be populated dynamically -->
                    </select>
                </div>

                <!-- Instructions -->
                <div class="form-group">
                    <label for="editInstructions">📝 คำสั่ง</label>
                    <textarea id="editInstructions" name="instructions" required></textarea>
                </div>

                <!-- Remarks -->
                <div class="form-group">
                    <label for="editRemarks">💬 หมายเหตุ</label>
                    <textarea id="editRemarks" name="remarks"></textarea>
                </div>

                <!-- Date Received by Legal Office -->
                <div class="form-group">
                    <label for="editDateReceivedLegalOffice">📅 วันที่รับเรื่องจากสำนักงานกฎหมาย</label>
                    <input type="date" id="editDateReceivedLegalOffice" name="date_received_legal_office" required />
                </div>

                <!-- Date Received by Responsible Officer -->
                <div class="form-group">
                    <label for="editDateReceivedResponsibleOfficer">📅 วันที่รับเรื่องจากผู้รับผิดชอบ</label>
                    <input type="date" id="editDateReceivedResponsibleOfficer" name="date_received_responsible_officer" required />
                </div>

                <!-- Date of Proposal -->
                <div class="form-group">
                    <label for="editDateProposal">📅 วันที่เสนอเรื่อง</label>
                    <input type="date" id="editDateProposal" name="date_proposal" required />
                </div>

                <!-- Processing Duration Days -->
                <div class="form-group">
                    <label for="editProcessingDurationDays">⏰ ระยะเวลาดำเนินการ (วัน)</label>
                    <input type="number" id="editProcessingDurationDays" name="processing_duration_days" min="0" required />
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="submit-button">บันทึก</button>
                    <button type="button" class="close-modal cancel-button">ยกเลิก</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Logout functionality
        function logout() {
        localStorage.removeItem('user_id');
        localStorage.removeItem('username');
        window.location.href = '/working-log/public/login.php';
        }

        // Function to show dialog modal with a message (ข้อความภาษาไทย)
        function showDialogModal(message) {
        const dialogModal = document.getElementById('dialogModal');
        const dialogMessage = document.getElementById('dialogMessage');
        dialogMessage.innerText = message;
        dialogModal.style.display = 'block';
        }

        // Close modal when clicking the close button
        document.getElementById('closeDialogModal').addEventListener('click', function() {
        document.getElementById('dialogModal').style.display = 'none';
        });

        document.addEventListener('DOMContentLoaded', function(){
            fetch('../src/controllers/AuthController.php?action=get_pending_forgot_password')
            .then(response => response.json())
            .then(data => {
                const notificationCountElem = document.getElementById('notificationCount');
                notificationCountElem.innerText = data.count;
            })
            .catch(error => console.error('Error fetching pending requests:', error));
        });

        // Toggle dropdown and load pending requests on icon click
        document.getElementById('notificationIcon').addEventListener('click', function(e) {
            e.preventDefault();
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('show');

            // Fetch pending forgot password requests
            fetch('../src/controllers/AuthController.php?action=get_pending_forgot_password')
            .then(response => response.json())
            .then(data => {
                console.log(data)
                const ul = dropdown.querySelector('ul');
                ul.innerHTML = '';
                if (data.success && data.pending && data.pending.length > 0) {
                data.pending.forEach((item, index) => {
                    // console.log(item)
                    // console.log('Item email:', item.email, 'Timestamp:', item.timestamp);

                    const li = document.createElement('li');
                    li.innerHTML = `
                    <span>
                        ${item.email} <br>
                        <small>${item.timestamp}</small>
                    </span>
                    <span class="notification-buttons">
                        <button class="approve-btn" data-index="${index}">Approve</button>
                        <button class="decline-btn" data-index="${index}">Decline</button>
                    </span>
                    `;
                    ul.appendChild(li);
                });
                } else {
                const li = document.createElement('li');
                li.innerText = 'ไม่มีการแจ้งเตือน';
                ul.appendChild(li);
                }
                // Update badge count dynamically
                const notificationCountElem = document.getElementById('notificationCount');
                notificationCountElem.innerText = data.count || 0;
            })
            .catch(error => console.error('Error fetching pending requests:', error));
        });

        // Approve button click handler
        document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('approve-btn')) {
            e.stopPropagation();
            const index = e.target.getAttribute('data-index');
            fetch('../src/controllers/AuthController.php?action=approve_forgot_password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ index: index })
            })
            .then(response => response.json())
            .then(data => {
            // ใช้ข้อความภาษาไทยใน modal แทน alert()
            showDialogModal(data.message || "การอนุมัติเปลี่ยนรหัสผ่านสำเร็จ");
            // Refresh notifications
            document.getElementById('notificationIcon').click();
            })
            .catch(error => console.error('เกิดข้อผิดพลาดในการอนุมัติ:', error));
        }
        });

        // Decline button click handler
        document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('decline-btn')) {
            e.stopPropagation();
            const index = e.target.getAttribute('data-index');
            fetch('../src/controllers/AuthController.php?action=decline_forgot_password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ index: index })
            })
            .then(response => response.json())
            .then(data => {
            showDialogModal(data.message || "การปฏิเสธคำขอเปลี่ยนรหัสผ่านเสร็จสิ้น");
            // Refresh notifications
            document.getElementById('notificationIcon').click();
            })
            .catch(error => console.error('เกิดข้อผิดพลาดในการปฏิเสธ:', error));
        }
        });
    </script>
    <script src="main.js"></script>
</body>
</html>
