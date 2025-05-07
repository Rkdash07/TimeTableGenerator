<?php
session_start();
include("../../connection.php");

// Fetch all subject-faculty mappings (no course filter)
function get_subject_faculty($conn) {
    $sql = "SELECT s.sno, s.subject_name, f.fno, f.faculty
            FROM subjects s
            JOIN subject_faculty sf ON s.sno = sf.subject_id
            JOIN faculties f ON sf.faculty_id = f.fno";
    $result = $conn->query($sql);
    $subject_faculty = [];
    while ($row = $result->fetch_assoc()) {
        $subject_faculty[] = $row;
    }
    return $subject_faculty;
}

// Fitness function: penalizes faculty/subject clashes and over-allocation
function fitness($timetable, $subject_faculty, $periods_per_day = 6, $days_per_week = 5) {
    $score = 0;
    $faculty_per_slot = [];
    $subject_count = [];

    foreach ($timetable as $day) {
        foreach ($day as $period) {
            if ($period === null) continue;
            $faculty = $period['fno'];
            $subject = $period['sno'];

            // Check for faculty clash in a slot
            if (isset($faculty_per_slot[$faculty])) {
                $score -= 5; // Penalty for faculty clash
            } else {
                $faculty_per_slot[$faculty] = true;
            }

            // Count subject allocation
            if (!isset($subject_count[$subject])) $subject_count[$subject] = 0;
            $subject_count[$subject]++;
        }
        $faculty_per_slot = [];
    }

    // Penalize subjects not meeting required lectures per week
    foreach ($subject_faculty as $sf) {
        $sid = $sf['sno'];
        $required = $sf['lecture_per_week'];
        $actual = isset($subject_count[$sid]) ? $subject_count[$sid] : 0;
        $score -= abs($required - $actual);
    }

    return $score;
}

// Generate a random timetable
function random_timetable($subject_faculty, $periods_per_day = 6, $days_per_week = 5) {
    $timetable = [];
    $subjects = $subject_faculty;
    for ($i = 0; $i < $days_per_week; $i++) {
        $day = [];
        shuffle($subjects);
        for ($j = 0; $j < $periods_per_day; $j++) {
            $day[] = $subjects[array_rand($subjects)];
        }
        $timetable[] = $day;
    }
    return $timetable;
}

// Crossover two timetables
function crossover($parent1, $parent2) {
    $child = [];
    for ($i = 0; $i < count($parent1); $i++) {
        $child[] = (rand(0, 1) == 0) ? $parent1[$i] : $parent2[$i];
    }
    return $child;
}

// Mutate a timetable
function mutate($timetable, $subject_faculty, $mutation_rate = 0.1) {
    for ($i = 0; $i < count($timetable); $i++) {
        for ($j = 0; $j < count($timetable[$i]); $j++) {
            if (rand() / getrandmax() < $mutation_rate) {
                $timetable[$i][$j] = $subject_faculty[array_rand($subject_faculty)];
            }
        }
    }
    return $timetable;
}

// Main GA function
function genetic_algorithm($conn, $generations = 200, $population_size = 30) {
    $subject_faculty = get_subject_faculty($conn);
    $periods_per_day = 6;
    $days_per_week = 5;
    $population = [];

    // Initial population
    for ($i = 0; $i < $population_size; $i++) {
        $population[] = random_timetable($subject_faculty, $periods_per_day, $days_per_week);
    }

    // GA loop
    for ($g = 0; $g < $generations; $g++) {
        // Evaluate fitness
        $fitness_scores = [];
        foreach ($population as $individual) {
            $fitness_scores[] = fitness($individual, $subject_faculty, $periods_per_day, $days_per_week);
        }

        // Select best individuals
        array_multisort($fitness_scores, SORT_DESC, $population);
        $population = array_slice($population, 0, $population_size);

        // Crossover and mutate to produce new population
        $new_population = [];
        for ($i = 0; $i < $population_size; $i++) {
            $parent1 = $population[array_rand($population)];
            $parent2 = $population[array_rand($population)];
            $child = crossover($parent1, $parent2);
            $child = mutate($child, $subject_faculty);
            $new_population[] = $child;
        }
        $population = $new_population;
    }

    // Return the best timetable
    $best = $population[0];
    return $best;
}

// Handle form submission
$timetable = null;
if (isset($_POST['generate'])) {
    $timetable = genetic_algorithm($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Time Table Generator</title>
    <link rel="stylesheet" href="../assets/css/material-dashboard.css?v=3.2.0" />
    <style>
        table { width: 80%; margin: 30px auto; border-collapse: collapse; }
        th, td { border: 1px solid #bbb; padding: 8px; text-align: center; }
        th { background: #eee; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Generate Timetable</h2>
        <form method="post">
            <button type="submit" name="generate" class="btn btn-secondary">Generate</button>
        </form>
        <?php if ($timetable): ?>
            <h3>Generated Timetable</h3>
            <table>
                <tr>
                    <th>Day / Period</th>
                    <?php for ($p = 1; $p <= 6; $p++): ?>
                        <th>Period <?php echo $p; ?></th>
                    <?php endfor; ?>
                </tr>
                <?php
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                foreach ($timetable as $d => $day): ?>
                    <tr>
                        <th><?php echo $days[$d]; ?></th>
                        <?php foreach ($day as $period): ?>
                            <td>
                                <?php
                                echo htmlspecialchars($period['subject_name']) . "<br>";
                                echo "<small>" . htmlspecialchars($period['faculty_name']) . "</small>";
                                ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
