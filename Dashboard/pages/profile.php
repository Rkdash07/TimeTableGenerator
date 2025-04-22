<?php
session_start();
include("../../connection.php");
// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}




// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["imageUpload"])) {
    $target_dir = "../uploads/"; // Directory where uploaded files will be stored
    $target_file = $target_dir . basename($_FILES["imageUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["imageUpload"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "<script>alert('File is not an image.');</script>";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["imageUpload"]["size"] > 5000000) { // 5MB limit
        echo "<script>alert('Sorry, your file is too large.');</script>";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif") {
        echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');</script>";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "<script>alert('Sorry, your file was not uploaded.');</script>";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["imageUpload"]["tmp_name"], $target_file)) {
            // Update the database with the image path
            $username = $_SESSION['username'];
            $sql = "UPDATE users SET profile_image = '$target_file' WHERE username = '$username'";
            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('The file ". htmlspecialchars( basename( $_FILES["imageUpload"]["name"])). " has been uploaded and your profile picture has been updated.'); window.location.href='profile.php';</script>";
            } else {
                echo "<script>alert('Sorry, there was an error updating your profile picture in the database.');</script>";
            }
        } else {
            echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
        }
    }
}

// Fetch user data, including profile image
$username = $_SESSION['username'];
$sql = "SELECT * FROM login WHERE username = '$username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $profileImage = !empty($row["profile_image"]) ? $row["profile_image"] : "../assets/img/demo.jpeg"; // Use a default image if none exists
    $userName = $row["username"]; // Get username from database
} else {
    $profileImage = "../assets/img/demo.jpeg"; // Default image if user not found
    $userName = "Your Name"; // Default name if user not found
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <title>
        Time Table - Profile
    </title>
    <!-- Fonts and icons -->
    <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <!-- Nucleo Icons -->
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- Material Icons -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <!-- CSS Files -->
    <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />

    <style>
        .profile-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
            /* Adjust as needed */
        }

        .profile-frame {
            width: 500px;
            height: 500px;
            border-radius: 50%;
            overflow: hidden;
            position: relative;
            border: 3px solid #ddd;
        }

        .profile-image {
            width: 100%;
            height: 120%;
            object-fit: cover;
            display: block;
        }

        .edit-icon {
            position: absolute;
            top: 10px; /* Changed from bottom */
            right: 10px;
            background-color: rgba(247, 8, 8, 0.5);
            color: white;
            padding: 5px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 1; /* Ensure it's above the image */
        }

        .profile-name {
            margin-top: 10px;
            font-size: 50px;
            font-weight: bold;
        }

        #imageUpload {
            display: none;
        }
    </style>
</head>

<body class="g-sidenav-show  bg-gray-100">
    <aside
        class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2  bg-white my-2"
        id="sidenav-main">

        <hr class="horizontal dark mt-0 mb-2">
        <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="../pages/dashboard.php">
                        <i class="material-symbols-rounded opacity-5">dashboard</i>
                        <span class="nav-link-text ms-1">Home</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="../pages/faculties.php">
                        <i class="material-symbols-rounded opacity-5">receipt_long</i>
                        <span class="nav-link-text ms-1">Faculties</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="../pages/subjects.php">
                        <i class="material-symbols-rounded opacity-5">view_in_ar</i>
                        <span class="nav-link-text ms-1">Subjects</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="../pages/tables.php">
                        <i class="material-symbols-rounded opacity-5">table_view</i>
                        <span class="nav-link-text ms-1">Tables</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active bg-gradient-dark text-white" href="../pages/profile.php">
                        <i class="material-symbols-rounded opacity-5">person</i>
                        <span class="nav-link-text ms-1">Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="../pages/logout.php">
                        <i class="material-symbols-rounded opacity-5">assignment</i>
                        <span class="nav-link-text ms-1">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur"
            data-scroll="true">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">
                            <strong>Profile</strong>
                        </li>
                    </ol>
                </nav>
                <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                        </div>
                    </a>
                </li>
            </div>
        </nav>
        <!-- End Navbar -->

        <div class="container-fluid py-4">
            <div class="row justify-content-center">
                <!-- Centered Content -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="profile-container">
                                <div class="profile-frame">
                                    <img id="profileImage" src="<?php echo htmlspecialchars($profileImage); ?>"
                                        alt="Profile Picture" class="profile-image">
                                    <form method="post" enctype="multipart/form-data">
                                        <label for="imageUpload" class="edit-icon">
                                            <i class="fas fa-edit"></i>
                                        </label>
                                        <input type="file" name="imageUpload" id="imageUpload" accept="image/*">
                                        <button type="submit" style="display:none;"></button>
                                    </form>
                                </div>
                                <div class="profile-name" id="userName">
                                    <?php echo htmlspecialchars($userName); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!--   Core JS Files   -->
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>
    <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="../assets/js/plugins/chartjs.min.js"></script>
    <!-- Add this script at the end of your body -->
    <script>
        document.getElementById('imageUpload').addEventListener('change', function() {
            // Automatically submit the form when a file is selected
            this.form.submit();
        });
    </script>
</body>

</html>
