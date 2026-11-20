<?php
require_once __DIR__."/manage_catalog_db.php";
require_once __DIR__."/admin_disclosure.php";
require_once __DIR__."/admin_table_actions.php";
require_once __DIR__."/file_upload.php";
?>
<!DOCTYPE html>
<html lang = "en">
<head>
    <meta charset = "utf-8">
    <meta name = "viewport" content = "width=device-width, initial-scale=1">
    <title>Manage Catalog | Boardgame Hub</title>
    <link rel = "icon" href = "img/logo/logo.ico">
    <link rel = "stylesheet" href = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel = "stylesheet" href = "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel = "stylesheet" href = "style/admin_theme.css?v=<?= filemtime(__DIR__."/style/admin_theme.css") ?>">
    <link rel = "stylesheet" href = "style/file_upload.css">
</head>
<body>
<?php include __DIR__."/admin_nav.php"; ?>
<main class = "container py-5">
    <h1>Manage Catalog</h1>
    <p><a class = "btn admin-back-button" href = "main_menu_admin.php">Back to Admin Menu</a></p>
    <?php if ( $error ): ?><div class = "alert alert-danger"><?= adminEscape($error) ?></div><?php endif; ?>
    <?php if ( $notice ): ?><div class = "alert alert-success"><?= adminEscape($notice) ?></div><?php endif; ?>
    <?php adminDisclosureStart($edit ? 'Edit Catalog' : 'Create Catalog', 'Enter board game details', (bool)($edit || $error)); ?>
    <form method = "post" enctype = "multipart/form-data" class = "admin-disclosure-content">
        <input type = "hidden" name = "token" value = "<?= adminEscape(adminToken()) ?>">
        <input type = "hidden" name = "action" value = "<?= $edit ? 'edit' : 'create' ?>">
        <input type = "hidden" name = "id" value = "<?= (int)($edit['id'] ?? 0) ?>">
        <div class = "row g-3">
        <?php foreach ( [ 'name' => 100, 'players' => 50, 'duration' => 50 ] as $field => $limit ): ?>
            <div class = "col-md-4">
                <label class = "form-label" for = "catalog-<?= $field ?>"><?= ucfirst($field) ?></label>
                <input class = "form-control" id = "catalog-<?= $field ?>" name = "<?= $field ?>" maxlength = "<?= $limit ?>" required value = "<?= adminEscape($form[$field] ?? '') ?>">
            </div>
        <?php endforeach; ?>
            <div class = "col-12">
                <label class = "form-label" for = "catalog-description">Description</label>
                <textarea class = "form-control" id = "catalog-description" name = "description" rows = "4" required><?= adminEscape($form['description'] ?? '') ?></textarea>
            </div>
            <fieldset class = "col-12">
                <legend class = "form-label fs-6">Categories (select one or more)</legend>
                <?php foreach ( $catalogCategories as $index => $category ): ?>
                    <div class = "form-check form-check-inline">
                        <input class = "form-check-input" type = "checkbox" id = "category-<?= $index ?>" name = "categories[]" value = "<?= adminEscape($category) ?>" <?= is_array($selectedCategories) && in_array($category, $selectedCategories, true) ? 'checked' : '' ?>>
                        <label class = "form-check-label" for = "category-<?= $index ?>"><?= adminEscape($category) ?></label>
                    </div>
                <?php endforeach; ?>
            </fieldset>
            <div class = "col-12">
                <?php renderFileUpload([ 'id' => 'catalog-image', 'name' => 'image', 'label' => 'Catalog Image', 'kind' => 'image', 'accept' => '.jpg,.jpeg,.png,.webp', 'types' => 'JPG, PNG or WebP', 'max_size' => 'maximum 2 MB', 'current_url' => !empty($edit['has_image_data']) ? 'catalog_image.php?id='.(int)$edit['id'] : ($edit['image'] ?? '') ]); ?>
                <p class = "form-text">Optional. Leave unchanged to keep the current image.</p>
            </div>
        </div>
        <button class = "btn admin-action mt-3" type = "submit"><?= $edit ? 'Save Changes' : 'Create Catalog' ?></button>
        <?php if ( $edit ): ?><a class = "btn btn-outline-secondary mt-3" href = "manage_catalog.php">Cancel</a><?php endif; ?>
    </form>
    <?php adminDisclosureEnd(); ?>
    <section class = "admin-panel">
        <h2>Manage Catalog</h2>
        <div class = "table-responsive">
            <table class = "table align-middle">
                <thead><tr><th>ID</th><th>Name</th><th>Categories</th><th>Players</th><th>Duration</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ( $games as $game ): ?>
                    <tr>
                        <td><?= (int)$game['id'] ?></td>
                        <td><?= adminEscape($game['name']) ?></td>
                        <td><?= adminEscape(implode(', ', json_decode($game['categories'], true) ?? [])) ?></td>
                        <td><?= adminEscape($game['players']) ?></td>
                        <td><?= adminEscape($game['duration']) ?></td>
                        <td><div class = "d-flex gap-2"><?php adminTableActions('edit_delete', [ 'edit_url' => 'manage_catalog.php?edit='.$game['id'], 'confirm' => 'Delete this catalog entry?', 'delete_action' => 'delete', 'id_field' => 'id', 'id' => $game['id'] ]); ?></div></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ( !$games ): ?><tr><td colspan = "6">No catalog entries yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<script src = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src = "components/file_upload.js"></script>
</body>
</html>
