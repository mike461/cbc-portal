<?php
session_start();
require 'db_connection.php';

$student_data = null;
$feedback_msg = "";

// Handle searching for a child's progress 
if (isset($_POST['search_child'])) {
    $name = htmlspecialchars($_POST['child_name']);
    $stmt = $pdo->prepare("SELECT s.*, a.cbc_level, a.remarks 
                           FROM students s 
                           LEFT JOIN projects p ON s.id = p.student_id 
                           LEFT JOIN assessments a ON p.id = a.project_id 
                           WHERE s.first_name LIKE ? OR s.second_name LIKE ?");
    $stmt->execute(["%$name%", "%$name%"]);
    $student_data = $stmt->fetchAll();
}

// Handle Parent Feedback submission 
if (isset($_POST['submit_feedback'])) {
    $student_id = $_POST['student_id'];
    $feedback = htmlspecialchars($_POST['parent_comment']);
    
    // In a full system, you'd have a 'parent_feedback' table. 
    // For now, we update the existing project record or a dedicated feedback column.
    $stmt = $pdo->prepare("UPDATE assessments SET remarks = CONCAT(remarks, '\n\nParent Feedback: ', ?) WHERE project_id = (SELECT id FROM projects WHERE student_id = ? LIMIT 1)");
    if($stmt->execute([$feedback, $student_id])) {
        $feedback_msg = "<div class='bg-green-100 text-green-700 p-3 rounded'>Feedback submitted to the teacher!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parent Portal | CBC Feedback</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen p-6">
    <div class="max-w-4xl mx-auto">
        <header class="mb-10 text-center">
            <h1 class="text-3xl font-bold text-slate-800">Parent Feedback Portal</h1>
            [cite_start]<p class="text-slate-500">View progress and provide feedback on learner's development [cite: 2]</p>
        </header>

        <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
            <form method="POST" class="flex gap-4">
                <input type="text" name="child_name" placeholder="Enter Student Name..." required 
                    class="flex-1 px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-purple-500">
                <button name="search_child" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">Search Child</button>
            </form>
        </div>

        <?php if ($student_data): ?>
            <?php foreach ($student_data as $row): ?>
            <div class="bg-white p-8 rounded-xl shadow-lg border-l-4 border-purple-500 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-slate-800"><?= $row['first_name'] ?>'s Progress</h2>
                    <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full font-bold"><?= $row['cbc_level'] ?></span>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <div>
                        [cite_start]<h3 class="font-semibold text-slate-400 uppercase text-xs mb-2">Teacher Remarks [cite: 3]</h3>
                        <p class="text-slate-700 bg-slate-50 p-4 rounded-lg italic">"<?= $row['remarks'] ?: 'No remarks yet.' ?>"</p>
                    </div>

                    <div>
                        <h3 class="font-semibold text-slate-400 uppercase text-xs mb-2">Your Feedback</h3>
                        <form method="POST">
                            <input type="hidden" name="student_id" value="<?= $row['id'] ?>">
                            <textarea name="parent_comment" rows="3" required placeholder="Share your thoughts on the progress..." 
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 outline-none mb-3"></textarea>
                            <button name="submit_feedback" class="w-full bg-slate-800 text-white py-2 rounded-lg hover:bg-slate-900 transition">Submit Feedback</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?= $feedback_msg ?>
    </div>
</body>
</html>