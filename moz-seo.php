<?php
/*
Plugin Name: ThemeMoz SEO
Plugin URI: http://thememoz.com/moz-seo
Description: Comprehensive Post/Page SEO Analysis and Suggestion Engine. Click "Settings" link below to customize options.
Author: ThemeMoz
Version: 1.0.0
Author URI: http://thememoz.com
*/
add_action('save_post', 'moz_save_moz_keyword',10,2);
add_filter('manage_posts_columns', 'moz_add_seo_columns', 10, 2);
add_action('manage_posts_custom_column', 'moz_display_seo_columns', 10, 2);
add_filter('manage_pages_columns', 'moz_add_seo_columns_pages', 10, 2);
add_action('manage_pages_custom_column', 'moz_display_seo_columns', 10, 2);
add_action('admin_head', 'moz_customize_css');
add_action('admin_menu', 'moz_post_options_box');
$moz = get_option('seo_settings');
if(isset($moz['no_cat_base'])){
	add_filter('category_rewrite_rules', 'moz_category_base_rewrite_rules');
	add_filter('query_vars', 'moz_category_base_query_vars');
	add_filter('request', 'moz_category_base_request');
	add_filter('category_link', 'moz_category_base',1000,2);
	add_action('created_category','moz_category_base_refresh_rules');
	add_action('edited_category','moz_category_base_refresh_rules');
	add_action('delete_category','moz_category_base_refresh_rules');
}
	
function moz_post_options_box() {
	if ( function_exists('add_meta_box') ) { 
		add_meta_box('rock-seo', __('Moz SEO'), 'moz_rock_seo', 'post', 'side', 'high');
		add_meta_box('rock-seo', __('Moz SEO'), 'moz_rock_seo', 'page', 'side', 'high'); 
		}		
	$moz_dir = plugins_url('/img', __FILE__);
	add_options_page( 'SEO Settings', 'SEO Settings', 'manage_options', 'moz-seo-admin.php', 'seo_settings_admin', $moz_dir.'/favicon.png', 'top');
	register_setting( 'seo_settings_options', 'seo_settings', 'seo_settings_validate' );
}

