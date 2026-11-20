<?php
function adminReviewActions(int $id, string $status, string $subject, bool $joinRequest = false): void
{
	foreach ( [ 'approved' => [ 'Approve', 'bi-check-lg', 'btn-success' ], 'rejected' => [ 'Reject', 'bi-x-lg', 'btn-danger' ] ]
		as $action => [$label, $icon, $buttonClass]
	): ?>
		<form method = "post">
			<input
				type = "hidden"
				name = "token"
				value = "<?= adminEscape(adminToken()) ?>"
			>
			<input
				type = "hidden"
				name = "action"
				value = "<?= $joinRequest ? ($action === 'approved' ? 'join_approve' : 'join_reject') : $action ?>"
			>
			<input
				type = "hidden"
				name = "<?= $joinRequest ? 'join_id' : 'id' ?>"
				value = "<?= $id ?>"
			>
			<button
				type = "submit"
				class = "btn btn-sm <?= $buttonClass ?>"
				title = "<?= $label ?>"
				aria-label = "<?= $label ?> <?= adminEscape(
					$subject
				) ?> <?= $id ?>" <?= $status === $action ? 'disabled' : '' ?>
			>
				<i
					class = "bi <?= $icon ?>"
					aria-hidden = "true"
				></i >
			</button >
		</form >
	<?php endforeach;
}
