	<!-- Begin Featured
	================================================== -->
	<section class="featured-posts">
        <div class="section-title">
            <h2><span>Featured</span></h2>
        </div>
        <div class="featured-events-grid">
            @foreach ($featurEvents as $featur)
                <!-- begin post -->
                <div class="event-card-container">
                    <div class="event-card-image">
                        <a href="{{ url('/post'.'/'.$featur->getId())}}">
                            <img src="{{ asset($featur->getImage()) }}" alt="{{ $featur->getTitle()->getValue() }}">
                        </a>
                    </div>
                    <div class="event-card-details">
                        <h3 class="event-card-title">
                            <a href="{{ url('/post'.'/'.$featur->getId())}}">{{ $featur->getTitle()->getValue() }}</a>
                        </h3>
                        <div class="event-card-info">
                            <div class="event-info-item">
                                <i class="fa fa-calendar"></i>
                                <span>{{ $featur->getDate()->getHumanReadableDate() }}</span>
                            </div>
                            <div class="event-info-item">
                                <i class="fa fa-clock-o"></i>
                                <span>{{ $featur->getTime()->getValue() ?: 'Time TBA' }}</span>
                            </div>
                            <div class="event-info-item">
                                <i class="fa fa-map-marker"></i>
                                <span>{{ $featur->getLocation()->getValue() }}</span>
                            </div>
                            <div class="event-info-item">
                                <i class="fa fa-user"></i>
                                <span>{{ $featur->organizerName ?? 'Unknown Organizer' }}</span>
                            </div>
                        </div>
                        <div class="event-card-footer">
                            <a href="{{ url('/post'.'/'.$featur->getId())}}" class="event-card-read-more" title="View Event Details">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                <!-- end post -->
            @endforeach
        </div>
        </section>
        <!-- End Featured
        ================================================== -->

