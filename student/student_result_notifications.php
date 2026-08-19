<?php
/*
|--------------------------------------------------------------------------
| STUDENT RESULT NOTIFICATION COMPONENT
|--------------------------------------------------------------------------
|
| Add this line inside the student's profile/dashboard:
|
| include 'student_result_notifications.php';
|
| If the profile is in another folder, adjust the include path.
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    isset($_SESSION['student_id']) &&
    is_numeric($_SESSION['student_id'])
) {

    $student_id =
        (int) $_SESSION['student_id'];

    /*
     * The table is created by admin/publish-result.php
     * when the first final result is published.
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


    if ($notification_table_exists) {

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
             ORDER BY
                is_read ASC,
                notification_id DESC
             LIMIT 10"
        );

        $student_notifications = [];

        if ($stmt_notifications) {

            mysqli_stmt_bind_param(
                $stmt_notifications,
                "i",
                $student_id
            );

            mysqli_stmt_execute(
                $stmt_notifications
            );

            $notification_result =
                mysqli_stmt_get_result(
                    $stmt_notifications
                );

            while (
                $notification_row =
                mysqli_fetch_assoc(
                    $notification_result
                )
            ) {

                $student_notifications[] =
                    $notification_row;
            }

            mysqli_stmt_close(
                $stmt_notifications
            );
        }


        $unread_count = 0;

        foreach (
            $student_notifications
            as $notification
        ) {

            if (
                (int)
                $notification['is_read']
                === 0
            ) {
                $unread_count++;
            }
        }

        ?>

        <div class="card shadow-sm mb-4"
             style="
                border:0;
                border-radius:15px;
                overflow:hidden;
             ">

            <div
                class="card-header text-white d-flex
                       justify-content-between
                       align-items-center"
                style="
                    background:linear-gradient(
                        135deg,
                        #2563eb,
                        #1d4ed8
                    );
                "
            >

                <div>

                    <i
                        class="fa-solid fa-bell me-2"
                    ></i>

                    <strong>
                        Notifications
                    </strong>

                </div>

                <?php if (
                    $unread_count > 0
                ): ?>

                    <span
                        class="badge bg-danger"
                    >
                        <?=
                        $unread_count
                        ?>
                        New
                    </span>

                <?php endif; ?>

            </div>


            <div class="card-body p-0">

                <?php if (
                    count(
                        $student_notifications
                    ) > 0
                ): ?>

                    <?php foreach (
                        $student_notifications
                        as $notification
                    ): ?>

                        <div
                            style="
                                padding:18px 20px;
                                border-bottom:
                                    1px solid #e5e7eb;
                                background:
                                    <?=
                                    (int)
                                    $notification[
                                        'is_read'
                                    ] === 0
                                    ? '#eff6ff'
                                    : '#ffffff';
                                    ?>;
                            "
                        >

                            <div
                                class="d-flex
                                       justify-content-between
                                       gap-3"
                            >

                                <div>

                                    <div
                                        style="
                                            font-weight:700;
                                            color:#111827;
                                            font-size:16px;
                                        "
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $notification[
                                                'title'
                                            ]
                                        );
                                        ?>

                                        <?php if (
                                            (int)
                                            $notification[
                                                'is_read'
                                            ] === 0
                                        ): ?>

                                            <span
                                                class="badge
                                                       bg-primary
                                                       ms-2"
                                            >
                                                NEW
                                            </span>

                                        <?php endif; ?>

                                    </div>


                                    <div
                                        style="
                                            color:#4b5563;
                                            margin-top:6px;
                                        "
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $notification[
                                                'message'
                                            ]
                                        );
                                        ?>

                                    </div>


                                    <small
                                        class="text-muted"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $notification[
                                                'created_at'
                                            ]
                                        );
                                        ?>

                                    </small>

                                </div>


                                <div
                                    class="d-flex
                                           align-items-center"
                                >

                                    <a
                                        href="<?=
                                        htmlspecialchars(
                                            $notification[
                                                'link'
                                            ]
                                        )
                                        ?>"
                                        class="btn
                                               btn-primary
                                               btn-sm"
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
                                    >

                                        <i
                                            class="fa-solid
                                                   fa-eye me-1"
                                        ></i>

                                        Click Now for See

                                    </a>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div
                        class="text-center
                               text-muted"
                        style="
                            padding:30px;
                        "
                    >

                        <i
                            class="fa-solid
                                   fa-bell-slash
                                   fa-2x
                                   mb-2"
                        ></i>

                        <div>
                            No new notifications.
                        </div>

                    </div>

                <?php endif; ?>

            </div>

        </div>


        <script>

        function
        markResultNotificationRead(
            notificationId
        ) {

            /*
             * This uses a small GET request to the same
             * profile page. The result still opens normally.
             *
             * If your profile has its own notification-read
             * endpoint, you can replace this later.
             */

            const url =
                "mark_result_notification_read.php?id="
                + encodeURIComponent(
                    notificationId
                );

            fetch(url, {
                method: "GET",
                credentials: "same-origin"
            }).catch(function () {
                // The result page still opens.
            });
        }

        </script>

        <?php
    }
}