/*category base*/
function moz_category_base_refresh_rules() {wp_cache_flush();global $wp_rewrite;$ce4_permalinks = get_option('permalink_structure');$wp_rewrite->set_permalink_structure($ce4_permalinks);$wp_rewrite->flush_rules();}
function moz_category_base_deactivate() {remove_filter('category_rewrite_rules', 'moz_category_base_rewrite_rules');moz_category_base_refresh_rules();}
function moz_category_base($catlink, $category_id) {$category = &get_category( $category_id );if ( is_wp_error( $category ) )return $category;$category_nicename = $category->slug;if ( $category->parent == $category_id )$category->parent = 0;elseif ($category->parent != 0 )$category_nicename = get_category_parents( $category->parent, false, '/', true ) . $category_nicename;$catlink = trailingslashit(get_option( 'home' )) . user_trailingslashit( $category_nicename, 'category' );return $catlink;}
function moz_category_base_rewrite_rules($category_rewrite) {$category_rewrite=array();$categories=get_categories(array('hide_empty'=>false));foreach($categories as $category) {$category_nicename = $category->slug;if ( $category->parent == $category->cat_ID )$category->parent = 0;elseif ($category->parent != 0 )$category_nicename = get_category_parents( $category->parent, false, '/', true ) . $category_nicename;$category_rewrite['('.$category_nicename.')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?category_name=$matches[1]&feed=$matches[2]';$category_rewrite['('.$category_nicename.')/page/?([0-9]{1,})/?$'] = 'index.php?category_name=$matches[1]&paged=$matches[2]';$category_rewrite['('.$category_nicename.')/?$'] = 'index.php?category_name=$matches[1]';}global $wp_rewrite;$old_base = $wp_rewrite->get_category_permastruct();$old_base = str_replace( '%category%', '(.+)', $old_base );$old_base = trim($old_base, '/');$category_rewrite[$old_base.'$'] = 'index.php?category_redirect=$matches[1]';return $category_rewrite;}
function moz_category_base_query_vars($public_query_vars) {$public_query_vars[] = 'category_redirect';return $public_query_vars;}
function moz_category_base_request($query_vars) {if(isset($query_vars['category_redirect'])) {$catlink = trailingslashit(get_option( 'home' )) . user_trailingslashit( $query_vars['category_redirect'], 'category' );status_header(301);header("Location: $catlink");exit();}return $query_vars;}
function seo_settings_validate($input) {if($input['no_cat_base']==0) {moz_category_base_deactivate();}global $wp_rewrite;$wp_rewrite->flush_rules();return $input;}
function seo_settings_admin(){global $wp_rewrite;$wp_rewrite->flush_rules();include_once dirname(__FILE__) . '/moz-seo-admin.php';}
define( 'MOZ_BASENAME', plugin_basename( __FILE__ ) );define( 'MOZ_BASEFOLDER', plugin_basename( dirname( __FILE__ ) ) );define( 'MOZ_FILENAME', str_replace( MOZ_BASEFOLDER.'/', '', plugin_basename(__FILE__) ) );
function moz_filter_plugin_meta($links, $file) {if ( $file == MOZ_BASENAME ) {array_unshift($links,sprintf( '<a href="options-general.php?page=moz-seo-admin.php">Settings</a>', MOZ_FILENAME, __('Settings') ));}return $links;}global $wp_version;if ( version_compare( $wp_version, '2.8alpha', '>' ) )add_filter( 'plugin_row_meta', 'moz_filter_plugin_meta', 10, 2 );add_filter( 'plugin_action_links', 'moz_filter_plugin_meta', 10, 2 );
if(isset($moz['nofollow']) || isset($moz['nofollow_folder']) && $moz['nofollow_folder'] !==''){add_filter('wp_insert_post_data', 'save_moz_nofollow' );}
function save_moz_nofollow($content) {$content["post_content"] = preg_replace_callback('~<(a[^>]+)>~isU', "moz_replace_nofollow", $content["post_content"]);return $content;}
function moz_replace_nofollow($match) {global $moz;list($original, $tag) = $match;$my_nofollow = $moz['nofollow'];$my_folder =  $moz['nofollow_folder'];$blog_url = get_bloginfo('url');if (strpos($tag, "nofollow") || (!$my_nofollow && !strpos($tag, $blog_url)) ) {return $original;}elseif (strpos($tag, $blog_url) && (!$my_folder || !strpos($tag, $my_folder))) {return $original;} else { return "<$tag rel=\"nofollow\">";}}
function moz_customize_css(){$moz_dir = plugins_url('/img', __FILE__);?>
<style type="text/css">
.seo_score.column-seo_score {padding-top:7px !important;}
.column-seo_score span {padding:5px; display:inline-block; -moz-border-radius:10px;border-radius:10px;background:#ddd; -webkit-box-shadow: 1px 2px #fff, -1px -1px #777; padding:6px 12px; margin-top:2px;font-weight:normal;text-shadow:0 1px #fff; color:#333;}
.column-seo_score span.seo-low {background:#ddd url(<?php echo $moz_dir ?>/alert-icon.png) no-repeat 4px center; padding:6px 0 6px 28px;font-weight:bold;width:25px;}
.column-seo_score span.seo-rocks {background:#ddd url(<?php echo $moz_dir ?>/thumbs-up.png) no-repeat 5px center; padding:6px 12px 6px 28px;font-weight:bold;}
</style>
<?php
}


function moz_add_seo_columns($posts_columns) {
	$posts_columns['seo_keyword'] = 'SEO Keyword';
	$posts_columns['seo_score'] = 'SEO Score';
	return $posts_columns;
}

function moz_add_seo_columns_pages($pages_columns) {
	$pages_columns['seo_keyword'] = 'SEO Keyword';
	$pages_columns['seo_score'] = 'SEO Score';
	return $pages_columns;
}

function moz_display_seo_columns($column_name, $post_id) {
	if ('seo_keyword' == $column_name) {
		if(get_post_meta($post_id, '_moz_keyword', true)){
			echo get_post_meta($post_id, '_moz_keyword', true);
		} else {
			echo "(Not Set)";
		}
	}
	if ('seo_score' == $column_name) {
		if(get_post_meta($post_id, '_moz_rockScore', true) < 0 || get_post_meta($post_id, '_moz_rockScore', true) =="") 			{
			echo "(Not Set)";
		} else {
			$theScore = get_post_meta($post_id, '_moz_rockScore', true);
			if($theScore <= 29) {
				$className = " class='seo-low'";
			} elseif($theScore >= 30 && $theScore <= 69) {
				$className = " class='seo-med'";
			} elseif($theScore >= 70 && $theScore <= 89) {
				$className = " class='seo-high'";
			} elseif($theScore >= 90) {
				$className = " class='seo-rocks'";
			}
			echo "<span".$className.">".get_post_meta($post_id, '_moz_rockScore', true)."</span>";
		}       
	}
}


function moz_sanitizeOLD($s) {
	$result = preg_replace("/[^a-zA-Z0-9]+/", "", $s);
	return $result;
}

function moz_sanitize($s) {
    $result = preg_replace("/[^a-zA-Z0-9'-]+/", "", html_entity_decode($s, ENT_QUOTES));
//    $result = preg_replace("/[^a-zA-Z0-9]+/", "", html_entity_decode($s, ENT_QUOTES));
    return $result;
}

function moz_sanitize2($s) {
    $result = preg_replace("/[^a-zA-Z0-9'-]+/", " ", html_entity_decode($s, ENT_QUOTES));
//  $result = preg_replace("/[^a-zA-Z0-9]+/", " ", html_entity_decode($s, ENT_QUOTES));
    return $result;
}

function moz_getKeyword($post) {
	$myKeyword = get_post_meta($post->ID, '_moz_keyword', true);
	if($myKeyword == "") $myKeyword = $post->post_title;
	$myKeyword = moz_sanitize2($myKeyword);
	return " ".$myKeyword;
}

function moz_keyword_density($post) {
	$word_count =  moz_word_count($post);
	$keyword_count = moz_keyword_count($post);
	$density = ($keyword_count / $word_count) * 100;
	$density = number_format($density, 1);
	return $density;
}

function moz_keyword_count($post) {
	$text = strip_tags($post->post_content);
	$keyword = trim(moz_getKeyword($post));
	$keyword = moz_sanitize2($keyword);
	$keyword_count = preg_match_all("#{$keyword}#si", $text, $matches);
	return $keyword_count;
}

function moz_word_count($post) {
	$text = strip_tags($post->post_content);
	$word_count = explode(' ', $text);
	$word_count = count($word_count);
	return $word_count;
}

function moz_get_kw_first_sentence($post) {
	$theContent = moz_sanitize_string( strip_tags(strtolower($post->post_content)) );
	$theKeyword = moz_sanitize_string( trim(strtolower(moz_getKeyword($post))) );
	$theKeyword = moz_sanitize2($theKeyword);
    $thePiecesByKeyword = moz_get_chunk_keyword($theKeyword,$theContent);
    if (count($thePiecesByKeyword) > 0) {
		$myPieceIndex = $thePiecesByKeyword[0];
		if (substr_count($myPieceIndex,'.') > 0) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	return FALSE;
}

function moz_get_chunk_keyword($theKeyword, $theContent) {
	if (!moz_get_kw_in_content($theKeyword,$theContent)) {
		return array();
	}
	
	$myPieceReturn = preg_split('/\b' . $theKeyword . '\b/i', $theContent);
	return $myPieceReturn;
}
        
function moz_get_kw_in_content($theKeyword, $theContent) {
	$theKeyword = preg_quote($theKeyword, '/');
	return preg_match('/\b' . $theKeyword . '\b/i', $theContent);
}

function moz_get_kw_last_sentence($post) {
	$theContent = moz_sanitize_string( strip_tags(strtolower($post->post_content)) );
	$theKeyword = moz_sanitize_string( trim(strtolower(moz_getKeyword($post))) );
	$theKeyword = moz_sanitize2($theKeyword);
    $needle = '/' . $theKeyword . '[^.!?]*[.!?][^.!?]*$/';
    $haystack = strip_tags(strtolower($theContent));
    return preg_match($needle, $haystack);
}

function moz_get_has_internal_link($thePost) {
	$theContent = $thePost->post_content;
	$myVar1 = array();
	preg_match_all('/<a\s[^>]*href=\"([^\"]*)\"[^>]*>(.*)<\/a>/siU',$theContent,$myVar1);
	$myVar2 = 0;
	foreach ($myVar1[1] as $myVar3) {
		$myVar4 = $myVar1[2][$myVar2];
		$myVar5 = FALSE;
		$theSiteURL = get_bloginfo('wpurl');
		$theSiteURLwithoutWWW = str_replace('http://www.','',$myVar3);
		$theSiteURLwithoutWWW = str_replace('http://','',$theSiteURLwithoutWWW);
		$theSiteURLwithoutWWW2 = str_replace('http://www.','',$theSiteURL);
		$theSiteURLwithoutWWW2 = str_replace('http://','',$theSiteURLwithoutWWW2);
		if (strpos($myVar3,'http://')!==0 || strpos($theSiteURLwithoutWWW,$theSiteURLwithoutWWW2)===0) {
			return TRUE;
		}
		$myVar2++;
	}
	return FALSE;
}

function moz_get_kw_title($post) {
	if($post->post_title == "") {
		return false;
	}
	$haystack = moz_sanitize($post->post_title);
	$needle = moz_sanitize(moz_getKeyword($post));
	$pos = stripos($haystack, $needle);
	if ($pos !== false) {
		return true;
	}
}

function moz_save_moz_keyword($postID, $post) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $postID;
	} else {
		global $moz;
		if($parent_id = wp_is_post_revision($postID)) {
			$postID = $parent_id;$post = get_post($postID);
		}
		$rockScore = 0;
		$demerit = 11;
		if(isset($moz['kwd_low']) && $moz['kwd_low'] !=='') $moz_densityLow = $moz['kwd_low']; else $moz_densityLow = 1.0;
		if(isset($moz['kwd_high']) && $moz['kwd_high'] !=='') $moz_densityHigh = $moz['kwd_high']; else $moz_densityHigh = 4.0;
		if(moz_keyword_density($post) >= $moz_densityLow && moz_keyword_density($post) <= $moz_densityHigh) {$rockScore+=10;$demerit-=1;}

		if(moz_get_kw_title($post)) {
			$rockScore+=10;
		} else {
			$demerit+=1;$demerit-=1;
		}
		if($moz['theme_h1']==1 || moz_get_seo('h1', $post) || function_exists('ce3_admin') || function_exists('ce4_admin')) {
			$rockScore+=10;$demerit-=1;
		}
		if($moz['theme_h2']==1 || moz_get_seo('h2', $post)) {
			$rockScore+=10;$demerit-=1;
		}
		if($moz['theme_h3']==1 || moz_get_seo('h3', $post)) {
			$rockScore+=10;$demerit-=1;
		}
		if(moz_get_seo('b', $post) OR moz_get_seo('strong', $post)) {
			$rockScore+=10;$demerit-=1;
		}
		if($moz['theme_img']==1 || moz_get_seo('img-alt', $post)) {
			$rockScore+=10;$demerit-=1;
		}
		if(moz_get_kw_first_sentence($post)) {
			$rockScore+=10;$demerit-=1;
		}
		if(moz_get_kw_last_sentence($post)) {
			$rockScore+=10;$demerit-=1;
		}
		if(moz_get_has_internal_link($post)) {
			$rockScore+=10;$demerit-=1;
		}
		if(moz_word_count($post) >= 299 ) {
			$demerit-=1;
		} else {
			if ($rockScore > 20) {
				$rockScore-=20;
			}
		}
		$rockScore = number_format($rockScore,0);
		moz_update_custom_meta($postID, $rockScore, '_moz_rockScore');
		moz_update_custom_meta($postID, $_POST['moz_keyword'], '_moz_keyword');
	}
}


function moz_get_seo($check, $post) {
	switch ($check) {
		case "b": return moz_doTheParse('b', $post);
		case "strong": return moz_doTheParse('strong', $post);
		case "h1": return moz_doTheParse('h1', $post);
		case "h2": return moz_doTheParse('h2', $post);
		case "h3": return moz_doTheParse('h3', $post);
		case "img-alt": return moz_doTheParse('img-alt', $post);
	}
}

function moz_doTheParse($heading, $post) {
	$content = $post->post_content;
	if($content=="" || !class_exists('DOMDocument')) {
		return false;
	}
	$keyword = moz_sanitize_string( trim(strtolower(moz_getKeyword($post))) );
	//JSB 1-27-2011
	$keyword = moz_sanitize2($keyword);
	@$dom = new DOMDocument;
	@$dom->loadHTML(moz_sanitize_string( strtolower($content) ));
	$xPath = new DOMXPath(@$dom);
	switch ($heading) {
		case "img-alt": 
			return $xPath->evaluate('boolean(//img[contains(@alt, "'.$keyword.'")])');
		default: 
			return $xPath->evaluate('boolean(/html/body//'.$heading.'[contains(.,"'.$keyword.'")])');
	}
}

function moz_update_custom_meta($postID, $newvalue, $field_name) {
	// To create new meta
	if(!get_post_meta($postID, $field_name)) {
		add_post_meta($postID, $field_name, $newvalue);
	} else {
		// or to update existing meta
		update_post_meta($postID, $field_name, $newvalue);
	}
}

function moz_sanitize_string( $content ) {
	$regex = '/( [\x00-\x7F] | [\xC0-\xDF][\x80-\xBF] | [\xE0-\xEF][\x80-\xBF]{2} | [\xF0-\xF7][\x80-\xBF]{3} ) | ./x';
	return preg_replace($regex, '$1', $content);
}

function moz_rock_seo($post)
{
global $moz;
wp_enqueue_script('jquery');
$rockScore = 0;
$demerit=11;
$moz_dir = plugins_url('/img', __FILE__);
	?><style type='text/css'>
#moz_loader{
    display:none;
    filter:alpha(opacity=75);
    -moz-opacity:0.75;
    -khtml-opacity: 0.75;
    opacity: 0.75;
    padding:10px;
    position:absolute;
    top:0;
    left:0;
    height:1000px;
    width:260px;
    background:#fff url(<?php echo $moz_dir ?>/loader.gif) no-repeat 170px 52px;
    
}

.moz-button {
    height:24px;
    position:absolute;
    top:47px;
    right:16px;
    border: 1px solid #8ec1da;
    background-color: #ddeef6;
    border-radius: 4px;
    box-shadow: inset 0 1px 3px #fff, inset 0 -12px #ddd, 0 0 3px #8ec1da;
    -o-box-shadow: inset 0 1px 3px #fff, inset 0 -12px #ddd, 0 0 3px #8ec1da;
    -webkit-box-shadow: inset 0 1px 3px #fff, inset 0 -12px #ddd, 0 0 3px #8ec1da;
    -moz-box-shadow: inset 0 1px 3px #fff, inset 0 -12px #ddd, 0 0 3px #8ec1da;
    color: #3985a8;
    text-shadow: 0 1px #fff;
    padding: 5px 10px;
    
}

#moz_related, #moz_related_keywords {
    display:none;
    margin:-13px -2px 10px -2px;
    
}

.moz_related_kw p {
    font-style:italic;
    margin:2px 0 20px 0 !important;
    
}

.moz_related_kw span {
    background:#ccc;
    border-radius:5px;
    -moz-border-radius:5px;
    -webkit-border-radius:5px;
    padding:3px 4px;
    display:inline-block;
    text-align:left;
    margin:0 -3px 5px 0;
    white-space:nowrap !important;
    
}

.inside.rock-seo li {
    background:url(<?php echo $moz_dir ?>/thumbs-up.png) no-repeat;
    padding:0 0 10px 25px;
    margin-left:0;
    color:#333;
    
}

.inside.rock-seo li.alert-on {
    color:#333;
    background:url(<?php echo $moz_dir ?>/alert-icon.png) no-repeat;
    
}

.inside.rock-seo li.alert-off {
    background:none;
    
}

.inside.rock-seo li span {
    font-size:1.5em;
    font-weight:bold;
    color:#fff;
    text-shadow: 1px 1px 4px#000;
    background:#32b10f;
    background: -webkit-gradient(linear, left bottom, left top, from(#fff), to(#32b10f));
    background: -moz-linear-gradient(bottom,#fff,#32b10f);
    filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#32b10f', endColorstr='#ffffff');
    padding:10px 12px 13px 12px;
    -moz-border-radius: 10px 10px 0 0;
    border-radius: 10px 10px 0 0;
    border:1px solid #888;
    border-bottom:none;
    
}

.inside.rock-seo li span.alert-on {
    font-size:1.2em;
    font-weight:bold;
    color:#333;
    text-shadow:0 1px #fff;
    background:#fed201;
    background: -webkit-gradient(linear, left bottom, left top, from(#fff), to(#fed201));
    background: -moz-linear-gradient(bottom,  #fff,  #fed201);
    filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#fed201', endColorstr='#ffffff');
    
}

.inside.rock-seo li {
    font-size:.95em !important;
    
}

.rockScore {
    border-radius:5px;
    -moz-border-radius:5px;
    -webkit-border-radius:5px;
    -moz-border-radius-topleft:0;
    -moz-border-radius-topright:0;
    border-top-right-radius:0;
    border-top-left-radius:0;
    color:#333;
    font-size:1.25em;
    font-weight:bold;
    padding:20px 15px 17px 10px;
    margin:0 -20px -9px -20px;
    text-shadow: 0 1px #fff;
    white-space:nowrap;
    background: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#cccccc));
    background: -moz-linear-gradient(top,  #fff,  #cccccc);
    filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#cccccc');
    
}

.rockScore .score {
    border:1px solid #888;
    border-top:none;
    font-size:1.5em;
    font-style:italic;
    color:#fff;
    text-shadow: -1px -1px #333;
    border-radius:10px;
    -moz-border-radius:10px;
    -webkit-border-radius:10px;
    -moz-border-radius-topleft:0;
    -moz-border-radius-topright:0;
    border-top-right-radius:0;
    border-top-left-radius:0;
    display:inline-block;
    margin:-20px -5px -10px 0;
    !important;
    padding:10px 10px 10px 10px;
    vertical-align:bottom;
    
}

.rockScore .rating {
    margin-left:10px;
    
}

#rock-seo {
    overflow:hidden;
}

.rockScore.red  .score {
    padding-left:7px;
    padding-right:7px;
    background:#fdb700;
    background: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#fdb700));
    background: -moz-linear-gradient(top,  #fff,  #fdb700);
    filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#fdb700');
    
}

.rockScore.yellow .score {
    background:#dab404;
    background: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#dab404));
    background: -moz-linear-gradient(top,  #fff,  #dab404);
    filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#dab404');
    
}

.rockScore.green .score {
    background:green;
    background: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#047e00));
    background: -moz-linear-gradient(top,  #fff,  #047e00);
    filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#047e00');
    
}

.text-box {
    color:#555;
    font-weight:normal;
    padding:6px 25px 17px 30px;
    margin:-6px -20px 0 -20px;
    text-shadow: 0 1px #fff;
    white-space:nowrap;
    background: -webkit-gradient(linear, left top, left bottom, from(#ebebeb), to(#ffffff));
    background: -moz-linear-gradient(top,#ebebeb,#ffffff);
    filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#ebebeb', endColorstr='#ffffff');
    text-indent:-13px;
    
}

#moz_keyword {
    height:24px;
//    padding-right:80px;
    margin-top:5px;
    width:250px;
    margin-left:-14px;
    
}

.moz_related_kw h4 {
    font-weight:normal;
    margin:-5px 0 7px 0 !important
}

.moz_related_kw span.highlight{
    background:green;
    color:#fff;
    
}

.inside.rock-seo {background:#fff; padding:0 20px !important; margin:0 -10px !important;}
#moz_get_related_keywords {margin:-18px -3px 0 0;}
	</style>
	<?php
	echo "<div class='inside rock-seo'><div id='moz_loader'>&nbsp;</div>";
	echo "<div class='text-box'>Primary Keyword Phrase:";
	echo "<br><input type='text' name='moz_keyword' id='moz_keyword' value='".trim(moz_getKeyword($post))."'></div>";
	echo "<div id='moz_related_keywords'>Related Keywords:</div>";
	echo "<div><input type='button' class='moz-button' value='Get LSI' title='Get Related Keywords (Latent Semantic Indexing)' id='moz_get_related_keywords' /></div><div id='moz_related'></div>";
	echo "<ul>";
	$kwDensity = moz_keyword_density($post);
	if(isset($moz['kwd_low']) && $moz['kwd_low'] !=='') $moz_densityLow = $moz['kwd_low']; else $moz_densityLow = 1.0;
	if(isset($moz['kwd_high']) && $moz['kwd_high'] !=='') $moz_densityHigh = $moz['kwd_high']; else $moz_densityHigh = 4.0;
	if($kwDensity < $moz_densityLow) echo "<li class='alert-on' title='Keyword density should be between ".$moz_densityLow."-".$moz_densityHigh."%'><b>Low</b> Keyword Density: <span class='alert-on'>".$kwDensity. "%</span></li>";
	elseif($kwDensity > $moz_densityHigh) echo "<li class='alert-on' title='Keyword density should be between 1-4%'><b>High</b> Keyword Density: <span class='alert-on'>".$kwDensity. "%</span></li>";
	else { $rockScore+=10;$demerit-=1; echo "<li>Keyword Density: <span>".$kwDensity."%</span></li>";}
	if(moz_get_kw_title($post) < 1) { echo "<li class='alert-on'>SEO Suggests: <b>Add keyword phrase</b> to post title</li>";} else {$rockScore+=10;$demerit-=1; echo "<li>Keyword phrase in post title!</li>";}
	if($moz['theme_h1']==1 || function_exists('ce3_admin') || function_exists('ce4_admin')){ $moz_theme_h1_text = " found in active theme";}
	if($moz['theme_h1']==1 || moz_get_seo('h1', $post) || function_exists('ce3_admin') || function_exists('ce4_admin')) {$rockScore+=10;$demerit-=1; echo "<li>H1 heading with keyword phrase".$moz_theme_h1_text."!</li>";} else { echo "<li class='alert-on'>SEO Suggests: <b>Add an H1 heading</b> containing your keyword phrase</li>";}	
	if($moz['theme_h2']==1 ){ $moz_theme_h2_text = " found in active theme";}
	if($moz['theme_h2']==1 || moz_get_seo('h2', $post)) {$rockScore+=10;$demerit-=1; echo "<li>H2 heading with keyword phrase".$moz_theme_h2_text."!</li>";} else { echo "<li class='alert-on'>SEO Suggests: <b>Add an H2 heading</b> containing your keyword phrase</li>";}
	if($moz['theme_h3']==1 ){ $moz_theme_h3_text = " found in active theme";}
	if($moz['theme_h3']==1 || moz_get_seo('h3', $post)) {$rockScore+=10;$demerit-=1; echo "<li>H3 heading with keyword phrase".$moz_theme_h3_text."!</li>";} else { echo "<li class='alert-on'>SEO Suggests: <b>Add an H3 heading</b> containing your keyword phrase</li>";}
	if(moz_get_seo('b', $post) OR moz_get_seo('strong', $post)) { $rockScore+=10;$demerit-=1; echo "<li>Keyword Phrase in bold/strong tag found!</li>";} else {echo "<li class='alert-on'>SEO Suggests: <b>highlight your primary keyword phrase with boldface or strong</b> near the top of your content</li>";}
	if($moz['theme_img']==1 ) $moz_theme_img_text = " found in active theme";
	if($moz['theme_img']==1 || moz_get_seo('img-alt', $post)) { $rockScore+=10;$demerit-=1; echo "<li>Image with keyword in alt text".$moz_theme_img_text."!</li>";} else {echo "<li class='alert-on'>SEO Suggests: Add an image with keyword phrase in <b>alt text</b></li>";}
	if(moz_get_kw_first_sentence($post) < 1) { echo "<li class='alert-on'>SEO Suggests: Add Keyword phrase to <b>first sentence</b></li>";} else {$rockScore+=10;$demerit-=1; echo "<li>Keyword phrase in first sentence!</li>";}
	if(moz_get_kw_last_sentence($post) < 1) { echo "<li class='alert-on'>SEO Suggests: Add Keyword phrase to <b>last sentence</b></li>";} else {$rockScore+=10;$demerit-=1; echo "<li>Keyword phrase in last sentence!</li>";}
	if(moz_get_has_internal_link($post) < 1) { echo "<li class='alert-on'>SEO Suggests: Add an <b>internal link</b> near to the top of your content.</li>";} else {$rockScore+=10;$demerit-=1; echo "<li>Internal link found!</li>";}
	if(moz_word_count($post) < 299 ) {echo "<li class='alert-on' title='Google likes longer pages of at least 300 words'>SEO Suggests: <b>Add</b> More Words!</li>"; if ($rockScore > 20) $rockScore-=20; } else {$demerit-=1; echo "<li>Post word count: ".moz_word_count($post)."</li>";}
	if(!$moz['nofollow']) { echo "<li class='alert-on'>SEO Can automatically add rel='nofollow' to your external links. Check SEO Settings to activate this feature</li>";} else {echo "<li>SEO is applying nofollow to external links in this post content</li>";}

	echo "</ul>";
	$rockScore = number_format($rockScore,0);
	if($rockScore <= 29) echo "<div class='rockScore red'>SEO Score: <span class='score'>".$rockScore."</span><span class='rating'>(Needs Work!)</span></div>";
	else if($rockScore >= 30 && $rockScore <= 69) echo "<div class='rockScore yellow'>SEO Score: <span class='score'>".$rockScore."</span><span class='rating'>(Not Bad!)</span></div>";
	else if($rockScore >= 70 && $rockScore < 90) echo "<div class='rockScore green'>SEO Score: <span class='score'>".$rockScore."</span><span class='rating'>(Sweet!)</span></div>";
	else if($rockScore >= 90) echo "<div class='rockScore green'>SEO Score: <span class='score'>".$rockScore."</span><span class='rating'>You Rock!</span></div>";
	else echo "<div class='rockScore yellow'>Temp SEO Score: <span class='score'>".$rockScore."</span></div>";
	echo "</div>";
	?>
	<script type="text/javascript">jQuery('#moz_get_related_keywords').click(function(){

		var AppKeyLSI = <?php if ( isset($moz['lsi_key']) && $moz['lsi_key'] !==''){echo "'".$moz['lsi_key']."';";} else {echo "'123A6DEC5173AA27B230F59CEAF6B08EDFDF36A3';";} ?>


		if (jQuery('#moz_keyword').val() == '') return false;jQuery('#moz_loader').show();var result = '<div class="moz_related_kw"><h4>Top Related Semantic Keywords:</h4>';
		jQuery.ajax({contentType: "application/json; charset=utf-8",dataType: "json",

		url: "http://api.bing.net/json.aspx?AppId="+AppKeyLSI+"&Version=2.2&Query="+jQuery('#moz_keyword').val()+"&Sources=RelatedSearch&JsonType=callback&JsonCallback=?",
			
		success: function(data){
			
			if(data['SearchResponse']['RelatedSearch'] === undefined) this.error('No results returned for this search term');jQuery('#moz_loader').hide();

				keywords = data['SearchResponse']['RelatedSearch']['Results'];

				for (var i = 0; i < keywords.length; i++)
				{
					//kw = keywords[i].Title.toLowerCase();
					kw = keywords[i].Title;
					result += '<span>' + kw + '</span>, ';
					if(i >= (50 - 1)) break;
				}

				result += '<p style="color:green"><b>Items in green are already in your content. Great work!</b></div></div>';
				jQuery('#moz_related').html( result );
				jQuery('#moz_related').show();
				jQuery('#moz_loader').hide();
	
		/* new LSI highlighting function */
		var html = jQuery('#content').html().toLowerCase();

		jQuery(".moz_related_kw").find("span").filter(function() {
			var myKW = jQuery(this).html().toLowerCase();
			return html.indexOf(myKW) != -1;
			}).each(function() {
				jQuery(this).addClass('highlight');
			});
		},error: function(errorThrown){result += '<span>&nbsp;'+errorThrown+'&nbsp;</span></div>';jQuery('#moz_related').html( result );jQuery('#moz_related').show();jQuery('#moz_loader').hide();}});return false;});
	</script>
	<?php	}
register_activation_hook(__FILE__, 'moz_post_options_box');
?>