<?php
include "../includes/auth.php";
include "../database.php";

$message = "";

if (isset($_POST["add_product"])) {
    $productName = trim($_POST["product_name"] ?? "");
    $flavor = trim($_POST["flavor"] ?? "");
    $price = (float) ($_POST["price"] ?? 0);
    $quantity = (int) ($_POST["quantity"] ?? 0);
    $status = ($_POST["status"] ?? "") === "sold_out" ? "sold_out" : "available";
    $imagePath = "";

    // Require an image file upload
    if (!isset($_FILES["product_image"]) || $_FILES["product_image"]["error"] === UPLOAD_ERR_NO_FILE) {
        $message = "Please choose a product image to upload.";
    } elseif ($_FILES["product_image"]["error"] !== UPLOAD_ERR_OK) {
        $message = "Error uploading file. Error code: " . (int)$_FILES["product_image"]["error"];
    } else {
        $file = $_FILES["product_image"];
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
        } elseif ($fileSize > 5 * 1024 * 1024) { // 5MB limit
            $message = "Image size exceeds maximum limit of 5MB.";
        } else {
            $uploadDir = __DIR__ . "/../assets/images/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate safe, unique filename
            $newFileName = "product_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $fileExt;
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destination)) {
                $imagePath = "assets/images/" . $newFileName;
            } else {
                $message = "Failed to save uploaded image. Please check directory permissions.";
            }
        }
    }

    if ($message === "") {
        if ($productName === "" || $flavor === "") {
            $message = "Name and flavor are required.";
        } elseif ($price < 0 || $quantity < 0) {
            $message = "Price and quantity cannot be negative.";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO products (product_name, flavor, price, image, quantity, status)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "ssdsis",
                $productName,
                $flavor,
                $price,
                $imagePath,
                $quantity,
                $status
            );

            if ($stmt->execute()) {
                header("Location: products.php");
                exit();
            } else {
                $message = "Could not add product. Please try again.";
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
    <title>Add Product | ACE PLUS Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">
<div class="admin-container">

    <!-- HEADER -->
    <div class="admin-header">
        <div>
            <h1>ADD PRODUCT</h1>
            <p>Add a new ACE PLUS product to the catalog.</p>
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
                <input type="text" id="product_name" name="product_name" placeholder="e.g. ACE PLUS" required
                       value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="flavor">Flavor</label>
                <input type="text" id="flavor" name="flavor" placeholder="e.g. Mango" required
                       value="<?php echo isset($_POST['flavor']) ? htmlspecialchars($_POST['flavor']) : ''; ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price ($)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" placeholder="1.99" required
                           value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" min="0" placeholder="100" required
                           value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : ''; ?>">
                </div>
            </div>

            <!-- FILE UPLOAD FIELD -->
            <div class="form-group">
                <label for="product_image">Upload Product Image (PNG, JPG, WEBP, SVG - Max 5MB)</label>
                <div class="upload-dropzone" id="dropzone">
                    <input type="file" id="product_image" name="product_image" accept=".jpg,.jpeg,.png,.webp,.svg" class="file-input">
                    <div class="upload-placeholder" id="uploadPlaceholder">
                        <span class="upload-icon"></span>
                        <p class="upload-title">Choose an image file or drag & drop</p>
                        <span class="upload-subtext">Supports PNG, JPG, WEBP, SVG</span>
                    </div>
                    <div class="upload-preview-wrapper" id="previewWrapper" style="display: none;">
                        <img id="imagePreview" src="" alt="Product Preview" class="upload-preview-img">
                        <button type="button" class="upload-clear-btn" id="clearImageBtn" title="Remove selected image">&times;</button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="available">Available</option>
                    <option value="sold_out">Sold Out</option>
                </select>
            </div>

            <button type="submit" name="add_product" class="auth-button">ADD PRODUCT</button>
        </form>
    </div>

</div>

<script>
const fileInput = document.getElementById('product_image');
const previewWrapper = document.getElementById('previewWrapper');
const uploadPlaceholder = document.getElementById('uploadPlaceholder');
const previewImg = document.getElementById('imagePreview');
const clearBtn = document.getElementById('clearImageBtn');

fileInput.addEventListener('change', function () {
    const file = this.files && this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
            previewWrapper.style.display = 'flex';
            uploadPlaceholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
});

clearBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    fileInput.value = '';
    previewImg.src = '';
    previewWrapper.style.display = 'none';
    uploadPlaceholder.style.display = 'block';
});
</script>
</body>
</html>
