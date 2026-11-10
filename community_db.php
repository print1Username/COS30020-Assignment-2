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

	if ($communityId !== false && $communityId !== null) {
		require_once __DIR__."/db_setup.php";
		require_once __DIR__."/db_connect.php";
		$statement = $pdo -> prepare("SELECT * FROM community_table WHERE id = ? AND status = 'approved'");
		$statement -> execute([$communityId]);
		$row = $statement -> fetch();
		if ($row) {
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
	$communities = $pdo -> query("SELECT id, COALESCE(title, contribution_title, related_activity) AS title, COALESCE(description, contribution_description, '') AS description, COALESCE(member_count, 0) AS member_count, created_at FROM community_table WHERE status = 'approved'") -> fetchAll();

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
