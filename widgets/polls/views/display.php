<h4><?php echo $title; ?></h4>

<!-- If poll is open and user has not already voted in this poll -->
<?php if ($is_open AND ! $already_voted): ?>
	
	<form method="post" action="<?php echo site_url('polls/' . $slug); ?>">
		<fieldset>
			<ul class="poll_options">
				<?php foreach($poll_options as $option): ?>
					<li>
						<label for="option_<?php echo $option['id']; ?>">
							<input type="<?php echo $input_type; ?>" name="options[]" value="<?php echo $option['id']; ?>" id="option_<?php echo $option['id']; ?>" />
							<span><?php echo $option['title']; ?></span>
						</label>
						<?php if ($option['type'] == 'other'): ?>
							<input type="text" name="other_options[<?php echo $option['id']; ?>]" id="other_option_<?php echo $option['id']; ?>" />
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
			
			<br />
			
			<hr />
			
			<input type="hidden" name="session_id" value="<?php echo $this->session->userdata('session_id'); ?>" />
			
			<input type="submit" name="submit" value="<?php echo lang('polls.vote') ?>" />
		</fieldset>
	</form>

<!-- If poll is closed or user has already voted in this poll -->
<?php else: ?>

	<?php if ($poll_options): ?>
		<ul>
			<?php foreach($poll_options as $option): ?>
				<li>
					<span><?php echo $option['title']; ?></span>
					<em>&ndash; <?php echo $option['votes']; ?> votes</em>
					<div style="width: <?php echo $option['votes'] > 0 ? round( ($option['votes'] / $total_votes * 100), 1) : '0'; ?>%;">
						<?php echo $option['votes'] > 0 ? round( ($option['votes'] / $total_votes * 100), 1) : '0'; ?>%
					</div>
					<?php echo $total_votes; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

<?php endif; ?>

<a href="<?php echo site_url('polls/' . $slug); ?>"><?php echo lang('polls.view_label') ?></a>