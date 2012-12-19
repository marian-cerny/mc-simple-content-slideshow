<h1>Simple content slideshow Wordpress plug-in</h1>

<p>by <a href="http://mariancerny.com/" title="Marian Cerny">Marian Cerny</a></p>

<p>A fairly customizable and universal slideshow plug-in for Wordpress. Supports slide and fade transitions, and works on touch devices too.</p>

<h2>How to use this plug-in</h2>

<p>
The plug-in creates a new hierarchical post type called <strong>Slideshows</strong>. The top level posts are used as slideshows and it's children are the individual slides. You can create as many slideshows with as many slides as you like.
</p>

<p>
Any text in the parent post is ignored. The text inside child posts is output as the slide content. Positioning this text (as well as the arrows, page numbers) should be done in your own css file. If for some reason the options are not overridden, use <code>!important</code> in your code.
</p>

<p>
In order to set a background for a slide, you must assign a <strong>featured image</strong> to it. 
</p>

<p>
A slideshow is inserted by using the <code>[slideshow name="slideshow-slug"]</code> shortcode. Use the <code>do_shortcode()</code> function to insert the slideshow inside your templates.
</p>

<p>
When inserting a slideshow, the options set in the options page are used by default. You can override some of these settings by specifying parameters for the shortcode. These include the following:

	<ul>
		<li>width</li>
		<li>height</li>
		<li>order</li>
		<li>orderby</li>
		<li>show_paging</li>
	</ul>
</p>