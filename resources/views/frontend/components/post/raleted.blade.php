<!-- Begin Related
================================================== -->
<div class="graybg">
	<div class="container">
		<div class="row listrecent listrelated">
			@foreach ($relatedEvents as $related)
			<!-- begin post -->
			<div class="col-md-4">
				<div class="card">
					<a href="{{ url('/post'.'/'.$related->getId())}}">
					<img class="img-fluid img-thumb" src="{{ asset($related->getImage())}}" alt="">
					</a>
					<div class="card-block">
						<h2 class="card-title"><a href="{{ url('/post'.'/'.$related->getId())}}">{{ $related->getTitle()->getValue() }}</a></h2>
						<div class="metafooter">
							<div class="wrapfooter">
								<span class="meta-footer-thumb">
								<a href="#"><img class="author-thumb" src="{{ asset('My_avatar.jpeg') }}" alt="Sal"></a>
								</span>
								<span class="author-meta">
								<span class="post-name"><a href="#">User {{ $related->getUserId() }}</a></span><br/>
								<span class="post-date">{{ $related->getDate()->getHumanReadableDate() }}</span><span class="dot"></span><span class="post-read">6 min read</span>
								</span>
								<span class="post-read-more"><a href="{{ url('/post'.'/'.$related->getId())}}" title="Read Story"><svg class="svgIcon-use" width="25" height="25" viewbox="0 0 25 25"><path d="M19 6c0-1.1-.9-2-2-2H8c-1.1 0-2 .9-2 2v14.66h.012c.01.103.045.204.12.285a.5.5 0 0 0 .706.03L12.5 16.85l5.662 4.126a.508.508 0 0 0 .708-.03.5.5 0 0 0 .118-.285H19V6zm-6.838 9.97L7 19.636V6c0-.55.45-1 1-1h9c.55 0 1 .45 1 1v13.637l-5.162-3.668a.49.49 0 0 0-.676 0z" fill-rule="evenodd"></path></svg></a></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- end post -->
			@endforeach
		</div>
	</div>
</div>
<!-- End Related Posts
================================================== -->
