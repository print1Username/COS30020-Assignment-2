<?php
require_once __DIR__."/admin_db.php";
require_once __DIR__."/validation.php";

$activityError = "";
$activityEdit = null;

if ( $_SERVER["REQUEST_METHOD"] === "POST" && in_array(
		$_POST["action"] ?? "",
		[ "activity_add", "activity_edit", "activity_delete" ], true
	) ) {
	adminCheckPost();
	$activityAction = $_POST["action"];
	$activityId = filter_var($_POST["activity_id"] ?? null, FILTER_VALIDATE_INT);
	try {
		if ( $activityAction === "activity_delete" ) {
			if ( !$activityId ) throw new RuntimeException("Invalid activity.");
			$pdo -> beginTransaction();
			$activityCheck = $pdo -> prepare("SELECT id FROM activity_info WHERE id=? FOR UPDATE");
			$activityCheck -> execute([ $activityId ]);
			if ( !$activityCheck -> fetch() ) throw new RuntimeException("Activity not found.");
			$registrationCheck = $pdo -> prepare("SELECT COUNT(*) FROM activity_table WHERE activity_id=?");
			$registrationCheck -> execute([ $activityId ]);
			if ( (int)$registrationCheck -> fetchColumn() > 0 ) {
				throw new RuntimeException("Remove this activity's registrations before deleting it.");
			}
			$delete = $pdo -> prepare("DELETE FROM activity_info WHERE id=?");
			$delete -> execute([ $activityId ]);
			$pdo -> commit();
			adminRedirect("manage_activities.php?tab=activities", "Activity deleted.");
		}

		$title = trim($_POST["activity_title"] ?? "");
		$type = trim($_POST["activity_type"] ?? "");
		$description = trim($_POST["activity_description"] ?? "");
		$date = trim($_POST["activity_date"] ?? "");
		$time = trim($_POST["activity_time"] ?? "");
		$endTime = trim($_POST["activity_end_time"] ?? "");
		$price = trim($_POST["activity_price"] ?? "");
		$venue = trim($_POST["activity_venue"] ?? "");
		$tableName = trim($_POST["activity_table_name"] ?? "");
		$image = trim($_POST["activity_image"] ?? "");
		$status = trim($_POST["activity_status"] ?? "pending");

		if ( $title === "" || strlen($title) > 100 || strlen($type) > 50 || strlen($venue) > 100 ||
			strlen($tableName) > 50 || strlen($image) > 100 ) {
			throw new RuntimeException("Enter valid activity details.");
		}
		if ( ($date !== "" && !isValidDate($date)) || ($time !== "" && !isValidTime($time)) ||
			($endTime !== "" && !isValidTime($endTime)) ||
			($price !== "" && (!is_numeric($price) || (float)$price < 0 || (float)$price > 999999.99)) ||
			!in_array($status, [ "pending", "approved", "rejected" ], true) ) {
			throw new RuntimeException("Enter valid activity date, time, price and status.");
		}

		$values = [ $title, $type ?: null, $description ?: null, $date ?: null, $time ?: null,
			$endTime ?: null, $price === "" ? null : $price, $venue ?: null, $tableName ?: null,
			$image ?: null, $status ];
		if ( $activityAction === "activity_add" ) {
			$save = $pdo -> prepare(
				"INSERT INTO activity_info (title,activity_type,description,date,time,end_time,price,venue,table_name,image,status) VALUES (?,?,?,?,?,?,?,?,?,?,?)"
			);
			$save -> execute($values);
		} else {
			if ( !$activityId ) throw new RuntimeException("Invalid activity.");
			$values[] = $activityId;
			$save = $pdo -> prepare(
				"UPDATE activity_info SET title=?,activity_type=?,description=?,date=?,time=?,end_time=?,price=?,venue=?,table_name=?,image=?,status=? WHERE id=?"
			);
			$save -> execute($values);
			if ( $save -> rowCount() === 0 ) {
				$exists = $pdo -> prepare("SELECT id FROM activity_info WHERE id=?");
				$exists -> execute([ $activityId ]);
				if ( !$exists -> fetch() ) throw new RuntimeException("Activity not found.");
			}
		}
		adminRedirect("manage_activities.php?tab=activities", "Activity saved.");
	} catch ( RuntimeException|PDOException $exception ) {
		if ( $pdo -> inTransaction() ) $pdo -> rollBack();
		$activityError = $exception instanceof PDOException ? "Unable to save activity. Please check the details." : $exception -> getMessage();
	}
}

if ( isset($_GET["activity_edit"]) ) {
	$activityStatement = $pdo -> prepare("SELECT * FROM activity_info WHERE id=?");
	$activityStatement -> execute([ $_GET["activity_edit"] ]);
	$activityEdit = $activityStatement -> fetch() ?: null;
}
