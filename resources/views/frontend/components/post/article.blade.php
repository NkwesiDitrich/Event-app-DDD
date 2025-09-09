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

			<!-- Begin Featured Image -->
			<img class="featured-image img-fluid" src="{{ asset($post->getImage()) }}" alt="{{ $post->getTitle()->getValue() }}">
			<!-- End Featured Image -->

			<!-- Begin Event Details Section -->
			<div class="event-details-section" style="background: #f8f9fa; padding: 30px; margin: 30px 0; border-radius: 8px; border-left: 4px solid #007bff;">
				<h3 style="color: #007bff; margin-bottom: 25px; font-size: 24px;">
					<i class="fa fa-calendar-alt" style="margin-right: 10px;"></i>Event Details
				</h3>
				
				<div class="row">
					<div class="col-md-6">
						<div class="detail-item" style="margin-bottom: 20px;">
							<h5 style="color: #333; margin-bottom: 8px;">
								<i class="fa fa-calendar" style="color: #007bff; margin-right: 8px;"></i>Date
							</h5>
							<p style="font-size: 16px; margin: 0; color: #666;">{{ $post->getDate()->getHumanReadableDate() }}</p>
						</div>
						
						@if($post->getTime()->getValue())
						<div class="detail-item" style="margin-bottom: 20px;">
							<h5 style="color: #333; margin-bottom: 8px;">
								<i class="fa fa-clock" style="color: #007bff; margin-right: 8px;"></i>Time
							</h5>
							<p style="font-size: 16px; margin: 0; color: #666;">{{ $post->getTime()->getValue() }}</p>
						</div>
						@endif
						
						<div class="detail-item" style="margin-bottom: 20px;">
							<h5 style="color: #333; margin-bottom: 8px;">
								<i class="fa fa-map-marker-alt" style="color: #007bff; margin-right: 8px;"></i>Location
							</h5>
							<p style="font-size: 16px; margin: 0; color: #666;">{{ $post->getLocation()->getValue() }}</p>
						</div>
					</div>
					
					<div class="col-md-6">
						<div class="detail-item" style="margin-bottom: 20px;">
							<h5 style="color: #333; margin-bottom: 8px;">
								<i class="fa fa-tag" style="color: #007bff; margin-right: 8px;"></i>Category
							</h5>
							<p style="font-size: 16px; margin: 0; color: #666;">Category {{ $post->getCategoryId() }}</p>
						</div>
						
						<div class="detail-item" style="margin-bottom: 20px;">
							<h5 style="color: #333; margin-bottom: 8px;">
								<i class="fa fa-user" style="color: #007bff; margin-right: 8px;"></i>Organized By
							</h5>
							<p style="font-size: 16px; margin: 0; color: #666;">User {{ $post->getUserId() }}</p>
						</div>
						
						<div class="detail-item" style="margin-bottom: 20px;">
							<h5 style="color: #333; margin-bottom: 8px;">
								<i class="fa fa-info-circle" style="color: #007bff; margin-right: 8px;"></i>Event Type
							</h5>
							<p style="font-size: 16px; margin: 0; color: #666;">
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
				<h3 style="color: #333; margin-bottom: 20px;">About This Event</h3>
				<p style="font-size: 16px; line-height: 1.6; color: #555;">
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

<!-- Add Font Awesome for icons if not already included -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
.event-details-section .detail-item h5 {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 14px;
    letter-spacing: 0.5px;
}

.registration-section:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
    box-shadow: 0 10px 25px rgba(0,123,255,0.3);
}

.tags li a {
    background: #f8f9fa;
    color: #007bff;
    border: 1px solid #dee2e6;
    text-transform: capitalize;
}

.tags li a:hover {
    background: #007bff;
    color: white;
    border-color: #007bff;
}
</style>
