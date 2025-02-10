<!-- form.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📝 แบบฟอร์มบันทึก</title>
    <link rel="stylesheet" href="./css/navbars.css">
    <link rel="stylesheet" href="./css/form.css">
    <style>
    </style>
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

<div class="form-container">
    <h2>📝 แบบฟอร์มบันทึก</h2>
    <form id="uploadForm" enctype="multipart/form-data">
        <!-- Task Title -->
        <div class="form-group">
            <label for="title">📄 หัวข้อ</label>
            <input type="text" id="title" name="title" class="form-input" required />
        </div>

        <!-- Subject (Foreign Key) -->
        <div class="form-group">
            <label for="subject_id">📚 หัวข้อเรื่อง</label>
            <select id="subject_id" name="subject_id" class="form-input" required>
                <option value="" disabled selected>-- เลือก --</option>
                <!-- Options loaded via fetchLookups.js -->
            </select>
        </div>

        <!-- Responsible Agency as Text Input -->
        <div class="form-group">
            <label for="responsible_agency">🏢 หน่วยงาน</label> 
            <input type="text" id="responsible_agency" name="responsible_agency" class="form-input" required />
        </div>

        <!-- Person in Charge (Foreign Key) -->
        <div class="form-group">
            <label for="person_in_charge_id">👤 ผู้รับผิดชอบ</label>
            <select id="person_in_charge_id" name="person_in_charge_id" class="form-input" required>
                <option value="" disabled selected>-- เลือก --</option>
                <!-- Options loaded via fetchLookups.js -->
            </select>
        </div>

        <!-- Date of Proposal -->
        <div class="form-group">
            <label for="date_proposal">📅 วันที่เสนอเรื่อง</label>
            <input type="date" id="date_proposal" name="date_proposal" class="form-input" required />
        </div>

        <!-- Date Received by Legal Office -->
        <div class="form-group">
            <label for="date_received_legal_office">📅 วันที่รับเรื่องจากสำนักงานกฎหมาย</label>
            <input type="date" id="date_received_legal_office" name="date_received_legal_office" class="form-input" required />
        </div>

        <!-- Date Received by Responsible Officer -->
        <div class="form-group">
            <label for="date_received_responsible_officer">📅 วันที่รับเรื่องจากผู้รับผิดชอบ</label>
            <input type="date" id="date_received_responsible_officer" name="date_received_responsible_officer" class="form-input" required />
        </div>

        <!-- Instructions -->
        <div class="form-group">
            <label for="instructions">📝 การสั่งการ</label>
            <textarea id="instructions" name="instructions" class="form-input" required></textarea>
        </div>

        <!-- Processing Duration (Days) -->
        <div class="form-group">
            <label for="processing_duration_days">⏰ ระยะเวลาดำเนินการ (วัน)</label>
            <input type="number" id="processing_duration_days" name="processing_duration_days" class="form-input" min="0" required />
        </div>

        <!-- Remarks -->
        <div class="form-group">
            <label for="remarks">💬 หมายเหตุ</label>
            <textarea id="remarks" name="remarks" class="form-input"></textarea>
        </div>

        <!-- Additional Comments -->
        <div class="form-group">
            <label for="body">💬 ความคิดเห็นเพิ่มเติม</label>
            <textarea id="body" name="body" class="form-input"></textarea>
        </div>
        <input type="hidden" id="task_code" name="task_code" value="" />
        <div class="form-actions">
            <button type="submit" class="submit-button">📤 ส่งข้อมูล</button>
            <button type="button" class="cancel-button">ยกเลิก</button>
        </div>
    </form>

    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>สำเร็จ!</h2>
            <p>งานถูกเพิ่มเรียบร้อยแล้ว.</p>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>ข้อผิดพลาด!</h2>
            <p>เกิดข้อผิดพลาดในการเพิ่มงาน. กรุณาลองอีกครั้ง.</p>
        </div>
    </div>

    <!-- Additional Message Containers (if needed) -->
    <div id="error-message" class="error-message"></div>
    <div id="fileList"></div>
</div>


