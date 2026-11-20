<?php
require_once __DIR__."/admin_db.php";
require_once __DIR__."/validation.php";
$kind = $reviewKind ?? "";
if ( !in_array($kind, [ "community", "activities" ], true) ) exit("Invalid management page.");
$community = $kind === "community";
$table = $community ? "community_table" : "activity_table";
$page = $community ? "manage_community.php?tab=post" : "manage_activities.php";
$error = "";
$edit = null;

function removeCommunityPostImage(?string $path): void
{
	if ( $path === null || !preg_match('~^community_images/[0-9]+/[a-f0-9]{32}\.(?:jpg|jpeg|png)$~i', $path) ) {
		return;
	}
	$absolutePath = __DIR__."/".$path;
	if ( is_file($absolutePath) ) {
		unlink($absolutePath);
	}
	$directory = dirname($absolutePath);
	if ( is_dir($directory) && count(scandir($directory)) === 2 ) {
		rmdir($directory);
	}
}

if ( $_SERVER["REQUEST_METHOD"] === "POST" && !in_array(
		$_POST["action"] ?? "",
		[ "community_add", "community_edit", "community_delete", "join_approve", "join_reject", "member_remove" ], true
	) ) {
	adminCheckPost();
	$action = $_POST["action"] ?? "";
	$id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
	$newImagePath = null;
	$oldImagePath = null;
	try {
		if ( $action === "delete" && $id ) {
			if ( $community ) {
				$pdo -> beginTransaction();
				$imageLookup = $pdo -> prepare("SELECT contribution_image FROM community_table WHERE id=?");
				$imageLookup -> execute([ $id ]);
				$oldImagePath = $imageLookup -> fetchColumn() ?: null;
			}
			$statement = $pdo -> prepare("DELETE FROM $table WHERE id=?");
			$statement -> execute([ $id ]);
			if ( $community ) $pdo -> commit();
			if ( $community && $statement -> rowCount() === 1 ) {
				removeCommunityPostImage($oldImagePath);
			}
			adminRedirect($page, "Entry deleted.");
		}
		if ( in_array($action, [ "approved", "rejected" ], true) && $id ) {
			$statement = $pdo -> prepare(
				"UPDATE $table SET status=? WHERE id=? AND status IN ('pending','approved','rejected') AND status<>?"
			);
			$statement -> execute([ $action, $id, $action ]);
			if ( $statement -> rowCount() === 0 ) {
				throw new RuntimeException("Status is already set or the entry no longer exists.");
			}
			adminRedirect($page, "Status updated.");
		}
		if ( !in_array(
				$action, [ "add", "edit" ],
				true
			) || ($action === "edit" && !$id) ) throw new RuntimeException("Invalid action.");
		$first = trim($_POST["first_name"] ?? "");
		$last = trim($_POST["last_name"] ?? "");
		$status = $community && $action === "add" ? "pending" : trim($_POST["status"] ?? "pending");
		if ( !isValidName($first) || !isValidName($last) || !in_array(
				$status, [ "pending", "approved", "rejected" ],
				true
			) ) throw new RuntimeException("Enter valid names and status.");
		if ( $community ) {
			$communityId = filter_var($_POST["community_id"] ?? null, FILTER_VALIDATE_INT);
			$email = trim($_POST["email"] ?? "");
			$title = trim($_POST["title"] ?? "");
			$description = trim($_POST["description"] ?? "");
			$communityLookup = $pdo -> prepare("SELECT title FROM community_info WHERE id=?");
			$communityLookup -> execute([ $communityId ?: 0 ]);
			$selectedCommunity = $communityLookup -> fetch();
			if ( !$selectedCommunity || ($email !== "" && !isValidEmail($email)) || $title === "" ||
				strlen($title) > 100 || $description === "" ) {
				throw new RuntimeException("Enter valid post and community details.");
			}
			$imageFile = $_FILES["contribution_image"] ?? null;
			$hasNewImage = is_array($imageFile) && ($imageFile["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
			$imageData = null;
			$imageMime = null;
			if ( $hasNewImage ) {
				$imageUpload = validateUpload(
					$imageFile,
					[ "jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png" ], 5 * 1024 * 1024
				);
				$imageData = file_get_contents($imageFile["tmp_name"]);
				if ( $imageData === false ) throw new RuntimeException("The post image could not be read.");
				$imageMime = $imageUpload["mime"];
			}
			$pdo -> beginTransaction();
			if ( $action === "add" ) {
				$statement = $pdo -> prepare(
					"INSERT INTO community_table (community_id,email,first_name,last_name,related_activity,status,title,description,created_at) VALUES (?,?,?,?,?,'pending',?,?,NOW())"
				);
				$statement -> execute(
					[ $communityId, $email ?: null, $first, $last, $selectedCommunity["title"], $title, $description ]
				);
				$postId = (int)$pdo -> lastInsertId();
				if ( $hasNewImage ) {
					$newImagePath = saveValidatedUpload(
						$imageFile, "community_images/".$postId,
						[ "jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png" ], 5 * 1024 * 1024
					);
					$updateImage = $pdo -> prepare(
						"UPDATE community_table SET contribution_image=?,community_image_data=?,community_image_mime=? WHERE id=?"
					);
					$updateImage -> execute([ $newImagePath, $imageData, $imageMime, $postId ]);
				}
			} else {
				$imageLookup = $pdo -> prepare("SELECT contribution_image FROM community_table WHERE id=? FOR UPDATE");
				$imageLookup -> execute([ $id ]);
				$oldImagePath = $imageLookup -> fetchColumn() ?: null;
				if ( $hasNewImage ) {
					$newImagePath = saveValidatedUpload(
						$imageFile, "community_images/".$id,
						[ "jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png" ], 5 * 1024 * 1024
					);
					$statement = $pdo -> prepare(
						"UPDATE community_table SET community_id=?,email=?,first_name=?,last_name=?,related_activity=?,contribution_image=?,community_image_data=?,community_image_mime=?,status=?,title=?,description=? WHERE id=?"
					);
					$statement -> execute(
						[ $communityId, $email ?: null, $first, $last, $selectedCommunity["title"], $newImagePath, $imageData, $imageMime, $status, $title, $description, $id ]
					);
				} else {
					$statement = $pdo -> prepare(
						"UPDATE community_table SET community_id=?,email=?,first_name=?,last_name=?,related_activity=?,status=?,title=?,description=? WHERE id=?"
					);
					$statement -> execute(
						[ $communityId, $email ?: null, $first, $last, $selectedCommunity["title"], $status, $title, $description, $id ]
					);
				}
			}
			$pdo -> commit();
			if ( $newImagePath !== null && $oldImagePath !== null && $newImagePath !== $oldImagePath ) {
				removeCommunityPostImage($oldImagePath);
			}
		} else {
			$email = trim($_POST["email"] ?? "");
			$contact = trim($_POST["contact_number"] ?? "");
			if ( !isValidEmail($email) || strlen($contact) > 15 ) throw new RuntimeException(
				"Enter valid registration details."
			);
			if ( $action === "add" ) {
				if ( $contact === "" ) throw new RuntimeException("Enter a contact number.");
				$activityId = filter_var($_POST["activity_id"] ?? null, FILTER_VALIDATE_INT);
				$activityCheck = $pdo -> prepare(
					"SELECT date, time FROM activity_info WHERE id=? AND status='approved'"
				);
				$activityCheck -> execute([ $activityId ?: 0 ]);
				$selectedActivity = $activityCheck -> fetch();
				if ( !$selectedActivity ) throw new RuntimeException("Select a valid activity.");
				$statement = $pdo -> prepare(
					"INSERT INTO activity_table (email,first_name,last_name,date,time,contact_number,status,activity_id) VALUES (?,?,?,?,?,?,?,?)"
				);
				$statement -> execute(
					[ $email, $first, $last, $selectedActivity['date'], $selectedActivity['time'], $contact ?: null, 'pending', $activityId ]
				);
			} else {
				$date = trim($_POST["date"] ?? "");
				$time = trim($_POST["time"] ?? "");
				if ( ($date !== "" && !isValidDate($date)) || ($time !== "" && !isValidTime(
							$time
						)) ) throw new RuntimeException("Enter valid registration details.");
				$statement = $pdo -> prepare(
					"UPDATE activity_table SET email=?,first_name=?,last_name=?,date=?,time=?,contact_number=?,status=? WHERE id=?"
				);
				$statement -> execute(
					[ $email, $first, $last, $date ?: null, $time ?: null, $contact ?: null, $status, $id ]
				);
			}
		}
		adminRedirect($page, "Entry saved.");
	} catch ( RuntimeException|PDOException $exception ) {
		if ( $pdo -> inTransaction() ) $pdo -> rollBack();
		if ( $community && $newImagePath !== null ) removeCommunityPostImage($newImagePath);
		$error = $exception instanceof PDOException ? "Unable to save entry. Please check the details." : $exception -> getMessage();
	}
}
if ( isset($_GET["edit"]) ) {
	$statement = $pdo -> prepare("SELECT * FROM $table WHERE id=?");
	$statement -> execute([ $_GET["edit"] ]);
	$edit = $statement -> fetch() ?: null;
}
if ( $community ) {
	$adminUserStatement = $pdo -> prepare("SELECT first_name,last_name,email FROM user_table WHERE email=?");
	$adminUserStatement -> execute([ $_SESSION["email"] ]);
	$adminUser = $adminUserStatement -> fetch() ?: [ "first_name" => "", "last_name" => "", "email" => "" ];
	$communities = $pdo -> query("SELECT id, title FROM community_info ORDER BY title") -> fetchAll();
	$rows = $pdo -> query(
		"SELECT post.id,post.community_id,post.email,post.first_name,post.last_name,post.contribution_image,post.related_activity,post.status,post.title,post.description,post.created_at,post.community_image_data IS NOT NULL AND OCTET_LENGTH(post.community_image_data) > 0 AND post.community_image_mime IN ('image/jpeg','image/png') AS has_community_image,community.title AS community_title FROM community_table AS post LEFT JOIN community_info AS community ON community.id=post.community_id ORDER BY post.id DESC"
	)
		-> fetchAll();
} else {
	$events = $pdo -> query(
		"SELECT id, title, activity_type, description, date, time, end_time, price, venue, table_name, status FROM activity_info ORDER BY date, time"
	)
		-> fetchAll();
	$rows = $pdo -> query(
		"SELECT registration.*, event.title AS activity_title FROM activity_table AS registration LEFT JOIN activity_info AS event ON event.id=registration.activity_id ORDER BY registration.id DESC"
	)
		-> fetchAll();
}
$notice = $_SESSION["admin_notice"] ?? "";
unset($_SESSION["admin_notice"]);
