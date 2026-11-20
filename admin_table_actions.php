<?php
require_once __DIR__."/admin_review_actions.php";

function adminTableActions(string $mode, array $options): void
{
	if ( $mode === 'review' ) {
		adminReviewActions(
			(int)$options['id'], $options['status'], $options['subject'],
			$options['join_request'] ?? false
		);
		return;
	}
	if ( !in_array($mode, [ 'edit_delete', 'delete_only' ], true) ) {
		throw new InvalidArgumentException('Invalid table action mode.');
	}
	if ( $mode === 'edit_delete' ): ?>
		<a class = "btn btn-sm btn-outline-primary" href = "<?= adminEscape($options['edit_url']) ?>">Edit</a >
	<?php endif; ?>
	<form method = "post" onsubmit = "return confirm('<?= adminEscape($options['confirm']) ?>')">
		<input type = "hidden" name = "token" value = "<?= adminEscape(adminToken()) ?>">
		<input type = "hidden" name = "action" value = "<?= adminEscape($options['delete_action']) ?>">
		<input
			type = "hidden" name = "<?= adminEscape($options['id_field']) ?>"
			value = "<?= adminEscape($options['id']) ?>"
		>
		<button
			type = "submit"
			class = "btn btn-sm btn-outline-danger" <?= !empty($options['disable_delete']) ? 'disabled' : '' ?>>Delete
		</button >
	</form >
	<?php
}
