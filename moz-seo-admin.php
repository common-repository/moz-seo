<?php
/**
 * moz_plugin_options
 *
 **/
$moz_dir = plugins_url('/img', __FILE__);
?>
<style type="text/css">
.moz-info {padding:5px 10px; background:#fff; margin:10px 0; border:1px solid #ccc; border-radius:6px; -moz-border-radius:6px; font-style:italic;max-width:550px;}
.moz-ok {background:url(<?php echo $moz_dir ?>/thumbs-up.png) no-repeat; padding:5px 10px 5px 25px; font-style:italic;}
.moz-alert {background:url(<?php echo $moz_dir ?>/alert-icon.png) no-repeat; padding:5px 10px 5px 25px; font-style:italic; }
input[type="text"] {border:2px solid orange;}
label {font-weight:bold !important;}
</style>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>Moz SEO Custom Settings</h2>
<form method="post" action="options.php">
<?php settings_fields('seo_settings_options'); ?>
<?php $options = get_option('seo_settings'); ?>

<table class="form-table">
<tbody>
<tr>
<th scope="row">Theme h1 Heading?</th>
<td><label><input name="seo_settings[theme_h1]" type="checkbox" value="1" <?php checked('1', isset($options['theme_h1'])); ?> />&nbsp;My active theme already utilizes h1 heading for post/page keywords or I'm already using it elsewhere.</label><div class="moz-info">Most SEO savvy themes, like <a href="http://thememoz.com">ThemeMoz</a> place each of your post's titles in an h1 heading tag. If your theme does this check this box and Moz SEO will automatically credit your posts for h1 content. If you are unsure, you can view one of your post pages on your site, then use "View Source" to search for the &lt;h1&gt; tag.</div></td>
</tr>
<!--
<tr>
<th scope="row">Theme h2 Headings?</th>
<td><label><input name="seo_settings[theme_h2]" type="checkbox" value="1" <?php checked('1', isset($options['theme_h2'])); ?> />&nbsp;My active theme already utilizes h2 heading(s) for post/page keywords or I'm using it elsewhere.</label><div class="moz-info">Most themes, including ThemeMoz, do not include this, so you should probably leave this unchecked.</div></td>
</tr>
<tr>
<th scope="row">Theme h3 Headings?</th>
<td><label><input name="seo_settings[theme_h3]" type="checkbox" value="1" <?php checked('1', isset($options['theme_h3'])); ?> />&nbsp;My active theme already utilizes h3 heading(s) for post/page keywords or I'm using it elsewhere.</label><div class="moz-info">Most themes, including ThemeMoz, do not include this, so you should probably leave this unchecked.</div></td>
</tr>
-->
<tr>
<th scope="row">Theme Alt Text Images?</th>
<td><label><input name="seo_settings[theme_img]" type="checkbox" value="1" <?php checked('1', isset($options['theme_img'])); ?> />&nbsp;My active theme places an image(s) with alt text tagged with my post keywords.</label><div class="moz-info">If your theme automatically displays images that contain alt text matching the keywords for your site or pages, check this box and Moz SEO will automatically credit each of your posts for it. If not, Moz SEO recommends that you place an image into each of your posts that contains alt text with your primary keyword phrase.</div></td>
</tr>
<tr>
<th scope="row">Nofollow external links?</th>
<td><label><input name="seo_settings[nofollow]" type="checkbox" value="1" <?php checked('1', isset($options['nofollow'])); ?> />&nbsp;Enable nofollow filter on external content links</label><div class="moz-info">Check this box to have Moz SEO add <b>rel="nofollow"</b> to all external links it finds in your post content. The filter runs each time you click "Update" while editing a post. <i><b>Tip</b>: Its good practice to have at least some externl links to external "trusted" sites. Use your blogroll link list for this as Moz SEO does not alter these links. Remember to remove the default links first!</div></td>
</tr>
<th scope="row">Nofollow Cloaked Internal Links?</th>
<td><label>URL Path to folder:<br/><input type="text" style="width:450px;" name="seo_settings[nofollow_folder]" value="<?php echo $options['nofollow_folder']; ?>" /></label>
<div class="moz-info">Input the URL path to your cloaked links folder to have Moz SEO add <b>rel="nofollow"</b> to all internal links it finds in your content with the specified URL pattern (enter the path to your cloaking folder in the input box, example, http://site.com/recommends/. Don't forget the trailing slash "/" at end of the URL. The filter runs each time you click "Update" while editing a post.</div></td>
</tr>
<tr>
<th scope="row">Clean Category URLs?</th>
<td><label><input name="seo_settings[no_cat_base]" type="checkbox" value="1" <?php checked('1', isset($options['no_cat_base'])); ?> />&nbsp;Remove "category" base from URLs to category landing pages.</label><div class="moz-info">To remove the word "/category/" from your URLs, check this box. Your category landing page urls will become <b>site.com/category-name</b> (instead of site.com/category/category-name). <b>Note</b>: If a post has the same name as a category, the category url trumps the post. However, you can easily change your post URLs to <b>/%postname%.html</b> at <?php if (function_exists('ce3_admin')) echo '"CE4 > SEO > Permalinks Settings"'; else echo '"Settings > Permalinks"';?> to avoid conflicts. <b>Requests for old category urls are automagically redirected to the new ones</b>.</div></td>
</tr>
<th scope="row">Custom Keyword Density?</th>
<td><label>Low: <input type="text" style="width:30px;" name="seo_settings[kwd_low]" value="<?php echo $options['kwd_low']; ?>" /></label> <label>High: <input type="text" style="width:30px;" name="seo_settings[kwd_high]" value="<?php echo $options['kwd_high']; ?>" /></label>
<div class="moz-info">Moz SEO suggests and defaults to a keyword density range of between 1 to 4 percent. To customize this range, input your desired post/page content ranges for keyword density here and Moz SEO will help you stay within your acceptable target range and score your posts accordingly.</div></td>
</tr>

<th scope="row">Personalized LSI?</th>
<td><label>Enter your personized LSI search Key here:<br/> <input type="text" style="width:305px;" name="seo_settings[lsi_key]" value="<?php echo $options['lsi_key']; ?>" /></label>
<div class="moz-info">For the absolute fastest LSI performance, you can <a href="http://www.bing.com/developers/createapp.aspx">obtain and use your own personalized API key from Bing</a>. The API key is currently free of charge and allows you to perform an unlimited number of personalized LSI queries in real-time. <b>Note: This is optional. If you don't supply a personalized LSI key, the default one will be used</b>.</div></td>
</tr>
<!--
<th scope="row">Check for Upgrade?</th>
<td><label>Coming Soon > </label><input type="button" value="Upgrade" />
<div class="moz-info">This feature is slated for an upcoming release.</div></td>
</tr>
-->
<tr>
<td colspan="2"><span class="moz-ok">Moz SEO Version <strong>1.0</strong></span> <?php if (PHP_VERSION < 5) { ?><span class="moz-alert"><strong>IMPORTANT:</strong> Your site's speed and performance will increase dramatically when you ask your server host to upgrade your site to PHP Version 5.0 or greater. You are currently running <strong>PHP Version: <?php echo PHP_VERSION; ?></strong></span><?php } else { echo '<span class="moz-ok">PHP Version: <strong>'.PHP_VERSION; echo '</strong></span>'; } ?>
<?php global $wp_version; if($wp_version > 2.7 ) echo '<span class="moz-ok">WordPress: <b>'.$wp_version.'</b>'; else echo '<span class="moz-alert">Wordpress: <b>'.$wp_version. '</b> (Moz SEO Suggests Version 2.8.2 or Better)'; ?>
</td>
</tr>

</tbody></table>
<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
</form>
</div>