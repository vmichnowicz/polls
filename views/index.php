<h2 id="page_title"><?php echo lang('polls.galleries_label'); ?></h2>

<?php if ($polls): ?>
<ul class="poll_options">
	<?php foreach($polls as $poll): ?>
		<li>
			<a href="<?php echo site_url('polls/' . $poll['slug']); ?>"><?php echo $poll['title']; ?></a>
			<?php if ($poll['members_only']): ?>
				<span class="members_only"><?php echo lang('polls.members_only_label') ?></span>
			<?php endif ;?>
		</li>
	<?php endforeach; ?>
</ul>
<?php else: ?>
   <p>There are no polls</p>
<?php endif; ?>
