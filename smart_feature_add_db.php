<?php
session_start();
$isUser = ($_SESSION["logged_in"] ?? false) === true &&
	($_SESSION["type"] ?? null) === "user" && isset($_SESSION["email"]);
$error = "";
$success = "";
$itemName = "";
$itemCategory = "";

if ($isUser && $_SERVER["REQUEST_METHOD"] === "POST") {
	$itemName = trim($_POST["item_name"] ?? "");
	$itemCategory = trim($_POST["item_category"] ?? "");
	if ($itemName === "" || $itemCategory === "" ||
		strlen($itemName) > 50 || strlen($itemCategory) > 50) {
		$error = "Enter a name and category (maximum 50 characters each).";
	} elseif (!isset($_FILES["description_file"]) ||
		$_FILES["description_file"]["error"] === UPLOAD_ERR_NO_FILE) {
		$error = "Please upload a PDF description.";
	} else {
		require_once __DIR__."/upload_helpers.php";
		$newFiles = [];
		try {
			$pdfPath = saveValidatedUpload(
				$_FILES["description_file"], "smart_feature_description",
				["pdf" => "application/pdf"], 7 * 1024 * 1024
			);
			$newFiles[] = $pdfPath;
			$imagePath = null;
			if (isset($_FILES["item_image"]) &&
				$_FILES["item_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
				$imagePath = saveValidatedUpload(
					$_FILES["item_image"], "smart_feature_images",
					["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png"],
					5 * 1024 * 1024
				);
				$newFiles[] = $imagePath;
			}
			require_once __DIR__."/db_setup.php";
			require_once __DIR__."/db_connect.php";
			$insert = $pdo->prepare("INSERT INTO smart_feature_table (item_name, item_category, item_image, description_file, status) VALUES (?, ?, ?, ?, 'pending')");
			$insert->execute([$itemName, $itemCategory, $imagePath, $pdfPath]);
			$success = "Your contribution has been submitted and is pending review.";
			$itemName = "";
			$itemCategory = "";
		} catch (RuntimeException | PDOException $e) {
			foreach ($newFiles as $path) {
				unlink(__DIR__."/".$path);
			}
			error_log($e->getMessage());
			$error = $e instanceof PDOException
				? "Your contribution could not be saved. Please try again."
				: $e->getMessage();
		}
	}
}
?>
