<h2><?php echo $poll['title']; ?></h2>


<hr />

<p>
<?php echo $poll['description']; ?>
</p>

<form method="post" action="<?php echo current_url(); ?>">
<?php if (count($poll['options']) > 0): ?>
<ul class="poll_options">
	<?php foreach($poll['options'] as $option): ?>
		<li>
			<label for="option_<?php echo $option['id']; ?>">
				<input type="radio" name="vote" id="option_<?php echo $option['id']; ?>" value="<?php echo $option['id']; ?>" />
				<span><?php echo $option['title']; ?></span>
			</label>
		</li>
	<?php endforeach; ?>
</ul>

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
