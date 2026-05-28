<?php
	$rdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $rdata)));
	$jdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $jdata)));
?>
<script>
	$j(function() {
		var tn = 'pnb';

		/* data for selected record, or defaults if none is selected */
		var data = {
			pnb_premises_id: <?php echo json_encode(['id' => $rdata['pnb_premises_id'], 'value' => $rdata['pnb_premises_id'], 'text' => $jdata['pnb_premises_id']]); ?>,
			pnb_whos_id: <?php echo json_encode(['id' => $rdata['pnb_whos_id'], 'value' => $rdata['pnb_whos_id'], 'text' => $jdata['pnb_whos_id']]); ?>,
			pnb_elairda_id: <?php echo json_encode(['id' => $rdata['pnb_elairda_id'], 'value' => $rdata['pnb_elairda_id'], 'text' => $jdata['pnb_elairda_id']]); ?>
		};

		/* initialize or continue using AppGini.cache for the current table */
		AppGini.cache = AppGini.cache || {};
		AppGini.cache[tn] = AppGini.cache[tn] || AppGini.ajaxCache();
		var cache = AppGini.cache[tn];

		/* saved value for pnb_premises_id */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'pnb_premises_id' && d.id == data.pnb_premises_id.id)
				return { results: [ data.pnb_premises_id ], more: false, elapsed: 0.01 };
			return false;
		});

		/* saved value for pnb_whos_id */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'pnb_whos_id' && d.id == data.pnb_whos_id.id)
				return { results: [ data.pnb_whos_id ], more: false, elapsed: 0.01 };
			return false;
		});

		/* saved value for pnb_elairda_id */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'pnb_elairda_id' && d.id == data.pnb_elairda_id.id)
				return { results: [ data.pnb_elairda_id ], more: false, elapsed: 0.01 };
			return false;
		});

		cache.start();
	});
</script>

