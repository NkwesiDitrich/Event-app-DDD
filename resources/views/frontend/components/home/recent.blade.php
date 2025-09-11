<!-- Begin List Posts
	================================================== -->
	<section class="recent-posts">
        <div class="section-title">
            <h2><span>All Stories</span></h2>
        </div>
        <div class="events-grid">
            @foreach ($recentEvents as $recent )
                 <!-- begin post -->
                <div class="event-card-container">
                    <div class="event-card-image">
                        <a href="{{ url('/post'.'/'.$recent->getId())}}">
                            <img src="{{ asset($recent->getImage())}}" alt="{{ $recent->getTitle()->getValue() }}">
                        </a>
                    </div>
                    <div class="event-card-details">
                        <h3 class="event-card-title">
                            <a href="{{ url('/post'.'/'.$recent->getId())}}">{{ $recent->getTitle()->getValue() }}</a>
                        </h3>
                        <p class="event-card-description">{{  Str::limit($recent->getDescription()->getValue(),100)  }}</p>
                        <div class="event-card-meta">
                            <div class="event-card-author">
                                <img src="{{ asset('My_avatar.jpeg') }}" alt="Organizer">
                                <div class="event-card-author-info">
                                    <div class="event-card-author-name">User {{ $recent->getUserId() }}</div>
                                    <div class="event-card-date">{{ $recent->getDate()->getHumanReadableDate() }}</div>
                                </div>
                            </div>
                            <a href="{{ url('/post'.'/'.$recent->getId())}}" class="event-card-read-more" title="Read Story">
                                Read More
                            </a>
                        </div>
                    </div>
                </div>
                <!-- end post -->
            @endforeach
           
        </div>
        
        {{-- Note: Pagination may need adjustment for DDD entities --}}
        {{-- {{ $recentEvents->links()  }} --}}
       
        </section>
        <!-- End List Posts
        ================================================== -->

