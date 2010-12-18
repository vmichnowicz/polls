<h2><?php echo $poll['title']; ?></h2>

<p>
	<?php echo $poll['description']; ?>
</p>

<?php if (count($poll['options']) > 0): ?>
<ul class="poll_options">
	<?php foreach($poll['options'] as $option): ?>
		<li<?php echo ( array_key_exists($option['id'], $user_vote) ) ? ' class="user_vote"' : NULL; ?>>
			<span><?php echo $option['title']; ?></span>
			<div style="width: <?php echo $option['percent']; ?>%"><?php echo $option['percent']; ?>%</div>
		</li>
	<?php endforeach; ?>
</ul>

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

<?php

if ( $this->poll_votes_m->allready_voted($poll['id']) )
{
	echo 'you already voted!';
}

?>


<?php echo $this->session->userdata('user_id'); ?>
<br />
<?php echo $this->session->userdata('ip_address'); ?>
<br />
<?php echo $this->session->userdata('user_agent'); ?>
<br />
<?php echo $this->session->userdata('session_id'); ?>