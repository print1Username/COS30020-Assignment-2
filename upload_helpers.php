<?php

function profileImageExists(string $email, mixed $path): bool
{
	if ( !is_string($path) ) {
		return false;
	}
	$defaultImages = [
		"admin@swin.edu.my" => "admin.png",
		"user1@swin.edu.my" => "user1.png",
		"user2@swin.edu.my" => "user2.png"
	];
	$defaultImage = isset($defaultImages[$email]) &&
		$path === "profile_images/".$email."/".$defaultImages[$email];
	$userImage = preg_match('~^profile_images/'.preg_quote($email, '~').'/[a-f0-9]{32}\.(?:jpg|png)$~i', $path) === 1;
	return ($defaultImage || $userImage) && is_file(__DIR__."/".$path);
}

function storeProfileImage(PDO $pdo, string $email, string $mime, string $path, ?string $databaseEmail = null): string
{
	// Keep the full email as the folder name, but reject unsafe filesystem characters.
	if ( !filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('~[<>:"/\\\\|?*\x00-\x1f]~', $email) ) {
		throw new RuntimeException("This email address cannot be used as a profile image folder name.");
	}
	$extension = [ "image/jpeg" => "jpg", "image/png" => "png" ][$mime] ?? null;
	if ( $extension === null ) {
		throw new RuntimeException("Please upload a valid JPG, JPEG, or PNG image.");
	}
	$directory = __DIR__."/profile_images/".$email;
	if ( !is_dir($directory) && !mkdir($directory, 0755, true) ) {
		throw new RuntimeException("The profile image folder could not be created.");
	}
	$relativePath = "profile_images/".$email."/".bin2hex(random_bytes(16)).".".$extension;
	$absolutePath = __DIR__."/".$relativePath;
	if ( !copy($path, $absolutePath) ) {
		if ( is_file($absolutePath) ) {
			unlink($absolutePath);
		}
		throw new RuntimeException("The local profile image could not be saved.");
	}
	$imageData = file_get_contents($path);
	if ( $imageData === false ) {
		unlink($absolutePath);
		throw new RuntimeException("The profile image could not be read.");
	}
	$update = $pdo -> prepare(
		"UPDATE user_table SET profile_image = ?, profile_image_data = ?, profile_image_mime = ? WHERE email = ?"
	);
	$update -> execute([ $relativePath, $imageData, $mime, $databaseEmail ?? $email ]);
	if ( $update -> rowCount() !== 1 ) {
		unlink($absolutePath);
		throw new RuntimeException("The profile image could not be linked to this account.");
	}
	return $relativePath;
}

function validateUpload(array $file, array $allowedMime, int $maxBytes): array
{
	if ( ($file["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ) {
		throw new RuntimeException("The file could not be uploaded.");
	}
	if ( ($file["size"] ?? 0) > $maxBytes || ($file["size"] ?? 0) < 1 ) {
		throw new RuntimeException("The file exceeds the allowed size or is empty.");
	}
	if ( !is_uploaded_file($file["tmp_name"]) ) {
		throw new RuntimeException("Invalid upload.");
	}
	$extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
	$mime = (new finfo(FILEINFO_MIME_TYPE)) -> file($file["tmp_name"]);
	if ( !isset($allowedMime[$extension]) || $allowedMime[$extension] !== $mime ) {
		throw new RuntimeException("Please upload a file in the required format.");
	}
	if ( $mime !== "application/pdf" && getimagesize($file["tmp_name"]) === false ) {
		throw new RuntimeException("Please upload a valid image.");
	}
	return [ "extension" => $extension, "mime" => $mime ];
}

function saveValidatedUpload(array $file, string $folder, array $allowedMime, int $maxBytes): string
{
	$upload = validateUpload($file, $allowedMime, $maxBytes);
	$directory = __DIR__."/".$folder;
	if ( !is_dir($directory) && !mkdir($directory, 0755, true) ) {
		throw new RuntimeException("The upload folder could not be created.");
	}
	$path = $folder."/".bin2hex(random_bytes(16)).".".$upload["extension"];
	if ( !move_uploaded_file($file["tmp_name"], __DIR__."/".$path) ) {
		throw new RuntimeException("The uploaded file could not be saved.");
	}
	return $path;
}
