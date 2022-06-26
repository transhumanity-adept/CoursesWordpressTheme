<?php

function create_tests_table(): void
{
    global $wpdb;
    global $tests_table_name;
    $currentTime = time();
    $defaultJSON = array(
        'time' => $currentTime,
        'blocks' => array(),
        'version' => '2.24.3'
    );
    $defaultJSONString = json_encode($defaultJSON);
    $createQuery = "CREATE TABLE IF NOT EXISTS $tests_table_name (
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY(`id`),
        `name` VARCHAR(255) NOT NULL UNIQUE,
        `header` VARCHAR(8000) NOT NULL DEFAULT '$defaultJSONString'
    );";
    $wpdb->query($createQuery);
}
function drop_tests_table(): void
{
    global $wpdb;
    global $tests_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $tests_table_name;";
    $wpdb->query($dropQuery);
}

function get_all_tests ( WP_REST_Request $request ) : WP_REST_Response
{
    global $tests_table_name;
    global $wpdb;
    $query = "SELECT * FROM $tests_table_name";
    $tests = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'tests' => $tests
    ];
    return  new WP_REST_Response(json_encode($response), 200);
}
function create_new_test ( WP_REST_Request $request ) : WP_REST_Response
{
    $body = json_decode($request->get_body());
    $newTest = $body->newTest;
    global $wpdb;
    global $tests_table_name;
    $result = $wpdb->get_results("INSERT INTO $tests_table_name (`name`) VALUES ('$newTest->name');");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $createdTest = $wpdb->get_results("SELECT * FROM $tests_table_name WHERE `name` = '$newTest->name';", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'createdTest' => $createdTest
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function update_test( WP_REST_Request $request ): WP_REST_Response
{
    $updateTestId = $request['testId'];
    $body = json_decode($request->get_body());
    $newTest = $body->newTest;
    global $wpdb;
    global $tests_table_name;
    $result = $wpdb->get_results("UPDATE $tests_table_name SET `name` = '$newTest->name' WHERE `id` = $updateTestId;");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $updatedTest = $wpdb->get_results("SELECT * FROM $tests_table_name WHERE `id` = $updateTestId;", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'updatedTest' => $updatedTest
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function delete_test( WP_REST_Request $request ) : WP_REST_Response
{
    $deletedTestId = $request['testId'];
    global $wpdb;
    global $tests_table_name;
    global $tests_in_themes_table_name;

    $result = $wpdb->get_results("DELETE FROM $tests_in_themes_table_name WHERE `test_id` = $deletedTestId");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    global $questions_in_test_table_name;
    $result = $wpdb->get_results("DELETE FROM $questions_in_test_table_name WHERE `test_id` = $deletedTestId");
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $deletedTest = $wpdb->get_results("SELECT * FROM $tests_table_name WHERE `id` = $deletedTestId", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $result = $wpdb->get_results("DELETE FROM $tests_table_name WHERE `id` = $deletedTestId");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedTest' => $deletedTest
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function update_test_header(WP_REST_Request $request): WP_REST_Response
{
    $body = json_decode($request->get_body());
    $updatedTestId = $request['testId'];
    $newHeader = $body->newHeader;
    global $wpdb;
    global $tests_table_name;
    $result = $wpdb->get_results("UPDATE $tests_table_name SET `header` = '$newHeader' WHERE `id` = '$updatedTestId';");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    };
    $updatedTest = $wpdb->get_results("SELECT * FROM $tests_table_name WHERE `id` = $updatedTestId", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    };
    $response = [
        'updatedTest' => $updatedTest,
    ];
    return new WP_REST_Response(json_encode($response), 200);
}

function create_tests_in_themes_table(): void
{
    global $wpdb;
    global $tests_in_themes_table_name;
    global $themes_table_name;
    global $tests_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $tests_in_themes_table_name (
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `theme_id` INT NOT NULL,
        FOREIGN KEY (`theme_id`) REFERENCES $themes_table_name (`id`),
        `test_id` INT NOT NULL,
        FOREIGN KEY (`test_id`) REFERENCES $tests_table_name (`id`),
        UNIQUE KEY `unique_key` (`theme_id`, `test_id`)
    );";
    $wpdb->query($createQuery);
}
function drop_tests_in_themes_table(): void
{
    global $wpdb;
    global $tests_in_themes_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $tests_in_themes_table_name;";
    $wpdb->query($dropQuery);
}

function get_all_tests_in_theme( WP_REST_Request $request ) : WP_REST_Response
{
    $themeId = $request['themeId'];
    global $wpdb;
    global $tests_in_themes_table_name;
    global $tests_table_name;
    $query = "SELECT `Tests`.*  FROM $tests_in_themes_table_name AS `ThemeTests`
              INNER JOIN $tests_table_name AS `Tests`
              ON `Tests`.`id` = `ThemeTests`.`test_id`
              WHERE `ThemeTests`.`theme_id` = $themeId;";
    $tests = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'tests' => $tests
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function delete_test_in_theme( WP_REST_Request $request ): WP_REST_Response
{
    $themeId = $request['themeId'];
    $testId = $request['testId'];
    global $wpdb;
    global $tests_in_themes_table_name;
    $query = "DELETE FROM $tests_in_themes_table_name WHERE `theme_id` = $themeId AND `test_id` = $testId;";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedTestId' => $testId
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function add_tests_in_theme ( WP_REST_Request $request ) : WP_REST_Response
{
    $themeId = $request['themeId'];
    $body = json_decode($request->get_body());
    $added_tests = $body->tests;
    global $wpdb;
    global $tests_in_themes_table_name;
    $values = implode("," , array_map(function($test) use ($themeId) { return "($themeId, $test->id)"; }, $added_tests));
    $query = "INSERT INTO $tests_in_themes_table_name (`theme_id`, `test_id`) VALUES $values";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'addedTests' => $added_tests
    ];
    return new WP_REST_Response(json_encode($response), 200);
}

function create_tests_in_courses_table(): void {
    global $wpdb;
    global $tests_in_courses_table_name;
    global $courses_table_name;
    global $tests_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $tests_in_courses_table_name (
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `course_id` INT NOT NULL,
        FOREIGN KEY (`course_id`) REFERENCES $courses_table_name (`id`),
        `test_id` INT NOT NULL,
        FOREIGN KEY (`test_id`) REFERENCES $tests_table_name (`id`),
        UNIQUE KEY `unique_key` (`course_id`, `test_id`)
    );";
    $wpdb->query($createQuery);
}
function drop_tests_in_courses_table(): void {
    global $wpdb;
    global $tests_in_courses_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $tests_in_courses_table_name;";
    $wpdb->query($dropQuery);
}

function get_all_tests_in_course( WP_REST_Request $request ) : WP_REST_Response
{
    $courseId = $request['courseId'];
    global $wpdb;
    global $tests_in_courses_table_name;
    global $tests_table_name;
    $query = "SELECT `Tests`.*  FROM $tests_in_courses_table_name AS `CourseTests`
              INNER JOIN $tests_table_name AS `Tests`
              ON `Tests`.`id` = `CourseTests`.`test_id`
              WHERE `CourseTests`.`course_id` = $courseId;";
    $tests = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'tests' => $tests
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function delete_test_in_course( WP_REST_Request $request ): WP_REST_Response
{
    $courseId = $request['courseId'];
    $testId = $request['testId'];
    global $wpdb;
    global $tests_in_courses_table_name;
    $query = "DELETE FROM $tests_in_courses_table_name WHERE `course_id` = $courseId AND `test_id` = $testId;";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedTestId' => $testId
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function add_tests_in_course ( WP_REST_Request $request ) : WP_REST_Response
{
    $courseId = $request['courseId'];
    $body = json_decode($request->get_body());
    $added_tests = $body->tests;
    global $wpdb;
    global $tests_in_courses_table_name;
    $values = implode("," , array_map(function($test) use ($courseId) { return "($courseId, $test->id)"; }, $added_tests));
    $query = "INSERT INTO $tests_in_courses_table_name (`course_id`, `test_id`) VALUES $values";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'addedTests' => $added_tests
    ];
    return new WP_REST_Response(json_encode($response), 200);
}