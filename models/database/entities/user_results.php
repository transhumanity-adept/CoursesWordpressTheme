<?php

function create_user_results_table(): void
{
    global $wpdb;
    global $user_results_table_name;
    $users_table_name = 'wp_users';
    global $courses_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $user_results_table_name(
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `user_id` BIGINT(20) UNSIGNED NOT NULL,
        FOREIGN KEY (`user_id`) REFERENCES $users_table_name(`ID`),
        `course_id` INT NOT NULL,
        FOREIGN KEY (`course_id`) REFERENCES $courses_table_name(`id`),
        UNIQUE KEY `unique_key` (`user_id`, `course_id`)
    );";
    $wpdb->query($createQuery);
}
function drop_user_results_table(): void
{
    global $wpdb;
    global $user_results_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $user_results_table_name;";
    $wpdb->query($dropQuery);
}