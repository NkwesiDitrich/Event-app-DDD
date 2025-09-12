@extends('backend.layout.sidenav-layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 col-sm-12 col-lg-12">
            <div class="card px-5 py-5">
                <div class="row justify-content-between">
                    <div class="col-md-8">
                        <h4>User Management Dashboard</h4>
                        <p class="text-muted">Manage your event participants and track attendance</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                </div>
                <hr class="bg-secondary"/>

                <!-- Statistics Cards -->
                <div class="row mb-4" id="statisticsCards">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Total Events</h6>
                                        <h3 id="totalEvents">0</h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-calendar-event fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Total Participants</h6>
                                        <h3 id="totalParticipants">0</h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-people fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Checked In</h6>
                                        <h3 id="checkedIn">0</h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-person-check fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Not Checked In</h6>
                                        <h3 id="notCheckedIn">0</h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-person-x fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5>Quick Actions</h5>
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="{{ url('/user-management/event-participants') }}" class="btn btn-outline-primary">
                                <i class="bi bi-person-check"></i> View Event Participants
                            </a>
                            <a href="{{ url('/user-management/events-created') }}" class="btn btn-outline-success">
                                <i class="bi bi-calendar-plus"></i> View Events Created
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-md-12">
                        <h5>Recent Activity</h5>
                        <div class="card">
                            <div class="card-body">
                                <div id="recentActivity">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2">Loading recent activity...</p>
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
    // Load dashboard data on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadDashboardData();
    });

    async function loadDashboardData() {
        try {
            // Load events statistics
            const eventsResponse = await axios.get('/user-management/events-created');
            if (eventsResponse.data.status === 'success') {
                const eventsData = eventsResponse.data.data;
                updateStatistics(eventsData.statistics);
                updateRecentActivity(eventsData.events.slice(0, 5)); // Show last 5 events
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            showToast('Error loading dashboard data', 'error');
        }
    }

    function updateStatistics(stats) {
        document.getElementById('totalEvents').textContent = stats.total_events || 0;
        document.getElementById('totalParticipants').textContent = stats.overall_participants.total_participants || 0;
        document.getElementById('checkedIn').textContent = stats.overall_participants.checked_in || 0;
        document.getElementById('notCheckedIn').textContent = stats.overall_participants.not_checked_in || 0;
    }

    function updateRecentActivity(events) {
        const container = document.getElementById('recentActivity');
        
        if (!events || events.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-calendar-x fs-1"></i>
                    <p class="mt-2">No recent events found</p>
                </div>
            `;
            return;
        }

        let html = '<div class="list-group list-group-flush">';
        events.forEach(event => {
            const statusBadge = event.is_upcoming ? 
                '<span class="badge bg-success">Upcoming</span>' : 
                '<span class="badge bg-secondary">Past</span>';
            
            const participantInfo = `${event.participant_stats.total_participants} participants, ${event.participant_stats.checked_in} checked in`;
            
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold">${event.title}</div>
                        <small class="text-muted">${event.date} at ${event.location}</small>
                        <br>
                        <small class="text-info">${participantInfo}</small>
                    </div>
                    ${statusBadge}
                </div>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
    }

    function refreshDashboard() {
        showToast('Refreshing dashboard...', 'info');
        loadDashboardData();
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
