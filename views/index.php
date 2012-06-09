<h2><?php echo lang('polls.polls'); ?></h2>

<?php if ( isset($polls) AND is_array($polls) ): ?>
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
   <p><?php echo lang('polls.no_polls_error'); ?></p>
<?php endif; ?>
