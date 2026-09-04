<?php

require '../_guard.php';

$connection = db();

$search = trim($_GET['q'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$message = $_GET['message'] ?? '';

$allowedStatuses = [
    'Pending',
    'Confirmed',
    'Checked in',
    'Completed',
    'Cancelled'
];

if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}


/*
|--------------------------------------------------------------------------
| UPDATE BOOKING STATUS
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'booking-status'
) {

    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if (
        $bookingId > 0 &&
        in_array($status, $allowedStatuses, true)
    ) {

        $update = $connection->prepare(
            'UPDATE bookings SET status = ? WHERE id = ?'
        );

        $update->execute([
            $status,
            $bookingId
        ]);

        header(
            'Location: index.php?message=' .
            urlencode('Booking status updated.')
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| STATISTICS
|--------------------------------------------------------------------------
*/

$totalBookings = (int) $connection
    ->query('SELECT COUNT(*) FROM bookings')
    ->fetchColumn();

$pendingBookings = (int) $connection
    ->query(
        "SELECT COUNT(*) FROM bookings WHERE status = 'Pending'"
    )
    ->fetchColumn();

$confirmedBookings = (int) $connection
    ->query(
        "SELECT COUNT(*) FROM bookings WHERE status = 'Confirmed'"
    )
    ->fetchColumn();

$cancelledBookings = (int) $connection
    ->query(
        "SELECT COUNT(*) FROM bookings WHERE status = 'Cancelled'"
    )
    ->fetchColumn();


/*
|--------------------------------------------------------------------------
| SEARCH + FILTER
|--------------------------------------------------------------------------
*/

$conditions = [];
$parameters = [];

if ($search !== '') {

    $conditions[] = '
        (
            users.name LIKE ?
            OR users.email LIKE ?
            OR bookings.space LIKE ?
            OR bookings.booking_type LIKE ?
            OR bookings.id LIKE ?
        )
    ';

    $term = '%' . $search . '%';

    $parameters = [
        $term,
        $term,
        $term,
        $term,
        $term
    ];
}


if ($statusFilter !== '') {

    $conditions[] = 'bookings.status = ?';

    $parameters[] = $statusFilter;
}


if ($dateFilter !== '') {

    $conditions[] = 'bookings.visit_date = ?';

    $parameters[] = $dateFilter;
}


/*
|--------------------------------------------------------------------------
| GET BOOKINGS
|--------------------------------------------------------------------------
*/

$sql = '
    SELECT
        bookings.*,
        users.name AS customer_name,
        users.email AS customer_email
    FROM bookings
    JOIN users
        ON users.id = bookings.user_id
';

if ($conditions) {

    $sql .= ' WHERE ' . implode(
        ' AND ',
        $conditions
    );
}

$sql .= '
    ORDER BY
        bookings.visit_date DESC,
        bookings.visit_time DESC,
        bookings.created_at DESC
';

$statement = $connection->prepare($sql);

$statement->execute($parameters);

$bookings = $statement->fetchAll();

$pageTitle = 'Bookings | LFT Admin';

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

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

</head>

<body>

<main class="admin-app">


    <!-- SIDEBAR -->

    <?php require '../sidebar.php'; ?>


    <!-- MAIN -->

    <div class="admin-main">


        <!-- TOPBAR -->

        <header class="admin-topbar">

            <div>

                <p class="section-label">
                    LFT DUMAGUETE
                </p>

                <h1>
                    Bookings
                </h1>

                <p>
                    Manage customer workspace bookings.
                </p>

            </div>


            <?php require '../profile.php'; ?>

        </header>



        <!-- MESSAGE -->

        <?php if ($message): ?>

            <div class="success-message">

                <i class="fa-solid fa-circle-check"></i>

                <?= e($message) ?>

            </div>

        <?php endif; ?>



        <!-- STATISTICS -->

        <section class="admin-kpis">


            <article>

                <i class="fa-regular fa-calendar-check"></i>

                <span>
                    Total bookings
                </span>

                <strong>
                    <?= $totalBookings ?>
                </strong>

                <small>
                    All booking requests
                </small>

            </article>


            <article>

                <i class="fa-regular fa-clock"></i>

                <span>
                    Pending
                </span>

                <strong>
                    <?= $pendingBookings ?>
                </strong>

                <small>
                    Waiting for confirmation
                </small>

            </article>


            <article>

                <i class="fa-solid fa-circle-check"></i>

                <span>
                    Confirmed
                </span>

                <strong>
                    <?= $confirmedBookings ?>
                </strong>

                <small>
                    Confirmed bookings
                </small>

            </article>


            <article>

                <i class="fa-solid fa-circle-xmark"></i>

                <span>
                    Cancelled
                </span>

                <strong>
                    <?= $cancelledBookings ?>
                </strong>

                <small>
                    Cancelled bookings
                </small>

            </article>


        </section>



        <!-- BOOKING PANEL -->

        <section class="admin-widget booking-panel">


            <!-- HEADING -->

            <div class="widget-heading">

                <div>

                    <h2>
                        Booking requests
                    </h2>

                    <p>
                        <?= count($bookings) ?>
                        <?= count($bookings) === 1
                            ? 'booking'
                            : 'bookings'
                        ?>
                    </p>

                </div>

            </div>



            <!-- FILTERS -->

            <form
                class="booking-filters"
                method="get"
            >


                <label for="booking-search">
                    Search
                </label>

                <input
                    id="booking-search"
                    type="text"
                    name="q"
                    value="<?= e($search) ?>"
                    placeholder="Customer, email, space, or booking ID"
                >


                <label for="booking-date">
                    Date
                </label>

                <input
                    id="booking-date"
                    type="date"
                    name="date"
                    value="<?= e($dateFilter) ?>"
                >


                <label for="booking-status">
                    Status
                </label>

                <select
                    id="booking-status"
                    name="status"
                >

                    <option value="">
                        All statuses
                    </option>

                    <?php foreach ($allowedStatuses as $status): ?>

                        <option
                            value="<?= e($status) ?>"
                            <?= $statusFilter === $status
                                ? 'selected'
                                : '' ?>
                        >

                            <?= e($status) ?>

                        </option>

                    <?php endforeach; ?>

                </select>


                <button
                    class="btn btn-green"
                    type="submit"
                >

                    <i class="fa-solid fa-filter"></i>

                    FILTER

                </button>


                <?php if (
                    $search !== '' ||
                    $statusFilter !== '' ||
                    $dateFilter !== ''
                ): ?>

                    <a
                        class="text-link"
                        href="index.php"
                    >
                        Clear
                    </a>

                <?php endif; ?>


            </form>



            <!-- BOOKING TABLE -->

            <div class="booking-table">


                <!-- TABLE HEADER -->

                <div class="booking-table-head">

                    <span>
                        Booking
                    </span>

                    <span>
                        Customer
                    </span>

                    <span>
                        Space
                    </span>

                    <span>
                        Date & Time
                    </span>

                    <span>
                        Type
                    </span>

                    <span>
                        Status
                    </span>

                    <span>
                        Action
                    </span>

                </div>



                <!-- BOOKINGS -->

                <?php if (!$bookings): ?>

                    <div class="empty-state">

                        <i class="fa-regular fa-calendar-xmark"></i>

                        <strong>
                            No bookings found.
                        </strong>

                        <p>
                            Try changing your search or filters.
                        </p>

                    </div>


                <?php else: ?>


                    <?php foreach ($bookings as $booking): ?>

                        <div class="booking-table-row">


                            <!-- BOOKING -->

                            <div>

                                <strong>
                                    #BK-<?= e(
                                        str_pad(
                                            $booking['id'],
                                            4,
                                            '0',
                                            STR_PAD_LEFT
                                        )
                                    ) ?>
                                </strong>

                                <small>

                                    Submitted:

                                    <?= e(
                                        date(
                                            'M j, Y',
                                            strtotime(
                                                $booking['created_at']
                                            )
                                        )
                                    ) ?>

                                </small>

                            </div>



                            <!-- CUSTOMER -->

                            <div class="customer-info">

                                <span class="customer-avatar">

                                    <?= e(
                                        strtoupper(
                                            substr(
                                                $booking['customer_name'],
                                                0,
                                                1
                                            )
                                        )
                                    ) ?>

                                </span>


                                <span>

                                    <strong>
                                        <?= e(
                                            $booking['customer_name']
                                        ) ?>
                                    </strong>

                                    <small>
                                        <?= e(
                                            $booking['customer_email']
                                        ) ?>
                                    </small>

                                </span>

                            </div>



                            <!-- SPACE -->

                            <span>

                                <?= e(
                                    $booking['space']
                                ) ?>

                            </span>



                            <!-- DATE & TIME -->

                            <div>

                                <strong>

                                    <?= e(
                                        date(
                                            'M j, Y',
                                            strtotime(
                                                $booking['visit_date']
                                            )
                                        )
                                    ) ?>

                                </strong>

                                <small>

                                    <?= e(
                                        date(
                                            'g:i A',
                                            strtotime(
                                                $booking['visit_time']
                                            )
                                        )
                                    ) ?>

                                </small>

                            </div>



                            <!-- BOOKING TYPE -->

                            <span>

                                <?= e(
                                    ucfirst(
                                        $booking['booking_type']
                                    )
                                ) ?>

                            </span>



                            <!-- STATUS -->

                            <span>

                                <span
                                    class="
                                        table-status
                                        status-<?= e(
                                            strtolower(
                                                str_replace(
                                                    ' ',
                                                    '-',
                                                    $booking['status']
                                                )
                                            )
                                        ) ?>"
                                >

                                    <?= e(
                                        $booking['status']
                                    ) ?>

                                </span>

                            </span>



                            <!-- ACTION -->

                            <div class="row-actions">

                                <a
                                    class="text-link"
                                    href="view.php?id=<?= (int) $booking['id'] ?>"
                                >

                                    View

                                </a>


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
                                        value="<?= (int) $booking['id'] ?>"
                                    >


                                    <select
                                        name="status"
                                        onchange="this.form.submit()"
                                        title="Change booking status"
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


                        </div>

                    <?php endforeach; ?>


                <?php endif; ?>


            </div>


        </section>


    </div>

</main>

</body>

</html>