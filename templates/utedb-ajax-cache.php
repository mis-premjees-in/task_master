<?php
	$rdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $rdata)));
	$jdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $jdata)));
?>
<script>
	$j(function() {
		var tn = 'utedb';

		/* data for selected record, or defaults if none is selected */
		var data = {
			utedb_madb: <?php echo json_encode(['id' => $rdata['utedb_madb'], 'value' => $rdata['utedb_madb'], 'text' => $jdata['utedb_madb']]); ?>,
			utedb_whos_id: <?php echo json_encode(['id' => $rdata['utedb_whos_id'], 'value' => $rdata['utedb_whos_id'], 'text' => $jdata['utedb_whos_id']]); ?>,
			utedb_premises_id: <?php echo json_encode(['id' => $rdata['utedb_premises_id'], 'value' => $rdata['utedb_premises_id'], 'text' => $jdata['utedb_premises_id']]); ?>
		};

		/* initialize or continue using AppGini.cache for the current table */
		AppGini.cache = AppGini.cache || {};
		AppGini.cache[tn] = AppGini.cache[tn] || AppGini.ajaxCache();
		var cache = AppGini.cache[tn];

		/* saved value for utedb_madb */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'utedb_madb' && d.id == data.utedb_madb.id)
				return { results: [ data.utedb_madb ], more: false, elapsed: 0.01 };
			return false;
		});

		/* saved value for utedb_whos_id */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'utedb_whos_id' && d.id == data.utedb_whos_id.id)
				return { results: [ data.utedb_whos_id ], more: false, elapsed: 0.01 };
			return false;
		});

		/* saved value for utedb_premises_id */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'utedb_premises_id' && d.id == data.utedb_premises_id.id)
				return { results: [ data.utedb_premises_id ], more: false, elapsed: 0.01 };
			return false;
		});

		cache.start();
	});
</script>

