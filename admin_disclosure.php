<?php
function adminDisclosureStart(string $title, string $subtitle, bool $open): void
{ ?>
	<details class="admin-panel admin-disclosure mb-4" <?= $open ? 'open' : '' ?>>
	<summary class="admin-disclosure-header"><span><span
				class="admin-disclosure-title"
			><?= adminEscape($title) ?></span><span
				class="admin-disclosure-subtitle"
			><?= adminEscape($subtitle) ?></span></span>
	</summary>
	<?php
}

function adminDisclosureEnd(): void
{ ?>
	</details>
	<?php
}
