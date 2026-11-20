<?php
require_once __DIR__."/auth.php";
require_once __DIR__."/helpers.php";
requireUser();


if (($communityDbPage ?? "list") === "detail") {
	$communityId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	$community = null;
	$joinStatus = null;
	$joinError = "";
	$quitNotice = $_SESSION["community_quit_notice"] ?? "";
	$cancelNotice = $_SESSION["community_cancel_notice"] ?? "";
	$postNotice = $_SESSION["community_post_notice"] ?? "";
	$postError = "";
	unset($_SESSION["community_quit_notice"]);
	unset($_SESSION["community_cancel_notice"]);
	unset($_SESSION["community_post_notice"]);

	if ($communityId !== false && $communityId !== null) {
		require_once __DIR__."/db_connect.php";
		$statement = $pdo -> prepare("SELECT id, title, description, member_count, created_at FROM community_info WHERE id = ?");
		$statement -> execute([$communityId]);
		$row = $statement -> fetch();
		if ($row) {
			$_SESSION["community_join_token"] ??= bin2hex(random_bytes(32));
			$joinStatement = $pdo -> prepare("SELECT status FROM community_member WHERE community_id = ? AND email = ? LIMIT 1");
			$joinStatement -> execute([$communityId, $_SESSION["email"]]);
			$joinStatus = $joinStatement -> fetchColumn() ?: null;
			$_SESSION["community_post_token"] ??= bin2hex(random_bytes(32));
			$postUserStatement = $pdo -> prepare("SELECT first_name, last_name, profile_image_data IS NOT NULL AND OCTET_LENGTH(profile_image_data) > 0 AS has_profile_image FROM user_table WHERE email = ?");
			$postUserStatement -> execute([$_SESSION["email"]]);
			$postingUser = $postUserStatement -> fetch();
			if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["join_community"])) {
				if (!hash_equals($_SESSION["community_join_token"], (string)($_POST["join_token"] ?? ""))) {
					$joinError = "Invalid join request. Please try again.";
				} elseif (in_array($joinStatus, ["pending", "approved"], true)) {
					$joinError = "You have already applied to or joined this community.";
				} else {
					try {
						if ($joinStatus === "rejected") {
							$saveJoin = $pdo -> prepare("UPDATE community_member SET status = 'pending' WHERE community_id = ? AND email = ?");
							$saveJoin -> execute([$communityId, $_SESSION["email"]]);
						} else {
							$saveJoin = $pdo -> prepare("INSERT INTO community_member (community_id, email, status) VALUES (?, ?, 'pending')");
							$saveJoin -> execute([$communityId, $_SESSION["email"]]);
						}
						$joinStatus = "pending";
					} catch (RuntimeException|PDOException $exception) {
						$joinError = $exception instanceof PDOException ? "The join request could not be saved. Please try again." : $exception -> getMessage();
					}
				}
			}
			if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit_community_post"])) {
				if (!hash_equals($_SESSION["community_post_token"], (string)($_POST["post_token"] ?? ""))) {
					$postError = "Invalid post request. Please try again.";
				} elseif ($joinStatus !== "approved") {
					$postError = "You must be an approved member before posting.";
				} elseif (!$postingUser) {
					$postError = "Your account details could not be found.";
				} else {
					$title = trim($_POST["title"] ?? "");
					$description = trim($_POST["description"] ?? "");
					$imagePath = null;
					$imageData = null;
					$imageMime = null;
					try {
						if ($title === "" || mb_strlen($title) > 100) {
							throw new RuntimeException("Title is required and must not exceed 100 characters.");
						}
						if ($description === "") {
							throw new RuntimeException("Description is required.");
						}
						if (isset($_FILES["contribution_image"]) && $_FILES["contribution_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
							$imagePath = saveValidatedUpload($_FILES["contribution_image"], "community_images",
								["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png"], 5 * 1024 * 1024);
							$imageData = file_get_contents(__DIR__."/".$imagePath);
							$imageMime = (new finfo(FILEINFO_MIME_TYPE)) -> file(__DIR__."/".$imagePath);
							if ($imageData === false || !in_array($imageMime, ["image/jpeg", "image/png"], true)) {
								throw new RuntimeException("The uploaded image could not be stored.");
							}
						}
						$savePost = $pdo -> prepare("INSERT INTO community_table (community_id, email, first_name, last_name, contribution_image, community_image_data, community_image_mime, related_activity, status, title, description, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, NOW())");
						$savePost -> execute([$communityId, $_SESSION["email"], $postingUser["first_name"], $postingUser["last_name"], $imagePath, $imageData, $imageMime, $row["title"], $title, $description]);
						$_SESSION["community_post_notice"] = "Your post has been submitted and is waiting for admin approval.";
						header("Location: community_detail.php?id=".urlencode((string)$communityId));
						exit;
					} catch (RuntimeException|PDOException $exception) {
						if ($imagePath !== null && is_file(__DIR__."/".$imagePath)) {
							unlink(__DIR__."/".$imagePath);
						}
						$postError = $exception instanceof PDOException ? "Your post could not be saved. Please try again." : $exception -> getMessage();
					}
				}
			}
			if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cancel_community_request"])) {
				if (!hash_equals($_SESSION["community_join_token"], (string)($_POST["join_token"] ?? ""))) {
					$joinError = "Invalid cancellation request. Please try again.";
				} else {
					try {
						$cancelStatement = $pdo -> prepare("DELETE FROM community_member WHERE community_id = ? AND email = ? AND status = 'pending'");
						$cancelStatement -> execute([$communityId, $_SESSION["email"]]);
						if ($cancelStatement -> rowCount() !== 1) {
							throw new RuntimeException("Your pending request could not be found.");
						}
						$_SESSION["community_cancel_notice"] = "Your community join request has been cancelled.";
						header("Location: community_detail.php?id=".urlencode((string)$communityId));
						exit;
					} catch (RuntimeException|PDOException $exception) {
						$joinError = $exception instanceof PDOException ? "The join request could not be cancelled. Please try again." : $exception -> getMessage();
					}
				}
			}
			if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["quit_community"])) {
				if (!hash_equals($_SESSION["community_join_token"], (string)($_POST["join_token"] ?? ""))) {
					$joinError = "Invalid quit request. Please try again.";
				} else {
					try {
						$pdo -> beginTransaction();
						$membershipStatement = $pdo -> prepare("SELECT id FROM community_member WHERE community_id = ? AND email = ? AND status = 'approved' FOR UPDATE");
						$membershipStatement -> execute([$communityId, $_SESSION["email"]]);
						$membership = $membershipStatement -> fetch();
						if (!$membership) {
							throw new RuntimeException("Your community membership could not be found.");
						}
						$decrementStatement = $pdo -> prepare("UPDATE community_info SET member_count = GREATEST(member_count - 1, 0) WHERE id = ?");
						$decrementStatement -> execute([$communityId]);
						if ($decrementStatement -> rowCount() !== 1) {
							throw new RuntimeException("The selected community could not be found.");
						}
						$deleteStatement = $pdo -> prepare("DELETE FROM community_member WHERE id = ? AND community_id = ? AND email = ?");
						$deleteStatement -> execute([$membership["id"], $communityId, $_SESSION["email"]]);
						if ($deleteStatement -> rowCount() !== 1) {
							throw new RuntimeException("Your community membership could not be removed.");
						}
						$pdo -> commit();
						$_SESSION["community_quit_notice"] = "You have successfully left this community.";
						header("Location: community_detail.php?id=".urlencode((string)$communityId));
						exit;
					} catch (RuntimeException|PDOException $exception) {
						if ($pdo -> inTransaction()) {
							$pdo -> rollBack();
						}
						$joinError = $exception instanceof PDOException ? "The quit request could not be completed. Please try again." : $exception -> getMessage();
					}
				}
			}
			$contributionStatement = $pdo -> prepare("SELECT post.id, post.title, post.description, post.created_at, post.first_name, post.last_name, post.email, post.community_image_data IS NOT NULL AND OCTET_LENGTH(post.community_image_data) > 0 AS has_community_image, user.profile_image_data IS NOT NULL AND OCTET_LENGTH(user.profile_image_data) > 0 AS has_profile_image
				FROM community_table AS post
				LEFT JOIN user_table AS user ON user.email = post.email
				WHERE post.community_id = ? AND post.status = 'approved'
				ORDER BY post.created_at DESC, post.id DESC");
			$contributionStatement -> execute([$communityId]);
			$contributions = $contributionStatement -> fetchAll();
			$community = [
				"title" => $row["title"],
				"description" => $row["description"] ?? "",
				"member_count" => $row["member_count"] ?? 0,
				"created_at" => $row["created_at"] ?? "",
				"contributions" => $contributions
			];
		}
	}

} else {
	/*
	 * ==========================
	 * Community Data
	 * ==========================
	 */

	require_once __DIR__."/db_connect.php";
	$communityStatement = $pdo -> prepare("SELECT community.id, community.title, COALESCE(community.description, '') AS description, community.member_count, community.created_at, membership.status AS join_status
		FROM community_info AS community
		LEFT JOIN community_member AS membership ON membership.community_id = community.id AND membership.email = ?");
	$communityStatement -> execute([$_SESSION["email"]]);
	$communities = $communityStatement -> fetchAll();

	/*
	 * ==========================
	 * Sorting Settings
	 * ==========================
	 */

	$membershipFilter = $_GET["membership_filter"] ?? "all";
	$sortType = $_GET["sort_type"] ?? "alphabetical";
	$sortOrder = $_GET["sort_order"] ?? "ascending";

	$allowedMembershipFilters = ["all", "joined", "pending", "not_joined"];
	$allowedSortTypes = ["time", "alphabetical"];
	$allowedSortOrders = ["ascending", "descending"];

	if (!in_array($membershipFilter, $allowedMembershipFilters, true)) {
		$membershipFilter = "all";
	}

	if (!in_array($sortType, $allowedSortTypes, true)) {
		$sortType = "alphabetical";
	}

	if (!in_array($sortOrder, $allowedSortOrders, true)) {
		$sortOrder = "ascending";
	}

	/*
	 * ==========================
	 * Filtering Logic
	 * ==========================
	 */

	$communities = array_values(array_filter($communities, function($community) use ($membershipFilter) {
		$joinStatus = $community["join_status"] ?? null;

		return match ($membershipFilter) {
			"joined" => $joinStatus === "approved",
			"pending" => $joinStatus === "pending",
			"not_joined" => !in_array($joinStatus, ["approved", "pending"], true),
			default => true
		};
	}));

	/*
	 * ==========================
	 * Sorting Logic
	 * ==========================
	 */

	usort($communities, function($a, $b) use ($sortType, $sortOrder) {
		if ($sortType === "alphabetical") {
			$result = strcasecmp(
				$a["title"] ?? "",
				$b["title"] ?? ""
			);
		} else {
			$result = strcmp(
				$a["created_at"] ?? "",
				$b["created_at"] ?? ""
			);
		}

		return $sortOrder === "descending" ? -$result : $result;
	});

	/*
	 * ==========================
	 * Helper Function
	 * ==========================
	 */

	function communitySelected($current, $value)
	{
		return $current === $value ? "checked" : "";
	}
}
?>
