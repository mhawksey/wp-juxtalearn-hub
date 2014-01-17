<style>
.juxtalearn-map .node rect {
  /*cursor: move;*/
  fill-opacity: .8;
  shape-rendering: crispEdges;
}
 
.juxtalearn-map .node text {
  pointer-events: none;
  text-shadow: 0 1px 0 #fff;
  font-size:11px;
  font-family: "Open Sans", Helvetica, Arial, "Nimbus Sans L", sans-serif;
}
.juxtalearn-map .node text.hide {
  display:none;
}
 
.juxtalearn-map .link {
  fill: none;
  stroke: #000;
  stroke-opacity: .2;
}
 
.juxtalearn-map .link:hover {
  stroke-opacity: .5;
}
</style>
<div id="juxtalearn_hub_overview" class="wrap"> 
	<div id="content" class="juxtalearn-map"><h2>JuxtaLearn Summary</h2><?php echo do_shortcode('[example_map]'); ?></div>
	<!-- <div class="juxtalearn_hub_header" style="text-align:center"><a href="http://oerresearchhub.org/" title="OER Research Hub" rel="home"> <img src="http://oerresearchhub.files.wordpress.com/2013/07/cropped-oer_700-banner-2.jpg" width="513" alt=""> </a></div> -->    
</div>