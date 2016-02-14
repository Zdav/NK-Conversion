<article id="RL_footerSponsors">
	<h3>{{blockSponsorsTitle}}</h3>
    <div id="RL_sponsorsNav">
        <a href="#" id="RL_sponsorsPrev"><span>Prev</span></a>
        <a href="#" id="RL_sponsorsNext"><span>Next</span></a>
    </div>
	<div id="RL_sponsorsWrapper" {{data}} data-width="{{elementWidth}}">
	    @if({{nbSponsorsImages}} > 0)
	        @if({{nbSponsorsImages}} > 1)
	        @endif
	        <div id="RL_sponsors" style="width:{{totalWidth}}px;left:{{initLeft}}px" data-left="{{initLeft}}">
	            @foreach(sponsorsImages as image)<!--
	                --><figure {{image.id}} {{image.current}}>
	                    <!-- <figcaption>{{image.title}}</figcaption> -->
	                    <a href="{{image.link}}" style="background-image:url({{image.src}})" title="{{image.title}}">
	                        <img src="{{image.src}}" alt="{{image.title}}" />
	                    </a>
	                </figure><!--
	         -->@endforeach
	        </div>
	    @endif
	</div>
</article>
<a href="#RL_mainNav" id="RL_backToTop"><span>Retour en haut</span></a>