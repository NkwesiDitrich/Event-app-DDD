<!-- Begin Article
================================================== -->
<div class="container">
	<div class="row">

		<!-- Begin Post -->
		<div class="col-md-10 col-md-offset-1 col-xs-12">
			<div class="mainheading">

				<!-- Removed Top Meta section as per user request -->
				
				@if (\Session::has('success'))
					<div class="alert alert-success">
						<ul>
							<li>{!! \Session::get('success') !!}</li>
						</ul>
					</div>
				@endif
				
				<h1 class="posttitle">{{ $post->getTitle()->getValue() }}</h1>

			</div>

			<!-- Begin Featured Image - Reduced Size -->
			<img class="post-featured-image" src="{{ asset($post->getImage()) }}" alt="{{ $post->getTitle()->getValue() }}">
			<!-- End Featured Image -->

			<!-- Begin Event Details Section - Improved Layout -->
			<div class="event-details-section">
				<h3>
					<i class="fa fa-calendar-alt"></i>Event Details
				</h3>
				
				<div class="row">
					<div class="col-md-6">
						<div class="event-detail-item">
							<h5>
								<i class="fa fa-calendar"></i>Date
							</h5>
							<p>{{ $post->getDate()->getHumanReadableDate() }}</p>
						</div>
						
						@if($post->getTime()->getValue())
						<div class="event-detail-item">
							<h5>
								<i class="fa fa-clock-o"></i>Time
							</h5>
							<p>{{ $post->getTime()->getValue() }}</p>
						</div>
						@endif
						
						<div class="event-detail-item">
							<h5>
								<i class="fa fa-map-marker"></i>Location
							</h5>
							<p>{{ $post->getLocation()->getValue() }}</p>
						</div>
					</div>
					
					<div class="col-md-6">
						<div class="event-detail-item">
							<h5>
								<i class="fa fa-tag"></i>Category
							</h5>
							<p>{{ $post->categoryName ?? 'Uncategorized' }}</p>
						</div>
						
						<div class="event-detail-item">
							<h5>
								<i class="fa fa-user"></i>Organized By
							</h5>
							<p>{{ $post->organizerName ?? 'Unknown Organizer' }}</p>
						</div>
						
						<div class="event-detail-item">
							<h5>
								<i class="fa fa-info-circle"></i>Event Type
							</h5>
							<p>
								<span class="badge" style="background: {{ $post->getType()->getValue() == 'Feature' ? '#28a745' : '#17a2b8' }}; color: white; padding: 5px 10px; border-radius: 15px;">
									{{ $post->getType()->getValue() }} Event
								</span>
							</p>
						</div>
					</div>
				</div>
			</div>
			<!-- End Event Details Section -->

			<!-- Begin Post Content -->
			<div class="article-post">
				<h3>About This Event</h3>
				<p>
					{{ $post->getDescription()->getValue() }}
				</p>
			</div>
			<!-- End Post Content -->

			<!-- Begin Event Registration Section -->
			<div class="event-registration-section">
				<div class="registration-call-to-action">
					<h4><i class="fa fa-ticket"></i> Register for This Event</h4>
					<p>Don't miss out on this amazing event! Register now to secure your spot.</p>
					<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#registrationModal">
						<i class="fa fa-user-plus"></i> Register Now
					</button>
				</div>
			</div>
			<!-- End Event Registration Section -->

		</div>
		<!-- End Post -->

	</div>
</div>
<!-- End Article
================================================== -->

<!-- Begin Registration Modal -->
<div class="modal fade" id="registrationModal" tabindex="-1" role="dialog" aria-labelledby="registrationModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="registrationModalLabel">
					<i class="fa fa-ticket"></i> Join this Event: {{ $post->getTitle()->getValue() }}
				</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="{{ url('/event-registration') }}" method="POST">
					@csrf
					<div class="form-group">
						<label class="form-label" for="name">Full Name *</label>
						<input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" required/>
						<input type="hidden" id="event_id" name="event_id" value="{{ $post->getId() }}"/>
						<input type="hidden" id="user_id" name="user_id" value="{{ $post->getUserId() }}"/>
					</div>
					
					<div class="form-group">
						<label class="form-label" for="mobile">Mobile Number</label>
						<input type="text" id="mobile" name="mobile" class="form-control" placeholder="Enter your mobile number" />
					</div>
					
					<div class="form-group">
						<label class="form-label" for="email">Email Address *</label>
						<input type="email" id="email" name="email" class="form-control" placeholder="Enter your email address" required/>
					</div>
					
					<div class="form-group">
						<label class="form-label" for="remark">Additional Comments</label>
						<textarea id="remark" name="remark" rows="3" class="form-control" placeholder="Any special requirements or comments..."></textarea>
					</div>
					
					<button type="submit" class="btn btn-primary btn-block">
						<i class="fa fa-check"></i> Complete Registration
					</button>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">
					<i class="fa fa-times"></i> Cancel
				</button>
			</div>
		</div>
	</div>
</div>
<!-- End Registration Modal -->

<style>
.event-details-section .event-detail-item h5 {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 14px;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.event-details-section .event-detail-item h5 i {
    color: #00ab6b;
    width: 16px;
    text-align: center;
}

.event-details-section .event-detail-item p {
    margin-left: 24px;
    font-size: 14px;
    color: #666;
}

.badge {
    font-size: 12px !important;
    font-weight: 500 !important;
}

/* Event Registration Section Styling */
.event-registration-section {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    padding: 30px;
    margin: 30px 0;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
}

.registration-call-to-action h4 {
    margin-bottom: 15px;
    font-size: 24px;
    font-weight: 600;
}

.registration-call-to-action h4 i {
    margin-right: 10px;
    color: #ffc107;
}

.registration-call-to-action p {
    font-size: 16px;
    margin-bottom: 25px;
    opacity: 0.9;
}

.registration-call-to-action .btn {
    padding: 12px 30px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 25px;
    transition: all 0.3s ease;
    border: 2px solid white;
    background: white;
    color: #007bff;
}

.registration-call-to-action .btn:hover {
    background: transparent;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 255, 255, 0.3);
}

/* Modal Styling */
.modal-header {
    background: #007bff;
    color: white;
    border-bottom: none;
}

.modal-header .modal-title {
    font-weight: 600;
}

.modal-header .modal-title i {
    color: #ffc107;
    margin-right: 8px;
}

.modal-header .close {
    color: white;
    opacity: 0.8;
}

.modal-header .close:hover {
    opacity: 1;
}

.modal-body {
    padding: 30px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.form-control {
    border-radius: 5px;
    border: 2px solid #e9ecef;
    padding: 10px 15px;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-block {
    padding: 12px;
    font-weight: 600;
    border-radius: 5px;
}

.modal-footer {
    border-top: 1px solid #e9ecef;
    padding: 20px 30px;
}

@media (max-width: 768px) {
    .event-details-section .event-detail-item p {
        margin-left: 0;
        margin-top: 5px;
    }
    
    .event-details-section .event-detail-item h5 {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .event-registration-section {
        padding: 20px;
        margin: 20px 0;
    }
    
    .registration-call-to-action h4 {
        font-size: 20px;
    }
    
    .registration-call-to-action .btn {
        padding: 10px 25px;
        font-size: 14px;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .modal-footer {
        padding: 15px 20px;
    }
}
</style>

