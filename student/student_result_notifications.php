<?php
/*
|--------------------------------------------------------------------------
| STUDENT RESULT NOTIFICATION COMPONENT
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    isset($_SESSION['student_id']) &&
    is_numeric($_SESSION['student_id'])
) {

    $student_id = (int) $_SESSION['student_id'];


    /*
    |--------------------------------------------------------------------------
    | CHECK NOTIFICATION TABLE
    |--------------------------------------------------------------------------
    */

    $notification_table_exists = false;

    $table_check = mysqli_query(
        $conn,
        "SHOW TABLES LIKE 'student_notifications'"
    );

    if (
        $table_check &&
        mysqli_num_rows($table_check) > 0
    ) {
        $notification_table_exists = true;
    }


    /*
    |--------------------------------------------------------------------------
    | GET STUDENT NOTIFICATIONS
    |--------------------------------------------------------------------------
    */

    if ($notification_table_exists) {

        $student_notifications = [];

        $stmt_notifications = mysqli_prepare(
            $conn,
            "SELECT
                notification_id,
                title,
                message,
                link,
                is_read,
                created_at
             FROM student_notifications
             WHERE student_id = ?
             ORDER BY is_read ASC, notification_id DESC
             LIMIT 10"
        );

        if ($stmt_notifications) {

            mysqli_stmt_bind_param(
                $stmt_notifications,
                "i",
                $student_id
            );

            mysqli_stmt_execute($stmt_notifications);

            $notification_result =
                mysqli_stmt_get_result($stmt_notifications);

            while (
                $notification_row =
                mysqli_fetch_assoc($notification_result)
            ) {
                $student_notifications[] = $notification_row;
            }

            mysqli_stmt_close($stmt_notifications);
        }


        /*
        |--------------------------------------------------------------------------
        | COUNT UNREAD NOTIFICATIONS
        |--------------------------------------------------------------------------
        */

        $unread_count = 0;

        foreach ($student_notifications as $notification) {

            if ((int) $notification['is_read'] === 0) {
                $unread_count++;
            }

        }

?>

<style>

/* ==========================================
   NOTIFICATION CARD
========================================== */

.result-notification-card {
    border: 0;
    border-radius: 15px;
    overflow: hidden;
}


/* ==========================================
   MESSAGE MOVING AREA
========================================== */

.notification-marquee {
    width: 100%;
    height: 32px;
    overflow: hidden;
    position: relative;
    white-space: nowrap;
    margin-top: 8px;
}


/* ==========================================
   SLOW MOVING MESSAGE
========================================== */

.notification-moving-message {
    position: absolute;
    top: 0;
    left: 0;

    display: inline-block;
    white-space: nowrap;

    font-size: 16px;
    color: #4b5563;

    line-height: 32px;

    will-change: transform;

    /* ধীরে ধীরে move হবে */
    animation: slowMove 20s linear infinite;
}


/* ==========================================
   SMOOTH RIGHT TO LEFT MOVEMENT
========================================== */

@keyframes slowMove {

    0% {
        transform: translateX(100%);
    }

    100% {
        transform: translateX(-100%);
    }

}


/* ==========================================
   HOVER করলে PAUSE হবে
========================================== */

.notification-marquee:hover
.notification-moving-message {
    animation-play-state: paused;
}


/* ==========================================
   MOBILE RESPONSIVE
========================================== */

@media (max-width: 768px) {

    .notification-moving-message {
        font-size: 14px;
        animation-duration: 18s;
    }

}

</style>


<!-- ==========================================
     RESULT NOTIFICATION CARD
========================================== -->

<div class="card shadow-sm mb-4 result-notification-card">


    <!-- HEADER -->

    <div
        class="
            card-header
            text-white
            d-flex
            justify-content-between
            align-items-center
        "
        style="
            background:
            linear-gradient(
                135deg,
                #198754,
                #157347
            );

            padding: 14px 22px;
        "
    >


        <div>

            <i
                class="
                    fa-solid
                    fa-bell
                    me-2
                "
            ></i>

            <strong style="font-size: 18px;">
                Result Notifications
            </strong>

        </div>


        <!-- UNREAD COUNT -->

        <?php if ($unread_count > 0): ?>

            <span
                class="
                    badge
                    bg-danger
                    rounded-pill
                "
            >
                <?= $unread_count ?> New
            </span>

        <?php endif; ?>


    </div>


    <!-- ==========================================
         CARD BODY
    =========================================== -->

    <div class="card-body p-3">


        <?php if (count($student_notifications) > 0): ?>


            <?php foreach (
                $student_notifications
                as $notification
            ): ?>


                <!-- SINGLE NOTIFICATION -->

                <div
                    class="mb-3"
                    style="
                        padding: 20px;
                        border: 1px solid #d1d5db;
                        border-radius: 14px;

                        background:
                        <?=
                        (int) $notification['is_read'] === 0
                        ? '#f8fafc'
                        : '#ffffff';
                        ?>
                        ;
                    "
                >


                    <div
                        class="
                            d-flex
                            justify-content-between
                            align-items-start
                            gap-3
                        "
                    >


                        <!-- LEFT SIDE -->

                        <div
                            style="
                                flex: 1;
                                min-width: 0;
                            "
                        >


                            <!-- TITLE -->

                            <div
                                class="
                                    d-flex
                                    align-items-center
                                    gap-2
                                "
                            >

                                <i
                                    class="
                                        fa-solid
                                        fa-circle-check
                                    "
                                    style="
                                        color: #198754;
                                        font-size: 22px;
                                    "
                                ></i>


                                <span
                                    style="
                                        font-size: 20px;
                                        font-weight: 700;
                                        color: #2f6655;
                                    "
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $notification['title']
                                    );
                                    ?>

                                </span>


                                <?php if (
                                    (int) $notification['is_read'] === 0
                                ): ?>

                                    <span
                                        class="
                                            badge
                                            bg-primary
                                        "
                                    >
                                        NEW
                                    </span>

                                <?php endif; ?>


                            </div>


                            <!-- ==========================================
                                 SLOW MOVING MESSAGE
                            =========================================== -->

                            <div class="notification-marquee">

                                <div
                                    class="
                                        notification-moving-message
                                    "
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $notification['message']
                                    );
                                    ?>

                                </div>

                            </div>


                            <!-- DATE -->

                            <div
                                class="
                                    mt-2
                                    text-muted
                                "
                                style="
                                    font-size: 14px;
                                "
                            >

                                <?php
                                echo htmlspecialchars(
                                    $notification['created_at']
                                );
                                ?>

                            </div>


                        </div>


                        <!-- RIGHT SIDE -->

                        <div
                            class="
                                d-flex
                                flex-column
                                align-items-end
                                gap-2
                            "
                        >


                            <!-- VIEW RESULT BUTTON -->

                            <a
                                href="<?=
                                htmlspecialchars(
                                    $notification['link']
                                )
                                ?>"
                                class="
                                    btn
                                    btn-success
                                    btn-sm
                                "
                                onclick="
                                    markResultNotificationRead(
                                        <?=
                                        (int)
                                        $notification[
                                            'notification_id'
                                        ]
                                        ?>
                                    );
                                "
                                style="
                                    font-weight: 600;
                                "
                            >

                                View Result & Marksheet

                                <i
                                    class="
                                        fa-solid
                                        fa-arrow-right
                                        ms-1
                                    "
                                ></i>

                            </a>


                        </div>


                    </div>


                </div>


            <?php endforeach; ?>


        <?php else: ?>


            <!-- NO NOTIFICATION -->

            <div
                class="
                    text-center
                    text-muted
                "
                style="
                    padding: 40px;
                "
            >

                <i
                    class="
                        fa-solid
                        fa-bell-slash
                        fa-2x
                        mb-3
                    "
                ></i>

                <div>
                    No new notifications.
                </div>

            </div>


        <?php endif; ?>


    </div>


</div>


<!-- ==========================================
     MARK NOTIFICATION AS READ
========================================== -->

<script>

function markResultNotificationRead(notificationId) {

    const url =
        "mark_result_notification_read.php?id="
        + encodeURIComponent(notificationId);

    fetch(
        url,
        {
            method: "GET",
            credentials: "same-origin"
        }
    )
    .catch(function () {

        /*
         * Request fail হলেও
         * Result page normally open হবে।
         */

    });

}

</script>


<?php

    }

}

?>