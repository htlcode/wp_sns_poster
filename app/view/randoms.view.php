<div class="wrap">
	<h1><?php echo __("Randoms list",WP_OTO_POSTER) ?></h1>
	<?php if(!empty($records)): ?>
		<table border="1">
			<thead>
				<tr>
					<td><?php echo __('ID',WP_OTO_POSTER); ?></td>
					<td><?php echo __('Title',WP_OTO_POSTER); ?></td>
					<td><?php echo __('Year',WP_OTO_POSTER); ?></td>
			        <td><?php echo __('Month',WP_OTO_POSTER); ?></td>
			        <td><?php echo __('Day / th',WP_OTO_POSTER); ?></td>
			        <td><?php echo __('Day of the week',WP_OTO_POSTER); ?></td>
			        <td><?php echo __('Hour',WP_OTO_POSTER); ?></td>
			        <td><?php echo __('Min',WP_OTO_POSTER); ?></td>
			        <td><?php echo __('Facebook',WP_OTO_POSTER); ?></td>
			        <td><?php echo __('Twitter',WP_OTO_POSTER); ?></td>
			        <td><?php echo __('Instagram',WP_OTO_POSTER); ?></td>
			        <td><?php echo __('Pinterest',WP_OTO_POSTER); ?></td>
			        <td><?php echo __('Actions',WP_OTO_POSTER); ?></td>
				</tr>
			</thead>
			<tbody>
			<?php foreach($records as $record): ?>
				<?php
				$edit_link = get_site_url().'/wp-admin/admin.php?page=wp_oto_poster_random&id='.$record['id'];
				$delete_link = get_site_url().'/wp-admin/admin.php?page=wp_oto_poster_randoms&delete='.$record['id'];
				?>
				<tr>
					<td><?php echo $record['id']; ?></td>
					<td><?php echo $record['title']; ?></td>
					<td><?php echo $record['year']; ?></td>
			        <td><?php echo $record['month']; ?></td>
			        <td><?php echo $record['day']; ?></td>
			        <td><?php echo $record['dayoftheweek']; ?></td>
			        <td><?php echo $record['hour']; ?></td>
			        <td><?php echo $record['min']; ?></td>
			        <td><?php if(!empty($record['is_facebook'])){echo 'x';}; ?></td>
			        <td><?php if(!empty($record['is_twitter'])){echo 'x';}; ?></td>
			        <td><?php if(!empty($record['is_instagram'])){echo 'x';}; ?></td>
			        <td><?php if(!empty($record['is_pinterest'])){echo 'x';}; ?></td>
			        <td><a href="<?php echo $edit_link ?>"><?php echo __('Edit'); ?></a> | <a href="<?php echo $delete_link ?>"><?php echo __('Delete'); ?></a></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>