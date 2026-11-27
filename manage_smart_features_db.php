<?php
require_once __DIR__."/admin_db.php";
require_once __DIR__."/upload_helpers.php";

function smartFeatureAdminAsset(?string $path, string $type): ?string
{
	$pattern = match ( $type ) {
		"image" => '~^(?:img|smart_feature_images)/[a-zA-Z0-9._/-]+$~',
		"pdf" => '~^smart_feature_description/[a-zA-Z0-9._/-]+$~',
		default => null
	};
	if ( $path === null || $pattern === null || !preg_match($pattern, $path) ||
		str_contains($path, "..") || !is_file(__DIR__."/".$path) ) return null;
	return $path;
}

function removeSmartFeatureAdminUpload(?string $path): bool
{
	if ( $path === null ) return true;
	$isImageUpload = preg_match(
		'~^smart_feature_images/[a-f0-9]{32}\.(?:jpg|jpeg|png)$~i',
		$path
	) === 1;
	$isPdfUpload = preg_match(
		'~^smart_feature_description/[a-f0-9]{32}\.pdf$~i',
		$path
	) === 1;
	if ( !$isImageUpload && !$isPdfUpload ) return true;
	$absolutePath = __DIR__."/".$path;
	if ( !is_file($absolutePath) ) return true;
	if ( unlink($absolutePath) ) return true;
	error_log("Could not remove a Smart Feature upload.");
	return false;
}

function smartFeatureAdminPostValue(string $key): string
{
	$value = $_POST[$key] ?? "";
	return is_string($value) ? trim($value) : "";
}

$smartFeatureStatuses = [ "pending", "approved", "rejected" ];
$requestedStatus = $_GET["status"] ?? $_POST["status_filter"] ?? "pending";
$status = is_string($requestedStatus) && in_array($requestedStatus, $smartFeatureStatuses, true)
	? $requestedStatus
	: "pending";
$error = "";
$submission = null;
$form = [];
$showEditForm = false;
$notice = $_SESSION["admin_notice"] ?? "";
unset($_SESSION["admin_notice"]);

$recordId = filter_var($_POST["id"] ?? $_GET["edit"] ?? null, FILTER_VALIDATE_INT);
if ( $recordId === false || $recordId === null || $recordId < 1 ) $recordId = null;
if ( $recordId !== null ) {
	$lookup = $pdo -> prepare(
		"SELECT id,item_name,item_category,item_image,description_file,item_description,players,duration,status
		FROM smart_feature_table WHERE id=? AND catalog_id IS NULL"
	);
	$lookup -> execute([ $recordId ]);
	$submission = $lookup -> fetch() ?: null;
	if ( $submission === null ) {
		$error = "User-submitted Smart Feature not found.";
	} else {
		$form = $submission;
		$showEditForm = isset($_GET["edit"]);
	}
}

