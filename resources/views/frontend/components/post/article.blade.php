<!-- Begin Article
================================================== -->
<div class="container">
	<div class="row">

		<!-- Begin Post -->
		<div class="col-md-8 col-md-offset-2 col-xs-12">
			<div class="mainheading">

				<!-- Begin Top Meta -->
				<div class="row post-top-meta">
					<div class="col-md-2">
						<a href="#"><img class="author-thumb" src="{{ asset('My_avatar.jpeg') }}" alt="Sal"></a>
					</div>
					<div class="col-md-10">
						<a class="link-dark" href="#">{{ $post->user->firstName }} {{ $post->user->LastName }}</a><a href="#" class="btn follow">Follow</a>
						<span class="author-description">Elite Author</span>
						<span class="post-date">{{ date('d F Y',strtotime($post->date)) }}</span><span class="dot"></span><span class="post-read">6 min read</span>
					</div>
				</div>
				<!-- End Top Menta -->
				@if (\Session::has('success'))
					<div class="alert alert-success">
						<ul>
							<li>{!! \Session::get('success') !!}</li>
						</ul>
					</div>
				@endif
				<h1 class="posttitle">{{ $post->user->title }}</h1>

			</div>

			<!-- Begin Featured Image -->
			<img class="featured-image img-fluid" src="{{ asset($post->image) }}" alt="">
			<!-- End Featured Image -->

			<!-- Begin Post Content -->
			<div class="article-post">
				<p>
					{{ $post->description }}
				</p>
				
				{{-- <blockquote>
					Gen-z strategy long tail churn rate seed money channels user experience incubator startup partner network low hanging fruit direct mailing. Client backing success startup assets responsive web design burn rate A/B testing metrics first mover advantage conversion.
				</blockquote> --}}
				
			</div>
			<!-- End Post Content -->

			<!-- Begin Tags -->
			<div class="after-post-tags">
				<ul class="tags">
					<li><a href="#">Design</a></li>
					<li><a href="#">Growth Mindset</a></li>
					<li><a href="#">Productivity</a></li>
					<li><a href="#">Personal Growth</a></li>
				</ul>
			</div>
			<!-- End Tags -->

		</div>
		<!-- End Post -->

	</div>
</div>
<!-- End Article
================================================== -->
<div class="hideshare"></div>
