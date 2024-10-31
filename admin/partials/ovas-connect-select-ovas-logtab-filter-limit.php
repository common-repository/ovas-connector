<?php $log_limit = get_option( 'ovas_options' )['ovas_logtab_filter_limit'] ?? -1; ?>
<select id="ovas_logtab_filter_limit" name="ovas_options[ovas_logtab_filter_limit]">
	<option value="1"  <?php echo $log_limit === '1' ? 'selected="selected"' : ''; ?>>Last 24 hours</option>
	<option value="7"  <?php echo $log_limit === '7' ? 'selected="selected"' : ''; ?>>Last week</option>
	<option value="14" <?php echo $log_limit === '14' ? 'selected="selected"' : ''; ?>>Last 2 weeks</option>
	<option value="31" <?php echo $log_limit === '31' ? 'selected="selected"' : ''; ?>>Last month</option>
	<option value="-1" <?php echo $log_limit === '-1' ? 'selected="selected"' : ''; ?>>All</option>
</select>
