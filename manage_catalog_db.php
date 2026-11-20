<?php
require_once __DIR__."/admin_db.php";
require_once __DIR__."/upload_helpers.php";
$catalogCategories = [ "Strategy", "Party Games", "Family Games", "Card Games", "Cooperative" ];
$error = "";
$edit = null;
$notice = $_SESSION["admin_notice"] ?? "";
unset($_SESSION["admin_notice"]);
$id = filter_var($_GET["edit"] ?? $_POST["id"] ?? 0, FILTER_VALIDATE_INT);
if ( $id ) {
    $query = $pdo -> prepare("SELECT id,name,categories,description,players,duration,image,image_data IS NOT NULL AS has_image_data FROM catalog_info WHERE id=?");
    $query -> execute([ $id ]);
    $edit = $query -> fetch();
    if ( !$edit ) $error = "Catalog entry not found.";
}
$form = $edit ?: [];
$selectedCategories = json_decode($form["categories"] ?? "[]", true) ?? [];
if ( $_SERVER["REQUEST_METHOD"] === "POST" ) {
    adminCheckPost();
    $action = $_POST["action"] ?? "";
    try {
        if ( !in_array($action, [ "create", "edit", "delete" ], true) ) throw new RuntimeException("Invalid action.");
        if ( $action !== "create" && !$edit ) throw new RuntimeException("Catalog entry not found.");
        if ( $action === "delete" ) {
            $query = $pdo -> prepare("DELETE FROM catalog_info WHERE id=?");
            $query -> execute([ $id ]);
            adminRedirect("manage_catalog.php", "Catalog entry deleted.");
        }
        $form = $_POST;
        $selectedCategories = $_POST["categories"] ?? [];
        if ( !is_array($selectedCategories) || !$selectedCategories || array_diff($selectedCategories, $catalogCategories) ) throw new RuntimeException("Select valid categories.");
        $values = [];
        foreach ( [ "name" => 100, "description" => 65535, "players" => 50, "duration" => 50 ] as $field => $limit ) {
            $value = trim((string)($_POST[$field] ?? ""));
            if ( $value === "" || strlen($value) > $limit ) throw new RuntimeException("Enter valid name, description, players and duration.");
            $values[] = $value;
        }
        $values[] = json_encode(array_values(array_unique($selectedCategories)));
        $imageData = null;
        $imageMime = null;
        if ( ($_FILES["image"]["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE ) {
            $upload = validateUpload($_FILES["image"], [ "jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png", "webp" => "image/webp" ], 2 * 1024 * 1024);
            $imageData = file_get_contents($_FILES["image"]["tmp_name"]);
            if ( $imageData === false ) throw new RuntimeException("Could not read image.");
            $imageMime = $upload["mime"];
        }
        if ( $action === "create" ) {
            // The existing catalog primary key is not auto-incrementing.
            $pdo -> beginTransaction();
            $ids = $pdo -> query("SELECT id FROM catalog_info ORDER BY id DESC FOR UPDATE") -> fetchAll(PDO::FETCH_COLUMN);
            $newId = $ids ? (int)$ids[0] + 1 : 1;
            $query = $pdo -> prepare("INSERT INTO catalog_info (name,description,players,duration,categories,id,image_data,image_mime) VALUES (?,?,?,?,?,?,?,?)");
            $query -> execute(array_merge($values, [ $newId, $imageData, $imageMime ]));
            $pdo -> commit();
        } else {
            $sql = "UPDATE catalog_info SET name=?,description=?,players=?,duration=?,categories=?";
            if ( $imageData !== null ) {
                $sql .= ",image_data=?,image_mime=?,image=NULL";
                $values[] = $imageData;
                $values[] = $imageMime;
            }
            $values[] = $id;
            $query = $pdo -> prepare($sql." WHERE id=?");
            $query -> execute($values);
        }
        adminRedirect("manage_catalog.php", "Catalog entry saved.");
    } catch ( PDOException $exception ) {
        if ( $pdo -> inTransaction() ) $pdo -> rollBack();
        $error = "Could not save the catalog change. Please try again.";
    } catch ( RuntimeException $exception ) {
        if ( $pdo -> inTransaction() ) $pdo -> rollBack();
        $error = $exception -> getMessage();
    }
}
$games = $pdo -> query("SELECT id,name,categories,players,duration FROM catalog_info ORDER BY id") -> fetchAll();
