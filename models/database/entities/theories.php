<?php

function create_theories_table(): void
{
    global $wpdb;
    global $theories_table_name;
    $currentTime = time();
    $defaultJSON = array(
        'time' => $currentTime,
        'blocks' => array(),
        'version' => '2.24.3'
    );
    $defaultJSONString = json_encode($defaultJSON);
    $createQuery = "CREATE TABLE IF NOT EXISTS $theories_table_name(
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `name` VARCHAR(255) NOT NULL UNIQUE,
        `content` VARCHAR(8000) NOT NULL DEFAULT '$defaultJSONString'
    );";
    $wpdb->query($createQuery);
}
function drop_theories_table(): void
{
    global $wpdb;
    global $theories_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $theories_table_name;";
    $wpdb->query($dropQuery);
}

function get_all_theories() : WP_REST_Response
{
    global $theories_table_name;
    global $wpdb;
    $query = "SELECT * FROM $theories_table_name";
    $theories = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'theories' => $theories
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function create_new_theory ( WP_REST_Request $request ) : WP_REST_Response
{
    $body = json_decode($request->get_body());
    $newTheory = $body->newTheory;
    global $wpdb;
    global $theories_table_name;
    $result = $wpdb->get_results("INSERT INTO $theories_table_name (`name`) VALUES ('$newTheory->name');");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $createdTheory = $wpdb->get_results("SELECT * FROM $theories_table_name WHERE `name` = '$newTheory->name';", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'createdTheory' => $createdTheory
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function update_theory( WP_REST_Request $request ): WP_REST_Response
{
    $updateTheoryId = $request['theoryId'];
    $body = json_decode($request->get_body());
    $newTheory = $body->newTheory;
    global $wpdb;
    global $theories_table_name;
    $result = $wpdb->get_results("UPDATE $theories_table_name SET `name` = '$newTheory->name' WHERE `id` = $updateTheoryId;");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $updatedTheory = $wpdb->get_results("SELECT * FROM $theories_table_name WHERE `id` = $updateTheoryId;", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'updatedTheory' => $updatedTheory
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function delete_theory( WP_REST_Request $request ) : WP_REST_Response
{
    $deletedTheoryId = $request['theoryId'];
    global $wpdb;
    global $theories_table_name;
    global $concepts_in_theories_table_name;
    $dropQuery = "DELETE FROM $concepts_in_theories_table_name WHERE `theory_id` = $deletedTheoryId";
    $result = $wpdb->get_results($dropQuery);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $deletedTheory = $wpdb->get_results("SELECT * FROM $theories_table_name WHERE `id` = $deletedTheoryId;", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $result = $wpdb->get_results("DELETE FROM $theories_table_name WHERE `id` = $deletedTheoryId;");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedTheory' => $deletedTheory
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function update_theory_content(WP_REST_Request $request): WP_REST_Response
{
    $body = json_decode($request->get_body());
    $updatedTheoryId = $request['theoryId'];
    $newContent = $body->newContent;
    global $wpdb;
    global $theories_table_name;
    $result = $wpdb->get_results("UPDATE $theories_table_name SET `content` = '$newContent' WHERE `id` = '$updatedTheoryId';");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    };
    $updatedTheory = $wpdb->get_results("SELECT * FROM $theories_table_name WHERE `id` = $updatedTheoryId", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    };
    $response = [
        'updatedTheory' => $updatedTheory,
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}

function create_theories_in_themes_table(): void
{
    global $wpdb;
    global $theories_in_themes_table_name;
    global $themes_table_name;
    global $theories_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $theories_in_themes_table_name(
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `theme_id` INT NOT NULL,
        FOREIGN KEY (`theme_id`) REFERENCES $themes_table_name (`id`),
        `theory_id` INT NOT NULL,
        FOREIGN KEY (`theory_id`) REFERENCES $theories_table_name (`id`),
        UNIQUE KEY `unique_key` (`theme_id`, `theory_id`)
    );";
    $wpdb->query($createQuery);
}
function drop_theories_in_themes_table(): void
{
    global $wpdb;
    global $theories_in_themes_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $theories_in_themes_table_name;";
    $wpdb->query($dropQuery);
}

function get_all_theories_in_theme( WP_REST_Request $request ) : WP_REST_Response
{
    $themeId = $request['themeId'];
    global $wpdb;
    global $theories_in_themes_table_name;
    global $theories_table_name;
    $query = "SELECT `Theories`.*  FROM $theories_in_themes_table_name AS `ThemeTheories`
              INNER JOIN $theories_table_name AS `Theories`
              ON `Theories`.`id` = `ThemeTheories`.`theory_id`
              WHERE `ThemeTheories`.`theme_id` = $themeId;";
    $theories = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'theories' => $theories
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function delete_theory_in_theme( WP_REST_Request $request ): WP_REST_Response
{
    $themeId = $request['themeId'];
    $theoryId = $request['theoryId'];
    global $wpdb;
    global $theories_in_themes_table_name;
    $query = "DELETE FROM $theories_in_themes_table_name WHERE `theme_id` = $themeId AND `theory_id` = $theoryId;";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedTheoryId' => $theoryId
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function add_theories_in_theme ( WP_REST_Request $request ) : WP_REST_Response
{
    $themeId = $request['themeId'];
    $body = json_decode($request->get_body());
    $added_theories = $body->theories;
    global $wpdb;
    global $theories_in_themes_table_name;
    $values = implode("," , array_map(function($theory) use ($themeId) { return "($themeId, $theory->id)"; }, $added_theories));
    $query = "INSERT INTO $theories_in_themes_table_name (`theme_id`, `theory_id`) VALUES $values";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'addedTheories' => $added_theories
    ];
    return new WP_REST_Response(json_encode($response), 200);
}

function create_theories_in_user_results(): void
{
    global $wpdb;
    global $theories_in_user_results;
    global $user_results_table_name;
    global $theories_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $theories_in_user_results(
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `user_results_id` INT NOT NULL,
        FOREIGN KEY (`user_results_id`) REFERENCES $user_results_table_name (`id`),
        `theory_id` INT NOT NULL,
        FOREIGN KEY (`theory_id`) REFERENCES $theories_table_name (`id`),
        UNIQUE KEY `unique_key` (`user_results_id`, `theory_id`)
    );";
    $wpdb->query($createQuery);
}
function drop_theories_in_user_results_table(): void
{
    global $wpdb;
    global $theories_in_user_results;
    $dropQuery = "DROP TABLE IF EXISTS $theories_in_user_results;";
    $wpdb->query($dropQuery);
}

function get_all_theories_in_user_results( WP_REST_Request $request ): WP_REST_Response {
    global $wpdb;
    global $user_results_table_name;
    global $theories_in_user_results;
    $userId = $request['userId'];
    $courseId = $request['courseId'];
    $query = "SELECT $theories_in_user_results.theory_id FROM $user_results_table_name INNER JOIN $theories_in_user_results ON $theories_in_user_results.user_results_id = $user_results_table_name.id WHERE $user_results_table_name.user_id = $userId AND $user_results_table_name.course_id = $courseId;";
    $theoriesId = $wpdb->get_results($query, ARRAY_N);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'theoriesId' => $theoriesId
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function add_theory_in_user_results( WP_REST_Request $request ): WP_REST_Response {
    global $wpdb;
    global $theories_in_user_results;
    global $user_results_table_name;
    $theory = json_decode($request->get_body())->theory;
    $user_id = $request['userId'];
    $course_id = $request['courseId'];
    $user_result_id = $wpdb->get_results("SELECT $user_results_table_name.`id` FROM $user_results_table_name WHERE `user_id` = $user_id AND `course_id` = $course_id;", ARRAY_N)[0][0];
    $query = "INSERT INTO $theories_in_user_results (`user_results_id`, `theory_id`) VALUES ($user_result_id, $theory->id);";
    $wpdb->query($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'addedTheory' => $theory
    ];
    return new WP_REST_Response(json_encode($response), 200);
}