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
	unset($_SESSION["community_quit_notice"]);

	if ($communityId !== false && $communityId !== null) {
		require_once __DIR__."/db_setup.php";
		require_once __DIR__."/db_connect.php";
		$statement = $pdo -> prepare("SELECT * FROM community_table WHERE id = ? AND record_type = 'community' AND status = 'approved'");
		$statement -> execute([$communityId]);
		$row = $statement -> fetch();
		if ($row) {
			$_SESSION["community_join_token"] ??= bin2hex(random_bytes(32));
			$joinStatement = $pdo -> prepare("SELECT status FROM community_table WHERE community_id = ? AND email = ? AND record_type = 'join' LIMIT 1");
			$joinStatement -> execute([$communityId, $_SESSION["email"]]);
			$joinStatus = $joinStatement -> fetchColumn() ?: null;
			if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["join_community"])) {
				if (!hash_equals($_SESSION["community_join_token"], (string)($_POST["join_token"] ?? ""))) {
					$joinError = "Invalid join request. Please try again.";
				} elseif (in_array($joinStatus, ["pending", "approved"], true)) {
					$joinError = "You have already applied to or joined this community.";
				} else {
					try {
						$userStatement = $pdo -> prepare("SELECT first_name, last_name FROM user_table WHERE email = ?");
						$userStatement -> execute([$_SESSION["email"]]);
						$joinUser = $userStatement -> fetch();
						if (!$joinUser) {
							throw new RuntimeException("Your account details could not be found.");
						}
						if ($joinStatus === "rejected") {
							$saveJoin = $pdo -> prepare("UPDATE community_table SET status = 'pending', first_name = ?, last_name = ?, related_activity = ? WHERE community_id = ? AND email = ? AND record_type = 'join'");
							$saveJoin -> execute([$joinUser["first_name"], $joinUser["last_name"], $row["title"], $communityId, $_SESSION["email"]]);
						} else {
							$saveJoin = $pdo -> prepare("INSERT INTO community_table (first_name, last_name, related_activity, status, record_type, community_id, email, contribution_title) VALUES (?, ?, ?, 'pending', 'join', ?, ?, 'Community Join Request')");
							$saveJoin -> execute([$joinUser["first_name"], $joinUser["last_name"], $row["title"], $communityId, $_SESSION["email"]]);
						}
						$joinStatus = "pending";
					} catch (RuntimeException|PDOException $exception) {
						$joinError = $exception instanceof PDOException ? "The join request could not be saved. Please try again." : $exception -> getMessage();
					}
				}
			}
			if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["quit_community"])) {
				if (!hash_equals($_SESSION["community_join_token"], (string)($_POST["join_token"] ?? ""))) {
					$joinError = "Invalid quit request. Please try again.";
				} else {
					try {
						$pdo -> beginTransaction();
						$membershipStatement = $pdo -> prepare("SELECT id, member_counted FROM community_table WHERE community_id = ? AND email = ? AND record_type = 'join' AND status = 'approved' FOR UPDATE");
						$membershipStatement -> execute([$communityId, $_SESSION["email"]]);
						$membership = $membershipStatement -> fetch();
						if (!$membership) {
							throw new RuntimeException("Your community membership could not be found.");
						}
						if ((int)$membership["member_counted"] === 1) {
							$decrementStatement = $pdo -> prepare("UPDATE community_table SET member_count = GREATEST(COALESCE(member_count, 0) - 1, 0) WHERE id = ? AND record_type = 'community'");
							$decrementStatement -> execute([$communityId]);
							if ($decrementStatement -> rowCount() !== 1) {
								throw new RuntimeException("The selected community could not be found.");
							}
						}
						$deleteStatement = $pdo -> prepare("DELETE FROM community_table WHERE id = ? AND community_id = ? AND email = ? AND record_type = 'join'");
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
			$community = [
				"title" => $row["title"] ?: ($row["contribution_title"] ?: $row["related_activity"]),
				"description" => $row["description"] ?: ($row["contribution_description"] ?? ""),
				"member_count" => $row["member_count"] ?? 0,
				"created_at" => $row["created_at"] ?? "",
				"contributions" => [[
					"title" => $row["contribution_title"] ?: ($row["title"] ?: $row["related_activity"]),
					"description" => $row["contribution_description"] ?? "",
					"contributor" => trim($row["first_name"]." ".$row["last_name"]),
					"activity" => $row["related_activity"],
					"media_type" => "image",
					"media" => $row["contribution_image"] ?? "",
					"created_at" => $row["contribution_date"] ?? ""
				]]
			];
		}
	}

} else {
	/*
	 * ==========================
	 * Community Data
	 * ==========================
	 */

	require_once __DIR__."/db_setup.php";
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

	$sortType = $_GET["sort_type"] ?? "time";
	$sortOrder = $_GET["sort_order"] ?? "ascending";

	$allowedSortTypes = ["time", "alphabetical"];
	$allowedSortOrders = ["ascending", "descending"];

	if (!in_array($sortType, $allowedSortTypes, true)) {
		$sortType = "time";
	}

	if (!in_array($sortOrder, $allowedSortOrders, true)) {
		$sortOrder = "ascending";
	}

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
