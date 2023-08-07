<style type="text/css">
#cssload-loader {
	margin: auto;
	left: 0;
	right: 0;
	width: 88px;
	margin-top: 70px;
}
#cssload-loader ul {
	margin: 0;
	list-style: none;
	width: 88px;
	height: 63px;
	position: relative;
	padding: 0;
	height: 10px;
}
#cssload-loader ul li {
	position: absolute;
	width: 2px;
	height: 0;
	background-color: rgb(0,0,0);
	bottom: 0;
}


#cssload-loader li:nth-child(1) {
	left: 0;
	animation: cssload-sequence1 1.15s ease infinite 0;
		-o-animation: cssload-sequence1 1.15s ease infinite 0;
		-ms-animation: cssload-sequence1 1.15s ease infinite 0;
		-webkit-animation: cssload-sequence1 1.15s ease infinite 0;
		-moz-animation: cssload-sequence1 1.15s ease infinite 0;
}
#cssload-loader li:nth-child(2) {
	left: 15px;
	animation: cssload-sequence2 1.15s ease infinite 0.12s;
		-o-animation: cssload-sequence2 1.15s ease infinite 0.12s;
		-ms-animation: cssload-sequence2 1.15s ease infinite 0.12s;
		-webkit-animation: cssload-sequence2 1.15s ease infinite 0.12s;
		-moz-animation: cssload-sequence2 1.15s ease infinite 0.12s;
}
#cssload-loader li:nth-child(3) {
	left: 29px;
	animation: cssload-sequence1 1.15s ease-in-out infinite 0.23s;
		-o-animation: cssload-sequence1 1.15s ease-in-out infinite 0.23s;
		-ms-animation: cssload-sequence1 1.15s ease-in-out infinite 0.23s;
		-webkit-animation: cssload-sequence1 1.15s ease-in-out infinite 0.23s;
		-moz-animation: cssload-sequence1 1.15s ease-in-out infinite 0.23s;
}
#cssload-loader li:nth-child(4) {
	left: 44px;
	animation: cssload-sequence2 1.15s ease-in infinite 0.35s;
		-o-animation: cssload-sequence2 1.15s ease-in infinite 0.35s;
		-ms-animation: cssload-sequence2 1.15s ease-in infinite 0.35s;
		-webkit-animation: cssload-sequence2 1.15s ease-in infinite 0.35s;
		-moz-animation: cssload-sequence2 1.15s ease-in infinite 0.35s;
}
#cssload-loader li:nth-child(5) {
	left: 58px;
	animation: cssload-sequence1 1.15s ease-in-out infinite 0.46s;
		-o-animation: cssload-sequence1 1.15s ease-in-out infinite 0.46s;
		-ms-animation: cssload-sequence1 1.15s ease-in-out infinite 0.46s;
		-webkit-animation: cssload-sequence1 1.15s ease-in-out infinite 0.46s;
		-moz-animation: cssload-sequence1 1.15s ease-in-out infinite 0.46s;
}
#cssload-loader li:nth-child(6) {
	left: 73px;
	animation: cssload-sequence2 1.15s ease infinite 0.58s;
		-o-animation: cssload-sequence2 1.15s ease infinite 0.58s;
		-ms-animation: cssload-sequence2 1.15s ease infinite 0.58s;
		-webkit-animation: cssload-sequence2 1.15s ease infinite 0.58s;
		-moz-animation: cssload-sequence2 1.15s ease infinite 0.58s;
}


@keyframes cssload-sequence1 {
	0% {
		height: 10px;
	}
	50% {
		height: 49px;
	}
	100% {
		height: 10px;
	}
}

@-o-keyframes cssload-sequence1 {
	0% {
		height: 10px;
	}
	50% {
		height: 49px;
	}
	100% {
		height: 10px;
	}
}

@-ms-keyframes cssload-sequence1 {
	0% {
		height: 10px;
	}
	50% {
		height: 49px;
	}
	100% {
		height: 10px;
	}
}

@-webkit-keyframes cssload-sequence1 {
	0% {
		height: 10px;
	}
	50% {
		height: 49px;
	}
	100% {
		height: 10px;
	}
}

@-moz-keyframes cssload-sequence1 {
	0% {
		height: 10px;
	}
	50% {
		height: 49px;
	}
	100% {
		height: 10px;
	}
}

@keyframes cssload-sequence2 {
	0% {
		height: 19px;
	}
	50% {
		height: 63px;
	}
	100% {
		height: 19px;
	}
}

@-o-keyframes cssload-sequence2 {
	0% {
		height: 19px;
	}
	50% {
		height: 63px;
	}
	100% {
		height: 19px;
	}
}

@-ms-keyframes cssload-sequence2 {
	0% {
		height: 19px;
	}
	50% {
		height: 63px;
	}
	100% {
		height: 19px;
	}
}

@-webkit-keyframes cssload-sequence2 {
	0% {
		height: 19px;
	}
	50% {
		height: 63px;
	}
	100% {
		height: 19px;
	}
}

@-moz-keyframes cssload-sequence2 {
	0% {
		height: 19px;
	}
	50% {
		height: 63px;
	}
	100% {
		height: 19px;
	}
}

#pfi-log {
	padding: 20px 10px;
	display: none;
}

input[name=pfi_feed_link] {
	width: 600px;
}
</style>

<script type="text/javascript">
	function pfiAjax() {
		pfiLog('<div id="cssload-loader"><ul><li></li><li></li><li></li><li></li><li></li><li></li></ul></div>');

		jQuery.ajax({
			type: 'get',
			url: ajaxurl,
			data: {
				'action': 'pfiAjaxImport'
			},
			dataType: 'json',
			success: function(json) {
				var err = 'Server returned empty result';
				if (json) {
					if (json.log != '') {
						pfiLog(json.log);
						return;
					}
		        }
				pfiError(err);
			},
			error: function(result) {
				pfiLog(result);
				pfiError('Server error');
			}
		});
	}

	function pfiLog(log) {
		jQuery('#pfi-log').html(log);
		jQuery('#pfi-log').fadeIn('fast');
	}

	function pfiError(err) {
		pfiLog('<br /><b style="color: red;">' + err + '</b>');
	}
</script>

<div class="wrap">
	<h1>MyCRM Importer Settings</h1>
	<form method="post" action="options.php">
	    <?php settings_fields('pfi_settings_group') ?>
	    <?php do_settings_sections('pfi_settings_group'); ?>

		<h2>General settings</h2>
		<table class="form-table">
			<tr>
				<th scope="row">Feed Link</th>
				<td><input type="text" name="pfi_feed_link" value="<?php echo esc_attr(get_option('pfi_feed_link')); ?>" placeholder="Feed link"></td>
			</tr>
		</table>

	    <div id="pfi-log" class="updated"></div>

	    <?php submit_button('Save Settings', 'primary', null, false); ?> <input type="button" class="button" value="Import Properties" onclick="pfiAjax(); return false;">
	</form>
</div>