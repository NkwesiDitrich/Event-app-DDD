<!-- Begin Article
================================================== -->
<div class="container">
	<div class="row">

		<!-- Begin Post -->
		<div class="col-md-10 col-md-offset-1 col-xs-12">
			<div class="mainheading">

				<!-- Begin Top Meta -->
				<div class="row post-top-meta">
					<div class="col-md-2">
						<a href="#"><img class="author-thumb" src="https://www.gravatar.com/avatar/e56154546cf4be74e393c62d1ae9f9d4?s=250&amp;d=mm&amp;r=x" alt="Organizer"></a>
					</div>
					<div class="col-md-10">
						<a class="link-dark" href="#">User {{ $post->getUserId() }}</a>
						<span class="author-description">Event Organizer</span>
						<span class="post-date">Published: {{ $post->getCreatedAt()->format('d F Y') }}</span>
					</div>
				</div>
				<!-- End Top Meta -->
				
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
							<p>Category {{ $post->getCategoryId() }}</p>
						</div>
						
						<div class="event-detail-item">
							<h5>
								<i class="fa fa-user"></i>Organized By
							</h5>
							<p>User {{ $post->getUserId() }}</p>
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

		</div>
		<!-- End Post -->

	</div>
</div>
<!-- End Article
================================================== -->

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
}
</style>

