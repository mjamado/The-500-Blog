<div class="paginator">
	Showing <?php echo $paginator->status['start']; ?> to <?php echo $paginator->status['end']; ?> of <?php echo $paginator->status['count']; ?> |
	<?php foreach($paginator->buttons as $paginatorButton): ?>
	<?php if($paginatorButton['active']): ?><a href="?page=<?php echo $paginatorButton['page']; ?><?php echo (isset($orderBy) ? ('&orderby=' . $orderBy) : '') . (isset($orderDir) ? ('&orderdir=' . $orderDir) : ''); ?>" title="Go to page <?php echo $paginatorButton['page']; ?>"><?php endif; ?><?php echo $paginatorButton['alt']; ?><?php if($paginatorButton['active']): ?></a><?php endif; ?>
	<?php endforeach; ?>
</div>