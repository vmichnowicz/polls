<h4><?php echo $data['title']; ?></h4>

<!-- If poll is open and user has not already voted in this poll -->
<?php if ($data['is_open'] AND ! $data['allready_voted']): ?>
	
	<form method="post" action="<?php echo site_url('polls/' . $data['slug']); ?>">
		<fieldset>
			<ul class="poll_options">
				<?php foreach($data['options'] as $option): ?>
					<li>
					
						<label for="option_<?php echo $option['id']; ?>">
						
							<?php if ($data['type'] == 'single'): ?>
								<input type="radio" name="vote" id="option_<?php echo $option['id']; ?>" value="<?php echo $option['id']; ?>" />
							<?php elseif ($data['type'] == 'multiple'): ?>
								<input type="checkbox" name="vote[<?php echo $option['id']; ?>][id]" id="option_<?php echo $option['id']; ?>" value="<?php echo $option['id']; ?>" />
							<?php endif; ?>
							
							<span><?php echo $option['title']; ?></span>
							
						</label>
						
						<?php if ($option['type'] == 'other'): ?>
							<input type="text" name="other[<?php echo $option['id']; ?>][other]" id="other_<?php echo $option['id']; ?>" />
						<?php endif; ?>
						
					</li>
				<?php endforeach; ?>
			</ul>
			
			<br />
			
			<hr />
			
			<input type="hidden" name="session_id" value="<?php echo $this->session->userdata('session_id'); ?>" />
			
			<input type="submit" value="Vote" />
		</fieldset>
	</form>

<!-- If poll is closed or user has already voted in this poll -->
<?php else: ?>

	<?php if ($data['options']): ?>
		<ul>
			<?php foreach($data['options'] as $option): ?>
				<li>
					<span><?php echo $option['title']; ?></span>
					<em>&ndash; <?php echo $option['votes']; ?> votes</em>
					<div style="width: <?php echo $option['votes'] > 0 ? round( ($option['votes'] / $data['total_votes'] * 100), 1) : '0'; ?>%;">
						<?php echo $option['votes'] > 0 ? round( ($option['votes'] / $data['total_votes'] * 100), 1) : '0'; ?>%
					</div>
					<?php echo $data['total_votes']; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

<?php endif; ?>

<a href="<?php echo site_url('polls/' . $data['slug']); ?>">View Poll</a>