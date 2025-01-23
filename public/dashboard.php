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
            <li><a href="form.php">📋 แบบฟอร์ม</a></li>
            <li><a href="logout.php" onclick="logout()">🚪 ออกจากระบบ</a></li>
        </ul>
    </nav>

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
                        <th>📝 การสั่งการ</th>
                        <th>💬 หมายเหตุ</th>
                        <th>📋 ข้อมูลเพิ่มเติม</th>
                        <th>✏️ แก้ไข</th>
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
            <span id="closeDetailModal" class="close">&times;</span>
            <div id="modalDetails"></div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
        <div class="modal-content">
            <span class="close-btn" aria-label="Close Modal">
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
                    <button type="button" class="cancel-button">ยกเลิก</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Handle user logout
        function logout() {
            localStorage.removeItem('user_id');
            localStorage.removeItem('username');
            window.location.href = 'login.php';
        }
    </script>
    <script src="main.js"></script>
</body>
</html>
