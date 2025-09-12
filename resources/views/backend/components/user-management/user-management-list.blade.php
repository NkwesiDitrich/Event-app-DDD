<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 col-sm-12 col-lg-12">
            <div class="card px-5 py-5">
                <div class="row justify-content-between">
                    <div class="align-items-center col">
                        <h4>User Management</h4>
                        <p class="text-muted">Manage participants for your events</p>
                    </div>
                    <div class="align-items-center col-auto">
                        <div class="d-flex align-items-center">
                            <!-- Event Filter -->
                            <select id="eventFilter" class="form-select me-3" style="min-width: 200px;">
                                <option value="">All Events</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}" {{ $eventId == $event->id ? 'selected' : '' }}>
                                        {{ $event->title }}
                                    </option>
                                @endforeach
                            </select>
                            
                            <!-- Refresh Button -->
                            <button id="refreshBtn" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
                
                @if(isset($error))
                    <div class="alert alert-warning">
                        {{ $error }}
                    </div>
                @endif

                <hr class="bg-secondary"/>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Registrations</h5>
                                <h3 id="totalRegistrations">{{ count($registrations) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Checked In</h5>
                                <h3 id="checkedInCount">
                                    {{ collect($registrations)->filter(function($reg) { return $reg->isCheckedIn(); })->count() }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Not Checked In</h5>
                                <h3 id="notCheckedInCount">
                                    {{ collect($registrations)->filter(function($reg) { return !$reg->isCheckedIn(); })->count() }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Events</h5>
                                <h3>{{ $events->count() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registrations Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="registrationsTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>Event</th>
                                <th>Registration Date</th>
                                <th>Check-in Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="registrationsTableBody">
                            @forelse($registrations as $registration)
                                <tr data-registration-id="{{ $registration->getId() }}">
                                    <td>
                                        <strong>{{ $registration->getName()->getValue() }}</strong>
                                        @if($registration->getParticipantUserName())
                                            <br><small class="text-muted">User: {{ $registration->getParticipantUserName() }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $registration->getMobile()->getValue() }}</td>
                                    <td>{{ $registration->getEmail() ? $registration->getEmail()->getValue() : 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $registration->getEventTitle() ?? 'Unknown Event' }}</span>
                                    </td>
                                    <td>{{ $registration->getFormattedDate() }}</td>
                                    <td>
                                        @if($registration->isCheckedIn())
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Checked In
                                            </span>
                                            @if($registration->getCheckedInAt())
                                                <br><small class="text-muted">{{ $registration->getCheckedInAt()->format('M j, Y H:i') }}</small>
                                            @endif
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="bi bi-clock"></i> Not Checked In
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($registration->isCheckedIn())
                                                <button class="btn btn-sm btn-outline-warning check-out-btn" 
                                                        data-registration-id="{{ $registration->getId() }}"
                                                        title="Check Out">
                                                    <i class="bi bi-box-arrow-right"></i> Check Out
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-success check-in-btn" 
                                                        data-registration-id="{{ $registration->getId() }}"
                                                        title="Check In">
                                                    <i class="bi bi-check-circle"></i> Check In
                                                </button>
                                            @endif
                                            
                                            <button class="btn btn-sm btn-danger unattend-btn" 
                                                    data-registration-id="{{ $registration->getId() }}"
                                                    data-participant-name="{{ $registration->getName()->getValue() }}"
                                                    title="Remove Registration">
                                                <i class="bi bi-person-x"></i> Unattend
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="noDataRow">
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                        <br>No registrations found for your events
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="d-none position-fixed top-0 start-0 w-100 h-100" 
     style="background: rgba(0,0,0,0.5); z-index: 9999;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Event filter change handler
    document.getElementById('eventFilter').addEventListener('change', function() {
        const eventId = this.value;
        const url = new URL(window.location);
        
        if (eventId) {
            url.searchParams.set('event_id', eventId);
        } else {
            url.searchParams.delete('event_id');
        }
        
        window.location.href = url.toString();
    });

    // Refresh button handler
    document.getElementById('refreshBtn').addEventListener('click', function() {
        window.location.reload();
    });

    // Check-in button handlers
    document.addEventListener('click', function(e) {
        if (e.target.closest('.check-in-btn')) {
            const btn = e.target.closest('.check-in-btn');
            const registrationId = btn.dataset.registrationId;
            handleCheckIn(registrationId, true);
        }
        
        if (e.target.closest('.check-out-btn')) {
            const btn = e.target.closest('.check-out-btn');
            const registrationId = btn.dataset.registrationId;
            handleCheckIn(registrationId, false);
        }
        
        if (e.target.closest('.unattend-btn')) {
            const btn = e.target.closest('.unattend-btn');
            const registrationId = btn.dataset.registrationId;
            const participantName = btn.dataset.participantName;
            handleUnattend(registrationId, participantName);
        }
    });

    function handleCheckIn(registrationId, checkIn) {
        showLoading();
        
        fetch('/user-management/check-in', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                registration_id: registrationId,
                check_in: checkIn
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.status === 'success') {
                showToast('Success', data.message, 'success');
                // Refresh the page to update the UI
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showToast('Error', data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showToast('Error', 'An error occurred while updating check-in status', 'error');
        });
    }

    function handleUnattend(registrationId, participantName) {
        if (!confirm(`Are you sure you want to remove ${participantName}'s registration? This action cannot be undone.`)) {
            return;
        }
        
        showLoading();
        
        fetch('/user-management/unattend', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                registration_id: registrationId
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.status === 'success') {
                showToast('Success', data.message, 'success');
                // Remove the row from the table
                const row = document.querySelector(`tr[data-registration-id="${registrationId}"]`);
                if (row) {
                    row.remove();
                }
                // Update statistics
                updateStatistics();
            } else {
                showToast('Error', data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showToast('Error', 'An error occurred while removing registration', 'error');
        });
    }

    function showLoading() {
        document.getElementById('loadingOverlay').classList.remove('d-none');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('d-none');
    }

    function showToast(title, message, type) {
        // Create toast element
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}:</strong> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        // Add to toast container (create if doesn't exist)
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '10000';
            document.body.appendChild(toastContainer);
        }
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        // Show the toast
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        
        // Remove from DOM after hiding
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    function updateStatistics() {
        const rows = document.querySelectorAll('#registrationsTableBody tr[data-registration-id]');
        const total = rows.length;
        let checkedIn = 0;
        
        rows.forEach(row => {
            const badge = row.querySelector('.badge');
            if (badge && badge.textContent.includes('Checked In')) {
                checkedIn++;
            }
        });
        
        document.getElementById('totalRegistrations').textContent = total;
        document.getElementById('checkedInCount').textContent = checkedIn;
        document.getElementById('notCheckedInCount').textContent = total - checkedIn;
        
        // Show/hide no data row
        const noDataRow = document.getElementById('noDataRow');
        if (total === 0 && !noDataRow) {
            document.getElementById('registrationsTableBody').innerHTML = `
                <tr id="noDataRow">
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                        <br>No registrations found for your events
                    </td>
                </tr>
            `;
        } else if (total > 0 && noDataRow) {
            noDataRow.remove();
        }
    }
});
</script>

<style>
.table th {
    border-top: none;
    font-weight: 600;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.badge {
    font-size: 0.75em;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}

@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 2px;
        margin-right: 0;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>
