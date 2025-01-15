<?php

ob_start(); // Optiuni pentru debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../database/db_connect.php";
require_once '../jpgraph-4.4.2/src/jpgraph.php';
require_once '../jpgraph-4.4.2/src/jpgraph_pie.php';
require_once '../jpgraph-4.4.2/src/jpgraph_pie3d.php';
require_once '../jpgraph-4.4.2/src/jpgraph_bar.php';
require_once '../jpgraph-4.4.2/src/jpgraph_line.php';

// Verifica daca userul este logat si este admin
if (!isset($_SESSION['email']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../pages/login");
    exit();
}

include "../includes/header.php";
include "../includes/navbar.php";

// Functia pentru randare charturi dinamice
function renderChart($chartType)
{
    global $conn;

    // Sterge output anterior pentru a preveni coruperea imaginilor
    ob_end_clean();
    header('Content-Type: image/png'); // Setare explicita a tipului de graph pentru output (image/png)

    switch ($chartType) {
        case 'device_pie':
            $query = "SELECT device_type, COUNT(*) as count 
                      FROM analytics 
                      WHERE device_type IS NOT NULL 
                      GROUP BY device_type";
            $result = $conn->query($query);

            if (!$result) {
                error_log("Database query failed: " . $conn->error);
                die("Database query failed.");
            }

            $deviceTypes = [];
            $deviceCounts = [];

            while ($row = $result->fetch_assoc()) {
                $deviceTypes[] = $row['device_type'];
                $deviceCounts[] = $row['count'];
            }

            $pieGraph = new PieGraph(450, 350);
            $pieGraph->SetShadow();

            $piePlot = new PiePlot3D($deviceCounts);
            $piePlot->SetLegends($deviceTypes);
            $piePlot->SetCenter(0.5, 0.5);
            $pieGraph->legend->SetColumns(2); // Legenda in 2 coloane
            $pieGraph->legend->SetFont(FF_FONT1, FS_NORMAL, 10); // Font legenda
            $pieGraph->legend->SetPos(0.5, 0.85, 'center', 'top'); // Pozitionare legenda sub graph
            $pieGraph->Add($piePlot);
            $pieGraph->Stroke();
            break;

        case 'browser_bar':
            $query = "SELECT browser, COUNT(*) as count 
                      FROM analytics 
                      WHERE browser IS NOT NULL 
                      GROUP BY browser";
            $result = $conn->query($query);

            if (!$result) {
                error_log("Database query failed: " . $conn->error);
                die("Database query failed.");
            }

            $browsers = [];
            $browserCounts = [];

            while ($row = $result->fetch_assoc()) {
                $browsers[] = $row['browser'];
                $browserCounts[] = $row['count'];
            }

            $barGraph = new Graph(450, 350);
            $barGraph->SetScale("textlin");
            $barGraph->SetMargin(80, 30, 40, 50);
            $barGraph->xaxis->SetTickLabels($browsers);
            $barGraph->xaxis->title->Set("Browser");
            $barGraph->yaxis->title->Set("Number of Visits");
            $barGraph->yaxis->SetLabelMargin(25);
            $barGraph->yaxis->title->SetMargin(30); // Mutare titlu spre stanga

            $barPlot = new BarPlot($browserCounts);
            $barGraph->Add($barPlot);
            $barGraph->Stroke();
            break;

        case 'visit_line':
            $query = "SELECT DATE(visit_time) as visit_date, COUNT(*) as count 
                      FROM analytics 
                      GROUP BY DATE(visit_time)";
            $result = $conn->query($query);

            if (!$result) {
                error_log("Database query failed: " . $conn->error);
                die("Database query failed.");
            }

            $visitDates = [];
            $visitCounts = [];

            while ($row = $result->fetch_assoc()) {
                $visitDates[] = $row['visit_date'];
                $visitCounts[] = $row['count'];
            }

            // Debug output pentru rezultatele querry-urilor
            error_log("Visit Dates: " . implode(', ', $visitDates));
            error_log("Visit Counts: " . implode(', ', $visitCounts));

            if (count($visitCounts) < 2) {
                // Configurare chart bar daca sunt mai putine entry-uri
                $barGraph = new Graph(450, 350);
                $barGraph->SetScale("textlin");
                $barGraph->SetMargin(80, 30, 40, 50);
                $barGraph->xaxis->SetTickLabels($visitDates);
                $barGraph->xaxis->title->Set("Date");
                $barGraph->yaxis->title->Set("Number of Visits");
                $barGraph->yaxis->SetLabelMargin(25);
                $barGraph->yaxis->title->SetMargin(30); // Mutare titlu spre stanga

                $barPlot = new BarPlot($visitCounts);
                $barGraph->Add($barPlot);
                $barGraph->Stroke();
                exit();
            }

            $lineGraph = new Graph(450, 350);
            $lineGraph->SetScale("intlin");
            $lineGraph->SetMargin(80, 30, 40, 50);
            $lineGraph->xaxis->SetTickLabels($visitDates);
            $lineGraph->xaxis->title->Set("Date");
            $lineGraph->yaxis->title->Set("Number of Visits");
            $lineGraph->yaxis->SetLabelMargin(25);
            $lineGraph->yaxis->title->SetMargin(30); // Mutare titlu spre stanga

            $linePlot = new LinePlot($visitCounts);
            $lineGraph->Add($linePlot);
            $lineGraph->Stroke();
            break;

        default:
            error_log("Invalid chart type requested.");
            die("Invalid chart type requested.");
    }
    exit();
}

// Randarea charturilor dinamic daca este nevoie
if (isset($_GET['chart_type'])) {
    renderChart($_GET['chart_type']);
}

?>

<div class="container mt-5">
    <h2 class="text-center mb-4" style="font-family: 'Times New Roman', serif;">Admin Dashboard</h2>
    <div class="row">
        <!-- Tip Chart Pie -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h4>Device Type Distribution</h4>
                    <img src="admin_dashboard?chart_type=device_pie" alt="Device Type Pie Chart" class="img-fluid">
                </div>
            </div>
        </div>
        <!-- Tip Chart Bar -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h4>Browser Usage Distribution</h4>
                    <img src="admin_dashboard?chart_type=browser_bar" alt="Browser Bar Chart" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Tip Chart Linie -->
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h4>Visits Over Time</h4>
                    <img src="admin_dashboard?chart_type=visit_line" alt="Visits Over Time Line Chart" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
