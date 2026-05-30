<?php
	$rdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $rdata)));
	$jdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $jdata)));
?>
<script>
	$j(function() {
		var tn = 'whos';

		/* data for selected record, or defaults if none is selected */
		var data = {
			whos_premise: <?php echo json_encode(['id' => $rdata['whos_premise'], 'value' => $rdata['whos_premise'], 'text' => $jdata['whos_premise']]); ?>
		};

		/* initialize or continue using AppGini.cache for the current table */
		AppGini.cache = AppGini.cache || {};
		AppGini.cache[tn] = AppGini.cache[tn] || AppGini.ajaxCache();
		var cache = AppGini.cache[tn];

		/* saved value for whos_premise */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'whos_premise' && d.id == data.whos_premise.id)
				return { results: [ data.whos_premise ], more: false, elapsed: 0.01 };
			return false;
		});

		cache.start();
	});
</script>

