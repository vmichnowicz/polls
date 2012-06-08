<section class="title">
	<h4><?php echo $poll['title']; ?> <em>(<?php echo $total_votes; ?> total votes)</em></h4>
</section>

<section class="item">
	<?php if($options): ?>
		<ul id="results">
			<?php foreach($options as $option): ?>
				<li>
					<p>
						<?php echo $option['title']; ?>
						<em>(<?php echo $option['votes']; ?> <?php echo lang('polls.votes'); ?>)</em>
					</p>
					<div style="width: <?php echo $option['percent']; ?>%;">
						<?php echo $option['percent']; ?>%
					</div>
					<?php if (count($option['other']) > 0): ?>
						<ul class="other">
							<?php foreach($option['other'] as $other): ?>
								<li><?php echo $other['text']; ?> <em>&mdash; <?php echo $other['created'] instanceof DateTime ? $poll['created']->format('Y-m-d') : NULL ; ?></em></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>