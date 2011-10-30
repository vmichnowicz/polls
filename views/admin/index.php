<section class="title">
	<h3><?php echo lang('polls.polls'); ?></h3>
</section>

<section class="item">
	<?php echo form_open('admin/polls/delete');?>

		<?php if (is_array($polls) AND count($polls) > 0): ?>
			<table border="0" class="table-list">
				<thead>
					<tr>
						<th><?php echo form_checkbox(array('name' => 'action_to_all', 'class' => 'check-all'));?></th>
						<th><?php echo lang('polls.title'); ?></th>
						<th><?php echo lang('polls.num_options'); ?></th>
						<th><?php echo lang('polls.open_date'); ?></th>
						<th><?php echo lang('polls.close_date'); ?></th>
						<th><?php echo lang('polls.created'); ?></th>
						<th><?php echo lang('polls.last_updated'); ?></th>
						<th><?php echo lang('polls.actions'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($polls as $poll): ?>
					<tr>
						<td><?php echo form_checkbox('action_to[]', $poll['id']); ?></td>
						<td><?php echo $poll['title']; ?></td>
						<td><?php echo count($poll['options']); ?></td>
						<td><?php echo ($poll['open_date']) ? date('M j, Y', $poll['open_date']) : '&nbsp'; ?></td>
						<td><?php echo ($poll['close_date']) ? date('M j, Y', $poll['close_date']) : '&nbsp'; ?></td>
						<td><?php echo date('M j, Y', $poll['created']); ?></td>
						<td><?php echo ($poll['last_updated']) ? date('M j, Y', $poll['last_updated']) : '&nbsp'; ?></td>
						<td>
							<?php echo anchor('polls/' . $poll['slug'], lang('polls.view_label'), array('class' => 'button small')); ?>
							<?php echo anchor('admin/polls/manage/' . $poll['id'], lang('polls.manage_label'), array('class' => 'button small')); ?>
							<?php echo anchor('admin/polls/results/' . $poll['id'], lang('polls.results_label'), array('class' => 'button small')); ?>
							<?php echo anchor('admin/polls/delete/' . $poll['id'], lang('polls.delete_label'), array('class'=>'button small confirm')); ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php $this->load->view('admin/partials/buttons', array('buttons' => array('delete') )); ?>

		<?php else: ?>
			<p><?php echo lang('polls.no_polls_error'); ?></p>
		<?php endif;?>

	<?php echo form_close(); ?>
</section>