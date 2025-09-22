<?php
session_start();
require 'PHPmailer-master/src/Exception.php';
require 'PHPmailer-master/src/PHPMailer.php';
require 'PHPmailer-master/src/SMTP.php';
require 'config.php'; // Connessione al DB

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$user_id = $_SESSION['user_id'] ?? 1;

// ---------- FUNZIONE INVIO MAIL ----------
function sendMail($to, $subject, $body){
    $mail = new PHPMailer(true);
    try{
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'link4schooll@gmail.com'; // sostituire
        $mail->Password = 'scwxilcoxbgllxhs';       // sostituire con password app
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('link4schooll@gmail.com','Calendario');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();
    }catch(Exception $e){
        error_log("Mail error: ".$mail->ErrorInfo);
    }
}

// ---------- API AJAX CRUD ----------
if(isset($_POST['action'])){
    $action = $_POST['action'];
    $title = $_POST['title'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $id = $_POST['id'] ?? null;

    if($action==='list'){
        $res = $conn->query("SELECT id,title,date,time FROM events WHERE user_id=$user_id");
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        exit;
    }
    if($action==='add'){
        $stmt = $conn->prepare("INSERT INTO events(user_id,title,date,time) VALUES(?,?,?,?)");
        $stmt->bind_param("isss",$user_id,$title,$date,$time);
        $stmt->execute();
        echo json_encode(['status'=>'ok','id'=>$stmt->insert_id]);
        exit;
    }
    if($action==='update' && $id){
        $stmt = $conn->prepare("UPDATE events SET title=?, date=?, time=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sssii",$title,$date,$time,$id,$user_id);
        $stmt->execute();
        echo json_encode(['status'=>'ok']);
        exit;
    }
    if($action==='delete' && $id){
        $stmt = $conn->prepare("DELETE FROM events WHERE id=? AND user_id=?");
        $stmt->bind_param("ii",$id,$user_id);
        $stmt->execute();
        echo json_encode(['status'=>'ok']);
        exit;
    }
}

// ---------- SCRIPT CRON (solo quando richiamato con ?cron=1) ----------
if(isset($_GET['cron'])){
    $now = new DateTime();
    $res = $conn->query("SELECT e.title,e.date,e.time,u.email 
        FROM events e 
        JOIN users u ON u.id=e.user_id 
        WHERE e.user_id=$user_id");

    while($row = $res->fetch_assoc()){
        $eventDT = new DateTime($row['date'].' '.$row['time']);
        $diff = ($eventDT->getTimestamp() - $now->getTimestamp())/60;

        if(abs($diff-1440)<1){ // 1 giorno prima
            sendMail($row['email'], "Promemoria: ".$row['title'],
                "Il tuo evento '{$row['title']}' è domani alle {$row['time']}.");
        }
        if(abs($diff-10)<1){ // 10 minuti prima
            sendMail($row['email'], "Promemoria: ".$row['title'],
                "Il tuo evento '{$row['title']}' è tra 10 minuti alle {$row['time']}.");
        }
    }
    exit("Cron eseguito");
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Calendario Personale</title>

</head>
<body>
<style>:root{
  --bg:#0a0d13; --bg-2:#0c1118;
  --surface:#0f1520; --surface-2:#121a26;
  --text:#eef3ff; --muted:#9aa6bf;
  --border:rgba(255,255,255,.10); --border-strong:rgba(255,255,255,.18);
  --accent:#5d79ff; --accent-2:#21b1ff;
  --radius:16px; --radius-lg:24px; --blur:14px;
  --speed:180ms; --shadow-1:0 20px 60px rgba(0,0,0,.45);
  --shadow-2:0 12px 32px rgba(0,0,0,.28);
}
html[data-theme="light"]{
  --bg:#f6f8fd; --bg-2:#ffffff;
  --surface:#ffffff; --surface-2:#f2f5fb;
  --text:#0b1220; --muted:#4b5568;
  --border:rgba(10,20,40,.10); --border-strong:rgba(10,20,40,.16);
  --accent:#4d6bff; --accent-2:#0aaee8;
  --shadow-1:0 16px 48px rgba(10,20,40,.16);
  --shadow-2:0 10px 28px rgba(10,20,40,.12);
}

html,body{height:100%}
*{box-sizing:border-box}
body{
  margin:0; color:var(--text);
  font: 500 16px/1.55 Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial;
  background: linear-gradient(180deg, var(--bg), var(--bg-2));
  overflow-x:hidden;
}
::selection{ background: rgba(93,121,255,.35); color:#fff }

/* Layout */
.main-content{ padding:24px; min-height:100vh }
@media (min-width: 980px){ .main-content{ margin-left: 288px } }

/* Sidebar */
.sidebar{
  position: fixed; top:20px; left:20px; bottom:20px; width:260px;
  background: var(--surface); border:1px solid var(--border); border-radius: var(--radius-lg);
  box-shadow: var(--shadow-1); padding: 14px 10px; overflow: hidden;
  transition: left 0.3s ease; z-index: 10000;
}
.sidebar ul{ list-style:none; margin:6px 0 0; padding:6px; display:grid; gap:6px }
.sidebar a{
  position:relative; display:flex; align-items:center; gap:10px;
  padding:11px 12px 11px 18px; color:var(--text); text-decoration:none; border-radius:12px;
  border:1px solid transparent; transition: transform var(--speed), border-color var(--speed), background var(--speed), color var(--speed);
}
.sidebar a::before{
  content:""; position:absolute; left:8px; top:10px; bottom:10px; width:4px;
  background: transparent; border-radius:4px; transition: all var(--speed);
}
.sidebar a .icon{ font-size:18px; color: var(--muted) }
.sidebar a:hover{ transform: translateY(-1px); background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03)); border-color: var(--border-strong);}
.sidebar a.active, .sidebar a[aria-current="page"]{ background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.04)); border-color: var(--border-strong); font-weight: 700;}
.sidebar a.active::before, .sidebar a[aria-current="page"]::before{ background: var(--accent); width:6px;}
.sidebar a#logout-link{ color:#fff; background: linear-gradient(180deg, rgba(239,68,68,.96), rgba(239,68,68,.78)); border-color: rgba(239,68,68,.84);}

