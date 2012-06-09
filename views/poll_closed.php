<h2><?php echo $poll['title']; ?></h2>

<p><?php echo $poll['description']; ?></p>

<?php if ( is_array($poll['options']) AND count($poll['options']) > 0): ?>
	<ul class="poll_options">
		<?php foreach($poll['options'] as $option): ?>
			<li<?php echo in_array($option['id'], $user_vote) ? ' class="user_vote"' : NULL; ?>>
				<span><?php echo $option['title']; ?></span>
				<div style="width: <?php echo $option['percent']; ?>%"><?php echo $option['percent']; ?>%</div>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php echo lang('polls.total_votes') ?>: <?php echo $poll['total_votes'] ?>
<?php else: ?>
	<p><?php echo lang('polls.no_options') ?></p>
<?php endif; ?>
	
<?php if ($comments_enabled): ?>
	<?php echo display_comments($poll['id']); ?>
<?php endif; ?>