<?php
require_once '../Includes/db.php';
$pageTitle = 'Events | LFT Dumaguete';
$currentPage = 'EVENTS';

$allEvents = db()->query("SELECT * FROM events WHERE status = 'Active' ORDER BY event_date, event_time")->fetchAll();
$today = date('Y-m-d');
$upcomingEvents = [];
$pastEvents = [];

foreach ($allEvents as $event) {
    if ($event['event_date'] >= $today) {
        $upcomingEvents[] = $event;
    } else {
        $pastEvents[] = $event;
    }
}
$pastEvents = array_reverse($pastEvents);

require '../Includes/header.php';

function renderEventCard(array $event): void
{
    $date = new DateTime($event['event_date']);
    ?>
    <article class="event-item">
        <img src="../assets/images/<?= e($event['image']) ?>" alt="<?= e($event['title']) ?>">
        <div class="event-date"><strong><?= $date->format('d') ?></strong><span><?= strtoupper($date->format('M')) ?></span></div>
        <div>
            <p class="section-label"><?= e(strtoupper($event['category'])) ?></p>
            <h3><?= e($event['title']) ?></h3>
            <p><?= e($event['description']) ?></p>
            <small><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?= e($event['location']) ?></small>
        </div>
        <span class="event-time"><?= e(date('g:i A', strtotime($event['event_time']))) ?></span>
    </article>
    <?php
}
?>
<main>
    <section class="page-hero" style="background-image: linear-gradient(90deg, rgba(11,48,35,.9), rgba(11,48,35,.35)), url('../assets/images/conference.png');">
        <div class="container">
            <p class="section-label">MEET, LEARN, GROW</p>
            <h1>There is always<br><span>something happening.</span></h1>
            <p>Join practical workshops, community meetups, and conversations that make work feel less solitary.</p>
        </div>
    </section>

    <section class="listing-section">
        <div class="container">
            <div class="page-intro"><p class="section-label">UPCOMING</p><h2>Gather around good ideas.</h2></div>

            <?php if (!$upcomingEvents): ?>
                <div class="empty-state">There are no published upcoming events right now. <a href="../Contact/index.php#message-us">Ask the LFT team what is coming next.</a></div>
            <?php else: ?>
                <div class="event-list">
                    <?php foreach ($upcomingEvents as $event) renderEventCard($event); ?>
                </div>
            <?php endif; ?>

            <?php if ($pastEvents): ?>
                <div class="page-intro" style="margin-top: 56px;"><p class="section-label">RECENTLY AT LFT</p><h2>Past community moments.</h2></div>
                <div class="event-list">
                    <?php foreach (array_slice($pastEvents, 0, 6) as $event) renderEventCard($event); ?>
                </div>
            <?php endif; ?>

            <div class="center-button"><a class="btn btn-green" href="../Contact/index.php#message-us">ASK ABOUT EVENTS</a></div>
        </div>
    </section>
</main>
<?php require '../Includes/footer.php'; ?>
