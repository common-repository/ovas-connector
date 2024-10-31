<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly ?>
<span class="filter">
	<input type="text" id="ovas_connect_logs_filter" onsubmit="filter();" value="<?php echo esc_attr( $filter ); ?>">
	<button type="button">
		<span class="dashicons dashicons-search" onclick="filterLogs(document.getElementById('ovas_connect_logs_filter').value);"></span>
	</button>
</span>

<span class="limit">
	<select id="logtab_filter_limit_override" name="logtab_filter_limit_override" onchange="filterLogs(document.getElementById('ovas_connect_logs_filter').value);">
		<option value="1"  <?php echo $log_limit === '1' ? 'selected="selected"' : ''; ?>>Last 24 hours</option>
		<option value="7"  <?php echo $log_limit === '7' ? 'selected="selected"' : ''; ?>>Last week</option>
		<option value="14" <?php echo $log_limit === '14' ? 'selected="selected"' : ''; ?>>Last 2 weeks</option>
		<option value="31" <?php echo $log_limit === '31' ? 'selected="selected"' : ''; ?>>Last month</option>
		<option value="-1" <?php echo $log_limit === '-1' ? 'selected="selected"' : ''; ?>>All</option>
	</select>
</span>
