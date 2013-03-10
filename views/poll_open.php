<h2><?php echo $poll['title']; ?></h2>

<hr />

<p>
	<?php echo $poll['description']; ?>
</p>

<?php if ( is_array($poll['options']) AND count($poll['options']) > 0): ?>
	<form method="post" action="<?php echo current_url(); ?>">
		<fieldset>
			<ul class="poll_options">
				<?php foreach($poll['options'] as $option): ?>
					<li>
						<label for="option_<?php echo $option['id']; ?>">
							<input type="<?php echo $poll['input_type']; ?>" name="options[]" value="<?php echo $option['id']; ?>" id="option_<?php echo $option['id']; ?>" />
							<span><?php echo $option['title']; ?></span>
						</label>
						<?php if ($option['type'] === 'other'): ?>
							<input type="text" name="other_options[<?php echo $option['id']; ?>]" id="other_option_<?php echo $option['id']; ?>" />
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
			
			<hr />
			
			<input type="hidden" name="session_id" value="<?php echo $this->session->userdata('session_id'); ?>" />
			
			<input type="submit" name="submit" value="<?php echo lang('polls.vote') ?>" />
		</fieldset>
	</form>

	<a href="<?php echo site_url() . '/polls/results/' . $poll['slug'] ?>"><?php echo lang('polls.results') ?></a><br />
	<?php echo lang('polls.total_votes') ?>: <?php echo $poll['total_votes'] ?>

<?php else: ?>
   <p><?php echo lang('polls.no_options') ?></p>
<?php endif; ?>

<?php if ($comments_enabled): ?>
    <div id="comments">
        <div id="existing-comments">
            <h4><?php echo lang('comments:title') ?></h4>
            <?php echo $this->comments->display() ?>
        </div>
        <?php echo $this->comments->form() ?>
    </div>
<?php endif; ?>