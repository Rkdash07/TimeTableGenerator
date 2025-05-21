<!--
=========================================================
* Material Dashboard 3 - v3.2.0
=========================================================

* Product Page: https://www.creative-tim.com/product/material-dashboard
* Copyright 2024 Creative Tim (https://www.creative-tim.com)
* Licensed under MIT (https://www.creative-tim.com/license)
* Coded by Creative Tim

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
-->
<?php
session_start();
include("../../connection.php");
$success_message = '';
// Fetch professors and subjects for dropdowns
$professors = [];
$prof_query = "SELECT faculty FROM faculties";
$prof_result = mysqli_query($conn, $prof_query);
while ($row = mysqli_fetch_assoc($prof_result)) {
    $professors[] = $row['faculty'];
}

$subjects_list = [];
$sub_query = "SELECT subject_name FROM subjects";
$sub_result = mysqli_query($conn, $sub_query);
while ($row = mysqli_fetch_assoc($sub_result)) {
    $subjects_list[] = $row['subject_name'];
}

// Optionally clear session if faculties are set
if (isset($_SESSION['faculties'])) {
    session_unset(); 
}
if (isset($_POST['action']) && $_POST['action'] === 'save') {
if (!empty($_POST['faculties'])) {
    $faculties = explode("\n", trim($_POST['faculties']));
} else {
    $faculties = []; 
}

$subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];
$allocations = isset($_POST['allocations']) ? $_POST['allocations'] : [];

    
$_SESSION['faculties'] = $faculties;
$_SESSION['subjects'] = $subjects;
$_SESSION['allocations'] = $allocations;

    // Insert into subject_faculty
    if (!empty($subjects)) {
        foreach ($subjects as $idx => $subject_name) {
            $sub_stmt = $conn->prepare("SELECT sno FROM subjects WHERE subject_name = ?");
            $sub_stmt->bind_param("s", $subject_name);
            $sub_stmt->execute();
            $sub_result = $sub_stmt->get_result();
            $sub_row = $sub_result->fetch_assoc();
            $subject_id = $sub_row ? $sub_row['sno'] : null;
            $sub_stmt->close();

            if ($subject_id) {
                foreach (['sectionA', 'sectionB', 'sectionC'] as $section) {
                    $faculty_name = $allocations[$section][$idx] ?? '';
                    if ($faculty_name) {
                        $fac_stmt = $conn->prepare("SELECT fno FROM faculties WHERE faculty = ?");
                        $fac_stmt->bind_param("s", $faculty_name);
                        $fac_stmt->execute(); 
                        $fac_result = $fac_stmt->get_result();
                        $fac_row = $fac_result->fetch_assoc();
                        $faculty_id = $fac_row ? $fac_row['fno'] : null;
                        $fac_stmt->close();

                        if ($faculty_id) {
                           $insert_stmt = $conn->prepare("INSERT INTO subject_faculty (subject_id, faculty_id, section) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE faculty_id = VALUES(faculty_id)");
                           $insert_stmt->bind_param("iis", $subject_id, $faculty_id, $section);
                            $insert_stmt->execute();
                            $insert_stmt->close();
                            
                        }
                    }
                }
            }
        }
        $success_message = "Data added successfully!!";  
    }
}

// For form repopulation
$faculties = $_SESSION['faculties'] ?? [];
$subjects = $_SESSION['subjects'] ?? [];
$allocations = $_SESSION['allocations'] ?? [];
?>
<?php if (!empty($success_message)): ?>
    <div class="alert alert-success" style="text-align:center;">
        <?php echo htmlspecialchars($success_message); ?>
    </div>
<?php endif; ?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <title>Time Table - Class Timetable Generator</title>
    <!-- Fonts and icons -->
    <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        .form-container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-control {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 0.375rem 0.75rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;

            &:focus {
                border-color: #86b7fe;
                outline: 0;
                box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            }
        }

        .view-button {
            margin-left: 10px;
        }

        .faculty-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .faculty-table th,
        .faculty-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .faculty-table th {
            background-color: #f2f2f2;
        }

        .delete-icon {
            color: red;
            cursor: pointer;

        }
    </style>
</head>

<body class="g-sidenav-show bg-gray-100">
    <!-- Sidebar -->
    <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2"
        id="sidenav-main">
        <hr class="horizontal dark mt-0 mb-2">
        <div class="collapse navbar-collapse w-auto " id="sidenav-collapse-main">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="../pages/dashboard.php">
                        <i class="material-symbols-rounded opacity-5"></i>
                        <span class="nav-link-text ms-1">Home</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="../pages/faculties.php">
                        <i class="material-symbols-rounded opacity-5"></i>
                        <span class="nav-link-text ms-1">Faculties</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="../pages/subjects.php">
                        <i class="material-symbols-rounded opacity-5"></i>
                        <span class="nav-link-text ms-1">Subjects</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white bg-gradient-dark active" href="../pages/tables.php">
                        <i class="material-symbols-rounded opacity-5"></i>
                        <span class="nav-link-text ms-1">Tables</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="../pages/profile.php">
                        <i class="material-symbols-rounded opacity-5"></i>
                        <span class="nav-link-text ms-1">Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="../pages/logout.php">
                        <i class="material-symbols-rounded opacity-5"></i>
                        <span class="nav-link-text ms-1">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur"
            data-scroll="true">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Class Timetable
                            Generator</li>
                    </ol>
                </nav>
            </div>
        </nav>

        <div class="container-fluid py-4">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-secondary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Class Timetable Generator</h6>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="form-container">
                        <form method="post" action="tables.php" id="timetableForm">

                            
                            <div class="form-group">
                                <label>Add Subjects and Assign Professors to Sections</label>
                                <table class="table align-items-center mb-0" id="subjectsTable">
                                <thead>
    <tr>
        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
            Subject
        </th>
        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
            Assign Professors for Each Section
        </th>
    </tr>
</thead>
<tbody id="subject-rows">
<?php
if (!empty($subjects)) { 
    foreach ($subjects as $key => $subject) {
        ?>
        <tr>
        <td>
        <select class="form-control" name="subjects[]" required>
    <option value="">Select Subject</option>
    <?php
    foreach ($subjects_list as $sub) {
        $selected = (isset($subjects[$key]) && $subjects[$key] == $sub) ? 'selected' : '';
        echo '<option value="' . htmlspecialchars($sub) . '" ' . $selected . '>' . htmlspecialchars($sub) . '</option>';
    }
    ?>
</select>
     </td>
            <td>
                <div>
                    <label for="sectionA">Section A</label>
                    <select class="form-control" name="allocations[sectionA][]" required>
                        <option value="">Select Professor</option>
                        <?php foreach ($professors as $professor) { ?>
                            <option value="<?php echo $professor; ?>" <?php echo (isset($allocations['sectionA'][$key]) && $allocations['sectionA'][$key] == $professor) ? 'selected' : ''; ?>>
                                <?php echo $professor; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label for="sectionB">Section B</label>
                    <select class="form-control" name="allocations[sectionB][]" required>
                        <option value="">Select Professor</option>
                        <?php foreach ($professors as $professor) { ?>
                            <option value="<?php echo $professor; ?>" <?php echo (isset($allocations['sectionB'][$key]) && $allocations['sectionB'][$key] == $professor) ? 'selected' : ''; ?>>
                                <?php echo $professor; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label for="sectionC">Section C</label>
                    <select class="form-control" name="allocations[sectionC][]" required>
                        <option value="">Select Professor</option>
                        <?php foreach ($professors as $professor) { ?>
                            <option value="<?php echo $professor; ?>" <?php echo (isset($allocations['sectionC'][$key]) && $allocations['sectionC'][$key] == $professor) ? 'selected' : ''; ?>>
                                <?php echo $professor; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </td>
        </tr>
        <?php
    }
}
?>
</tbody>

                                </table>
                                <button type="button" class="btn btn-secondary" onclick="addSubjectRow()">Add More
                                    Subjects</button>
                            </div>

                            <div style="text-align: center;">
                            <button type="submit" class="btn btn-secondary" id="save" name="action" value="save">Save</button>
                            <button type="submit" class="btn btn-secondary" id="generate" name="action" value="generate">Generate Timetable</button>
                            </div>
                            <div class="login-options" style="margin-left: 250px;">
                <a href="../../Dashboard/pages/sample.php" class="guest-link">
                    <strong> sample </strong> <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
            </div>
                        </form>
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
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            new PerfectScrollbar(document.querySelector('#sidenav-scrollbar'))
        }
    </script>
<script>
    // Use the correct PHP variables for all professors and all subjects
    const professorOptions = <?php echo json_encode($professors); ?>;
    const subjectOptions = <?php echo json_encode($subjects_list); ?>;

    function addSubjectRow() {
        const table = document.getElementById('subject-rows');
        const row = document.createElement('tr');

        let subjectOptionsHTML = `<option value="">Select Subject</option>`;
        subjectOptions.forEach(subject => {
            subjectOptionsHTML += `<option value="${subject}">${subject}</option>`;
        });

        let professorOptionsHTML = `<option value="">Select Professor</option>`;
        professorOptions.forEach(professor => {
            professorOptionsHTML += `<option value="${professor}">${professor}</option>`;
        });

        row.innerHTML = `
            <td>
                <select class="form-control" name="subjects[]" required>
                    ${subjectOptionsHTML}
                </select>
            </td>
            <td>
                <div>
                    <label for="sectionA">Section A</label>
                    <select class="form-control" name="allocations[sectionA][]" required>
                        ${professorOptionsHTML}
                    </select>
                </div>
                <div>
                    <label for="sectionB">Section B</label>
                    <select class="form-control" name="allocations[sectionB][]" required>
                        ${professorOptionsHTML}
                    </select>
                </div>
                <div>
                    <label for="sectionC">Section C</label>
                    <select class="form-control" name="allocations[sectionC][]" required>
                        ${professorOptionsHTML}
                    </select>
                </div>
            </td>
        `;
        table.appendChild(row);
    }
</script>

</script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
    <script>
    document.getElementById('generate').addEventListener('click', function() {
    document.getElementById('timetableForm').action = 'generate.php';
});
document.getElementById('save').addEventListener('click', function() {
    document.getElementById('timetableForm').action = 'tables.php';
});
    </script>
</body>

</html>