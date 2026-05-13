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
			utedb_what1: <?php echo json_encode($jdata['utedb_what1']); ?>,
			utedb_who1: <?php echo json_encode($jdata['utedb_who1']); ?>,
			utedb_when1: <?php echo json_encode($jdata['utedb_when1']); ?>,
			utedb_which1: <?php echo json_encode($jdata['utedb_which1']); ?>,
			utedb_where1: <?php echo json_encode($jdata['utedb_where1']); ?>,
			utedb_why1: <?php echo json_encode($jdata['utedb_why1']); ?>,
			utedb_howr1: <?php echo json_encode($jdata['utedb_howr1']); ?>,
			utedb_hows1: <?php echo json_encode($jdata['utedb_hows1']); ?>,
			utedb_howq1: <?php echo json_encode($jdata['utedb_howq1']); ?>,
			utedb_howt1: <?php echo json_encode($jdata['utedb_howt1']); ?>,
			utedb_why2: <?php echo json_encode($jdata['utedb_why2']); ?>,
			utedb_why3: <?php echo json_encode($jdata['utedb_why3']); ?>,
			utedb_where2: <?php echo json_encode($jdata['utedb_where2']); ?>,
			utedb_where3: <?php echo json_encode($jdata['utedb_where3']); ?>,
			utedb_which2: <?php echo json_encode($jdata['utedb_which2']); ?>,
			utedb_which3: <?php echo json_encode($jdata['utedb_which3']); ?>,
			utedb_when2: <?php echo json_encode($jdata['utedb_when2']); ?>,
			utedb_when3: <?php echo json_encode($jdata['utedb_when3']); ?>,
			utedb_who2: <?php echo json_encode($jdata['utedb_who2']); ?>,
			utedb_who3: <?php echo json_encode($jdata['utedb_who3']); ?>,
			utedb_what2: <?php echo json_encode($jdata['utedb_what2']); ?>,
			utedb_what3: <?php echo json_encode($jdata['utedb_what3']); ?>,
			utedb_howr2: <?php echo json_encode($jdata['utedb_howr2']); ?>,
			utedb_howr3: <?php echo json_encode($jdata['utedb_howr3']); ?>,
			utedb_hows2: <?php echo json_encode($jdata['utedb_hows2']); ?>,
			utedb_hows3: <?php echo json_encode($jdata['utedb_hows3']); ?>,
			utedb_howq2: <?php echo json_encode($jdata['utedb_howq2']); ?>,
			utedb_howq3: <?php echo json_encode($jdata['utedb_howq3']); ?>,
			utedb_howt2: <?php echo json_encode($jdata['utedb_howt2']); ?>,
			utedb_howt3: <?php echo json_encode($jdata['utedb_howt3']); ?>
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
				$j('#utedb_what1' + d[rnd]).html(data.utedb_what1);
				$j('#utedb_who1' + d[rnd]).html(data.utedb_who1);
				$j('#utedb_when1' + d[rnd]).html(data.utedb_when1);
				$j('#utedb_which1' + d[rnd]).html(data.utedb_which1);
				$j('#utedb_where1' + d[rnd]).html(data.utedb_where1);
				$j('#utedb_why1' + d[rnd]).html(data.utedb_why1);
				$j('#utedb_howr1' + d[rnd]).html(data.utedb_howr1);
				$j('#utedb_hows1' + d[rnd]).html(data.utedb_hows1);
				$j('#utedb_howq1' + d[rnd]).html(data.utedb_howq1);
				$j('#utedb_howt1' + d[rnd]).html(data.utedb_howt1);
				$j('#utedb_why2' + d[rnd]).html(data.utedb_why2);
				$j('#utedb_why3' + d[rnd]).html(data.utedb_why3);
				$j('#utedb_where2' + d[rnd]).html(data.utedb_where2);
				$j('#utedb_where3' + d[rnd]).html(data.utedb_where3);
				$j('#utedb_which2' + d[rnd]).html(data.utedb_which2);
				$j('#utedb_which3' + d[rnd]).html(data.utedb_which3);
				$j('#utedb_when2' + d[rnd]).html(data.utedb_when2);
				$j('#utedb_when3' + d[rnd]).html(data.utedb_when3);
				$j('#utedb_who2' + d[rnd]).html(data.utedb_who2);
				$j('#utedb_who3' + d[rnd]).html(data.utedb_who3);
				$j('#utedb_what2' + d[rnd]).html(data.utedb_what2);
				$j('#utedb_what3' + d[rnd]).html(data.utedb_what3);
				$j('#utedb_howr2' + d[rnd]).html(data.utedb_howr2);
				$j('#utedb_howr3' + d[rnd]).html(data.utedb_howr3);
				$j('#utedb_hows2' + d[rnd]).html(data.utedb_hows2);
				$j('#utedb_hows3' + d[rnd]).html(data.utedb_hows3);
				$j('#utedb_howq2' + d[rnd]).html(data.utedb_howq2);
				$j('#utedb_howq3' + d[rnd]).html(data.utedb_howq3);
				$j('#utedb_howt2' + d[rnd]).html(data.utedb_howt2);
				$j('#utedb_howt3' + d[rnd]).html(data.utedb_howt3);
				return true;
			}

			return false;
		});

		cache.start();
	});
</script>

