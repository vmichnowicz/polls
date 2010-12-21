<h3><?php echo lang('polls.new_poll_label'); ?></h3>

<?php echo form_open_multipart(uri_string(), 'class="crud"'); ?>
	<ul>
		<li>
			<label for="title"><?php echo lang('polls.title_label'); ?></label>
			<input type="text" id="title" name="title" maxlength="255" value="<?php echo set_value('title'); ?>" />
			<span class="required-icon tooltip">Required</span>
		</li>

		<li class="even">
			<label for="slug"><?php echo lang('polls.slug_label'); ?></label>
			<?php echo form_input('slug', set_value('slug'), 'class="width-15"'); ?>
			<span class="required-icon tooltip">Required</span>
		</li>

		<li class="odd description">
			<label for="description"><?php echo lang('polls.description_label'); ?></label>
			<?php echo form_textarea(array('id'=>'description', 'name'=>'description', 'value' => set_value('description'), 'rows' => 10, 'class' => 'wysiwyg-simple')); ?>
		</li>

		<li class="even">
			<label for="options"><?php echo lang('polls.options_label'); ?></label>

			<div style="float: left">
			<ul id="options">
				<?php if ( isset($poll['options']) ): ?>
					<?php foreach($poll['options'] as $option): ?>
						<?php if ($option !== ''): ?>
							<li><input type="text" name="options[]" value="<?php echo $option; ?>" /></li>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
				<li id="new_option">
					<select id="new_option_type">
						<option value="defined"><?php echo lang('polls.defined'); ?></option>
						<option value="other"><?php echo lang('polls.other'); ?></option>
					</select>
					<input type="text" name="new_option_title" id="new_option_title" />
					<input type="button" id="add_new_option" value="Add Option" />
				</li>
			</div>
		</li>
		
		<li class="odd">
			<label for="type"><?php echo lang('polls.type_label'); ?></label>
			<?php echo form_dropdown('type', array('single'=>lang('polls.single'), 'multiple'=>lang('polls.multiple')), set_value('type')); ?>
		</li>
		
		<li class="even">
			<label for="open_date"><?php echo lang('polls.open_date_label'); ?></label>
			<?php echo form_input('open_date', set_value('open_date'), 'id="open_date"'); ?>
		</li>

		<li class="odd">
			<label for="close_date"><?php echo lang('polls.close_date_label'); ?></label>
			<?php echo form_input('close_date', set_value('close_date'), 'id="close_date"'); ?>
		</li>

		<li class="even">
			<label for="comments"><?php echo lang('polls.comments_label'); ?></label>
			<?php echo form_dropdown('comments_enabled', array('1'=>lang('polls.yes'), '0'=>lang('polls.no')), set_value('comments_enabled')); ?>
		</li>

		<li class="odd">
			<label for="members_only"><?php echo lang('polls.members_only_label'); ?></label>
			<?php echo form_dropdown('members_only', array('1'=>lang('polls.yes'), '0'=>lang('polls.no')), set_value('members_only')); ?>
		</li>

	</ul>

	<?php $this->load->view('admin/partials/buttons', array('buttons' => array('save', 'cancel') )); ?>

<?php echo form_close(); ?>
