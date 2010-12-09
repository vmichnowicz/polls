<h2><?php echo $poll['title']; ?></h2>


<hr />

<p>
	<?php echo $poll['description']; ?>
</p>

<?php if (count($poll['options']) > 0): ?>
	<form method="post" action="<?php echo current_url(); ?>">
	
		<ul class="poll_options">
			<?php foreach($poll['options'] as $option): ?>
				<li>
				
					<label for="option_<?php echo $option['id']; ?>">
					
						<?php if ($poll['type'] == 'single'): ?>
							<input type="radio" name="vote[<?php echo $option['id']; ?>][id]" id="option_<?php echo $option['id']; ?>" value="<?php echo $option['id']; ?>" />
						<?php elseif ($poll['type'] == 'multiple'): ?>
							<input type="checkbox" name="vote[<?php echo $option['id']; ?>][id]" id="option_<?php echo $option['id']; ?>" value="<?php echo $option['id']; ?>" />
						<?php endif; ?>
						
						<span><?php echo $option['title']; ?></span>
						
					</label>
					
					<?php if ($option['type'] == 'other'): ?>
						<input type="text" name="vote[<?php echo $option['id']; ?>][other]" id="other_<?php echo $option['id']; ?>" />
					<?php endif; ?>
					
				</li>
			<?php endforeach; ?>
		</ul>
		
		<br />
		
		<hr />
		
		<input type="submit" value="Vote" />
	
	</form>

	<a href="<?php echo site_url() . '/polls/results/' . $poll['slug'] ?>"><?php echo lang('polls.results') ?></a><br />
	<?php echo lang('polls.total_votes') ?>: <?php echo $poll['total_votes'] ?>

<?php else: ?>
   <p><?php echo lang('polls.no_options') ?></p>
<?php endif; ?>

<?php
if ($comments_enabled)
{	
	echo display_comments($poll['id']);
}
?>
