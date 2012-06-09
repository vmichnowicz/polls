<section class="title">
	<h4><?php echo lang('polls.new_poll_label'); ?></h4>
</section>

<section class="item form_inputs">
	<?php echo form_open_multipart(uri_string(), 'class="crud"'); ?>
		<fieldset>
			<ul>
				<li>
					<label for="title"><?php echo lang('polls.title_label'); ?></label>
					<input type="text" id="title" name="title" maxlength="255" value="<?php echo set_value('title'); ?>" />
					<span class="required-icon tooltip">Required</span>
				</li>

				<li class="even">
					<label for="slug"><?php echo lang('polls.slug_label'); ?></label>
					<input type="text" name="slug" id="slug" maxlength="255" value="<?php echo set_value('slug'); ?>" />
					<span class="required-icon tooltip">Required</span>
				</li>
			</ul>
			<?php $this->load->view('admin/partials/buttons', array('buttons' => array('save', 'cancel') )); ?>
		</fieldset>
	<?php echo form_close(); ?>
</section>