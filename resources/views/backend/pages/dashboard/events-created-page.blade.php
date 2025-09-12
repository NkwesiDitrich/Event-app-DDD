@extends('backend.layout.sidenav-layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 col-sm-12 col-lg-12">
            <div class="card px-5 py-5">
                <div class="row justify-content-between">
                    <div class="col-md-8">
                        <h4>Events Created</h4>
                        <p class="text-muted">View your events and their participant statistics</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-outline-primary" onclick="refreshEvents()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                </div>
                <hr class="bg-secondary"/>

                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Filter by Status</label>
                        <select class="form-select" id="statusFilter" onchange="filterEvents()">
                            <option value="">All Events</option>
                            <option value="upcoming">Upcoming Events</option>
                            <option value="past">Past Events</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by event title..." onkeyup="searchEvents()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">View Mode</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="viewMode" id="cardView" value="card" checked onchange="toggleViewMode()">
                            <label class="btn btn-outline-primary" for="cardView">
                                <i class="bi bi-grid"></i> Cards
                            </label>
                            <input type="radio" class="btn-check" name="viewMode" id="tableView" value="table" onchange="toggleViewMode()">
                            <label class="btn btn-outline-primary" for="tableView">
                                <i class="bi bi-list"></i> Table
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4" id="eventStats">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 id="totalEvents">0</h3>
                                <p class="mb-0">Total Events</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 id="upcomingEvents">0</h3>
                                <p class="mb-0">Upcoming Events</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3 id="totalParticipants">0</h3>
                                <p class="mb-0">Total Participants</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3 id="checkedInParticipants">0</h3>
                                <p class="mb-0">Checked In</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Events Display -->
                <div class="row">
                    <div class="col-md-12">
                        <!-- Card View -->
                        <div id="cardViewContainer" class="row">
                            <div class="col-12 text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading events...</p>
                            </div>
                        </div>

                        <!-- Table View -->
                        <div id="tableViewContainer" class="card d-none">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Event</th>
                                                <th>Date & Time</th>
                                                <th>Location</th>
                                                <th>Participants</th>
                                                <th>Check-in Rate</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="eventsTableBody">
                                            <!-- Table rows will be inserted here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <span id="paginationInfo">Showing 0 of 0 events</span>
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

<!-- Event Participants Modal -->
<div class="modal fade" id="participantsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="participantsModalTitle">Event Participants</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="participantsModalContent">
                    <!-- Participants will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    let currentStatusFilter = '';
    let currentSearch = '';
    let currentViewMode = 'card';

    // Load events on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadEvents();
    });

    async function loadEvents(page = 1) {
        try {
            const params = new URLSearchParams({
                page: page,
                per_page: 12
            });

            if (currentStatusFilter) params.append('status', currentStatusFilter);

            const response = await axios.get(`/user-management/events-created?${params}`);
            
            if (response.data.status === 'success') {
                const data = response.data.data;
                updateEventsDisplay(data.events);
                updateStatistics(data.statistics);
                updatePagination(data.pagination, data.total);
                currentPage = page;
            }
        } catch (error) {
            console.error('Error loading events:', error);
            showToast('Error loading events', 'error');
        }
    }

    function updateEventsDisplay(events) {
        if (currentViewMode === 'card') {
            updateCardView(events);
        } else {
            updateTableView(events);
        }
    }

    function updateCardView(events) {
        const container = document.getElementById('cardViewContainer');
        
        if (!events || events.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-4 text-muted">
                    <i class="bi bi-calendar-x fs-1"></i>
                    <p class="mt-2">No events found</p>
                </div>
            `;
            return;
        }

        let html = '';
        events.forEach(event => {
            const statusBadge = event.is_upcoming ? 
                '<span class="badge bg-success">Upcoming</span>' : 
                '<span class="badge bg-secondary">Past</span>';
            
            const checkInRate = event.participant_stats.check_in_rate;
            const progressColor = checkInRate >= 70 ? 'success' : checkInRate >= 40 ? 'warning' : 'danger';

            html += `
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="card h-100">
                        ${event.image ? `<img src="/uploads/${event.image}" class="card-img-top" style="height: 200px; object-fit: cover;">` : ''}
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title">${event.title}</h5>
                                ${statusBadge}
                            </div>
                            <p class="card-text text-muted small">${event.description.substring(0, 100)}...</p>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> ${event.date}<br>
                                    <i class="bi bi-geo-alt"></i> ${event.location}<br>
                                    <i class="bi bi-clock"></i> ${event.time}
                                </small>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small>Participants: ${event.participant_stats.total_participants}</small>
                                    <small>Check-in: ${checkInRate}%</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-${progressColor}" style="width: ${checkInRate}%"></div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary flex-fill" onclick="viewParticipants(${event.id}, '${event.title}')">
                                    <i class="bi bi-people"></i> View Participants
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }

    function updateTableView(events) {
        const tbody = document.getElementById('eventsTableBody');
        
        if (!events || events.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="bi bi-calendar-x fs-1"></i>
                        <p class="mt-2">No events found</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        events.forEach(event => {
            const statusBadge = event.is_upcoming ? 
                '<span class="badge bg-success">Upcoming</span>' : 
                '<span class="badge bg-secondary">Past</span>';
            
            const checkInRate = event.participant_stats.check_in_rate;
            const progressColor = checkInRate >= 70 ? 'success' : checkInRate >= 40 ? 'warning' : 'danger';

            html += `
                <tr>
                    <td>
                        <div class="fw-bold">${event.title}</div>
                        <small class="text-muted">${event.description.substring(0, 50)}...</small>
                    </td>
                    <td>
                        <div>${event.date}</div>
                        <small class="text-muted">${event.time}</small>
                    </td>
                    <td>${event.location}</td>
                    <td>
                        <div>${event.participant_stats.total_participants} total</div>
                        <small class="text-success">${event.participant_stats.checked_in} checked in</small>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                <div class="progress-bar bg-${progressColor}" style="width: ${checkInRate}%"></div>
                            </div>
                            <small>${checkInRate}%</small>
                        </div>
                    </td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewParticipants(${event.id}, '${event.title}')">
                            <i class="bi bi-people"></i> View
                        </button>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
    }

    function updateStatistics(stats) {
        document.getElementById('totalEvents').textContent = stats.total_events || 0;
        document.getElementById('upcomingEvents').textContent = stats.upcoming_events || 0;
        document.getElementById('totalParticipants').textContent = stats.overall_participants.total_participants || 0;
        document.getElementById('checkedInParticipants').textContent = stats.overall_participants.checked_in || 0;
    }

    function updatePagination(pagination, total) {
        const info = document.getElementById('paginationInfo');
        const controls = document.getElementById('paginationControls');
        
        const start = ((pagination.current_page - 1) * pagination.per_page) + 1;
        const end = Math.min(pagination.current_page * pagination.per_page, total);
        
        info.textContent = `Showing ${start}-${end} of ${total} events`;
        
        let html = '';
        
        // Previous button
        if (pagination.current_page > 1) {
            html += `<li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="loadEvents(${pagination.current_page - 1})">Previous</a>
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
                    <a class="page-link" href="javascript:void(0)" onclick="loadEvents(${i})">${i}</a>
                </li>`;
            }
        }
        
        // Next button
        if (pagination.has_more) {
            html += `<li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="loadEvents(${pagination.current_page + 1})">Next</a>
            </li>`;
        }
        
        controls.innerHTML = html;
    }

    function toggleViewMode() {
        const cardView = document.getElementById('cardView');
        const tableView = document.getElementById('tableView');
        const cardContainer = document.getElementById('cardViewContainer');
        const tableContainer = document.getElementById('tableViewContainer');

        if (cardView.checked) {
            currentViewMode = 'card';
            cardContainer.classList.remove('d-none');
            tableContainer.classList.add('d-none');
        } else {
            currentViewMode = 'table';
            cardContainer.classList.add('d-none');
            tableContainer.classList.remove('d-none');
        }
        
        loadEvents(currentPage);
    }

    function filterEvents() {
        currentStatusFilter = document.getElementById('statusFilter').value;
        loadEvents(1);
    }

    function searchEvents() {
        currentSearch = document.getElementById('searchInput').value;
        // Implement search functionality if needed
        loadEvents(1);
    }

    async function viewParticipants(eventId, eventTitle) {
        try {
            const modal = new bootstrap.Modal(document.getElementById('participantsModal'));
            document.getElementById('participantsModalTitle').textContent = `Participants - ${eventTitle}`;
            document.getElementById('participantsModalContent').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading participants...</p>
                </div>
            `;
            
            modal.show();

            const response = await axios.get(`/user-management/event-participants/${eventId}`);
            
            if (response.data.status === 'success') {
                const participants = response.data.data.participants;
                updateParticipantsModal(participants);
            }
        } catch (error) {
            console.error('Error loading participants:', error);
            document.getElementById('participantsModalContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> Error loading participants
                </div>
            `;
        }
    }

    function updateParticipantsModal(participants) {
        const container = document.getElementById('participantsModalContent');
        
        if (!participants || participants.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-people fs-1"></i>
                    <p class="mt-2">No participants found for this event</p>
                </div>
            `;
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-sm">';
        html += '<thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
        
        participants.forEach(participant => {
            const statusBadge = participant.checked_in ? 
                '<span class="badge bg-success">Checked In</span>' : 
                '<span class="badge bg-warning">Not Checked In</span>';
            
            const checkInButton = participant.checked_in ?
                `<button class="btn btn-sm btn-outline-warning" onclick="toggleCheckInModal(${participant.id})">Check Out</button>` :
                `<button class="btn btn-sm btn-outline-success" onclick="toggleCheckInModal(${participant.id})">Check In</button>`;

            html += `
                <tr>
                    <td>${participant.name}</td>
                    <td>${participant.email}</td>
                    <td>${statusBadge}</td>
                    <td>${checkInButton}</td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        container.innerHTML = html;
    }

    async function toggleCheckInModal(registrationId) {
        try {
            const response = await axios.post('/user-management/check-in', {
                registration_id: registrationId
            });
            
            if (response.data.status === 'success') {
                showToast(response.data.message, 'success');
                // Refresh the modal content and main page
                const modal = document.getElementById('participantsModal');
                const eventId = modal.getAttribute('data-event-id');
                if (eventId) {
                    viewParticipants(eventId, '');
                }
                loadEvents(currentPage);
            }
        } catch (error) {
            console.error('Error toggling check-in:', error);
            showToast('Error updating check-in status', 'error');
        }
    }

    function refreshEvents() {
        showToast('Refreshing events...', 'info');
        loadEvents(currentPage);
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
