<?php
require_once __DIR__."/admin_db.php";
require_once __DIR__."/validation.php";
$kind = $reviewKind ?? "";
if (!in_array($kind, ["community", "activities"], true)) exit("Invalid management page.");
$community = $kind === "community";
$table = $community ? "community_table" : "activity_table";
$page = $community ? "manage_community.php" : "manage_activities.php";
$error = "";
$edit = null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
	adminCheckPost();
	$action = $_POST["action"] ?? "";
	$id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
	try {
		if ($action === "delete" && $id) {
			$statement = $pdo -> prepare("DELETE FROM $table WHERE id=?");
			$statement -> execute([$id]);
			adminRedirect($page, "Entry deleted.");
		}
		if (in_array($action, ["approved", "rejected"], true) && $id) {
			if ($community) {
				$pdo -> beginTransaction();
				$lookup = $pdo -> prepare("SELECT record_type, community_id, member_counted FROM community_table WHERE id=? FOR UPDATE");
				$lookup -> execute([$id]);
				$reviewRow = $lookup -> fetch();
				if (!$reviewRow) throw new RuntimeException("The community entry could not be found.");
				if ($reviewRow["record_type"] === "join" && $action === "approved" && !(int)$reviewRow["member_counted"]) {
					$increment = $pdo -> prepare("UPDATE community_table SET member_count = COALESCE(member_count, 0) + 1 WHERE id=? AND record_type='community'");
					$increment -> execute([$reviewRow["community_id"]]);
					if ($increment -> rowCount() !== 1) throw new RuntimeException("The selected community could not be found.");
					$statement = $pdo -> prepare("UPDATE community_table SET status='approved', member_counted=1 WHERE id=? AND record_type='join'");
					$statement -> execute([$id]);
				} else {
					$statement = $pdo -> prepare("UPDATE community_table SET status=? WHERE id=?");
					$statement -> execute([$action, $id]);
				}
				$pdo -> commit();
				adminRedirect($page, "Status updated.");
			}
			$pendingOnly = $community ? "" : " AND status='pending'";
			$statement = $pdo -> prepare("UPDATE $table SET status=? WHERE id=?".$pendingOnly);
			$statement -> execute([$action, $id]);
			if (!$community && $statement -> rowCount() === 0) throw new RuntimeException("Only pending registrations can be approved or rejected. Use Edit to change a reviewed registration.");
			adminRedirect($page, "Status updated.");
		}
		if (!in_array($action, ["add", "edit"],
				true) || ($action === "edit" && !$id)) throw new RuntimeException("Invalid action.");
		$first = trim($_POST["first_name"] ?? "");
		$last = trim($_POST["last_name"] ?? "");
		$status = trim($_POST["status"] ?? "pending");
		if (!isValidName($first) || !isValidName($last) || !in_array($status, ["pending", "approved", "rejected"],
				true)) throw new RuntimeException("Enter valid names and status.");
		if ($community) {
			$related = trim($_POST["related_activity"] ?? "");
			$image = trim($_POST["contribution_image"] ?? "");
			$title = trim($_POST["contribution_title"] ?? "");
			$description = trim($_POST["contribution_description"] ?? "");
			if ($related === "" || strlen($related) > 50 || strlen($image) > 100 || strlen($title) > 100) throw new RuntimeException("Enter valid contribution details.");
			if ($action === "add") {
				$statement = $pdo -> prepare("INSERT INTO community_table (first_name,last_name,related_activity,contribution_image,contribution_title,contribution_description,status) VALUES (?,?,?,?,?,?,?)");
				$statement -> execute([$first, $last, $related, $image ?: null, $title ?: null, $description ?: null, $status]);
			} else {
				$statement = $pdo -> prepare("UPDATE community_table SET first_name=?,last_name=?,related_activity=?,contribution_image=?,contribution_title=?,contribution_description=?,status=? WHERE id=?");
				$statement -> execute([$first, $last, $related, $image ?: null, $title ?: null, $description ?: null, $status, $id]);
			}
		} else {
			$email = trim($_POST["email"] ?? "");
			$contact = trim($_POST["contact_number"] ?? "");
			if (!isValidEmail($email) || strlen($contact) > 15) throw new RuntimeException("Enter valid registration details.");
			if ($action === "add") {
				if ($contact === "") throw new RuntimeException("Enter a contact number.");
				$activityId = filter_var($_POST["activity_id"] ?? null, FILTER_VALIDATE_INT);
				$activityCheck = $pdo -> prepare("SELECT date, time FROM activity_info WHERE id=? AND status='approved'");
				$activityCheck -> execute([$activityId ?: 0]);
				$selectedActivity = $activityCheck -> fetch();
				if (!$selectedActivity) throw new RuntimeException("Select a valid activity.");
				$statement = $pdo -> prepare("INSERT INTO activity_table (email,first_name,last_name,date,time,contact_number,status,activity_id) VALUES (?,?,?,?,?,?,?,?)");
				$statement -> execute([$email, $first, $last, $selectedActivity['date'], $selectedActivity['time'], $contact ?: null, 'pending', $activityId]);
			} else {
				$date = trim($_POST["date"] ?? "");
				$time = trim($_POST["time"] ?? "");
				if (($date !== "" && !isValidDate($date)) || ($time !== "" && !isValidTime($time))) throw new RuntimeException("Enter valid registration details.");
				$statement = $pdo -> prepare("UPDATE activity_table SET email=?,first_name=?,last_name=?,date=?,time=?,contact_number=?,status=? WHERE id=?");
				$statement -> execute([$email, $first, $last, $date ?: null, $time ?: null, $contact ?: null, $status, $id]);
			}
		}
		adminRedirect($page, "Entry saved.");
	} catch (RuntimeException|PDOException $exception) {
		if ($pdo -> inTransaction()) $pdo -> rollBack();
		$error = $exception instanceof PDOException ? "Unable to save entry. Please check the details." : $exception -> getMessage();
	}
}
if (isset($_GET["edit"])) {
	$statement = $pdo -> prepare("SELECT * FROM $table WHERE id=?");
	$statement -> execute([$_GET["edit"]]);
	$edit = $statement -> fetch() ?: null;
}
if ($community) {
	$rows = $pdo -> query("SELECT * FROM community_table ORDER BY id DESC") -> fetchAll();
} else {
	$events = $pdo -> query("SELECT id, title, activity_type, description, date, time, end_time, price, venue, table_name, status FROM activity_info ORDER BY date, time")
		-> fetchAll();
	$rows = $pdo -> query("SELECT registration.*, event.title AS activity_title FROM activity_table AS registration LEFT JOIN activity_info AS event ON event.id=registration.activity_id ORDER BY registration.id DESC")
		-> fetchAll();
}
$notice = $_SESSION["admin_notice"] ?? "";
unset($_SESSION["admin_notice"]);
