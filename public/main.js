document.addEventListener('DOMContentLoaded', () => {
    // Handle form submission for editing
    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(editForm);
            const taskId = document.getElementById('editTaskId').value;
            
            // Optional: Add client-side validation here if needed

            // Disable the submit button to prevent multiple submissions
            const submitButton = editForm.querySelector('.submit-button');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'กำลังส่ง...'; // Change button text to indicate loading
            }

            fetch('./utils/editTask.php', { // Ensure the correct path based on main.js location
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'บันทึก';
                }
                if (data.success) {
                    alert('แก้ไขข้อมูลสำเร็จ!'); 
                    closeModal('editModal'); // Close modal on success
                    fetchTaskData();  // Refresh table data
                } else {
                    alert('เกิดข้อผิดพลาดในการแก้ไข: ' + data.message);
                }
            })
            .catch(error => {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'บันทึก';
                }
                console.error('Error editing task:', error);
                alert('เกิดข้อผิดพลาดในการแก้ไขข้อมูล.');
            });
        });
    }

    // Event Listener for Close and Cancel Buttons
    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            if (modal) {
                closeModal(modal.id);
                if (modal.id === 'editModal') {
                    const editForm = document.getElementById('editForm');
                    if (editForm) editForm.reset();
                }
                // Similarly, reset other forms if any
            }
        });
    });

    // Close Modals when clicking outside the modal content
    window.addEventListener('click', function(event) {
        document.querySelectorAll('.modal').forEach(modal => {
            if (event.target === modal) {
                closeModal(modal.id);
                if (modal.id === 'editModal') {
                    const editForm = document.getElementById('editForm');
                    if (editForm) editForm.reset();
                }
                // Similarly, reset other forms if any
            }
        });
    });

    // Clear search inputs and reset criteria
    const clearSearchButton = document.getElementById('clearSearchButton');
    if (clearSearchButton) {
        clearSearchButton.addEventListener('click', function() {
            document.getElementById('searchTerm').value = '';
            document.getElementById('searchCriteria').value = 'task_code'; // Default criteria
            fetchTaskData(); // Reload data without search filters
        });
    }

    // Fetch and populate dropdown options dynamically
    function fetchOptions() {
        fetch('./utils/fetchLookups.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateDropdown('subject_id', data.subjects, 'subject');
                    populateDropdown('person_in_charge_id', data.personsInCharge, 'person_name');
                    populateDropdown('editSubject', data.subjects, 'subject');
                    populateDropdown('editPerson', data.personsInCharge, 'person_name');
                } else {
                    console.error('Failed to fetch options', data.message);
                }
            })
            .catch(error => console.error('Error fetching options:', error));
    }

    // Populate dropdown options with a specified display key
    function populateDropdown(dropdownId, options, displayKey) {
        const dropdown = document.getElementById(dropdownId);
        if (!dropdown) {
            console.error(`Dropdown with ID "${dropdownId}" not found.`);
            return; // Ensure the dropdown exists
        }
        dropdown.innerHTML = ''; // Clear existing options

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = '-- เลือก --';
        dropdown.appendChild(defaultOption);

        options.forEach(option => {
            const optionElement = document.createElement('option');
            optionElement.value = option.id;
            optionElement.textContent = option[displayKey] || '--';
            dropdown.appendChild(optionElement);
        });
    }

    // Initialize on page load
    window.onload = function() {
        fetchTaskData();  // Load initial data
        fetchOptions();   // Populate dropdowns
        // Ensure modals are hidden
        closeModal('editModal');
        closeModal('detailModal');
    };

    // Fetch task data with optional pagination and search parameters
    function fetchTaskData(searchCriteria = '', searchTerm = '', page = 1, limit = 10) {
        const taskTableBody = document.querySelector('#taskTable tbody');
        const recordCountLabel = document.getElementById('recordCount');
    
        // Get the selected limit from the dropdown
        const selectedLimit = document.getElementById('recordCountSelect').value;
        limit = (selectedLimit === 'ทั้งหมด') ? 1000 : parseInt(selectedLimit); // Set a high limit for 'ทั้งหมด'
    
        // Calculate the starting index for the current page
        const startingIndex = (page - 1) * limit;
    
        taskTableBody.innerHTML = '';  // Clear existing rows
        const loadingMessage = document.createElement('tr');
        loadingMessage.innerHTML = `<td colspan="9" style="text-align:center;">กำลังโหลด...</td>`;
        taskTableBody.appendChild(loadingMessage);
    
        // Build the fetch URL with query parameters
        let url = `./utils/fetchTasks.php?limit=${limit}&page=${page}`;
        if (searchCriteria && searchTerm) {
            url += `&searchCriteria=${encodeURIComponent(searchCriteria)}&searchTerm=${encodeURIComponent(searchTerm)}`;
        }
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                // console.log("Fetched Data:", data);
                taskTableBody.innerHTML = '';  // Clear loading message
                if (data.success) {
                    populateTable(data.tasks);
                    // Update the record count display to show both total and current page count
                    recordCountLabel.textContent = `📊 จำนวนบันทึกทั้งสิ้น: ${data.total} (แสดง: ${data.tasks.length} รายการ)`;
                    setupPagination(data.total, page, limit);
                } else {
                    alert('ข้อผิดพลาด: ' + data.message);
                    recordCountLabel.textContent = '📊 จำนวนบันทึกทั้งสิ้น: 0';
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                taskTableBody.innerHTML = '';
                const errorRow = document.createElement('tr');
                errorRow.innerHTML = `<td colspan="9" style="text-align:center; color: red;">เกิดข้อผิดพลาดในการโหลดข้อมูล</td>`;
                taskTableBody.appendChild(errorRow);
                recordCountLabel.textContent = '📊 จำนวนบันทึกทั้งสิ้น: 0';
            });
    }
    

    // Populate the task table with fetched data
    function populateTable(data) {
        const taskTableBody = document.querySelector('#taskTable tbody');
        taskTableBody.innerHTML = '';  // Clear the table

        data.forEach((task) => {
            taskTableBody.appendChild(createTaskRow(task));
        });
    }

    // Create a table row for each task record
    function createTaskRow(task) {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>${task.task_code || 'ไม่พบเจอ'}</td> <!-- Display Task Code -->
            <td>${task.task || 'ไม่พบเจอ'}</td>
            <td>${task.subject || 'ไม่พบเจอ'}</td>
            <td>${task.responsible_agency || 'ไม่พบเจอ'}</td>
            <td>${task.person_name || 'ไม่พบเจอ'}</td>
            <td>
                <button class="detail-btn" data-id="${task.id}" title="รายละเอียด">
                    <i class="fas fa-info-circle"></i>
                </button>
            </td>
            <td>
                <button class="edit-btn" data-id="${task.id}" title="แก้ไข">
                    <i class="fas fa-edit"></i>
                </button>
            </td>
        `;
        return row;
    }

    // Handle clicks for dynamic elements in the task table
    const taskTable = document.querySelector('#taskTable');
    if (taskTable) {
        taskTable.addEventListener('click', function (event) {
            const target = event.target.closest('button');
            if (!target || !target.dataset.id) return;
            const taskId = target.dataset.id;

            if (target.classList.contains('detail-btn')) {
                fetchTaskAndDisplayModal(taskId, 'detail');
            } else if (target.classList.contains('edit-btn')) {
                fetchTaskAndDisplayModal(taskId, 'edit');
            } else if (target.classList.contains('delete-btn')) {
                handleDelete(taskId);
            }
        });
    }

    // Fetch task details and display in the appropriate modal
    function fetchTaskAndDisplayModal(taskId, mode) {
        // Use fetchTaskID.php instead of fetchTask.php
        fetch(`./utils/fetchTaskID.php?id=${taskId}`)
            .then(response => response.json())
            .then(data => {
                // console.log(data);
                if (data.success) {
                    if (mode === 'detail') {
                        displayDetailsModal(data.task);
                    } else if (mode === 'edit') {
                        populateEditModal(data.task);
                        openModal('editModal');
                    }
                } else {
                    alert('ข้อผิดพลาด: ไม่สามารถดึงข้อมูลได้');
                }
            })
            .catch(error => console.error('Error fetching task details:', error));
    }

    // Display the task details in a modal
    function displayDetailsModal(task) {
        const modalDetails = document.getElementById('modalDetails');
        if (!modalDetails) {
            console.error('Element with ID "modalDetails" not found in the DOM.');
            return;
        }

        modalDetails.innerHTML = `
            <p><strong>📄 เลขที่:</strong> ${task.task_code || 'ไม่พบเจอ'}</p>
            <p><strong>📋 เรื่อง:</strong> ${task.task || 'ไม่พบเจอ'}</p>
            <p><strong>📚 งาน:</strong> ${task.subject || 'ไม่พบเจอ'}</p>
            <p><strong>🏢 หน่วยงานเจ้าของเรื่อง:</strong> ${task.responsible_agency || 'ไม่พบเจอ'}</p>
            <p><strong>👤 ผู้รับผิดชอบ:</strong> ${task.person_name || 'ไม่พบเจอ'}</p>
            <p><strong>📅 วันที่รับเรื่องจากสำนักงานกฎหมาย:</strong> ${task.date_received_legal_office || 'ไม่พบเจอ'}</p>
            <p><strong>📅 วันที่รับเรื่องจากผู้รับผิดชอบ:</strong> ${task.date_received_responsible_officer || 'ไม่พบเจอ'}</p>
            <p><strong>📅 วันเดือนปีที่เสนอเรื่อง:</strong> ${task.date_proposal || 'ไม่พบเจอ'}</p>
            <p><strong>⏰ ระยะเวลาดำเนินการ (วัน):</strong> ${task.processing_duration_days || 'ไม่พบเจอ'}</p>
            <p><strong>📝 คำสั่ง:</strong> ${task.instructions || 'ไม่พบเจอ'}</p>
            <p><strong>💬 หมายเหตุ:</strong> ${task.remarks || 'ไม่พบเจอ'}</p>
        `;

        openModal('detailModal');
    }

    // Populate the edit modal with task data
    function populateEditModal(task) {
        // Mapping of element IDs to task properties
        const fieldMappings = {
            'editTaskId': task.id,
            'editTaskTitle': task.task,
            'editSubject': task.subject_id,
            'editAgency': task.responsible_agency,
            'editPerson': task.person_in_charge_id,
            'editInstructions': task.instructions,
            'editRemarks': task.remarks,
            'editDateReceivedLegalOffice': task.date_received_legal_office,
            'editDateReceivedResponsibleOfficer': task.date_received_responsible_officer,
            'editDateProposal': task.date_proposal,
            'editProcessingDurationDays': task.processing_duration_days
        };

        for (const [id, value] of Object.entries(fieldMappings)) {
            const element = document.getElementById(id);
            if (element) {
                element.value = value || '';
            } else {
                console.error(`Element with ID "${id}" not found in the DOM.`);
            }
        }
    }

    // Handle delete action for a task
    function handleDelete(taskId) {
        if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนี้?')) {
            fetch(`./utils/deleteTask.php?id=${taskId}`, { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('ลบข้อมูลสำเร็จ');
                        fetchTaskData(); // Refresh table after deletion
                    } else {
                        alert('ลบข้อมูลล้มเหลว: ' + data.message);
                    }
                })
                .catch(error => console.error('Error deleting task:', error));
        }
    }

    // Update pagination buttons based on total records, current page, and limit
    function setupPagination(total, currentPage, limit) {
        const paginationContainer = document.getElementById('pagination-container');
        if (!paginationContainer) {
            console.error('Pagination container not found.');
            return;
        }

        paginationContainer.innerHTML = '';

        const totalPages = Math.ceil(total / limit);
        if (totalPages <= 1) return; // No need for pagination

        for (let page = 1; page <= totalPages; page++) {
            const pageButton = document.createElement('button');
            pageButton.textContent = page;
            pageButton.classList.add('pagination-button');
            if (page === currentPage) pageButton.classList.add('active');
            pageButton.addEventListener('click', () => fetchTaskData('', '', page, limit));
            paginationContainer.appendChild(pageButton);
        }
    }

    // Trigger data fetch on search
    const searchButton = document.getElementById('searchButton');
    if (searchButton) {
        searchButton.addEventListener('click', function() {
            const searchCriteria = document.getElementById('searchCriteria').value;
            const searchTerm = document.getElementById('searchTerm').value.trim();
            fetchTaskData(searchCriteria, searchTerm);
        });
    }

    // Update data display based on selected record count
    const recordCountSelect = document.getElementById('recordCountSelect');
    if (recordCountSelect) {
        recordCountSelect.addEventListener('change', function() {
            fetchTaskData(); // Re-fetch with new limit
        });
    }

    // Function to open a modal
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.classList.add('no-scroll'); // Prevent background scroll
            // Set focus to the first input inside the modal for accessibility
            const firstInput = modal.querySelector('input, select, textarea, button');
            if (firstInput) firstInput.focus();
        } else {
            console.error(`Modal with ID "${modalId}" not found.`);
        }
    }

    // Function to close a modal
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('no-scroll'); // Restore background scroll
        } else {
            console.error(`Modal with ID "${modalId}" not found.`);
        }
    }
});
