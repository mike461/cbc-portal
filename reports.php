<?php
// 1. ERROR REPORTING & BUFFERING (Strictly managed)
ob_start(); // Start buffering at the very top
ini_set('display_errors', 0); // Hide errors from the PDF stream
error_reporting(0);

require('fpdf.php');
require('db_connection.php');
session_start();

// 2. SECURITY CHECK
if (!isset($_SESSION['teacher_authorized'])) {
    ob_end_clean();
    die("Access Denied.");
}

// 3. DATA FETCHING
if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();

        if (!$student) { 
            ob_end_clean();
            die("Student not found."); 
        }

        $stmt = $pdo->prepare("SELECT * FROM projects WHERE student_id = ? ORDER BY created_at DESC");
        $stmt->execute([$student_id]);
        $projects = $stmt->fetchAll();

        // 4. PDF GENERATION
        $pdf = new FPDF();
        $pdf->AddPage();
        
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(0, 15, 'CBC LEARNER PROGRESS REPORT', 0, 1, 'C');
        $pdf->SetDrawColor(46, 204, 113);
        $pdf->Line(20, 25, 190, 25);
        $pdf->Ln(10);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 10, 'Student Name:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, strtoupper($student['first_name'] . ' ' . $student['second_name']), 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 10, 'Grade/Stream:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $student['grade'] . ' - ' . $student['stream'], 0, 1);
        $pdf->Ln(10);

        $pdf->SetFillColor(51, 65, 85);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(50, 10, 'Learning Evidence', 1, 0, 'C', true);
        $pdf->Cell(25, 10, 'CBC Level', 1, 0, 'C', true);
        $pdf->Cell(20, 10, 'Score %', 1, 0, 'C', true);
        $pdf->Cell(95, 10, 'Teacher Feedback', 1, 1, 'C', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 9);
        
        if (count($projects) > 0) {
            foreach ($projects as $row) {
                $reflection = (strlen($row['reflection'] ?? '') > 25) ? substr($row['reflection'], 0, 22) . '...' : ($row['reflection'] ?? 'No reflection');
                $pdf->Cell(50, 12, $reflection, 1, 0, 'L');
                $pdf->Cell(25, 12, $row['cbc_level'] ?? 'N/A', 1, 0, 'C');
                $pdf->Cell(20, 12, ($row['marks'] ?? '0') . '%', 1, 0, 'C');
                $remarks = ($row['remarks']) ? $row['remarks'] : 'Progressing well.';
                $pdf->Cell(95, 12, $remarks, 1, 1, 'L');
            }
        } else {
            $pdf->Cell(190, 15, 'No assessed projects found.', 1, 1, 'C');
        }

        // 5. THE CRITICAL CLEANUP
        $pdfData = $pdf->Output('S'); // Save PDF to a string first
        
        ob_get_clean(); // Wipe out EVERYTHING currently in the buffer
        ob_start();     // Start fresh
        
        header('Content-Type: application/pdf');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="Report_' . $student['first_name'] . '.pdf"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($pdfData));
        
        echo $pdfData; // Send ONLY the PDF data
        ob_end_flush();
        exit();

    } catch (Exception $e) {
        ob_end_clean();
        die("Error.");
    }
}
?>