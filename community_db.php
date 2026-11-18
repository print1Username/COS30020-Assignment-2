<?php
session_start();

if (($_SESSION["logged_in"] ?? false) !== true ||
	($_SESSION["type"] ?? null) !== "user" ||
	!isset($_SESSION["email"])) {
	header("Location: login.php");
	exit;
}


if (($communityDbPage ?? "list") === "detail") {
	$communityId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	$community = null;
	$joinStatus = null;
	$joinError = "";

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

	function communityDetailEscape($value)
	{
		return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
	}
} else {
	/*
	 * ==========================
	 * Community Data
	 * ==========================
	 */

	require_once __DIR__."/db_setup.php";
	require_once __DIR__."/db_connect.php";
	$communityStatement = $pdo -> prepare("SELECT community.id, COALESCE(community.title, community.contribution_title, community.related_activity) AS title, COALESCE(community.description, community.contribution_description, '') AS description, COALESCE(community.member_count, 0) AS member_count, community.created_at, membership.status AS join_status
		FROM community_table AS community
		LEFT JOIN community_table AS membership ON membership.community_id = community.id AND membership.email = ? AND membership.record_type = 'join'
		WHERE community.record_type = 'community' AND community.status = 'approved'");
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

	function communityEscape($value)
	{
		return htmlspecialchars(
			(string)$value,
			ENT_QUOTES,
			"UTF-8"
		);
	}

	function communitySelected($current, $value)
	{
		return $current === $value ? "checked" : "";
	}
}
?>
