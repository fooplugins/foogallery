<?php
$nextgen = new FooGallery_NextGen_Helper();

//if ( isset($_POST['foogallery_nextgen_import']) && isset($_POST['nextgen-id']) ) {
//
//	if ( check_admin_referer( 'foogallery_nextgen_import', 'foogallery_nextgen_import' ) ) {
//		$nextgen_gallery_ids = $_POST['nextgen-id'];
//
//		$default_timeout = ini_get( 'max_execution_time' );
//		set_time_limit( 0 );
//
//		foreach ( $nextgen_gallery_ids as $gid ) {
//			$foogallery_title = stripslashes( $_POST['foogallery-name-' . $gid] );
//			$nextgen->import_gallery( $gid, $foogallery_title );
//		}
//
//		set_time_limit( $default_timeout );
//	}
//}
?>
<style>
	.foogallery-badge-foobot {
		position: absolute;
		top: 5px;
		right: 0;
		background: url(<?php echo FOOGALLERY_URL; ?>assets/foobot_small.png) no-repeat;
		width: 82px;
		height: 150px;
		z-index: 100;
	}

	.foogallery-help {
		display: block;
		line-height: 19px;
		padding: 11px 15px 11px 5px;
		font-size: 14px;
		text-align: left;
		margin: 5px 0 20px 2px;
		background-color: #fff;
		border-left: 4px solid #1e8cbe;
		-webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
		box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
	}

	.foogallery-help:before {
		content: "\f223";
		font: 400 20px/1 dashicons !important;
		speak: none;
		color: #1e8cbe;
		display: inline-block;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
		padding-left: 5px;
		vertical-align: bottom;
	}

  .spinner-shown {
    display: block !important;
    margin: 0;
    margin-top:-10px;
    margin-left:-1px;
  }

  .nextgen-import-progress-not_started {
    color: #f00 !important;
  }

  .nextgen-import-progress-started {
    color: #f80 !important;
  }

  .nextgen-import-progress-completed {
    color: #080 !important;
  }

	.nextgen-import-progress {
		position: relative;
		padding-right:10px;
	}

	.nextgen-import-progressbar-back {
		display: inline-block;
		background: #ddd;
		height: 3px;
		position: absolute;
		bottom: 2px;
		width:100%;
	}

	.nextgen-import-progressbar {
		margin-top: 10px;
		display: inline-block;
		width:500px;
		height:10px;
		background:#ddd;
		position: relative;
	}

	.nextgen-import-progressbar span {
		position: absolute;
		height:100%;
		left:0;
		background: #888;
	}
</style>
<script>
	jQuery(function ($) {

		function nextgen_ajax(action, success_callback) {
			var data = jQuery("#nextgen_import_form").serialize();

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data + "&action=" + action,
				success: success_callback
			});
		}

	    function nextgen_import_continue() {
		    nextgen_ajax('foogallery_nextgen_import_refresh', function(data) {
			    $('#nextgen_import_form').html(data);

			    //check if we need to carry on polling
			    var percentage = $('#nextgen_import_progress').val();
			    if ( percentage < 100 ) {
				    setTimeout(nextgen_import_continue, 500);
			    }
		    });
	    }

		$('#nextgen_import_form').on('click', '.start_import', function (e) {
			e.preventDefault();

			//show the spinner
			$(this).hide();
			$('#import_spinner .spinner').show();

			nextgen_ajax('foogallery_nextgen_import', function (data) {
				$('#nextgen_import_form').html(data);
				setTimeout(nextgen_import_continue, 500);
			});
		});

		$('#nextgen_import_form').on('click', '.continue_import', function(e) {
			e.preventDefault();
			nextgen_import_continue();
		});

		$('#nextgen_import_form').on('click', '.cancel_import', function(e) {
			if (!confirm('<?php echo __('Are you sure you want to cancel?', 'foogallery'); ?>')) {
				e.preventDefault();
				return false;
			}
		});
	});
</script>
<div class="wrap about-wrap">
	<h2><?php _e( 'NextGen Gallery Importer', 'foogallery' ); ?></h2>

	<div class="foogallery-help">
		<?php _e( 'Choose the NextGen galleries you want to import into FooGallery. Please note that importing galleries with lots of images can take a while.', 'foogallery' ); ?>
	</div>
	<?php
	$galleries = $nextgen->get_galleries();
	if ( !$galleries ) {
		_e( 'There are no NextGen galleries to import!', 'foogallery' );
	} else {
		?>
		<form id="nextgen_import_form" method="POST">
			<?php $nextgen->render_import_form( $galleries ); ?>
		</form>
	<?php } ?>
</div>
