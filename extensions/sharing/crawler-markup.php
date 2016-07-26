<?php
/**
 * HTML Markup generated for crawlers
 *
 * Date: 26/07/2016
 *
 */

global $foogallery_sharing_current_share_network;
global $foogallery_sharing_current_share_info;

$meta_tags['property="og:type"'] = $foogallery_sharing_current_share_info['type'];
$meta_tags['property="og:image"'] = $foogallery_sharing_current_share_info['image'];
$meta_tags['property="og:title"'] = $foogallery_sharing_current_share_info['title'];
$meta_tags['property="og:description"'] = $foogallery_sharing_current_share_info['description'];
$meta_tags['property="og:site_name"'] = get_bloginfo();

$meta_tags = apply_filters( 'foogallery_sharing_output_meta_tags-' . $foogallery_sharing_current_share_network, $meta_tags, $foogallery_sharing_current_share_info);

$json_args['@context'] = 'http://schema.org';
$json_args['@type'] = 'ImageObject';
$json_args['author'] = foogallery_get_setting( 'sharing_author', '' );
$json_args['contentUrl"'] = $foogallery_sharing_current_share_info['image'];
$json_args['name"'] = $foogallery_sharing_current_share_info['title'];
$json_args['description"'] = $foogallery_sharing_current_share_info['description'];

$json_args = apply_filters( 'foogallery_sharing_output_json-' . $foogallery_sharing_current_share_network, $json_args, $foogallery_sharing_current_share_info);
?><!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
<head>
	<?php
	foreach ( $meta_tags as $key => $value ) {
		echo "<meta {$key} content=\"{$value}\">";
	}
	?>
<!--	<meta property="fb:app_id" content="966242223397117" />-->
<!--	<meta property="og:type" content="article" />-->
<!--	<meta property="og:image" content="http://steveush.ddns.net/wp/wp-content/uploads/2016/02/grean_ears_by_peehs.jpg" />-->
<!--	<meta property="og:title" content="Green Ears" />-->
<!--	<meta property="og:description" content="This is the description for Green Ears." />-->
<!--	<meta property="og:site_name" content="Test Site" />-->
<!--	<meta property="twitter:card" content="photo" />-->
<!--	<meta property="twitter:site" content="@dnk_dev" />-->
<!--	<meta property="twitter:image" content="http://steveush.ddns.net/wp/wp-content/uploads/2016/02/grean_ears_by_peehs.jpg" />-->
<!--	<meta property="twitter:title" content="Green Ears" />-->
<!--	<meta property="twitter:description" content="This is the description for Green Ears." />-->
</head>
<body>
<script type="application/ld+json">
	{
<?php
	foreach ( $json_args as $key => $value ) {
		echo "		\"{$key}\": \"{$value}\",
";
	} ?>
	}
</script>
</body>
</html>

