@extends('backend.layout.sidenav-layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 col-sm-12 col-lg-12">
            <div class="card px-5 py-5">
                <div class="row justify-content-between">
                    <div class="col-md-8">
                        <h4>Event Participants</h4>
                        <p class="text-muted">View and manage participants for your events</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-outline-primary" onclick="refreshParticipants()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                </div>
                <hr class="bg-secondary"/>

                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Filter by Event</label>
                        <select class="form-select" id="eventFilter" onchange="filterParticipants()">
                            <option value="">All Events</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter by Status</label>
                        <select class="form-select" id="statusFilter" onchange="filterParticipants()">
                            <option value="">All Participants</option>
                            <option value="checked_in">Checked In</option>
                            <option value="not_checked_in">Not Checked In</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by name or email..." onkeyup="searchParticipants()">
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4" id="participantStats">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 id="totalParticipants">0</h3>
                                <p class="mb-0">Total Participants</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 id="checkedInCount">0</h3>
                                <p class="mb-0">Checked In</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3 id="notCheckedInCount">0</h3>
                                <p class="mb-0">Not Checked In</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3 id="checkInRate">0%</h3>
                                <p class="mb-0">Check-in Rate</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Participants Table -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="participantsTable">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Mobile</th>
                                                <th>Event</th>
                                                <th>Registration Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="participantsTableBody">
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    <p class="mt-2">Loading participants...</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <span id="paginationInfo">Showing 0 of 0 participants</span>
                                    </div>
                                    <div>
                                        <nav>
                                            <ul class="pagination mb-0" id="paginationControls">
                                                <!-- Pagination buttons will be inserted here -->
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    let currentEventFilter = '';
    let currentStatusFilter = '';
    let currentSearch = '';

    // Load participants on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadParticipants();
    });

    async function loadParticipants(page = 1) {
        try {
            const params = new URLSearchParams({
                page: page,
                per_page: 10
            });

            if (currentEventFilter) params.append('event_id', currentEventFilter);
            if (currentStatusFilter) params.append('status', currentStatusFilter);

            const response = await axios.get(`/user-management/event-participants?${params}`);
            
            if (response.data.status === 'success') {
                const data = response.data.data;
                updateParticipantsTable(data.participants);
                updateStatistics(data.statistics);
                updateEventFilter(data.events);
                updatePagination(data.pagination, data.total);
                currentPage = page;
            }
        } catch (error) {
            console.error('Error loading participants:', error);
            showToast('Error loading participants', 'error');
        }
    }

    function updateParticipantsTable(participants) {
        const tbody = document.getElementById('participantsTableBody');
        
        if (!participants || participants.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="bi bi-people fs-1"></i>
                        <p class="mt-2">No participants found</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        participants.forEach(participant => {
            const statusBadge = participant.checked_in ? 
                '<span class="badge bg-success">Checked In</span>' : 
                '<span class="badge bg-warning">Not Checked In</span>';
            
            const checkInButton = participant.checked_in ?
                `<button class="btn btn-sm btn-outline-warning" onclick="toggleCheckIn(${participant.id})">
                    <i class="bi bi-person-dash"></i> Check Out
                </button>` :
                `<button class="btn btn-sm btn-outline-success" onclick="toggleCheckIn(${participant.id})">
                    <i class="bi bi-person-check"></i> Check In
                </button>`;

            html += `
                <tr>
                    <td>${participant.name}</td>
                    <td>${participant.email}</td>
                    <td>${participant.mobile || 'N/A'}</td>
                    <td>
                        <small class="text-muted">${participant.event_title}</small><br>
                        <small class="text-info">${participant.event_date}</small>
                    </td>
                    <td>${new Date(participant.created_at).toLocaleDateString()}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="btn-group" role="group">
                            ${checkInButton}
                            <button class="btn btn-sm btn-outline-danger" onclick="unattendParticipant(${participant.id})">
                                <i class="bi bi-person-x"></i> Remove
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
    }

    function updateStatistics(stats) {
        document.getElementById('totalParticipants').textContent = stats.total_participants || 0;
        document.getElementById('checkedInCount').textContent = stats.checked_in || 0;
        document.getElementById('notCheckedInCount').textContent = stats.not_checked_in || 0;
        
        const rate = stats.total_participants > 0 ? 
            Math.round((stats.checked_in / stats.total_participants) * 100) : 0;
        document.getElementById('checkInRate').textContent = rate + '%';
    }

    function updateEventFilter(events) {
        const select = document.getElementById('eventFilter');
        const currentValue = select.value;
        
        // Clear existing options except "All Events"
        select.innerHTML = '<option value="">All Events</option>';
        
        events.forEach(event => {
            const option = document.createElement('option');
            option.value = event.id;
            option.textContent = `${event.title} (${event.date})`;
            select.appendChild(option);
        });
        
        // Restore previous selection
        select.value = currentValue;
    }

    function updatePagination(pagination, total) {
        const info = document.getElementById('paginationInfo');
        const controls = document.getElementById('paginationControls');
        
        const start = ((pagination.current_page - 1) * pagination.per_page) + 1;
        const end = Math.min(pagination.current_page * pagination.per_page, total);
        
        info.textContent = `Showing ${start}-${end} of ${total} participants`;
        
        let html = '';
        
        // Previous button
        if (pagination.current_page > 1) {
            html += `<li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="loadParticipants(${pagination.current_page - 1})">Previous</a>
            </li>`;
        }
        
        // Page numbers
        for (let i = 1; i <= pagination.total_pages; i++) {
            if (i === pagination.current_page) {
                html += `<li class="page-item active">
                    <span class="page-link">${i}</span>
                </li>`;
            } else {
                html += `<li class="page-item">
                    <a class="page-link" href="javascript:void(0)" onclick="loadParticipants(${i})">${i}</a>
                </li>`;
            }
        }
        
        // Next button
        if (pagination.has_more) {
            html += `<li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="loadParticipants(${pagination.current_page + 1})">Next</a>
            </li>`;
        }
        
        controls.innerHTML = html;
    }

    function filterParticipants() {
        currentEventFilter = document.getElementById('eventFilter').value;
        currentStatusFilter = document.getElementById('statusFilter').value;
        loadParticipants(1);
    }

    function searchParticipants() {
        currentSearch = document.getElementById('searchInput').value;
        // Implement search functionality if needed
        loadParticipants(1);
    }

    async function toggleCheckIn(registrationId) {
        try {
            const response = await axios.post('/user-management/check-in', {
                registration_id: registrationId
            });
            
            if (response.data.status === 'success') {
                showToast(response.data.message, 'success');
                loadParticipants(currentPage);
            }
        } catch (error) {
            console.error('Error toggling check-in:', error);
            showToast('Error updating check-in status', 'error');
        }
    }

    async function unattendParticipant(registrationId) {
        if (!confirm('Are you sure you want to remove this participant?')) {
            return;
        }

        try {
            const response = await axios.post('/user-management/unattend', {
                registration_id: registrationId
            });
            
            if (response.data.status === 'success') {
                showToast(response.data.message, 'success');
                loadParticipants(currentPage);
            }
        } catch (error) {
            console.error('Error removing participant:', error);
            showToast('Error removing participant', 'error');
        }
    }

    function refreshParticipants() {
        showToast('Refreshing participants...', 'info');
        loadParticipants(currentPage);
    }

    function showToast(message, type = 'success') {
        Toastify({
            text: message,
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: type === 'success' ? "#28a745" : type === 'error' ? "#dc3545" : "#17a2b8",
        }).showToast();
    }
</script>
@endsection
