<section class="title">
	<h3><?php echo lang('polls.manage_label'); ?></h3>
</section>

<section class="item">
	<?php echo form_open($this->uri->uri_string(), 'class="crud"'); ?>

		<!-- Poll ID -->
		<input type="hidden" name="poll_id" id="poll_id" value="<?php echo $poll['id']; ?>" />

		<ul>
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

			<li id="section_options" class="even">
				<h4><?php echo lang('polls.options_label'); ?></h4>

				<p>
					<?php echo lang('polls.options_info'); ?>
				</p>

				<ul id="new_option">
					<li>
						<select name="new_option_type" id="new_option_type">
							<option value="defined"><?php echo lang('polls.defined'); ?></option>
							<option value="other"><?php echo lang('polls.other'); ?></option>
						</select>
						<input type="text" name="new_option_title" id="new_option_title" />
						<input type="button" id="add_new_option" value="<?php echo lang('polls.add_option_label'); ?>" />
					</li>
				</ul>

				<ul id="options" class="sortable">
					<?php if ( isset($poll['options']) ): ?>
						<?php foreach($poll['options'] as $option): ?>
							<?php if ($option !== ''): ?>
								<li>
									<?php echo form_dropdown('options[' . $option['id'] . '][type]', array('defined'=>lang('polls.defined'), 'other'=>lang('polls.other')), $option['type'], 'id=""options'); ?>
									<input type="text" name="options[<?php echo $option['id']; ?>][title]" value="<?php echo $option['title']; ?>" />
								</li>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>

			</li>

			<li class="odd">
				<label for="type"><?php echo lang('polls.type_label'); ?></label>
				<p style="width: 700px">
					<?php echo lang('polls.type_info'); ?>
				</p>
				<?php echo form_dropdown('type', array('single' => lang('polls.single'), 'multiple' => lang('polls.multiple')), $poll['type'], 'id="type"'); ?>
			</li>

			<li class="even">
				<label for="multiple_votes"><?php echo lang('polls.multiple_votes_label'); ?></label>
				<?php echo form_dropdown('multiple_votes', array('0'=>lang('polls.no'), '1'=>lang('polls.yes')), $poll['multiple_votes'], 'id="multiple_votes"'); ?>
			</li>

			<li class="odd">
				<label for="open_date"><?php echo lang('polls.open_date_label'); ?></label>
				<?php echo form_input('open_date', $poll['open_date'] instanceof DateTime ? $poll['open_date']->format('Y-m-d') : NULL, 'id="open_date"'); ?>
			</li>

			<li class="even">
				<label for="close_date"><?php echo lang('polls.close_date_label'); ?></label>
				<?php echo form_input('close_date',$poll['close_date'] instanceof DateTime ? $poll['close_date']->format('Y-m-d') : NULL, 'id="close_date"'); ?>
			</li>

			<li class="odd">
				<label for="comments"><?php echo lang('polls.comments_label'); ?></label>
				<?php echo form_dropdown('comments_enabled', array('1'=>lang('polls.yes'), '0'=>lang('polls.no')), $poll['comments_enabled'], 'id="comments"'); ?>
			</li>

			<li class="even">
				<label for="members_only"><?php echo lang('polls.members_only_label'); ?></label>
				<?php echo form_dropdown('members_only', array('1'=>lang('polls.yes'), '0'=>lang('polls.no')), $poll['members_only'], 'id="members_only"'); ?>
			</li>

		</ul>

		<?php $this->load->view('admin/partials/buttons', array('buttons' => array('save', 'cancel') )); ?>

	<?php echo form_close(); ?>

</section>