<?php
session_start();
include("../../connection.php");
function get_subject_faculty($conn) {
    $sql = "SELECT s.sno, s.subject_name, s.subject_hours_per_week, 
                   f.fno, f.faculty
            FROM subjects s
            JOIN subject_faculty sf ON s.sno = sf.subject_id
            JOIN faculties f ON sf.faculty_id = f.fno
            WHERE sf.section = 'sectionA'"; 
 $result = $conn->query($sql);
    $subject_faculty = [];
    while ($row = $result->fetch_assoc()) {
        $row['faculty_name'] = $row['faculty'];
         $row['subject_hours_per_week'] = !empty($row['subject_hours_per_week']) ? (int)$row['subject_hours_per_week'] : 0;
        $subject_faculty[] = $row; 
    }
    return $subject_faculty;
}



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

  foreach ($subject_faculty as $sf) {
    $sid = $sf['sno'];
    $required = $sf['subject_hours_per_week']; 
    $actual = isset($subject_count[$sid]) ? $subject_count[$sid] : 0;
    $score -= abs($required - $actual);
}

    return $score;
}


function random_timetable($subject_faculty, $periods_per_day = 6, $days_per_week = 5) {
    $timetable = [];
    $total_periods = $periods_per_day * $days_per_week;

   
    $subject_pool = [];
    foreach ($subject_faculty as $sf) {
        for ($i = 0; $i < $sf['subject_hours_per_week']; $i++) {
            $subject_pool[] = $sf;
        }
    }

    $remaining_slots = $total_periods - count($subject_pool);
    for ($i = 0; $i < $remaining_slots; $i++) {
        $subject_pool[] = null;
    }

    shuffle($subject_pool); 
    
    $index = 0;
    for ($day = 0; $day < $days_per_week; $day++) {
        $day_schedule = [];
        for ($period = 0; $period < $periods_per_day; $period++) {
            $day_schedule[] = $subject_pool[$index];
            $index++;
        }
        $timetable[] = $day_schedule;
    }

    return $timetable;
}



function crossover($parent1, $parent2) {
    $child = [];
    for ($i = 0; $i < count($parent1); $i++) {
        $child[] = (rand(0, 1) == 0) ? $parent1[$i] : $parent2[$i];
    }
    return $child;
}

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


function genetic_algorithm($conn, $generations = 200, $population_size = 30) {
    $subject_faculty = get_subject_faculty($conn);
    $periods_per_day = 6;
    $days_per_week = 5;
    $population = [];

    for ($i = 0; $i < $population_size; $i++) {
        $population[] = random_timetable($subject_faculty, $periods_per_day, $days_per_week);
    }

    
    for ($g = 0; $g < $generations; $g++) {
        $fitness_scores = [];
        foreach ($population as $individual) {
            $fitness_scores[] = fitness($individual, $subject_faculty, $periods_per_day, $days_per_week);
        }

        array_multisort($fitness_scores, SORT_DESC, $population);
        $population = array_slice($population, 0, $population_size);

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


    $best = $population[0];
    return $best;
}

function timetable_to_html($timetable) {
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    $html = '<table border="1" style="width:80%;margin:30px auto;border-collapse:collapse;">';
    $html .= '<tr><th>Day / Period</th>';
    for ($p = 1; $p <= 6; $p++) {
        $html .= "<th>Period $p</th>";
    }
    $html .= '</tr>';
    foreach ($timetable as $d => $day) {
        $html .= "<tr><th>{$days[$d]}</th>";
        foreach ($day as $period) {
            if ($period === null) {
                $html .= "<td></td>"; // Blank cell
            } else {
                $html .= "<td>" .
                    htmlspecialchars($period['subject_name']) . "<br><small>" .
                    htmlspecialchars($period['faculty_name']) . "</small></td>";
            }
        }
        $html .= "</tr>";
    }
    $html .= '</table>';
    return $html;
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
        <form method="post" style="display: flex; justify-content: center;">
            <button type="submit" name="generate"  class="btn btn-secondary btn-lg">Generate</button>
        </form>
        <?php if ($timetable): ?>
            <h3>Generated Timetable</h3>
        <?php
        $timetable_html = timetable_to_html($timetable);
        echo $timetable_html;
        ?>
    <form method="post" action="pdf.php" target="_blank" style="display: flex; justify-content: center;">
        <input type="hidden" name="timetable_html" value="<?php echo htmlspecialchars($timetable_html); ?>">
        <button type="submit" class="btn btn-secondary">Save as PDF</button>
    </form>
<?php endif; ?>
   
            <!-- <table>
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
            </table> -->
    </div>
</body>
</html>
