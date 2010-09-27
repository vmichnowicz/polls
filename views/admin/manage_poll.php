<div class="box" id="galleries_form_box">
	<h3><?php echo lang('polls.manage_poll_label'); ?></h3>
	<div class="box-container">
		<?php echo form_open_multipart($this->uri->uri_string(), 'class="crud"'); ?>
			<ol>
				<li>
					<label for="title"><?php echo lang('polls.title_label'); ?></label>
					<input type="text" id="title" name="title" maxlength="255" value="<?php echo $poll['title']; ?>" />
					<span class="required-icon tooltip">Required</span>
				</li>

				<li class="even">
					<label for="slug"><?php echo lang('polls.slug_label'); ?></label>
					<?php echo form_input('slug', $poll['slug'], 'class="width-15"'); ?>
					<span class="required-icon tooltip">Required</span>
				</li>

				<li class="odd description">
					<label for="description"><?php echo lang('polls.description_label'); ?></label>
					<?php echo form_textarea(array('id'=>'description', 'name'=>'description', 'value' => $poll['description'], 'rows' => 10, 'class' => 'wysiwyg-simple')); ?>
				</li>
				
				<li class="even">
					<label for="options"><?php echo lang('polls.options_label'); ?></label>
					<ol id="options">
						<?php if ( isset($poll['options']) ): ?>
							<?php foreach($poll['options'] as $option): ?>
								<?php if ($option !== ''): ?>
									<li><input type="text" name="options[<?php echo $option['id']; ?>]" value="<?php echo $option['title']; ?>" /></li>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php else: ?>
							<li><input type="text" name="options[]" /></li>
						<?php endif; ?>
						<li><input type="text" name="options[]" /></li>
					</ol>
				</li>
				
				<li class="odd">
					<label for="open_date"><?php echo lang('polls.open_date_label'); ?></label>
					<?php echo form_input('open_date', timestamp_to_date($poll['open_date']), 'id="open_date"'); ?>
				</li>
				
				<li class="even">
					<label for="close_date"><?php echo lang('polls.close_date_label'); ?></label>
					<?php echo form_input('close_date', timestamp_to_date($poll['close_date']), 'id="close_date"'); ?>
				</li>
				
				<li class="odd">
					<label for="comments"><?php echo lang('polls.comments_label'); ?></label>
					<?php echo form_dropdown('comments_enabled', array('1'=>lang('polls.yes'), '0'=>lang('polls.no')), $poll['comments_enabled']); ?>
				</li>
				
				<li class="even">
					<label for="members_only"><?php echo lang('polls.members_only_label'); ?></label>
					<?php echo form_dropdown('members_only', array('1'=>lang('polls.yes'), '0'=>lang('polls.no')), $poll['members_only']); ?>
				</li>
				
			</ol>

			<?php $this->load->view('admin/partials/buttons', array('buttons' => array('save', 'cancel') )); ?>
		<?php echo form_close(); ?>
	</div>
</div>
