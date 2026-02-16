<?php
session_start();
require 'db_connection.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['teacher_authorized']) || $_SESSION['teacher_authorized'] !== true) {
    header("Location: index.php");
    exit();
}

// 2. INTERNAL SAVING LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'award_marks') {
    $p_id = $_POST['project_id'];
    $level = $_POST['level'];
    $marks = $_POST['marks'];
    $remarks = $_POST['remarks'];

    try {
        $sql = "UPDATE projects SET cbc_level = ?, marks = ?, remarks = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$level, $marks, $remarks, $p_id])) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit(); 
}

// 3. FETCH DATA
$query = "SELECT p.*, s.first_name, s.second_name, s.grade, s.stream 
          FROM projects p JOIN students s ON p.student_id = s.id 
          ORDER BY p.created_at DESC";
$projects = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Admin | CBC Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans text-slate-900">
    <div class="flex">
        <div class="w-64 bg-slate-900 min-h-screen p-6 text-white fixed shadow-2xl z-20">
            <h2 class="text-2xl font-black mb-8 text-emerald-400 tracking-tighter uppercase">CBC Portal</h2>
            <nav class="space-y-2">
                <a href="#" class="block py-3 px-4 bg-slate-800 border-l-4 border-emerald-500 rounded font-bold">Assessments</a>
                <a href="reports.php" class="block py-3 px-4 hover:bg-slate-800 transition rounded text-slate-400 hover:text-white">Generate Reports</a>
                <a href="logout.php" class="block py-3 px-4 text-red-400 mt-20 border-t border-slate-800 hover:bg-red-900/20 transition rounded">Sign Out</a>
            </nav>
        </div>

        <div class="flex-1 p-10 ml-64">
            <header class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-4xl font-black text-slate-800">Project Review</h1>
                    <p class="text-slate-500 font-medium">Evaluate student evidence and award performance levels.</p>
                </div>
                
                <div class="relative w-full max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" id="studentSearch" onkeyup="filterStudents()" 
                        placeholder="Search Name, Grade or Stream (e.g. Grade 2 West)..." 
                        class="block w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl leading-5 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition font-medium shadow-sm">
                </div>
            </header>

            <div class="grid gap-10" id="projectGrid">
                <?php foreach ($projects as $row): ?>
                <div class="project-card bg-white rounded-3xl shadow-xl overflow-hidden border border-white flex flex-col xl:flex-row">
                    
                    <div class="xl:w-[450px] bg-slate-900 flex items-center justify-center shrink-0 border-r border-slate-100 relative group" style="min-height: 350px;">
                        <?php if($row['media_type'] == 'video'): ?>
                            <video src="<?= $row['media_url'] ?>" controls class="max-w-full max-h-full"></video>
                        <?php else: ?>
                            <img src="<?= $row['media_url'] ?>" 
                                 class="max-w-full max-h-full object-contain cursor-zoom-in transition-transform duration-300 group-hover:scale-105"
                                 onclick="window.open(this.src, '_blank')" 
                                 title="Click to view full size">
                        <?php endif; ?>
                        <div class="absolute bottom-3 right-3 bg-black/60 text-white text-[10px] px-3 py-1 rounded-full opacity-0 group-hover:opacity-100 transition pointer-events-none">
                            Click to Expand
                        </div>
                    </div>

                    <div class="p-8 flex-1 bg-white">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h2 class="student-name text-2xl font-bold text-slate-900"><?= $row['first_name'] . ' ' . $row['second_name'] ?></h2>
                                <p class="student-info text-emerald-600 font-bold uppercase tracking-widest text-xs mt-1">
                                    <span class="grade-text"><?= $row['grade'] ?></span> &bull; <span class="stream-text"><?= $row['stream'] ?></span>
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="resetPassword(<?= $row['student_id'] ?>, '<?= $row['first_name'] ?>')" class="text-[10px] bg-blue-50 text-blue-600 px-3 py-1 rounded-md border border-blue-200 font-bold uppercase hover:bg-blue-600 hover:text-white transition">Reset Pass</button>
                                
                                <a href="reports.php?student_id=<?= $row['student_id'] ?>" class="p-2 text-slate-400 hover:text-emerald-600 transition" title="Download Report">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </a>
                                <button onclick="deleteProject(<?= $row['id'] ?>)" class="p-2 text-slate-300 hover:text-red-500 transition">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div class="bg-slate-50 p-4 rounded-2xl mb-6 border border-slate-100">
                            <span class="text-[10px] font-black text-slate-400 uppercase block mb-1">Student Reflection</span>
                            <p class="text-slate-600 italic leading-relaxed text-sm">"<?= htmlspecialchars($row['reflection']) ?>"</p>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="text-xs font-black text-slate-500 uppercase mb-2 block ml-1">Performance Level</label>
                                <select id="level_<?= $row['id'] ?>" class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl p-3 outline-none focus:border-emerald-500 focus:bg-white transition font-bold text-slate-700">
                                    <option value="">Select Level</option>
                                    <option value="EE" <?= $row['cbc_level'] == 'EXCEEDING EXPECTATION' ? 'selected' : '' ?>>Exceeding Expectation (Exceeding Expectation)</option>
                                    <option value="ME" <?= $row['cbc_level'] == 'MEETING EXPECTATION' ? 'selected' : '' ?>>Meeting Expectation (Meeting Expectation)</option>
                                    <option value="AE" <?= $row['cbc_level'] == 'APPROACHING EXPECTATION' ? 'selected' : '' ?>>Approaching Expectation (Approaching Expectation)</option>
                                    <option value="BE" <?= $row['cbc_level'] == 'BELOW EXPECTATION' ? 'selected' : '' ?>>Below Expectation (Below Expectation)</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-black text-slate-500 uppercase mb-2 block ml-1">Percentage Score (%)</label>
                                <input type="number" id="marks_<?= $row['id'] ?>" value="<?= $row['marks'] ?>"
                                    class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl p-3 outline-none focus:border-emerald-500 focus:bg-white transition font-bold text-slate-700">
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="text-xs font-black text-slate-500 uppercase mb-2 block ml-1">Professional Remarks</label>
                            <textarea id="remarks_<?= $row['id'] ?>" rows="2"
                                class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl p-3 outline-none focus:border-emerald-500 focus:bg-white transition font-medium text-slate-700"><?= htmlspecialchars($row['remarks']) ?></textarea>
                        </div>

                        <button onclick="awardMarks(<?= $row['id'] ?>)" 
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold py-4 rounded-2xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-3">
                            Save & Award Grade
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        // UPDATED: Multi-Criteria Search Filter
        function filterStudents() {
            const input = document.getElementById('studentSearch');
            const filter = input.value.toLowerCase();
            const cards = document.getElementsByClassName('project-card');

            for (let i = 0; i < cards.length; i++) {
                const name = cards[i].querySelector('.student-name').innerText.toLowerCase();
                const grade = cards[i].querySelector('.grade-text').innerText.toLowerCase();
                const stream = cards[i].querySelector('.stream-text').innerText.toLowerCase();
                
                // Creates a combined string like "grade 2 west" to check against search
                const fullCriteria = `${name} ${grade} ${stream}`;
                
                if (fullCriteria.includes(filter)) {
                    cards[i].style.display = "";
                } else {
                    cards[i].style.display = "none";
                }
            }
        }

        function awardMarks(projectId) {
            const level = document.getElementById('level_' + projectId).value;
            const marks = document.getElementById('marks_' + projectId).value;
            const remarks = document.getElementById('remarks_' + projectId).value;

            const formData = new FormData();
            formData.append('action', 'award_marks');
            formData.append('project_id', projectId);
            formData.append('level', level);
            formData.append('marks', marks);
            formData.append('remarks', remarks);

            const btn = event.currentTarget;
            btn.innerHTML = "Saving...";
            btn.disabled = true;

            fetch('teacher_dashboard.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    btn.innerHTML = "✅ Grade Awarded";
                    btn.classList.replace('bg-emerald-600', 'bg-blue-600');
                    setTimeout(() => {
                        btn.innerHTML = "Save & Award Grade";
                        btn.classList.replace('bg-blue-600', 'bg-emerald-600');
                        btn.disabled = false;
                    }, 2000);
                }
            });
        }

        function resetPassword(studentId, name) {
            if (confirm("Reset password for " + name + " to 'password123'?")) {
                const formData = new FormData();
                formData.append('student_id', studentId);
                fetch('reset_student_password.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(d => { if(d.status === 'success') alert("Password reset successful."); });
            }
        }

        function deleteProject(id) {
            if (confirm("Delete permanently?")) {
                const fd = new FormData(); fd.append('project_id', id);
                fetch('delete_project.php', { method: 'POST', body: fd }).then(() => location.reload());
            }
        }
    </script>
</body>
</html>