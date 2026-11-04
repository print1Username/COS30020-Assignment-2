<?php

function saveValidatedUpload(array $file, string $folder, array $allowedMime, int $maxBytes): string
{
	if (($file["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
		throw new RuntimeException("The file could not be uploaded.");
	}
	if (($file["size"] ?? 0) > $maxBytes || ($file["size"] ?? 0) < 1) {
		throw new RuntimeException("The file exceeds the allowed size or is empty.");
	}
	if (!is_uploaded_file($file["tmp_name"])) {
		throw new RuntimeException("Invalid upload.");
	}
	$extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
	$mime = (new finfo(FILEINFO_MIME_TYPE))->file($file["tmp_name"]);
	if (!isset($allowedMime[$extension]) || $allowedMime[$extension] !== $mime) {
		throw new RuntimeException("Please upload a file in the required format.");
	}
	if ($mime !== "application/pdf" && getimagesize($file["tmp_name"]) === false) {
		throw new RuntimeException("Please upload a valid image.");
	}
	$directory = __DIR__."/".$folder;
	if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
		throw new RuntimeException("The upload folder could not be created.");
	}
	$path = $folder."/".bin2hex(random_bytes(16)).".".$extension;
	if (!move_uploaded_file($file["tmp_name"], __DIR__."/".$path)) {
		throw new RuntimeException("The uploaded file could not be saved.");
	}
	return $path;
}
