<?php
$col_subrows = ((bool)env('FEATURE_SCOREENGINE') ? 'col-md-3' : 'col-md-6');

$this->Html->scriptStart(['inline' => false, 'safe' => false]);
echo 'setTimeout(window.location.reload.bind(window.location), 30 * 1000);';
$this->Html->scriptEnd();
?>
<h2>Competition Central</h2>

<div class="row">
	<?php if ( (bool)env('FEATURE_SCOREENGINE') ): ?>
	<div class="col-md-6">
		<h3>Uptime Overview</h3>
		<?= $this->EngineOutputter->generateScoreBoard(); ?>
	</div>
	<?php endif; ?>

	<div class="<?= $col_subrows; ?>">
		<h3>Active Injects</h3>
		<div class="list-group">
			<?php foreach ( $active_injects AS $i ): ?>
			<a href="<?= $this->Html->url('/staff/inject/'.$i->getInjectId()); ?>" class="list-group-item<?= $i->isRecent() ? ' list-group-item-info' : ''; ?>"><?= $i->getTitle(); ?></a>
			<?php endforeach; ?>

			<?php if ( empty($active_injects) ): ?>
			<a href="#" class="list-group-item">No injects.</a>
			<?php endif; ?>
		</div>
	</div>
	
	<div class="<?= $col_subrows; ?>">
		<h3>Recently Expired</h3>
		<div class="list-group">
			<?php foreach ( $recent_expired AS $i ): ?>
			<a href="<?= $this->Html->url('/staff/inject/'.$i->getInjectId()); ?>" class="list-group-item<?= $i->isRecent() ? ' list-group-item-warning' : ''; ?>"><?= $i->getTitle(); ?></a>
			<?php endforeach; ?>

			<?php if ( empty($recent_expired) ): ?>
			<a href="#" class="list-group-item">No injects.</a>
			<?php endif; ?>
		</div>
	</div>
</div>

<h3>Recent Actions</h3>
<table class="table table-bordered">
	<tr>
		<td>Who?</td>
		<td>When?</td>
		<td>Type</td>
		<td>Message</td>
	</tr>
	<?php foreach ( $recent_logs AS $r ): ?>
	<tr>
		<td width="25%"><?= $r['User']['Group']['name']; ?> - <strong><?= $r['User']['username']; ?></strong></td>
		<td width="15%"><?= $this->Time->timeAgoInWords($r['Log']['time']); ?>
		<td width="10%"><?= $r['Log']['type']; ?></td>
		<td width="50%"><?= $r['Log']['message']; ?></td>
	</tr>
	<?php endforeach; ?>
</table>

<?php
	echo $this->Paginator->counter([
		'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	]);
?>
</p>
<ul class="pagination">
	<?php
		echo $this->Paginator->first(
			'&laquo;',
			[
				'tag' => 'li',
				'escape' => false
			]
		);
		echo $this->Paginator->prev(
			'<',
			[
				'tag' => 'li',
				'escape' => false
			],
			'<a href="#"><</a>',
			[
				'class' => 'prev disabled',
				'tag' => 'li',
				'escape' => false
			]
		);
		echo $this->Paginator->numbers([
			'separator' => '',
			'tag' => 'li',
			'currentLink' => true,
			'currentClass' => 'active',
			'currentTag' => 'a'
		]);

		echo $this->Paginator->next(
			'>',
			[
				'tag' => 'li',
				'escape' => false
			],
			'<a href="#">></a>',
			[
				'class' => 'prev disabled',
				'tag' => 'li',
				'escape' => false
			]
		);
		echo $this->Paginator->last(
			'&raquo;',
			[
				'tag' => 'li',
				'escape' => false
			]
		);
	?>
</ul>