<?php
require_once __DIR__."/admin_db.php";
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
		$registrationOnly = $community ? "" : " AND record_type='registration'";
		if ($action === "delete" && $id) {
			$statement = $pdo->prepare("DELETE FROM $table WHERE id=?".$registrationOnly);
			$statement->execute([$id]);
			adminRedirect($page, "Entry deleted.");
		}
		if (in_array($action, ["approved", "rejected"], true) && $id) {
			$statement = $pdo->prepare("UPDATE $table SET status=? WHERE id=?".$registrationOnly);
			$statement->execute([$action, $id]);
			adminRedirect($page, "Status updated.");
		}
		if (!in_array($action, ["add", "edit"], true) || ($action === "edit" && !$id)) throw new RuntimeException("Invalid action.");
		$first = trim($_POST["first_name"] ?? "");
		$last = trim($_POST["last_name"] ?? "");
		$status = trim($_POST["status"] ?? "pending");
		if (!preg_match('/^[A-Za-z ]{1,50}$/', $first) || !preg_match('/^[A-Za-z ]{1,50}$/', $last) || !in_array($status, ["pending", "approved", "rejected"], true)) throw new RuntimeException("Enter valid names and status.");
		if ($community) {
			$related = trim($_POST["related_activity"] ?? "");
			$image = trim($_POST["contribution_image"] ?? "");
			$title = trim($_POST["contribution_title"] ?? "");
			$description = trim($_POST["contribution_description"] ?? "");
			if ($related === "" || strlen($related) > 50 || strlen($image) > 100 || strlen($title) > 100) throw new RuntimeException("Enter valid contribution details.");
			if ($action === "add") {
				$statement = $pdo->prepare("INSERT INTO community_table (first_name,last_name,related_activity,contribution_image,contribution_title,contribution_description,status) VALUES (?,?,?,?,?,?,?)");
				$statement->execute([$first,$last,$related,$image ?: null,$title ?: null,$description ?: null,$status]);
			} else {
				$statement = $pdo->prepare("UPDATE community_table SET first_name=?,last_name=?,related_activity=?,contribution_image=?,contribution_title=?,contribution_description=?,status=? WHERE id=?");
				$statement->execute([$first,$last,$related,$image ?: null,$title ?: null,$description ?: null,$status,$id]);
			}
		} else {
			$email = trim($_POST["email"] ?? "");
			$date = trim($_POST["date"] ?? "");
			$time = trim($_POST["time"] ?? "");
			$contact = trim($_POST["contact_number"] ?? "");
			if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>50 || strlen($contact)>15 || ($date !== "" && (!($parsed = DateTime::createFromFormat('!Y-m-d',$date)) || $parsed->format('Y-m-d') !== $date)) || ($time !== "" && !preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/',$time))) throw new RuntimeException("Enter valid registration details.");
			if ($action === "add") {
				$statement = $pdo->prepare("INSERT INTO activity_table (email,first_name,last_name,date,time,contact_number,status,record_type) VALUES (?,?,?,?,?,?,?,'registration')");
				$statement->execute([$email,$first,$last,$date ?: null,$time ?: null,$contact ?: null,$status]);
			} else {
				$statement = $pdo->prepare("UPDATE activity_table SET email=?,first_name=?,last_name=?,date=?,time=?,contact_number=?,status=? WHERE id=? AND record_type='registration'");
				$statement->execute([$email,$first,$last,$date ?: null,$time ?: null,$contact ?: null,$status,$id]);
			}
		}
		adminRedirect($page, "Entry saved.");
	} catch (RuntimeException|PDOException $exception) {
		$error = $exception instanceof PDOException ? "Unable to save entry. Please check the details." : $exception->getMessage();
	}
}
if (isset($_GET["edit"])) {
	$statement = $pdo->prepare("SELECT * FROM $table WHERE id=?".($community ? "" : " AND record_type='registration'"));
	$statement->execute([$_GET["edit"]]);
	$edit = $statement->fetch() ?: null;
}
$rows = $pdo->query("SELECT * FROM $table".($community ? "" : " WHERE record_type='registration'")." ORDER BY id DESC")->fetchAll();
$notice = $_SESSION["admin_notice"] ?? "";
unset($_SESSION["admin_notice"]);
