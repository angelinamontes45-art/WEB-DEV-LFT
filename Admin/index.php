<?php

require '../Includes/db.php';

$user = requireRole(['admin']);
$connection = db();

$allowedStatuses = [
    'Pending',
    'Confirmed',
    'Checked in',
    'Completed',
    'Cancelled'
];

$message = '';

/*
|--------------------------------------------------------------------------
| UPDATE BOOKING STATUS
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'booking-status'
) {

    $status = $_POST['status'] ?? '';
    $bookingId = (int) ($_POST['booking_id'] ?? 0);

    if (
        $bookingId > 0 &&
        in_array($status, $allowedStatuses, true)
    ) {

        $statement = $connection->prepare(
            'UPDATE bookings SET status = ? WHERE id = ?'
        );

        $statement->execute([
            $status,
            $bookingId
        ]);

        $message = 'Booking status updated.';
    }
}


/*
|--------------------------------------------------------------------------
| DASHBOARD STATISTICS
|--------------------------------------------------------------------------
*/

$stats = [

    'bookings' => (int) $connection
        ->query('SELECT COUNT(*) FROM bookings')
        ->fetchColumn(),

    'spaces' => (int) $connection
        ->query('SELECT COUNT(*) FROM spaces WHERE active = 1')
        ->fetchColumn(),

    'members' => (int) $connection
        ->query(
            'SELECT COUNT(*) FROM users WHERE role = "customer"'
        )
        ->fetchColumn(),

    'events' => (int) $connection
        ->query(
            'SELECT COUNT(*) FROM events WHERE active = 1'
        )
        ->fetchColumn()

];


/*
|--------------------------------------------------------------------------
| RECENT BOOKINGS
|--------------------------------------------------------------------------
*/

