<ol>
	<li class="even">
		<label for="poll_id">Poll ID</label>
		<select name="poll_id" id="poll_id">
			<?php if ($polls): ?>
				<?php foreach ($polls as $poll): ?>
					<option value="<?php echo $poll['id']; ?>"><?php echo $poll['title']; ?></option>
				<?php endforeach; ?>
			<?php endif; ?>
		</select>
	</li>
</ol>