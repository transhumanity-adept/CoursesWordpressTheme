<?php

function create_options_table(): void
{
    global $wpdb;
    global $options_table_name;
    $currentTime = time();
    $defaultJSON = array(
        'time' => $currentTime,
        'blocks' => array(),
        'version' => '2.24.3'
    );
    $defaultJSONString = json_encode($defaultJSON);
    $createQuery = "CREATE TABLE IF NOT EXISTS $options_table_name(
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `value` VARCHAR(8000) NOT NULL DEFAULT '$defaultJSONString'
    )";
    $wpdb->query($createQuery);
}
function drop_options_table(): void
{
    global $wpdb;
    global $options_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $options_table_name";
    $wpdb->query($dropQuery);
}

function get_all_options ( WP_REST_Request $request ) : WP_REST_Response
{
    global $options_table_name;
    global $wpdb;
    $query = "SELECT * FROM $options_table_name";
    $options = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'options' => $options
    ];
    return  new WP_REST_Response(json_encode($response), 200);
}
function create_new_option ( WP_REST_Request $request ) : WP_REST_Response
{
    global $wpdb;
    global $options_table_name;
    $result = $wpdb->insert($options_table_name, array(
        'id' => null,
    ));
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $createdOption = $wpdb->get_results("SELECT * FROM $options_table_name WHERE `id` = $wpdb->insert_id;", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'createdOption' => $createdOption
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function delete_myoption( WP_REST_Request $request ) : WP_REST_Response
{
    $deletedOptionId = $request['optionId'];
    global $wpdb;
    global $options_table_name;
    global $options_in_question_table_name;
    $dropQuery = "DELETE FROM $options_in_question_table_name WHERE `option_id` = $deletedOptionId";
    $result = $wpdb->get_results($dropQuery);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $deletedOption = $wpdb->get_results("SELECT * FROM $options_table_name WHERE `id` = $deletedOptionId;", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $result = $wpdb->get_results("DELETE FROM $options_table_name WHERE `id` = $deletedOptionId;");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedOption' => $deletedOption
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function update_option_value(WP_REST_Request $request): WP_REST_Response
{
    $body = json_decode($request->get_body());
    $updatedOptionId = $request['optionId'];
    $newValue = $body->newValue;
    global $wpdb;
    global $options_table_name;
    $result = $wpdb->get_results("UPDATE $options_table_name SET `value` = '$newValue' WHERE `id` = '$updatedOptionId';");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    };
    $updatedOption = $wpdb->get_results("SELECT * FROM $options_table_name WHERE `id` = $updatedOptionId", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    };
    $response = [
        'updatedOption' => $updatedOption,
    ];
    return new WP_REST_Response(json_encode($response), 200);
}

function create_options_in_questions_table(): void
{
    global $wpdb;
    global $options_in_question_table_name;
    global $questions_table_name;
    global $options_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $options_in_question_table_name (
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `question_id` INT NOT NULL,
        FOREIGN KEY (`question_id`) REFERENCES $questions_table_name(`id`),
        `option_id` INT NOT NULL,
        FOREIGN KEY (`option_id`) REFERENCES $options_table_name(`id`),
        `is_right_answer` TINYINT(1) NOT NULL DEFAULT 0,
        UNIQUE KEY `unique_key` (`question_id`, `option_id`, `is_right_answer`)
    )";
    $wpdb->query($createQuery);
}
function drop_options_in_questions_table(): void
{
    global $wpdb;
    global $options_in_question_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $options_in_question_table_name";
    $wpdb->query($dropQuery);
}

function get_all_options_in_question( WP_REST_Request $request ) : WP_REST_Response
{
    $questionId = $request['questionId'];
    global $wpdb;
    global $options_in_question_table_name;
    global $options_table_name;
    $query = "SELECT `Options`.*, `QuestionOptions`.`is_right_answer`   FROM $options_in_question_table_name AS `QuestionOptions`
              INNER JOIN $options_table_name AS `Options`
              ON `Options`.`id` = `QuestionOptions`.`option_id`
              WHERE `QuestionOptions`.`question_id` = $questionId;";
    $options = $wpdb->get_results($query, ARRAY_A);
    $newOptions = array_map(function($option)
    {
        $option['isRightAnswer'] = $option['is_right_answer'] == '1';
        array_splice($option, 2, 1);
        return $option;
    }, $options);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'options' => $newOptions
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function delete_option_in_question( WP_REST_Request $request ): WP_REST_Response
{
    $questionId = $request['questionId'];
    $optionId = $request['optionId'];
    global $wpdb;
    global $options_in_question_table_name;
    $query = "DELETE FROM $options_in_question_table_name WHERE `question_id` = $questionId AND `option_id` = $optionId;";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedOptionId' => $optionId
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function add_options_in_question ( WP_REST_Request $request ) : WP_REST_Response
{
    $questionId = $request['questionId'];
    $body = json_decode($request->get_body());
    $added_options = $body->options;
    global $wpdb;
    global $options_in_question_table_name;
    $values = implode("," , array_map(function($option) use ($questionId) { return "($questionId, $option->id)"; }, $added_options));
    $query = "INSERT INTO $options_in_question_table_name (`question_id`, `option_id`) VALUES $values";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'addedOptions' => $added_options
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function update_question_option_isright( WP_REST_Request $request) : WP_REST_Response {
    $questionId = $request['questionId'];
    $optionId = $request['optionId'];
    $newRight = json_decode($request->get_body())->newRight;
    $newRightString = $newRight ? '1' : '0';
    global $wpdb;
    global $options_in_question_table_name;
    $updateQuery = "UPDATE $options_in_question_table_name SET `is_right_answer` = '$newRightString' WHERE `question_id` = $questionId AND `option_id` = $optionId;";
    $result = $wpdb->get_results($updateQuery);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'updatedRight' => $newRight
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}