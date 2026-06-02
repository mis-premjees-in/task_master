<?php
	$rdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $rdata)));
	$jdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $jdata)));
?>
<script>
	$j(function() {
		var tn = 'premises';

		/* data for selected record, or defaults if none is selected */
		var data = {
			premises_opening: <?php echo json_encode(['id' => $rdata['premises_opening'], 'value' => $rdata['premises_opening'], 'text' => $jdata['premises_opening']]); ?>,
			premises_closing: <?php echo json_encode(['id' => $rdata['premises_closing'], 'value' => $rdata['premises_closing'], 'text' => $jdata['premises_closing']]); ?>
		};

		/* initialize or continue using AppGini.cache for the current table */
		AppGini.cache = AppGini.cache || {};
		AppGini.cache[tn] = AppGini.cache[tn] || AppGini.ajaxCache();
		var cache = AppGini.cache[tn];

		/* saved value for premises_opening */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'premises_opening' && d.id == data.premises_opening.id)
				return { results: [ data.premises_opening ], more: false, elapsed: 0.01 };
			return false;
		});

		/* saved value for premises_closing */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'premises_closing' && d.id == data.premises_closing.id)
				return { results: [ data.premises_closing ], more: false, elapsed: 0.01 };
			return false;
		});

		cache.start();
	});
</script>

