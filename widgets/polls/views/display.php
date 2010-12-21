<h4><?php echo $data['title']; ?></h4>

<?php if ($data['options']): ?>
	<ul>
		<?php foreach($data['options'] as $option): ?>
			<li>
				<span><?php echo $option['title']; ?></span>
				<em>&ndash; <?php echo $option['votes']; ?> votes</em>
				<div style="width: <?php echo $option['votes'] > 0 ? round( ($option['votes'] / $data['total_votes'] * 100), 1) : '0'; ?>%;">
					<?php echo $option['votes'] > 0 ? round( ($option['votes'] / $data['total_votes'] * 100), 1) : '0'; ?>%
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<a href="<?php echo site_url('polls/' . $data['slug']); ?>">View Poll</a>