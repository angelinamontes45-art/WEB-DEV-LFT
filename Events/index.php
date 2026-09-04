<?php
$pageTitle = 'Events | LFT Dumaguete';
$currentPage = 'EVENTS';
require '../Includes/header.php';
$events = db()->query("SELECT * FROM events WHERE status = 'Active' ORDER BY event_date, event_time")->fetchAll();
?>
<main>
    <section class="page-hero" style="background-image: linear-gradient(90deg, rgba(11,48,35,.9), rgba(11,48,35,.35)), url('../assets/images/conference.png');"><div class="container"><p class="section-label">MEET, LEARN, GROW</p><h1>There is always<br><span>something happening.</span></h1><p>Join practical workshops, community meetups, and conversations that make work feel less solitary.</p></div></section>
    <section class="listing-section"><div class="container"><div class="page-intro"><p class="section-label">ON THE CALENDAR</p><h2>Gather around good ideas.</h2></div><div class="event-list">
        <?php foreach ($events as $event): $date = new DateTime($event['event_date']); ?><article class="event-item"><img src="../assets/images/<?= e($event['image']) ?>" alt="<?= e($event['title']) ?>"><div class="event-date"><strong><?= $date->format('d') ?></strong><span><?= strtoupper($date->format('M')) ?></span></div><div><p class="section-label"><?= e(strtoupper($event['category'])) ?></p><h3><?= e($event['title']) ?></h3><p><?= e($event['description']) ?></p><small><?= e($event['location']) ?></small></div><span class="event-time"><?= e(date('g:i A', strtotime($event['event_time']))) ?></span></article><?php endforeach; ?>
        <?php if (false): ?>
        <article class="event-item"><div class="event-date"><strong>15</strong><span>JUN</span></div><div><p class="section-label">WORKSHOP</p><h3>Build Your Focus System</h3><p>A practical morning session for creating routines that make deep work easier.</p></div><span class="event-time">9:00 AM</span></article>
        <article class="event-item"><div class="event-date"><strong>22</strong><span>JUN</span></div><div><p class="section-label">COMMUNITY MEETUP</p><h3>Dumaguete Makers Night</h3><p>Meet the people building thoughtful businesses and creative projects around the city.</p></div><span class="event-time">6:00 PM</span></article>
        <article class="event-item"><div class="event-date"><strong>29</strong><span>JUN</span></div><div><p class="section-label">CREATOR SESSION</p><h3>Make Better Content</h3><p>Share ideas, learn from local creators, and see what the studio can do for your next project.</p></div><span class="event-time">2:00 PM</span></article>
        <?php endif; ?></div><div class="center-button"><a class="btn btn-green" href="../Contact/index.php#tour">ASK ABOUT UPCOMING EVENTS</a></div></div></section>
</main>
<?php require '../Includes/footer.php'; ?>