/* Hamburger */
.hamburger { display:none; flex-direction:column; gap:5px; cursor:pointer; position:fixed; top:20px; left:20px; z-index:10001;}
.hamburger span { display:block; width:24px; height:3px; background: var(--text); border-radius:2px; transition: all 0.3s ease; }

/* Overlay mobile */
.sidebar-overlay { position: fixed; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.5); z-index: 9998; display: none; }
.sidebar-overlay.show { display:block; }

@media (max-width: 980px){
  .hamburger { display:flex; }
  .sidebar { top:0; left:-260px; height:100%; border-radius:0; padding-top:60px; border-right:1px solid var(--border);}
  .sidebar.open { left:0; }
  .main-content { margin-left:0; padding:18px; }
}

/* Topbar */
.topbar{ position: sticky; top: 20px; z-index:6; margin: 0 20px 18px; background: var(--surface); border:1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow-1); display:flex; align-items:center; gap:14px; padding: 14px 18px; justify-content:space-between;}
.topbar h1{ margin:0; font-weight:800; font-size: clamp(20px, 2.2vw, 30px) }

/* Calendar */
.calendar-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;}
.calendar-header button { background:var(--accent); color:#fff; border:none; padding:6px 12px; border-radius:12px; cursor:pointer; }
#calendar { display:grid; grid-template-columns: repeat(7, 1fr); gap:6px; }
.day { background: var(--surface); border:1px solid var(--border); border-radius: var(--radius); padding:12px; min-height:80px; display:flex; flex-direction:column; position:relative;}
.day-header { font-weight:700; margin-bottom:6px; }
.day.today { border:2px solid var(--accent); }
.event { background: var(--accent-2); color:#fff; font-size:14px; padding:2px 6px; border-radius:8px; margin-top:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; cursor:pointer; }

/* Modal */
#event-modal { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,.6); display:none; align-items:center; justify-content:center; z-index:10000; }
#event-modal .modal-content { background:var(--surface); padding:24px; border-radius:var(--radius-lg); width:320px; box-shadow:var(--shadow-1);}
#event-modal input { width:100%; padding:8px; border-radius:12px; border:1px solid var(--border); margin-bottom:12px; background:var(--surface-2); color:var(--text);}
#event-modal button { padding:6px 12px; border:none; border-radius:12px; cursor:pointer; }
#event-modal .cancel-btn { background:var(--border); color:var(--text); margin-right:8px; }
#event-modal .save-btn { background:var(--accent); color:#fff; }
</style></style>
<!-- Hamburger -->
<div class="hamburger" id="hamburger">
  <span></span><span></span><span></span>
</div>
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <ul>
    <li><a href="dashboard.php">🤝 Matching</a></li>
    <li><a href="preferences.php">⚙️ Preferenze</a></li>
    <li><a href="feedback.php">📝 Feedback</a></li>
    <li><a href="test.php">📊 Test</a></li>
    <li><a href="call.php">☎️ Call</a></li>
    <li><a href="tutor.html">✅ Tutor</a></li>
    <li><a href="leaderboard.php">🏆 Classifica</a></li>
    <li><a href="calendario.php" aria-current="page">🗓️ Calendario</a></li>
    <li><a href="logout.php" id="logout-link">🚪 Logout</a></li>
  </ul>
</div>

<div class="main-content">
  <div class="topbar">
    <h1>Calendario Personale</h1>
    <button id="add-event-btn" class="save-btn">Aggiungi Evento</button>
  </div>
  <div class="calendar-header">
    <button id="prev-month">&lt;</button>
    <div id="month-year"></div>
    <button id="next-month">&gt;</button>
  </div>
  <div id="calendar"></div>
</div>

<!-- Modal -->
<div id="event-modal">
  <div class="modal-content">
    <h3>Evento</h3>
    <input type="text" id="event-title" placeholder="Titolo evento">
    <input type="date" id="event-date">
    <input type="time" id="event-time">
    <div style="text-align:right;">
      <button class="cancel-btn" id="cancel-event">Annulla</button>
      <button class="save-btn" id="save-event">Salva</button>
      <button class="save-btn" id="delete-event" style="background:#ef4444;">Elimina</button>
    </div>
  </div>
</div>

<script>
const calendarEl=document.getElementById('calendar');
const modal=document.getElementById('event-modal');
const addBtn=document.getElementById('add-event-btn');
const saveBtn=document.getElementById('save-event');
const cancelBtn=document.getElementById('cancel-event');
const deleteBtn=document.getElementById('delete-event');
const titleInput=document.getElementById('event-title');
const dateInput=document.getElementById('event-date');
const timeInput=document.getElementById('event-time');
let events=[], selectedEventId=null;
let currentDate=new Date();

function fetchEvents(){
    fetch('calendario.php',{method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=list'})
    .then(r=>r.json()).then(d=>{events=d; renderCalendar();});
}

function renderCalendar(){
    calendarEl.innerHTML='';
    const year=currentDate.getFullYear(), month=currentDate.getMonth();
    document.getElementById('month-year').innerText=
        currentDate.toLocaleString('it-IT',{month:'long',year:'numeric'});
    const firstDay=new Date(year,month,1).getDay();
    const daysInMonth=new Date(year,month+1,0).getDate();
    const todayStr=new Date().toISOString().split('T')[0];

    for(let i=0;i<(firstDay||7)-1;i++) calendarEl.appendChild(document.createElement('div'));

    for(let d=1;d<=daysInMonth;d++){
        const dayEl=document.createElement('div'); dayEl.className='day';
        const dayHeader=document.createElement('div'); dayHeader.className='day-header'; dayHeader.innerText=d;
        dayEl.appendChild(dayHeader);
        const dateStr=`${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
        if(dateStr===todayStr) dayEl.classList.add('today');
        events.filter(e=>e.date===dateStr).forEach(e=>{
            const evEl=document.createElement('div'); evEl.className='event';
            evEl.innerText=e.time+' - '+e.title;
            evEl.addEventListener('click',()=>{
                selectedEventId=e.id;
                titleInput.value=e.title; dateInput.value=e.date; timeInput.value=e.time;
                deleteBtn.style.display='inline-block'; modal.style.display='flex';
            });
            dayEl.appendChild(evEl);
        });
        calendarEl.appendChild(dayEl);
    }
}

addBtn.addEventListener('click',()=>{
    selectedEventId=null; titleInput.value=''; dateInput.value=''; timeInput.value='';
    deleteBtn.style.display='none'; modal.style.display='flex';
});
cancelBtn.addEventListener('click',()=>{modal.style.display='none';});
saveBtn.addEventListener('click',()=>{
    const title=titleInput.value.trim(), date=dateInput.value, time=timeInput.value;
    if(!title||!date||!time){alert('Compila tutti i campi');return;}
    const action=selectedEventId?'update':'add';
    const body=`action=${action}&title=${encodeURIComponent(title)}&date=${date}&time=${time}`+(selectedEventId?`&id=${selectedEventId}`:'');
    fetch('calendario.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body})
    .then(r=>r.json()).then(()=>{modal.style.display='none'; fetchEvents();});
});
deleteBtn.addEventListener('click',()=>{
    if(!selectedEventId) return;
    fetch('calendario.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=delete&id=${selectedEventId}`})
    .then(r=>r.json()).then(()=>{modal.style.display='none'; fetchEvents();});
});

// navigazione mesi
document.getElementById('prev-month').addEventListener('click',()=>{currentDate.setMonth(currentDate.getMonth()-1); renderCalendar();});
document.getElementById('next-month').addEventListener('click',()=>{currentDate.setMonth(currentDate.getMonth()+1); renderCalendar();});

fetchEvents();

// hamburger menu
document.addEventListener('DOMContentLoaded', ()=>{
    const hamburger=document.getElementById('hamburger');
    const sidebar=document.getElementById('sidebar');
    const overlay=document.getElementById('sidebar-overlay');
    hamburger.addEventListener('click',()=>{sidebar.classList.toggle('open');overlay.classList.toggle('show');});
    overlay.addEventListener('click',()=>{sidebar.classList.remove('open');overlay.classList.remove('show');});
});
</script>
</body>
</html>