<script>
    // Handle logout
    function logout() {
        localStorage.removeItem('user_id');
        localStorage.removeItem('username');
        window.location.href = 'login.php';
    }

    // 1) Load Lookup Data (Subjects, Persons in Charge) and Cache Them
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const response = await fetch('./utils/fetchLookups.php');
            const data = await response.json();
            
            console.log('Lookup Data:', data);
            if (!data.success) {
                console.error('Failed to fetch lookups:', data.message);
                return;
            }

            // Cache subjects and personsInCharge in localStorage
            localStorage.setItem('subjects', JSON.stringify(data.subjects));
            localStorage.setItem('personsInCharge', JSON.stringify(data.personsInCharge));

            // Populate Subjects Dropdown
            const subjectSelect = document.getElementById('subject_id');
            data.subjects.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject.id;
                option.textContent = subject.subject;
                subjectSelect.appendChild(option);
            });

            // Populate Persons in Charge Dropdown
            const personInChargeSelect = document.getElementById('person_in_charge_id');
            data.personsInCharge.forEach(person => {
                const option = document.createElement('option');
                option.value = person.id;
                option.textContent = person.person_name;
                personInChargeSelect.appendChild(option);
            });
            
        } catch (error) {
            console.error('Error loading lookups:', error);
        }

        // **Hide Modals on Page Load**
        const errorModal = document.getElementById('errorModal');
        const successModal = document.getElementById('successModal');

        if (errorModal) {
            errorModal.style.display = 'none';
        }

        if (successModal) {
            successModal.style.display = 'none';
        }
    });

    // Define variables in a scope accessible to both event handlers
    let newIndex = 0;
    let selectedId = '';
    const thaiBuddhistYear = new Date().getFullYear() + 543;

    // Subject change event listener
    document.getElementById('subject_id').addEventListener('change', async (event) => {
        selectedId = event.target.value;

        if (!selectedId) {
            // If no subject is selected, reset the task_code
            resetTaskCode();
            return;
        }

        try {
            // Fetch the count of tasks for the selected subject
            const countResponse = await fetch(`./utils/fetchSubjectTaskCount.php?subject_id=${selectedId}`);
            const countData = await countResponse.json();

            if (!countData.success) {
                console.error('Failed to fetch task count:', countData.message);
                showErrorMessage('ไม่สามารถดึงข้อมูลจำนวนงานสำหรับหัวข้อนี้ได้');
                resetTaskCode();
                return;
            }

            // Set newIndex as count + 1
            newIndex = parseInt(countData.total) + 1;

            // Create label text
            const labelText = `เลขที่ ${newIndex}/${selectedId}/${thaiBuddhistYear}`;

            // Remove existing custom label if any
            const existingLabel = document.getElementById('subject-selection-label');
            if (existingLabel) {
                existingLabel.remove();
            }

            // Create and append new label
            const label = document.createElement('div');
            label.id = 'subject-selection-label';
            label.classList.add('subject-selection-label'); // Use CSS class for styling
            label.textContent = labelText;

            // Find the form-container and prepend the label
            const formContainer = document.querySelector('.form-container');
            formContainer.prepend(label); // Inserts the label as the first child

            // Set the value of the hidden task_code input
            document.getElementById('task_code').value = `${newIndex}/${selectedId}/${thaiBuddhistYear}`;

        } catch (error) {
            console.error('Error fetching task count:', error);
            showErrorMessage('เกิดข้อผิดพลาดในการดึงข้อมูลจำนวนงาน');
            resetTaskCode();
        }
    });

    // Function to reset the task_code label and hidden input
    function resetTaskCode() {
        // Remove existing label if any
        const existingLabel = document.getElementById('subject-selection-label');
        if (existingLabel) {
            existingLabel.remove();
        }

        // Reset the hidden task_code input
        document.getElementById('task_code').value = '';

        // Reset newIndex
        newIndex = 0;
    }

    // 2) Handle form submission (AJAX to uploadTask.php)
    const uploadForm = document.getElementById('uploadForm');
    const formMessage = document.getElementById('formMessage');

    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(uploadForm);

        try {
            const response = await fetch('./utils/uploadTask.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                // Show the success modal
                showModal('successModal');

                // Optionally, reset the form
                uploadForm.reset();

                // Reset task_code label and hidden input
                resetTaskCode();

                // Re-trigger subject change to update newIndex if a subject is selected
                const subjectSelect = document.getElementById('subject_id');
                if (subjectSelect.value) {
                    const event = new Event('change');
                    subjectSelect.dispatchEvent(event);
                }

            } else {
                // Show the error modal
                showModal('errorModal');
            }
        } catch (error) {
            console.error('Error uploading task:', error);
            showModal('errorModal');
        }

        // Clear any existing messages
        formMessage.innerHTML = '';
        formMessage.classList.remove('success', 'error');
        formMessage.style.display = 'none';

    });

    // Function to show a modal by ID
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.classList.add('no-scroll'); // Prevent background scroll
        }
    }

    // Function to close a modal by ID
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('no-scroll'); // Restore background scroll
        }
    }

    // Close modals when the close button is clicked
    document.querySelectorAll('.close-btn').forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });

    // Close modals when clicking outside the modal content
    window.addEventListener('click', (event) => {
        document.querySelectorAll('.modal').forEach(modal => {
            if (event.target === modal) {
                closeModal(modal.id);
            }
        });
    });

    // Function to display error messages below the submit button
    function showErrorMessage(message) {
        formMessage.textContent = message;
        formMessage.classList.add('error');
        formMessage.style.display = 'block';
    }

    // Handle Cancel Button Click (Optional)
    const cancelButton = document.querySelector('.cancel-button');
    cancelButton.addEventListener('click', () => {
        uploadForm.reset();
        resetTaskCode();
        // Clear any messages or modals
        formMessage.innerHTML = '';
        formMessage.classList.remove('success', 'error');
        formMessage.style.display = 'none';
    });
</script>


</body>
</html>