$bookings = $connection
    ->query("
        SELECT
            bookings.*,
            users.name AS customer_name
        FROM bookings
        JOIN users
            ON users.id = bookings.user_id
        ORDER BY bookings.created_at DESC
        LIMIT 8
    ")
    ->fetchAll();


/*
|--------------------------------------------------------------------------
| UPCOMING EVENTS
|--------------------------------------------------------------------------
*/

$events = $connection
    ->query("
        SELECT *
        FROM events
        WHERE active = 1
        ORDER BY event_date, event_time
        LIMIT 3
    ")
    ->fetchAll();


/*
|--------------------------------------------------------------------------
| BOOKING STATUS COUNTS
|--------------------------------------------------------------------------
*/

$statuses = array_fill_keys(
    $allowedStatuses,
    0
);

$statusResults = $connection
    ->query("
        SELECT
            status,
            COUNT(*) AS total
        FROM bookings
        GROUP BY status
    ")
    ->fetchAll();


foreach ($statusResults as $row) {

    if (isset($statuses[$row['status']])) {

        $statuses[$row['status']] = (int) $row['total'];

    }
}


$pageTitle = 'Admin Dashboard | LFT Dumaguete';

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?= e($pageTitle) ?></title>


    <!-- Main CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

</head>


<body>


<main class="admin-app">


    <!-- SIDEBAR -->

    <?php require 'sidebar.php'; ?>


    <!-- MAIN CONTENT -->

    <div class="admin-main">


        <!-- TOPBAR -->

        <header class="admin-topbar">

            <div>

                <p class="section-label">
                    LFT DUMAGUETE
                </p>

                <h1>
                    Dashboard
                </h1>

                <p>
                    Welcome back, <?= e($user['name']) ?>!
                </p>

            </div>


            <?php require 'profile.php'; ?>

        </header>



        <!-- SUCCESS MESSAGE -->

        <?php if ($message): ?>

            <div class="success-message">

                <i class="fa-solid fa-circle-check"></i>

                <?= e($message) ?>

            </div>

        <?php endif; ?>



        <!-- STATISTICS -->

        <section class="admin-kpis">


            <!-- BOOKINGS -->

            <article>

                <i class="fa-regular fa-calendar-check"></i>

                <span>
                    Total bookings
                </span>

                <strong>
                    <?= $stats['bookings'] ?>
                </strong>

                <small>
                    All requests
                </small>

            </article>



            <!-- SPACES -->

            <article>

                <i class="fa-solid fa-couch"></i>

                <span>
                    Active spaces
                </span>

                <strong>
                    <?= $stats['spaces'] ?>
                </strong>

                <small>
                    Available spaces
                </small>

            </article>



            <!-- MEMBERS -->

            <article>

                <i class="fa-solid fa-users"></i>

                <span>
                    Members
                </span>

                <strong>
                    <?= $stats['members'] ?>
                </strong>

                <small>
                    Customer accounts
                </small>

            </article>



            <!-- EVENTS -->

            <article>

                <i class="fa-regular fa-calendar"></i>

                <span>
                    Upcoming events
                </span>

                <strong>
                    <?= $stats['events'] ?>
                </strong>

                <small>
                    Published events
                </small>

            </article>


        </section>



        <!-- CHARTS -->

        <section class="admin-grid">


            <!-- BOOKINGS OVERVIEW -->

            <article class="admin-widget booking-chart">

                <div class="widget-heading">

                    <div>

                        <h2>
                            Bookings overview
                        </h2>

                        <p>
                            Recent booking activity
                        </p>

                    </div>

                    <a href="bookings/">
                        View all
                    </a>

                </div>


                <div class="chart-bars">

                    <?php

                    $chartValues = [
                        max(1, $stats['bookings'] - 6),
                        max(1, $stats['bookings'] - 3),
                        max(1, $stats['bookings'] - 5),
                        max(1, $stats['bookings'] - 1),
                        max(1, $stats['bookings'] - 4),
                        max(1, $stats['bookings'])
                    ];

                    $maxChart = max($chartValues);

                    ?>

                    <?php foreach ($chartValues as $index => $value): ?>

                        <div class="chart-column">

                            <span
                                style="
                                    height:
                                    <?= (int) (
                                        ($value / $maxChart) * 100
                                    ) ?>%;
                                "
                            ></span>

                            <small>

                                <?= date(
                                    'M j',
                                    strtotime(
                                        '-' . (5 - $index) . ' days'
                                    )
                                ) ?>

                            </small>

                        </div>

                    <?php endforeach; ?>

                </div>

            </article>



            <!-- BOOKING STATUS -->

            <article class="admin-widget status-widget">

                <div class="widget-heading">

                    <div>

                        <h2>
                            Bookings by status
                        </h2>

                        <p>
                            <?= $stats['bookings'] ?>
                            total requests
                        </p>

                    </div>

                </div>


                <div class="status-list">


                    <?php foreach (
                        ['Confirmed', 'Pending', 'Cancelled']
                        as $status
                    ): ?>

                        <div>

                            <span
                                class="
                                    status-dot
                                    status-<?= strtolower($status) ?>
                                "
                            ></span>

                            <span>
                                <?= e($status) ?>
                            </span>

                            <strong>
                                <?= $statuses[$status] ?>
                            </strong>

                        </div>

                    <?php endforeach; ?>


                </div>

            </article>


        </section>



        <!-- LOWER SECTION -->

        <section class="admin-grid lower-grid">


            <!-- RECENT BOOKINGS -->

            <article class="admin-widget">

                <div class="widget-heading">

                    <div>

                        <h2>
                            Recent bookings
                        </h2>

                        <p>
                            Latest customer requests
                        </p>

                    </div>

                    <a href="bookings/">
                        View all bookings
                    </a>

                </div>


                <div class="admin-table">


                    <div class="table-head">

                        <span>Customer</span>

                        <span>Space</span>

                        <span>Date</span>

                        <span>Status</span>

                        <span>Action</span>

                    </div>


                    <?php if (!$bookings): ?>

                        <div class="empty-state">

                            No bookings yet.

                        </div>


                    <?php else: ?>


                        <?php foreach ($bookings as $booking): ?>

                            <div class="table-row">


                                <span>
                                    <?= e(
                                        $booking['customer_name']
                                    ) ?>
                                </span>


                                <span>
                                    <?= e(
                                        $booking['space']
                                        ?? 'N/A'
                                    ) ?>
                                </span>


                                <span>
                                    <?= e(
                                        $booking['visit_date']
                                    ) ?>
                                </span>


                                <span>

                                    <span
                                        class="
                                            table-status
                                            status-<?= strtolower(
                                                str_replace(
                                                    ' ',
                                                    '-',
                                                    $booking['status']
                                                )
                                            )
                                        ?>"
                                    >

                                        <?= e(
                                            $booking['status']
                                        ) ?>

                                    </span>

                                </span>


                                <form
                                    method="post"
                                >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="booking-status"
                                    >

                                    <input
                                        type="hidden"
                                        name="booking_id"
                                        value="<?= e(
                                            (string) $booking['id']
                                        ) ?>"
                                    >


                                    <select
                                        name="status"
                                        onchange="this.form.submit()"
                                    >

                                        <?php foreach (
                                            $allowedStatuses
                                            as $status
                                        ): ?>

                                            <option
                                                value="<?= e($status) ?>"
                                                <?= $booking['status'] === $status
                                                    ? 'selected'
                                                    : '' ?>
                                            >

                                                <?= e($status) ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </form>


                            </div>

                        <?php endforeach; ?>


                    <?php endif; ?>


                </div>

            </article>



            <!-- UPCOMING EVENTS -->

            <article class="admin-widget upcoming-widget">


                <div class="widget-heading">

                    <div>

                        <h2>
                            Upcoming events
                        </h2>

                        <p>
                            What is happening at LFT
                        </p>

                    </div>

                    <a href="events/">
                        View all
                    </a>

                </div>



                <?php if (!$events): ?>

                    <div class="empty-state">
                        No upcoming events.
                    </div>


                <?php else: ?>


                    <?php foreach ($events as $event): ?>

                        <div class="event-preview">


                            <div class="event-thumb">

                                <i class="fa-regular fa-calendar"></i>

                            </div>


                            <div>

                                <strong>
                                    <?= e(
                                        $event['title']
                                    ) ?>
                                </strong>

                                <span>

                                    <?= e(
                                        date(
                                            'M j, Y',
                                            strtotime(
                                                $event['event_date']
                                            )
                                        )
                                    ) ?>

                                    ·

                                    <?= e(
                                        date(
                                            'g:i A',
                                            strtotime(
                                                $event['event_time']
                                            )
                                        )
                                    ) ?>

                                </span>


                                <small>
                                    <?= e(
                                        $event['category']
                                    ) ?>
                                </small>

                            </div>


                        </div>

                    <?php endforeach; ?>


                <?php endif; ?>


            </article>


        </section>


    </div>

</main>


</body>

</html>