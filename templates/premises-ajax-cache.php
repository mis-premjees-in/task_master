<?php
	$rdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $rdata)));
	$jdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $jdata)));
?>
<script>
	$j(function() {
		var tn = 'premises';

		/* data for selected record, or defaults if none is selected */
		var data = {
			premjees_opening: <?php echo json_encode(['id' => $rdata['premjees_opening'], 'value' => $rdata['premjees_opening'], 'text' => $jdata['premjees_opening']]); ?>,
			premjees_closing: <?php echo json_encode(['id' => $rdata['premjees_closing'], 'value' => $rdata['premjees_closing'], 'text' => $jdata['premjees_closing']]); ?>
		};

		/* initialize or continue using AppGini.cache for the current table */
		AppGini.cache = AppGini.cache || {};
		AppGini.cache[tn] = AppGini.cache[tn] || AppGini.ajaxCache();
		var cache = AppGini.cache[tn];

		/* saved value for premjees_opening */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'premjees_opening' && d.id == data.premjees_opening.id)
				return { results: [ data.premjees_opening ], more: false, elapsed: 0.01 };
			return false;
		});

		/* saved value for premjees_closing */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'premjees_closing' && d.id == data.premjees_closing.id)
				return { results: [ data.premjees_closing ], more: false, elapsed: 0.01 };
			return false;
		});

		cache.start();
	});
</script>