if ( $_SERVER["REQUEST_METHOD"] === "POST" ) {
	adminCheckPost();
	$action = smartFeatureAdminPostValue("action");
	$newUploads = [];
	try {
		if ( !in_array($action, [ "approved", "rejected", "edit", "delete" ], true) ||
			$recordId === null || $submission === null ) {
			throw new RuntimeException("User-submitted Smart Feature not found.");
		}

		if ( in_array($action, [ "approved", "rejected" ], true) ) {
			$update = $pdo -> prepare(
				"UPDATE smart_feature_table SET status=?
				WHERE id=? AND catalog_id IS NULL AND status IN ('pending','approved','rejected') AND status<>?"
			);
			$update -> execute([ $action, $recordId, $action ]);
			if ( $update -> rowCount() !== 1 ) {
				throw new RuntimeException("The submission status is already set or the record is no longer available.");
			}
			adminRedirect(
				"manage_smart_features.php?status=".$action,
				"Smart Feature ".($action === "approved" ? "approved." : "rejected.")
			);
		}

		if ( $action === "delete" ) {
			$pdo -> beginTransaction();
			$lock = $pdo -> prepare(
				"SELECT item_image,description_file FROM smart_feature_table
				WHERE id=? AND catalog_id IS NULL FOR UPDATE"
			);
			$lock -> execute([ $recordId ]);
			$lockedSubmission = $lock -> fetch();
			if ( !$lockedSubmission ) {
				throw new RuntimeException("The submission is no longer available.");
			}
			$delete = $pdo -> prepare(
				"DELETE FROM smart_feature_table WHERE id=? AND catalog_id IS NULL"
			);
			$delete -> execute([ $recordId ]);
			if ( $delete -> rowCount() !== 1 ) {
				throw new RuntimeException("The submission could not be deleted.");
			}
			$pdo -> commit();
			$imageRemoved = removeSmartFeatureAdminUpload($lockedSubmission["item_image"]);
			$pdfRemoved = removeSmartFeatureAdminUpload($lockedSubmission["description_file"]);
			$filesRemoved = $imageRemoved && $pdfRemoved;
			$message = $filesRemoved
				? "Smart Feature deleted."
				: "Smart Feature deleted, but an uploaded file could not be removed.";
			adminRedirect("manage_smart_features.php?status=".$status, $message);
		}

		$showEditForm = true;
		$form = $_POST;
		$itemName = smartFeatureAdminPostValue("item_name");
		$itemCategory = smartFeatureAdminPostValue("item_category");
		$itemDescription = smartFeatureAdminPostValue("item_description");
		$players = smartFeatureAdminPostValue("players");
		$duration = smartFeatureAdminPostValue("duration");
		if ( $itemName === "" || strlen($itemName) > 50 ||
			$itemCategory === "" || strlen($itemCategory) > 50 ||
			strlen($itemDescription) > 65535 || strlen($players) > 50 || strlen($duration) > 50 ) {
			throw new RuntimeException("Enter a name and category within 50 characters; optional details must fit their fields.");
		}

		$imagePath = null;
		$pdfPath = null;
		if ( isset($_FILES["item_image"]) && is_array($_FILES["item_image"]) &&
			($_FILES["item_image"]["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE ) {
			$imagePath = saveValidatedUpload(
				$_FILES["item_image"], "smart_feature_images",
				[ "jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png" ],
				5 * 1024 * 1024
			);
			$newUploads[] = $imagePath;
		}
		if ( isset($_FILES["description_file"]) && is_array($_FILES["description_file"]) &&
			($_FILES["description_file"]["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE ) {
			$pdfPath = saveValidatedUpload(
				$_FILES["description_file"], "smart_feature_description",
				[ "pdf" => "application/pdf" ], 7 * 1024 * 1024
			);
			$newUploads[] = $pdfPath;
		}

		$pdo -> beginTransaction();
		$lock = $pdo -> prepare(
			"SELECT item_image,description_file FROM smart_feature_table
			WHERE id=? AND catalog_id IS NULL FOR UPDATE"
		);
		$lock -> execute([ $recordId ]);
		$lockedSubmission = $lock -> fetch();
		if ( !$lockedSubmission ) {
			throw new RuntimeException("The submission is no longer available.");
		}

		$sql = "UPDATE smart_feature_table
			SET item_name=?,item_category=?,item_description=?,players=?,duration=?";
		$values = [
			$itemName, $itemCategory, $itemDescription !== "" ? $itemDescription : null,
			$players !== "" ? $players : null, $duration !== "" ? $duration : null
		];
		if ( $imagePath !== null ) {
			$sql .= ",item_image=?";
			$values[] = $imagePath;
		}
		if ( $pdfPath !== null ) {
			$sql .= ",description_file=?";
			$values[] = $pdfPath;
		}
		$sql .= " WHERE id=? AND catalog_id IS NULL";
		$values[] = $recordId;
		$update = $pdo -> prepare($sql);
		$update -> execute($values);
		$pdo -> commit();

		$filesRemoved = true;
		if ( $imagePath !== null ) {
			$filesRemoved = removeSmartFeatureAdminUpload($lockedSubmission["item_image"]) && $filesRemoved;
		}
		if ( $pdfPath !== null ) {
			$filesRemoved = removeSmartFeatureAdminUpload($lockedSubmission["description_file"]) && $filesRemoved;
		}
		$message = $filesRemoved
			? "Smart Feature updated."
			: "Smart Feature updated, but a replaced upload could not be removed.";
		adminRedirect("manage_smart_features.php?status=".$status, $message);
	} catch ( PDOException $exception ) {
		if ( $pdo -> inTransaction() ) $pdo -> rollBack();
		foreach ( $newUploads as $path ) removeSmartFeatureAdminUpload($path);
		error_log($exception -> getMessage());
		$error = "The Smart Feature could not be saved. Please try again.";
	} catch ( RuntimeException $exception ) {
		if ( $pdo -> inTransaction() ) $pdo -> rollBack();
		foreach ( $newUploads as $path ) removeSmartFeatureAdminUpload($path);
		$error = $exception -> getMessage();
	}
}

$list = $pdo -> prepare(
	"SELECT id,item_name,item_category,item_image,description_file,item_description,players,duration,status
	FROM smart_feature_table WHERE catalog_id IS NULL AND status=? ORDER BY id DESC"
);
$list -> execute([ $status ]);
$smartFeatureSubmissions = $list -> fetchAll();
