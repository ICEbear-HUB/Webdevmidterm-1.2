<?php
include "../includes/auth.php";
include "../database.php";

if (!isset($_GET["id"])) {
    header("Location: products.php");
    exit();
}

$id = (int) $_GET["id"];
$message = "";

// =========================
// FETCH EXISTING PRODUCT FIRST
// =========================
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: products.php");
    exit();
}

// =========================
// HANDLE UPDATE
// =========================
if (isset($_POST["update_product"])) {
    $productName = trim($_POST["product_name"] ?? "");
    $flavor = trim($_POST["flavor"] ?? "");
    $price = (float) ($_POST["price"] ?? 0);
    $quantity = (int) ($_POST["quantity"] ?? 0);
    $status = ($_POST["status"] ?? "") === "sold_out" ? "sold_out" : "available";
    $imagePath = $product["image"];

    // Check if a new image file was uploaded
    if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES["product_image"];

        if ($file["error"] === UPLOAD_ERR_OK) {
            $allowedExtensions = ["jpg", "jpeg", "png", "webp", "svg"];
            $allowedMimeTypes = ["image/jpeg", "image/png", "image/webp", "image/svg+xml"];

            $fileName = $file["name"];
            $fileTmpPath = $file["tmp_name"];
            $fileSize = $file["size"];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fileTmpPath);
            finfo_close($finfo);

            if (!in_array($fileExt, $allowedExtensions, true) || !in_array($mimeType, $allowedMimeTypes, true)) {
                $message = "Invalid image type. Only JPG, PNG, WEBP, and SVG are allowed.";
            } elseif ($fileSize > 5 * 1024 * 1024) {
                $message = "Image size exceeds maximum limit of 5MB.";
            } else {
                $uploadDir = __DIR__ . "/../assets/images/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $newFileName = "product_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $fileExt;
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destination)) {
                    $imagePath = "assets/images/" . $newFileName;
                } else {
                    $message = "Failed to save uploaded image. Please check directory permissions.";
                }
            }
        } else {
            $message = "Error uploading file. Error code: " . (int)$file["error"];
        }
    }

    if ($message === "") {
        if ($productName === "" || $flavor === "") {
            $message = "Name and flavor are required.";
        } elseif ($price < 0 || $quantity < 0) {
            $message = "Price and quantity cannot be negative.";
        } else {
            $stmt = $conn->prepare(
                "UPDATE products
                 SET product_name = ?, flavor = ?, price = ?, image = ?, quantity = ?, status = ?
                 WHERE id = ?"
            );
            $stmt->bind_param(
                "ssdsisi",
                $productName,
                $flavor,
                $price,
                $imagePath,
                $quantity,
                $status,
                $id
            );

            if ($stmt->execute()) {
                header("Location: products.php");
                exit();
            } else {
                $message = "Could not update product. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product | ACE PLUS Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">
<div class="admin-container">

    <!-- HEADER -->
    <div class="admin-header">
        <div>
            <h1>EDIT PRODUCT</h1>
            <p>Update details for <?php echo htmlspecialchars($product["product_name"]); ?> - <?php echo htmlspecialchars($product["flavor"]); ?>.</p>
        </div>
        <a href="products.php" class="admin-logout">BACK TO PRODUCTS</a>
    </div>

    <?php if ($message !== ""): ?>
        <div class="error-message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="admin-form-card">
        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <div class="form-group">
                <label for="product_name">Product Name</label>
                <input type="text" id="product_name" name="product_name" required
                       value="<?php echo htmlspecialchars($product['product_name']); ?>">
            </div>

            <div class="form-group">
                <label for="flavor">Flavor</label>
                <input type="text" id="flavor" name="flavor" required
                       value="<?php echo htmlspecialchars($product['flavor']); ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price ($)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required
                           value="<?php echo htmlspecialchars($product['price']); ?>">
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" min="0" required
                           value="<?php echo htmlspecialchars($product['quantity']); ?>">
                </div>
            </div>

            <!-- FILE UPLOAD FIELD -->
            <div class="form-group">
                <label for="product_image">Product Image (Upload new image to replace current)</label>
                <div class="upload-dropzone" id="dropzone">
                    <input type="file" id="product_image" name="product_image" accept=".jpg,.jpeg,.png,.webp,.svg" class="file-input">
                    <div class="upload-preview-wrapper" id="previewWrapper">
                        <img id="imagePreview" src="../<?php echo htmlspecialchars($product['image']); ?>" alt="Current Product Image" class="upload-preview-img">
                        <span class="upload-subtext" id="previewNotice">Current image shown. Click or drag to change.</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="available" <?php echo $product['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="sold_out" <?php echo $product['status'] === 'sold_out' ? 'selected' : ''; ?>>Sold Out</option>
                </select>
            </div>

            <button type="submit" name="update_product" class="auth-button">SAVE CHANGES</button>
        </form>
    </div>

</div>

<script>
const fileInput = document.getElementById('product_image');
const previewImg = document.getElementById('imagePreview');
const previewNotice = document.getElementById('previewNotice');

fileInput.addEventListener('change', function () {
    const file = this.files && this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
            if (previewNotice) {
                previewNotice.textContent = "New image selected: " + file.name;
            }
        };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>
