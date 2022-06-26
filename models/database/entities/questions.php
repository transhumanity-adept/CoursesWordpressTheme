<?php

function create_questions_table(): void
{
    global $wpdb;
    global $questions_table_name;
    $currentTime = time();
    $defaultJSON = array(
        'time' => $currentTime,
        'blocks' => array(),
        'version' => '2.24.3'
    );
    $defaultJSONString = json_encode($defaultJSON);
    $createQuery = "CREATE TABLE IF NOT EXISTS $questions_table_name (
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `name` VARCHAR(255) NOT NULL UNIQUE,
        `text` VARCHAR(8000) NOT NULL DEFAULT '$defaultJSONString'
    )";
    $wpdb->query($createQuery);
}
function drop_questions_table(): void
{
    global $wpdb;
    global $questions_table_name;
    $dropQuery = "DROP TABLE $questions_table_name";
    $wpdb->query($dropQuery);
}

function get_all_questions ( WP_REST_Request $request ) : WP_REST_Response
{
    global $questions_table_name;
    global $wpdb;
    $query = "SELECT * FROM $questions_table_name";
    $questions = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'questions' => $questions
    ];
    return  new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function create_new_question ( WP_REST_Request $request ) : WP_REST_Response
{
    $body = json_decode($request->get_body());
    $newQuestion = $body->newQuestion;
    global $wpdb;
    global $questions_table_name;
    $result = $wpdb->get_results("INSERT INTO $questions_table_name (`name`) VALUES ('$newQuestion->name');");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $createdQuestion = $wpdb->get_results("SELECT * FROM $questions_table_name WHERE `name` = '$newQuestion->name';", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'createdQuestion' => $createdQuestion
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function update_question( WP_REST_Request $request ): WP_REST_Response
{
    $updateQuestionId = $request['questionId'];
    $body = json_decode($request->get_body());
    $newQuestion = $body->newQuestion;
    global $wpdb;
    global $questions_table_name;
    $result = $wpdb->get_results("UPDATE $questions_table_name SET `name` = '$newQuestion->name' WHERE `id` = $updateQuestionId;");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $updatedQuestion = $wpdb->get_results("SELECT * FROM $questions_table_name WHERE `id` = $updateQuestionId;", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'updatedQuestion' => $updatedQuestion
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function delete_question( WP_REST_Request $request ) : WP_REST_Response
{
    $deletedQuestionId = $request['questionId'];
    global $wpdb;
    global $questions_table_name;
    global $options_in_question_table_name;
    $dropQuery = "DELETE FROM $options_in_question_table_name WHERE `question_id` = $deletedQuestionId";
    $result = $wpdb->get_results($dropQuery);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    global $questions_in_test_table_name;
    $dropQuery = "DELETE FROM $questions_in_test_table_name WHERE `question_id` = $deletedQuestionId";
    $result = $wpdb->get_results($dropQuery);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $deletedQuestion = $wpdb->get_results("SELECT * FROM $questions_table_name WHERE `id` = $deletedQuestionId;", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $result = $wpdb->get_results("DELETE FROM $questions_table_name WHERE `id` = $deletedQuestionId;");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedQuestion' => $deletedQuestion
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function update_question_text(WP_REST_Request $request): WP_REST_Response
{
    $body = json_decode($request->get_body());
    $updatedQuestionId = $request['questionId'];
    $newText = $body->newText;
    global $wpdb;
    global $questions_table_name;
    $result = $wpdb->get_results("UPDATE $questions_table_name SET `text` = '$newText' WHERE `id` = '$updatedQuestionId';");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    };
    $updatedQuestion = $wpdb->get_results("SELECT * FROM $questions_table_name WHERE `id` = $updatedQuestionId", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    };
    $response = [
        'updatedQuestion' => $updatedQuestion,
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}

function create_questions_in_tests_table(): void
{
    global $wpdb;
    global $questions_in_test_table_name;
    global $questions_table_name;
    global $tests_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $questions_in_test_table_name(
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `question_id` INT NOT NULL,
        FOREIGN KEY (`question_id`) REFERENCES $questions_table_name(`id`),
        `test_id` INT NOT NULL,
        FOREIGN KEY (`test_id`) REFERENCES $tests_table_name(`id`),
        UNIQUE KEY `unique_key` (`question_id`, `test_id`)
    )";
    $wpdb->query($createQuery);
}
function drop_questions_in_tests_table(): void
{
    global $wpdb;
    global $questions_in_test_table_name;
    $dropQuery = "DROP TABLE $questions_in_test_table_name";
    $wpdb->query($dropQuery);
}

function get_all_questions_in_test( WP_REST_Request $request ) : WP_REST_Response
{
    $testId = $request['testId'];
    global $wpdb;
    global $questions_in_test_table_name;
    global $questions_table_name;
    $query = "SELECT `Questions`.*  FROM $questions_in_test_table_name AS `TestQuestions`
              INNER JOIN $questions_table_name AS `Questions`
              ON `Questions`.`id` = `TestQuestions`.`question_id`
              WHERE `TestQuestions`.`test_id` = $testId;";
    $questions = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'questions' => $questions
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function delete_question_in_test( WP_REST_Request $request ): WP_REST_Response
{
    $testId = $request['testId'];
    $questionId = $request['questionId'];
    global $wpdb;
    global $questions_in_test_table_name;
    $query = "DELETE FROM $questions_in_test_table_name WHERE `test_id` = $testId AND `question_id` = $questionId;";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedQuestionId' => $questionId
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function add_questions_in_test( WP_REST_Request $request ) : WP_REST_Response
{
    $testId = $request['testId'];
    $body = json_decode($request->get_body());
    $added_questions = $body->questions;
    global $wpdb;
    global $questions_in_test_table_name;
    $values = implode("," , array_map(function($question) use ($testId) { return "($question->id, $testId)"; }, $added_questions));
    $query = "INSERT INTO $questions_in_test_table_name (`question_id`, `test_id`) VALUES $values";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'addedQuestions' => $added_questions
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}

function create_questions_in_user_results_table(): void
{
    global $wpdb;
    global $questions_in_user_results_table_name;
    global $user_results_table_name;
    global $questions_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $questions_in_user_results_table_name(
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `user_result_id` INT NOT NULL,
        FOREIGN KEY (`user_result_id`) REFERENCES $user_results_table_name (`id`),
        `question_id` INT NOT NULL,
        FOREIGN KEY (`question_id`) REFERENCES $questions_table_name (`id`),
        UNIQUE KEY `unique_key` (`user_result_id`, `question_id`)
    );";
    $wpdb->query($createQuery);
}
function drop_questions_in_user_results_table(): void
{
    global $wpdb;
    global $questions_in_user_results_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $questions_in_user_results_table_name;";
    $wpdb->query($dropQuery);
}

function get_all_questions_in_user_results( WP_REST_Request $request ): WP_REST_Response {
    global $wpdb;
    global $user_results_table_name;
    global $questions_in_user_results_table_name;
    $userId = $request['userId'];
    $courseId = $request['courseId'];
    $query = "SELECT $questions_in_user_results_table_name.question_id FROM $user_results_table_name INNER JOIN $questions_in_user_results_table_name ON $questions_in_user_results_table_name.user_result_id = $user_results_table_name.id WHERE $user_results_table_name.user_id = $userId AND $user_results_table_name.course_id = $courseId;";
    $questionsId = $wpdb->get_results($query, ARRAY_N);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'questionsId' => $questionsId
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function add_question_in_user_results( WP_REST_Request $request ): WP_REST_Response {
    global $wpdb;
    global $questions_in_user_results_table_name;
    global $user_results_table_name;
    $question = json_decode($request->get_body())->question;
    $user_id = $request['userId'];
    $course_id = $request['courseId'];
    $user_result_id = $wpdb->get_results("SELECT $user_results_table_name.`id` FROM $user_results_table_name WHERE `user_id` = $user_id AND `course_id` = $course_id;", ARRAY_N)[0][0];
    $query = "INSERT INTO $questions_in_user_results_table_name (`user_result_id`, `question_id`) VALUES ($user_result_id, $question->id);";
    $wpdb->query($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'addedQuestion' => $question
    ];
    return new WP_REST_Response(json_encode($response), 200);
}