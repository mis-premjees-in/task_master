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
			utedb_madb_who1: <?php echo json_encode(['id' => $rdata['utedb_madb_who1'], 'value' => $rdata['utedb_madb_who1'], 'text' => $jdata['utedb_madb_who1']]); ?>,
			utedb_premises_id: <?php echo json_encode($jdata['utedb_premises_id']); ?>,
			utedb_elairda_id: <?php echo json_encode(['id' => $rdata['utedb_elairda_id'], 'value' => $rdata['utedb_elairda_id'], 'text' => $jdata['utedb_elairda_id']]); ?>
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

		/* saved value for utedb_madb autofills */
		cache.addCheck(function(u, d) {
			if(u != tn + '_autofill.php') return false;

			for(var rnd in d) if(rnd.match(/^rnd/)) break;

			if(d.mfk == 'utedb_madb' && d.id == data.utedb_madb.id) {
				$j('#utedb_premises_id' + d[rnd]).html(data.utedb_premises_id);
				return true;
			}

			return false;
		});

		/* saved value for utedb_madb_who1 */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'utedb_madb_who1' && d.id == data.utedb_madb_who1.id)
				return { results: [ data.utedb_madb_who1 ], more: false, elapsed: 0.01 };
			return false;
		});

		/* saved value for utedb_elairda_id */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'utedb_elairda_id' && d.id == data.utedb_elairda_id.id)
				return { results: [ data.utedb_elairda_id ], more: false, elapsed: 0.01 };
			return false;
		});

		cache.start();
	});
</script>

