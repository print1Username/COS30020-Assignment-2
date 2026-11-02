<?php
session_start();

$dataFile = "data/community.json";
$communityId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$community = null;

if ($communityId !== false && $communityId !== null && file_exists($dataFile)) {
	$communities = json_decode(file_get_contents($dataFile), true);

	if (is_array($communities)) {
		foreach ($communities as $item) {
			if ((int)($item["id"] ?? 0) === $communityId) {
				$community = $item;
				break;
			}
		}
	}
}

function communityDetailEscape($value)
{
	return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

?>

<!DOCTYPE html>
<html lang="en">
	<head>
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
		<link rel="stylesheet" href="style/community_detail.css">
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
				<?php
				$communityTitle = $community["title"] ?? "Untitled Community";
				$communityDescription = $community["description"] ?? "No description available.";
				$memberCount = (int)($community["member_count"] ?? 0);
				$createdAt = $community["created_at"] ?? "";
				$contributions = $community["contributions"] ?? [];
				?>
				<header class="community-detail-header">
					<span class="community-detail-eyebrow">BOARDGAME HUB COMMUNITY</span>
					<h1><?php echo communityDetailEscape($communityTitle); ?></h1>
					<p><?php echo communityDetailEscape($communityDescription); ?></p>
					<div class="community-detail-meta">
						<span><i class="bi bi-people-fill"></i> <?php echo $memberCount; ?> Members</span>
						<?php if ($createdAt !== ""): ?><span><i class="bi bi-calendar3"></i>
							Created <?php echo communityDetailEscape(date("d M Y", strtotime($createdAt))); ?>
							</span><?php endif; ?>
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

					<?php if (empty($contributions)): ?>
						<div class="community-detail-no-contributions"><i class="bi bi-image"></i>
							<p>No contributions have been shared yet.</p></div>
					<?php else: ?>
						<div class="community-detail-contribution-grid">
							<?php foreach ($contributions as $contribution): ?>
								<?php
								$mediaType = $contribution["media_type"] ?? "image";
								$media = $contribution["media"] ?? "";
								$mediaExists = $media !== "" && file_exists($media);
								$title = $contribution["title"] ?? "Untitled contribution";
								$description = $contribution["description"] ?? "No description available.";
								$contributor = $contribution["contributor"] ?? "Unknown contributor";
								$activity = $contribution["activity"] ?? "Not specified";
								$contributionDate = $contribution["created_at"] ?? "";
								?>
								<article class="community-detail-card">
									<div class="community-detail-media">
										<?php if ($mediaType === "video" && $mediaExists): ?>
											<video controls>
												<source src="<?php echo communityDetailEscape($media); ?>">
												Your browser does not support video playback.
											</video>
										<?php elseif ($mediaExists): ?>
											<img
												src="<?php echo communityDetailEscape($media); ?>"
												alt="<?php echo communityDetailEscape($title); ?>"
											>
										<?php else: ?>
											<div
												class="community-detail-media-placeholder"
												aria-label="Contribution media unavailable"
											><i
													class="bi bi-image"
												></i><span>Media unavailable</span></div>
										<?php endif; ?>
									</div>
									<div class="community-detail-card-body">
										<div class="community-detail-card-title-row">
											<h3><?php echo communityDetailEscape($title); ?></h3>
											<?php if ($contributionDate !== ""): ?>
												<time
												datetime="<?php echo communityDetailEscape($contributionDate); ?>"><?php echo communityDetailEscape(date("d M Y", strtotime($contributionDate))); ?></time><?php endif; ?>
										</div>
										<p class="community-detail-description"><?php echo communityDetailEscape($description); ?></p>
										<dl class="community-detail-information">
											<div>
												<dt><i class="bi bi-person-fill"></i> Contributor</dt>
												<dd><?php echo communityDetailEscape($contributor); ?></dd>
											</div>
											<div>
												<dt><i class="bi bi-controller"></i> Related activity</dt>
												<dd><?php echo communityDetailEscape($activity); ?></dd>
											</div>
										</dl>
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
	</body>
</html>
