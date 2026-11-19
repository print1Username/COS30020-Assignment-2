<?php
$communityDbPage = "detail";
require_once __DIR__."/community_db.php";
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<link rel="icon" href="public/logo.ico" sizes="any">
		<link rel="icon" type="image/png" href="public/logo.png">

		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Community Details | Boardgame Hub</title>
		<link
			href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
			integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
			crossorigin="anonymous"
		>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
		<link rel="stylesheet" href="style/navbar.css">
		<link
			rel="stylesheet"
			href="style/community_detail.css?v=<?php echo filemtime(__DIR__."/style/community_detail.css"); ?>"
		>
	</head>
	<body>
	<?php include "navbar.php"; ?>

	<main class="community-detail-page">
		<div class="container community-detail-container">
			<a href="community.php" class="community-detail-back-link"><i class="bi bi-arrow-left"></i> Back to
				Community</a>

			<?php if ($community === null): ?>
				<section class="community-detail-empty" aria-labelledby="not-found-title">
					<i class="bi bi-people-fill"></i>
					<h1 id="not-found-title">Community not found</h1>
					<p>The community you selected is unavailable or no longer exists.</p>
					<a href="community.php" class="btn community-detail-primary-button">View all communities</a>
				</section>
			<?php else: ?>
				<?php if ($quitNotice !== ""): ?>
					<div class="alert alert-success alert-dismissible fade show" role="status">
						<i class="bi bi-check-circle-fill" aria-hidden="true"></i>
						<?php echo e($quitNotice); ?>
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				<?php endif; ?>
				<?php if ($cancelNotice !== ""): ?>
					<div class="alert alert-success alert-dismissible fade show" role="status">
						<i class="bi bi-check-circle-fill" aria-hidden="true"></i>
						<?php echo e($cancelNotice); ?>
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				<?php endif; ?>
				<?php if ($joinError !== ""): ?>
					<div
						class="alert alert-danger" role="alert"
					><?php echo e($joinError); ?></div><?php endif; ?>
				<?php if ($postNotice !== ""): ?>
					<div class="alert alert-success alert-dismissible fade show" role="status">
						<i class="bi bi-check-circle-fill" aria-hidden="true"></i>
						<?php echo e($postNotice); ?>
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				<?php endif; ?>
				<?php if ($postError !== ""): ?>
					<div class="alert alert-danger" role="alert"><?php echo e($postError); ?></div>
				<?php endif; ?>
				<?php if ($joinStatus === "pending"): ?>
					<div class="alert alert-dismissible fade show community-detail-pending-alert" role="status">
						<i class="bi bi-hourglass-split" aria-hidden="true"></i>
						<span>Your request requires admin approval.</span>
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				<?php endif; ?>
				<?php
				$communityTitle = $community["title"] ?? "Untitled Community";
				$communityDescription = $community["description"] ?? "No description available.";
				$memberCount = (int)($community["member_count"] ?? 0);
				$createdAt = $community["created_at"] ?? "";
				$contributions = $community["contributions"] ?? [];
				?>
				<header class="community-detail-header">
					<span class="community-detail-eyebrow">BOARDGAME HUB COMMUNITY</span>
					<h1><?php echo e($communityTitle); ?></h1>
					<p><?php echo e($communityDescription); ?></p>
					<div class="community-detail-meta">
						<span><i class="bi bi-people-fill"></i> <?php echo $memberCount; ?> Members</span>
						<?php if ($createdAt !== ""): ?><span><i class="bi bi-calendar3"></i>
							Created <?php echo e(date("d M Y", strtotime($createdAt))); ?>
							</span><?php endif; ?>
					</div>
					<div class="community-detail-membership-actions">
						<form method="post" class="community-detail-join-form">
							<input
								type="hidden" name="join_token"
								value="<?php echo e($_SESSION["community_join_token"]); ?>"
							>
							<button
								type="submit" name="join_community" value="1"
								class="btn community-detail-primary-button<?php echo $joinStatus === "pending" ? " community-detail-pending-button" : ($joinStatus === "approved" ? " community-detail-approved-status" : ""); ?>" <?php echo in_array($joinStatus,
								["pending", "approved"], true) ? "disabled" : ""; ?>>
								<i class="bi <?php echo $joinStatus === "pending" ? "bi-hourglass-split" : ($joinStatus === "approved" ? "bi-check-circle-fill" : "bi-person-plus-fill"); ?>"></i>
								<?php echo $joinStatus === "pending" ? "Pending Approval" : ($joinStatus === "approved" ? "Already in Community" : "Join Community"); ?>
							</button>
						</form>
						<?php if ($joinStatus === "pending"): ?>
							<form method="post" class="community-detail-cancel-form">
								<input
									type="hidden" name="join_token"
									value="<?php echo e($_SESSION["community_join_token"]); ?>"
								>
								<button
									type="submit" name="cancel_community_request" value="1"
									class="btn community-detail-cancel-button"
								>
									<i class="bi bi-x-circle" aria-hidden="true"></i> Cancel
								</button>
							</form>
						<?php endif; ?>
						<?php if ($joinStatus === "approved"): ?>
							<form method="post" class="community-detail-quit-form">
								<input
									type="hidden" name="join_token"
									value="<?php echo e($_SESSION["community_join_token"]); ?>"
								>
								<button
									type="submit" name="quit_community" value="1"
									class="btn community-detail-quit-button"
								>
									<i class="bi bi-box-arrow-right" aria-hidden="true"></i> Quit
								</button>
							</form>
						<?php endif; ?>
					</div>
				</header>

				<section class="community-detail-contributions" aria-labelledby="contributions-title">
					<div class="community-detail-section-heading">
						<div><span class="community-detail-eyebrow">COMMUNITY SHOWCASE</span>
							<h2 id="contributions-title">Contributions</h2></div>
						<span
							class="community-detail-count"
						><?php echo count($contributions); ?> item<?php echo count($contributions) === 1 ? "" : "s"; ?></span>
					</div>

					<?php if ($joinStatus === "approved"): ?>
						<button
							type="button" class="community-post-composer community-post-composer-trigger"
							data-bs-toggle="modal" data-bs-target="#communityPostModal"
						>
							<span class="community-post-avatar">
								<?php if (!empty($postingUser["has_profile_image"])): ?>
									<img
										src="profile_image.php?email=<?php echo urlencode($_SESSION["email"]); ?>"
										alt="Your profile photo"
									>
								<?php else: ?>
									<i class="bi bi-person-circle" aria-hidden="true"></i>
								<?php endif; ?>
							</span>
							<span>Share something with this community</span>
							<i class="bi bi-pencil-square" aria-hidden="true"></i>
						</button>
						<div
							class="modal fade" id="communityPostModal" tabindex="-1"
							aria-labelledby="communityPostModalLabel" aria-hidden="true"
						>
							<div class="modal-dialog modal-dialog-centered">
								<div class="modal-content community-post-modal">
									<form method="post" enctype="multipart/form-data" class="community-post-form">
										<div class="modal-header">
											<h2 class="modal-title fs-5" id="communityPostModalLabel">Create a Community
												Post</h2>
											<button
												type="button" class="btn-close" data-bs-dismiss="modal"
												aria-label="Close"
											></button>
										</div>
										<div class="modal-body">
											<input
												type="hidden" name="post_token"
												value="<?php echo e($_SESSION["community_post_token"]); ?>"
											>
											<label for="community-post-title">Title</label>
											<input
												type="text" id="community-post-title" name="title" maxlength="100"
												required value="<?php echo e($_POST["title"] ?? ""); ?>"
											>
											<label for="community-post-description">Description</label>
											<textarea
												id="community-post-description" name="description" rows="5" required
											><?php echo e($_POST["description"] ?? ""); ?></textarea>
											<label for="community-post-image">Image <span>(optional, JPG/JPEG/PNG, max 5MB)</span></label>
											<input
												type="file" id="community-post-image" name="contribution_image"
												accept="image/jpeg,image/png"
											>
										</div>
										<div class="modal-footer">
											<button
												type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
											>Cancel
											</button>
											<button
												type="submit" name="submit_community_post" value="1"
												class="btn community-detail-primary-button"
											>Submit Post
											</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					<?php else: ?>
						<div class="community-post-composer community-post-composer-locked">
							<span class="community-post-avatar">
								<?php if (!empty($postingUser["has_profile_image"])): ?>
									<img
										src="profile_image.php?email=<?php echo urlencode($_SESSION["email"]); ?>"
										alt="Your profile photo"
									>
								<?php else: ?>
									<i class="bi bi-person-circle" aria-hidden="true"></i>
								<?php endif; ?>
							</span>
							<p><?php echo $joinStatus === "pending" ? "Your membership request is waiting for admin approval." : "You must join this community and receive admin approval before posting."; ?></p>
						</div>
					<?php endif; ?>

					<?php if (empty($contributions)): ?>
						<div class="community-detail-no-contributions"><i class="bi bi-image"></i>
							<p>No contributions have been shared yet.</p></div>
					<?php else: ?>
						<div class="community-detail-contribution-grid">
							<?php foreach ($contributions as $contribution): ?>
								<?php
								$media = $contribution["media"] ?? "";
								$mediaExists = $media !== "" && is_file(__DIR__."/".$media);
								$title = $contribution["title"] ?? "Untitled contribution";
								$description = $contribution["description"] ?? "No description available.";
								$contributor = trim(($contribution["first_name"] ?? "")." ".($contribution["last_name"] ?? ""));
								$contributionDate = $contribution["created_at"] ?? "";
								?>
								<article class="community-detail-card">
									<div class="community-post-user">
										<span class="community-post-avatar">
											<?php if (!empty($contribution["email"]) && !empty($contribution["has_profile_image"])): ?>
												<img
													src="profile_image.php?email=<?php echo urlencode($contribution["email"]); ?>"
													alt="<?php echo e($contributor); ?> profile photo"
												>
											<?php else: ?>
												<i class="bi bi-person-circle" aria-hidden="true"></i>
											<?php endif; ?>
										</span>
										<div class="community-post-user-details">
											<strong><?php echo e($contributor !== "" ? $contributor : "Unknown contributor"); ?></strong>
											<?php if ($contributionDate !== ""): ?>
												<timedatetime="<?php echo e(date("c",
													strtotime($contributionDate))); ?>"><?php echo e(date("d M Y, g:i A",
													strtotime($contributionDate))); ?></time><?php endif; ?>
										</div>
									</div>
									<div class="community-post-content">
										<h3><?php echo e($title); ?></h3>
										<p class="community-detail-description"><?php echo e($description); ?></p>
										<?php if ($mediaExists): ?><img class="community-post-image"
																		src="<?php echo e($media); ?>"
																		alt="<?php echo e($title); ?>"><?php endif; ?>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</section>
			<?php endif; ?>
		</div>
	</main>

	<script
		src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
		crossorigin="anonymous"
	></script>
	<?php if ($postError !== "" && $joinStatus === "approved"): ?>
		<script>
            bootstrap.Modal.getOrCreateInstance(document.getElementById("communityPostModal")).show();
		</script>
	<?php endif; ?>
	</body>
</html>
