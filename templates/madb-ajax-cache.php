<?php
	$rdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $rdata)));
	$jdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $jdata)));
?>
<script>
	$j(function() {
		var tn = 'madb';

		/* data for selected record, or defaults if none is selected */
		var data = {
			madb_what1: <?php echo json_encode(['id' => $rdata['madb_what1'], 'value' => $rdata['madb_what1'], 'text' => $jdata['madb_what1']]); ?>,
			madb_who1: <?php echo json_encode(['id' => $rdata['madb_who1'], 'value' => $rdata['madb_who1'], 'text' => $jdata['madb_who1']]); ?>,
			madb_when1: <?php echo json_encode(['id' => $rdata['madb_when1'], 'value' => $rdata['madb_when1'], 'text' => $jdata['madb_when1']]); ?>,
			madb_which1: <?php echo json_encode(['id' => $rdata['madb_which1'], 'value' => $rdata['madb_which1'], 'text' => $jdata['madb_which1']]); ?>,
			madb_where1: <?php echo json_encode(['id' => $rdata['madb_where1'], 'value' => $rdata['madb_where1'], 'text' => $jdata['madb_where1']]); ?>,
			madb_why1: <?php echo json_encode(['id' => $rdata['madb_why1'], 'value' => $rdata['madb_why1'], 'text' => $jdata['madb_why1']]); ?>,
			madb_why2: <?php echo json_encode($jdata['madb_why2']); ?>,
			madb_why3: <?php echo json_encode($jdata['madb_why3']); ?>,
			madb_where2: <?php echo json_encode($jdata['madb_where2']); ?>,
			madb_where3: <?php echo json_encode($jdata['madb_where3']); ?>,
			madb_which2: <?php echo json_encode($jdata['madb_which2']); ?>,
			madb_which3: <?php echo json_encode($jdata['madb_which3']); ?>,
			madb_when2: <?php echo json_encode($jdata['madb_when2']); ?>,
			madb_when3: <?php echo json_encode($jdata['madb_when3']); ?>,
			madb_who2: <?php echo json_encode($jdata['madb_who2']); ?>,
			madb_who3: <?php echo json_encode($jdata['madb_who3']); ?>,
			madb_what2: <?php echo json_encode($jdata['madb_what2']); ?>,
			madb_what3: <?php echo json_encode($jdata['madb_what3']); ?>
		};

		/* initialize or continue using AppGini.cache for the current table */
		AppGini.cache = AppGini.cache || {};
		AppGini.cache[tn] = AppGini.cache[tn] || AppGini.ajaxCache();
		var cache = AppGini.cache[tn];

		/* saved value for madb_what1 */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'madb_what1' && d.id == data.madb_what1.id)
				return { results: [ data.madb_what1 ], more: false, elapsed: 0.01 };
			return false;
		});

		/* saved value for madb_what1 autofills */
		cache.addCheck(function(u, d) {
			if(u != tn + '_autofill.php') return false;

			for(var rnd in d) if(rnd.match(/^rnd/)) break;

			if(d.mfk == 'madb_what1' && d.id == data.madb_what1.id) {
				$j('#madb_what2' + d[rnd]).html(data.madb_what2);
				$j('#madb_what3' + d[rnd]).html(data.madb_what3);
				return true;
			}

			return false;
		});

		/* saved value for madb_who1 */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'madb_who1' && d.id == data.madb_who1.id)
				return { results: [ data.madb_who1 ], more: false, elapsed: 0.01 };
			return false;
		});

		/* saved value for madb_who1 autofills */
		cache.addCheck(function(u, d) {
			if(u != tn + '_autofill.php') return false;

			for(var rnd in d) if(rnd.match(/^rnd/)) break;

			if(d.mfk == 'madb_who1' && d.id == data.madb_who1.id) {
				$j('#madb_who2' + d[rnd]).html(data.madb_who2);
				$j('#madb_who3' + d[rnd]).html(data.madb_who3);
				return true;
			}

			return false;
		});

		/* saved value for madb_when1 */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'madb_when1' && d.id == data.madb_when1.id)
				return { results: [ data.madb_when1 ], more: false, elapsed: 0.01 };
			return false;
		});

		/* saved value for madb_when1 autofills */
		cache.addCheck(function(u, d) {
			if(u != tn + '_autofill.php') return false;

			for(var rnd in d) if(rnd.match(/^rnd/)) break;

			if(d.mfk == 'madb_when1' && d.id == data.madb_when1.id) {
				$j('#madb_when2' + d[rnd]).html(data.madb_when2);
				$j('#madb_when3' + d[rnd]).html(data.madb_when3);
				return true;
			}

			return false;
		});

		/* saved value for madb_which1 */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'madb_which1' && d.id == data.madb_which1.id)
				return { results: [ data.madb_which1 ], more: false, elapsed: 0.01 };
			return false;
		});

		/* saved value for madb_which1 autofills */
		cache.addCheck(function(u, d) {
			if(u != tn + '_autofill.php') return false;

			for(var rnd in d) if(rnd.match(/^rnd/)) break;

			if(d.mfk == 'madb_which1' && d.id == data.madb_which1.id) {
				$j('#madb_which2' + d[rnd]).html(data.madb_which2);
				$j('#madb_which3' + d[rnd]).html(data.madb_which3);
				return true;
			}

			return false;
		});

		/* saved value for madb_where1 */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'madb_where1' && d.id == data.madb_where1.id)
				return { results: [ data.madb_where1 ], more: false, elapsed: 0.01 };
			return false;
		});

		/* saved value for madb_where1 autofills */
		cache.addCheck(function(u, d) {
			if(u != tn + '_autofill.php') return false;

			for(var rnd in d) if(rnd.match(/^rnd/)) break;

			if(d.mfk == 'madb_where1' && d.id == data.madb_where1.id) {
				$j('#madb_where2' + d[rnd]).html(data.madb_where2);
				$j('#madb_where3' + d[rnd]).html(data.madb_where3);
				return true;
			}

			return false;
		});

		/* saved value for madb_why1 */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'madb_why1' && d.id == data.madb_why1.id)
				return { results: [ data.madb_why1 ], more: false, elapsed: 0.01 };
			return false;
		});

		/* saved value for madb_why1 autofills */
		cache.addCheck(function(u, d) {
			if(u != tn + '_autofill.php') return false;

			for(var rnd in d) if(rnd.match(/^rnd/)) break;

			if(d.mfk == 'madb_why1' && d.id == data.madb_why1.id) {
				$j('#madb_why2' + d[rnd]).html(data.madb_why2);
				$j('#madb_why3' + d[rnd]).html(data.madb_why3);
				return true;
			}

			return false;
		});

		cache.start();
	});
</script>

