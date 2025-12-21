<?php

if ( ! class_exists( 'FooGallery_Import_View_Helper' ) ) {

	/**
	 * Class FooGallery_Import_View_Helper
	 */
	class FooGallery_Import_View_Helper {
		public function render_import_form() {
			?>
			<style>
				#foogallery_import_form textarea {
					width: 100%;
					height: 200px;
				}
				.foogallery_gallery_import_results {
					display: none;
					margin-top: 10px;
					padding: 10px;
					background: #fff;
					border: 1px solid #ccd0d4;
					max-height: 300px;
					overflow: auto;
				}
				.foogallery_gallery_import_progress_wrap {
					display: none;
					margin-top: 10px;
				}
				.foogallery_gallery_import_progress {
					width: 100%;
				}
			</style>
			<script>
				jQuery(function ($) {
					var currentJobId = null;
					var pollTimer = null;

					function getNonce() {
						return $('#foogallery_gallery_import').val();
					}

					function setSpinner(active) {
						$('.foogallery_import_spinner').toggleClass('is-active', !!active);
					}

					function showProgress(show) {
						$('.foogallery_gallery_import_progress_wrap').toggle(!!show);
					}

					function setProgress(percent, done, total, stage) {
						var pct = parseInt(percent || 0, 10);
						if (isNaN(pct)) pct = 0;
						$('.foogallery_gallery_import_progress').val(pct);

						var text = pct + '%';
						if (typeof done !== 'undefined' && typeof total !== 'undefined') {
							text += ' (' + done + '/' + total + ')';
						}
						if (stage) {
							text += ' - ' + stage;
						}
						$('.foogallery_gallery_import_progress_text').text(text);
					}

					function appendMessage(msg) {
						if (!msg) return;
						var $results = $('.foogallery_gallery_import_results');
						$results.show();
						$results.append($('<div/>').text(msg));
						$results.scrollTop($results[0].scrollHeight);
					}

					function clearMessages() {
						$('.foogallery_gallery_import_results').empty().hide();
					}

					function setControls(state) {
						$('.foogallery_gallery_import_start').prop('disabled', !!state.running);
						$('.foogallery_gallery_import_resume').toggle(!state.running && !!state.canResume);
						$('.foogallery_gallery_import_delete').toggle(!state.running && !!state.canDelete);
					}

					function stopPolling() {
						if (pollTimer) {
							clearTimeout(pollTimer);
							pollTimer = null;
						}
					}

					function request(mode, extra, onSuccess) {
						var data = $.extend({
							action: 'foogallery_gallery_import',
							mode: mode,
							'_wpnonce': getNonce()
						}, extra || {});

						$.ajax({
							type: "POST",
							url: ajaxurl,
							dataType: 'json',
							data: data,
							success: function(resp) {
								if (!resp || resp.success !== true) {
									var message = (resp && resp.data && resp.data.message) ? resp.data.message : '<?php echo esc_js( __( 'Something went wrong with the import!', 'foogallery' ) ); ?>';
									appendMessage(message);
									setSpinner(false);
									setControls({ running: false, canResume: !!currentJobId, canDelete: !!currentJobId });
									return;
								}
								onSuccess(resp.data || {});
							},
							error: function(xhr) {
								var message = '<?php echo esc_js( __( 'Something went wrong with the import!', 'foogallery' ) ); ?>';
								if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
									message = xhr.responseJSON.data.message;
								} else if (xhr && xhr.responseText) {
									try {
										var parsed = JSON.parse(xhr.responseText);
										if (parsed && parsed.data && parsed.data.message) {
											message = parsed.data.message;
										}
									} catch (e) {}
								}
								appendMessage(message);
								setSpinner(false);
								setControls({ running: false, canResume: !!currentJobId, canDelete: !!currentJobId });
							}
						});
					}

					function handleProgress(data) {
						if (data.job_id) {
							currentJobId = data.job_id;
						}
						if (typeof data.percent !== 'undefined') {
							showProgress(true);
							setProgress(data.percent, data.done, data.total, data.stage);
						}
						if (data.message) {
							appendMessage(data.message);
						}
					}

					function pollStep() {
						stopPolling();
						if (!currentJobId) return;

						request('step', { job_id: currentJobId }, function(data) {
							handleProgress(data);
							if (data.complete) {
								setSpinner(false);
								setControls({ running: false, canResume: false, canDelete: true });
								return;
							}
							pollTimer = setTimeout(pollStep, 350);
						});
					}

					function startImport() {
						stopPolling();
						clearMessages();
						showProgress(true);
						setProgress(0, 0, 0, '');
						setSpinner(true);
						setControls({ running: true, canResume: false, canDelete: false });

						request('start', { data: $('#foogallery_import_form textarea').val() }, function(data) {
							handleProgress(data);
							pollTimer = setTimeout(pollStep, 350);
						});
					}

					function resumeImport() {
						stopPolling();
						if (!currentJobId) return;
						setSpinner(true);
						setControls({ running: true, canResume: false, canDelete: false });
						pollTimer = setTimeout(pollStep, 10);
					}

					function deleteImportJob() {
						stopPolling();
						if (!currentJobId) return;
						setSpinner(true);

						request('delete', { job_id: currentJobId }, function(data) {
							currentJobId = null;
							setSpinner(false);
							showProgress(false);
							clearMessages();
							setControls({ running: false, canResume: false, canDelete: false });
							if (data && data.deleted) {
								appendMessage('<?php echo esc_js( __( 'Import job deleted.', 'foogallery' ) ); ?>');
							}
						});
					}

					function checkStatus() {
						request('status', {}, function(data) {
							if (!data || !data.has_job) {
								setControls({ running: false, canResume: false, canDelete: false });
								return;
							}

							currentJobId = data.job_id || null;
							showProgress(true);
							setProgress(data.percent, data.done, data.total, data.stage);

							if (data.complete) {
								setControls({ running: false, canResume: false, canDelete: true });
							} else {
								setControls({ running: false, canResume: true, canDelete: true });
							}
						});
					}

					$('#foogallery_import_form').on('click', '.foogallery_gallery_import_start', function (e) {
						e.preventDefault();
						startImport();
					});

					$('#foogallery_import_form').on('click', '.foogallery_gallery_import_resume', function (e) {
						e.preventDefault();
						resumeImport();
					});

					$('#foogallery_import_form').on('click', '.foogallery_gallery_import_delete', function (e) {
						e.preventDefault();
						deleteImportJob();
					});

					checkStatus();
				});
			</script>
			<?php
			wp_nonce_field( 'foogallery_gallery_import', 'foogallery_gallery_import', false );
			?>
			<form id="foogallery_import_form" method="POST">
				<textarea></textarea>
				<br />
				<br />
				<input type="submit" name="foogallery_gallery_import" class="button button-primary foogallery_gallery_import_start" value="<?php echo esc_attr( __( 'Import', 'foogallery' ) ); ?>">
				<button type="button" class="button foogallery_gallery_import_resume" style="display: none;"><?php echo esc_html( __( 'Resume last import', 'foogallery' ) ); ?></button>
				<button type="button" class="button foogallery_gallery_import_delete" style="display: none;"><?php echo esc_html( __( 'Delete import job', 'foogallery' ) ); ?></button>
				<span class="foogallery_import_spinner spinner" style="float: none"></span>
				<div class="foogallery_gallery_import_progress_wrap">
					<progress class="foogallery_gallery_import_progress" value="0" max="100"></progress>
					<div class="foogallery_gallery_import_progress_text"></div>
				</div>
				<div class="foogallery_gallery_import_results"></div>
			</form>
			<?php
		}
	}
}
