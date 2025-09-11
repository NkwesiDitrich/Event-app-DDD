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
                        <p class="event-card-description">{{  Str::limit($featur->getDescription()->getValue(),100)  }}</p>
                        <div class="event-card-meta">
                            <div class="event-card-author">
                                <img src="{{ asset('My_avatar.jpeg') }}" alt="Organizer">
                                <div class="event-card-author-info">
                                    <div class="event-card-author-name">User {{ $featur->getUserId() }}</div>
                                    <div class="event-card-date">{{ $featur->getDate()->getHumanReadableDate() }}</div>
                                </div>
                            </div>
                            <a href="{{ url('/post'.'/'.$featur->getId())}}" class="event-card-read-more" title="Read Story">
                                Read More
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

