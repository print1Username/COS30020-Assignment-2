<?php

function storeProfileImage(PDO $pdo, string $email, string $mime, string $path, bool $saveLocal = false): ?string
{
	$packetLimit = (int)$pdo -> query("SELECT @@max_allowed_packet") -> fetchColumn();
	if (filesize($path) + 131072 > $packetLimit) {
		throw new RuntimeException("MySQL max_allowed_packet is too small for this profile image.");
	}
	$stream = fopen($path, "rb");
	if ($stream === false) {
		throw new RuntimeException("The profile image could not be read.");
	}
	try {
		$reset = $pdo -> prepare("UPDATE user_table SET profile_image = NULL, profile_image_data = '', profile_image_mime = ? WHERE email = ?");
		$reset -> execute([$mime, $email]);
		$append = $pdo -> prepare("UPDATE user_table SET profile_image_data = CONCAT(profile_image_data, ?) WHERE email = ?");
		while (!feof($stream)) {
			$chunk = fread($stream, 128 * 1024);
			if ($chunk === false) {
				throw new RuntimeException("The profile image could not be read.");
			}
			if ($chunk !== "") {
				$append -> execute([$chunk, $email]);
			}
		}
	} finally {
		fclose($stream);
	}
	if (!$saveLocal) {
		return null;
	}
	// Keep the full email as the folder name, but reject unsafe filesystem characters.
	if (!filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('~[<>:"/\\\\|?*\x00-\x1f]~', $email)) {
		throw new RuntimeException("This email address cannot be used as a profile image folder name.");
	}
	$extension = ["image/jpeg" => "jpg", "image/png" => "png"][$mime] ?? null;
	if ($extension === null) {
		throw new RuntimeException("Please upload a valid JPG, JPEG, or PNG image.");
	}
	$directory = __DIR__."/profile_images/".$email;
	if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
		throw new RuntimeException("The profile image folder could not be created.");
	}
	$localPath = $directory."/".bin2hex(random_bytes(16)).".".$extension;
	if (!copy($path, $localPath)) {
		if (is_file($localPath)) {
			unlink($localPath);
		}
		throw new RuntimeException("The local profile image could not be saved.");
	}
	return $localPath;
}

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
	$mime = (new finfo(FILEINFO_MIME_TYPE)) -> file($file["tmp_name"]);
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
