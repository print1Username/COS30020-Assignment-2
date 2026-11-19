<?php

function renderFileUpload(array $config): void
{
	$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
	$id = (string)($config["id"] ?? "file-upload");
	$name = (string)($config["name"] ?? "file");
	$label = (string)($config["label"] ?? "Upload File");
	$accept = (string)($config["accept"] ?? "");
	$kind = ($config["kind"] ?? "file") === "image" ? "image" : "file";
	$types = (string)($config["types"] ?? "Allowed file types");
	$maxSize = (string)($config["max_size"] ?? "maximum size applies");
	$currentUrl = (string)($config["current_url"] ?? "");
	$currentName = (string)($config["current_name"] ?? "");
	$hasCurrent = $currentUrl !== "";
	$autoSubmit = !empty($config["auto_submit"]);
	$allowRemove = !empty($config["allow_remove"]) && $hasCurrent;
	$removeName = (string)($config["remove_name"] ?? "remove_file");
	$removeValue = (string)($config["remove_value"] ?? "1");
	$theme = ($_SESSION["type"] ?? "user") === "admin" ? "admin" : "user";
	$icon = $kind === "image" ? "bi-file-earmark-image" : "bi-file-earmark-pdf";
	?>
	<div
		class="file-upload file-upload--<?php echo $escape($theme); ?>"
		data-file-upload data-file-kind="<?php echo $escape($kind); ?>"
		data-auto-submit="<?php echo $autoSubmit ? "true" : "false"; ?>"
	>
		<div class="file-upload__surface">
			<div class="file-upload__visual" data-file-upload-visual>
				<?php if ($kind === "image" && $hasCurrent): ?>
					<img src="<?php echo $escape($currentUrl); ?>" alt="Current uploaded image">
				<?php else: ?>
					<i class="bi <?php echo $escape($icon); ?>" aria-hidden="true"></i>
				<?php endif; ?>
			</div>
			<div class="file-upload__details">
				<?php if ($hasCurrent && $kind !== "image"): ?>
					<a
						class="file-upload__name" href="<?php echo $escape($currentUrl); ?>" data-file-upload-name
						target="_blank" rel="noopener"
					><?php echo $escape($currentName !== "" ? $currentName : basename($currentUrl)); ?></a>
				<?php else: ?>
					<span
						class="file-upload__name"
						data-file-upload-name
					><?php echo $escape($hasCurrent && $currentName !== "" ? $currentName : $label); ?></span>
				<?php endif; ?>
				<small class="file-upload__requirements"><?php echo $escape($types); ?>
					, <?php echo $escape($maxSize); ?></small>
			</div>
			<div class="file-upload__actions">
				<input
					type="file" id="<?php echo $escape($id); ?>" name="<?php echo $escape($name); ?>"
					accept="<?php echo $escape($accept); ?>" data-file-upload-input hidden
				>
				<button type="button" class="btn file-upload__button" data-file-upload-button>
					<i class="bi bi-upload" aria-hidden="true"></i>
					<?php echo $hasCurrent ? "Replace" : "Upload"; ?>
				</button>
				<?php if ($allowRemove): ?>
					<button
						type="submit" name="<?php echo $escape($removeName); ?>"
						value="<?php echo $escape($removeValue); ?>" class="btn file-upload__remove"
					><i class="bi bi-trash" aria-hidden="true"></i> Remove
					</button>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}
