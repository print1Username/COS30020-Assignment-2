<?php
require_once __DIR__."/smart_feature_add_db.php";
?>
<!DOCTYPE html>
<html lang = "en">
	<head >
		<link
			rel = "icon"
			href = "img/logo/logo.ico"
			sizes = "any"
		>
		<link
			rel = "icon"
			type = "image/png"
			href = "img/logo/logo.png"
		>

		<meta charset = "utf-8">
		<meta
			name = "viewport"
			content = "width=device-width, initial-scale=1"
		>
		<title >Contribute a Board Game | Boardgame Hub</title >
		<link
			href = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
			rel = "stylesheet"
		>
		<link
			rel = "stylesheet"
			href = "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
		>
		<link
			rel = "stylesheet"
			href = "style/navbar.css"
		>
		<link
			rel = "stylesheet"
			href = "style/smart_feature_css.css"
		>
	</head >
	<body class = "smart-feature-page smart-feature-add-page">
	<?php include "navbar.php"; ?>
	<main >
		<header class = "smart-feature-hero">
			<div class = "container">
				<h1 >Contribute a Board Game</h1 >
				<p >Share a board game with the Boardgame Hub community.</p >
			</div >
		</header >
		<section class = "smart-feature-section">
			<div class = "container">
				<?php if ( !$isUser ): ?>
					<p class = "smart-feature-panel smart-feature-message">Please
						<a href = "login.php">log in as a user</a >
						to submit a contribution.
					</p >
				<?php else: ?>
					<?php if ( $error !== "" ): ?>
						<div
							class = "alert alert-danger"
							role = "alert"
						><?php echo htmlspecialchars($error); ?></div ><?php endif; ?>
					<?php if ( $success !== "" ): ?>
						<div
							class = "alert alert-success"
							role = "status"
						><?php echo htmlspecialchars($success); ?></div ><?php endif; ?>
					<form
						method = "post"
						action = "smart_feature_add.php"
						enctype = "multipart/form-data"
						class = "smart-feature-panel smart-feature-contribution-form"
					>
						<div class = "row g-4">
							<div class = "col-md-6">
								<label
									for = "item_name"
									class = "form-label"
								>Board game name
								</label >
								<input
									id = "item_name"
									name = "item_name"
									class = "form-control"
									maxlength = "50"
									value = "<?php echo htmlspecialchars($itemName); ?>"
									placeholder = "e.g. Catan"
									required
								>
							</div >
							<div class = "col-md-6">
								<label
									for = "item_category"
									class = "form-label"
								>Category
								</label >
								<select
									id = "item_category"
									name = "item_category"
									class = "form-select"
									required
								>
									<option value = "" disabled <?php echo $itemCategory === "" ? "selected" : ""; ?>>Choose a category</option >
									<?php foreach ( [ "Strategy", "Party Games", "Family Games", "Card Games", "Cooperative" ] as $category ): ?>
										<option value = "<?php echo htmlspecialchars($category); ?>" <?php echo $itemCategory === $category ? "selected" : ""; ?>><?php echo htmlspecialchars($category); ?></option >
									<?php endforeach; ?>
								</select >
							</div >
							<div class = "col-md-6">
								<span class = "form-label">Board game image <span class = "smart-feature-upload-optional">(optional)</span></span >
								<label class = "smart-feature-dropzone" data-smart-feature-dropzone>
									<input
										type = "file"
										id = "item_image"
										name = "item_image"
										class = "smart-feature-dropzone-input"
										accept = "image/jpeg,image/png"
										aria-describedby = "item-image-help item-image-file-name"
									>
									<span class = "smart-feature-dropzone-icon"><i class = "bi bi-cloud-arrow-up" aria-hidden = "true"></i></span>
									<span class = "smart-feature-dropzone-title">Choose an image or drag it here</span>
									<span class = "smart-feature-dropzone-help" id = "item-image-help">JPG or PNG, up to 5MB</span>
									<span class = "smart-feature-dropzone-file-name" id = "item-image-file-name" data-dropzone-file-name aria-live = "polite">No image selected</span>
								</label >
							</div >
							<div class = "col-md-6">
								<span class = "form-label">PDF description</span >
								<label class = "smart-feature-dropzone" data-smart-feature-dropzone>
									<input
										type = "file"
										id = "description_file"
										name = "description_file"
										class = "smart-feature-dropzone-input"
										accept = "application/pdf"
										required
										aria-describedby = "description-file-help description-file-name"
									>
									<span class = "smart-feature-dropzone-icon"><i class = "bi bi-file-earmark-arrow-up" aria-hidden = "true"></i></span>
									<span class = "smart-feature-dropzone-title">Choose a PDF or drag it here</span>
									<span class = "smart-feature-dropzone-help" id = "description-file-help">PDF file, up to 7MB</span>
									<span class = "smart-feature-dropzone-file-name" id = "description-file-name" data-dropzone-file-name aria-live = "polite">No PDF selected</span>
								</label >
							</div >
						</div >
						<button
							type = "submit"
							class = "btn smart-feature-button smart-feature-submit-button"
						><i class = "bi bi-send" aria-hidden = "true"></i> Submit for review
						</button >
					</form >
				<?php endif; ?>
			</div >
		</section >
	</main >
	<script>
		document.querySelectorAll('[data-smart-feature-dropzone]').forEach((dropzone) => {
			const input = dropzone.querySelector('input[type="file"]');
			const fileName = dropzone.querySelector('[data-dropzone-file-name]');
			if (!(input instanceof HTMLInputElement) || !fileName) return;

			const updateFileName = () => {
				fileName.textContent = input.files?.[0]?.name || 'No file selected';
			};

			input.addEventListener('change', updateFileName);
			dropzone.addEventListener('dragover', (event) => {
				event.preventDefault();
				dropzone.classList.add('is-dragover');
			});
			dropzone.addEventListener('dragleave', (event) => {
				if (event.relatedTarget instanceof Node && dropzone.contains(event.relatedTarget)) return;
				dropzone.classList.remove('is-dragover');
			});
			dropzone.addEventListener('drop', (event) => {
				event.preventDefault();
				dropzone.classList.remove('is-dragover');
				const file = event.dataTransfer?.files[0];
				if (!file || typeof DataTransfer === 'undefined') return;

				const transfer = new DataTransfer();
				transfer.items.add(file);
				input.files = transfer.files;
				input.dispatchEvent(new Event('change', { bubbles: true }));
			});
		});
	</script>
	</body >
</html >
