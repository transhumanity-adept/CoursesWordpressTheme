<?php

function create_competences_table(): void
{
    global $wpdb;
    global $competences_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $competences_table_name (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL UNIQUE,
        PRIMARY KEY (`id`)
    );";
    $wpdb->query($createQuery);
}
function drop_competences_table(): void
{
    global $wpdb;
    global $competences_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $competences_table_name";
    $wpdb->query($dropQuery);
}

function get_all_competences() : WP_REST_Response
{
    global $competences_table_name;
    global $wpdb;
    $query = "SELECT * FROM $competences_table_name";
    $competences = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'competences' => $competences
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function create_new_competence ( WP_REST_Request $request ) : WP_REST_Response
{
    $body = json_decode($request->get_body());
    $newCompetence = $body->newCompetence;
    global $wpdb;
    global $competences_table_name;
    $result = $wpdb->get_results("INSERT INTO $competences_table_name (`name`) VALUES ('$newCompetence->name');");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $createdCompetence = $wpdb->get_results("SELECT * FROM $competences_table_name WHERE `name` = '$newCompetence->name';", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'createdCompetence' => $createdCompetence
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function update_competence( WP_REST_Request $request ): WP_REST_Response
{
    $updateCompetenceId = $request['competenceId'];
    $body = json_decode($request->get_body());
    $newCompetence = $body->newCompetence;
    global $wpdb;
    global $competences_table_name;
    $result = $wpdb->get_results("UPDATE $competences_table_name SET `name` = '$newCompetence->name' WHERE `id` = $updateCompetenceId;");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $updatedCompetence = $wpdb->get_results("SELECT * FROM $competences_table_name WHERE `id` = $updateCompetenceId;", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'updatedCompetence' => $updatedCompetence
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function delete_competence( WP_REST_Request $request ) : WP_REST_Response
{
    $deletedCompetenceId = $request['competenceId'];
    global $wpdb;
    global $competences_table_name;
    global $concepts_in_competences_table_name;
    $dropQuery = "DELETE FROM $concepts_in_competences_table_name WHERE `competence_id` = $deletedCompetenceId";
    $result = $wpdb->get_results($dropQuery);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $deletedCompetence = $wpdb->get_results("SELECT * FROM $competences_table_name WHERE `id` = $deletedCompetenceId;", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $result = $wpdb->get_results("DELETE FROM $competences_table_name WHERE `id` = $deletedCompetenceId;");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedCompetence' => $deletedCompetence
    ];
    return new WP_REST_Response(json_encode($response), 200);
}

function create_competences_in_questions_table(): void
{
    global $wpdb;
    global $competences_in_questions_table_name;
    global $competences_table_name;
    global $questions_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $competences_in_questions_table_name(
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `question_id` INT NOT NULL,
        FOREIGN KEY (`question_id`) REFERENCES $questions_table_name (`id`),
        `competence_id` INT NOT NULL,
        FOREIGN KEY (`competence_id`) REFERENCES $competences_table_name (`id`),
        UNIQUE KEY `unique_key` (`question_id`, `competence_id`)
    );";
    $wpdb->query($createQuery);
}
function drop_competences_in_questions_table(): void
{
    global $wpdb;
    global $competences_in_questions_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $competences_in_questions_table_name;";
    $wpdb->query($dropQuery);
}

function get_all_competences_in_question( WP_REST_Request $request ) : WP_REST_Response
{
    $questionId = $request['questionId'];
    global $wpdb;
    global $competences_in_questions_table_name;
    global $competences_table_name;
    $query = "SELECT `Competences`.* FROM $competences_in_questions_table_name AS `QuestionCompetences`
              INNER JOIN $competences_table_name AS `Competences`
              ON `Competences`.`id` = `QuestionCompetences`.`competence_id`
              WHERE `QuestionCompetences`.`question_id` = $questionId;";
    $competences = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'competences' => $competences
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function delete_competence_in_question( WP_REST_Request $request ): WP_REST_Response
{
    $questionId = $request['questionId'];
    $competenceId = $request['competenceId'];
    global $wpdb;
    global $competences_in_questions_table_name;
    $query = "DELETE FROM $competences_in_questions_table_name WHERE `question_id` = $questionId AND `competence_id` = $competenceId;";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedCompetenceId' => $competenceId
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function add_competences_in_question ( WP_REST_Request $request ) : WP_REST_Response
{
    $questionId = $request['questionId'];
    $body = json_decode($request->get_body());
    $added_competences = $body->competences;
    global $wpdb;
    global $competences_in_questions_table_name;
    $values = implode("," , array_map(function($competence) use ($questionId) { return "($questionId, $competence->id)"; }, $added_competences));
    $query = "INSERT INTO $competences_in_questions_table_name (`question_id`, `competence_id`) VALUES $values";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'addedCompetences' => $added_competences
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}

function create_competences_in_themes_table(): void
{
    global $wpdb;
    global $competences_in_themes_table_name;
    global $competences_table_name;
    global $themes_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $competences_in_themes_table_name(
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `theme_id` INT NOT NULL,
        FOREIGN KEY (`theme_id`) REFERENCES $themes_table_name (`id`),
        `competence_id` INT NOT NULL,
        FOREIGN KEY (`competence_id`) REFERENCES $competences_table_name (`id`),
        UNIQUE KEY `unique_key` (`theme_id`, `competence_id`)
    );";
    $wpdb->query($createQuery);
}
function drop_competences_in_themes_table(): void
{
    global $wpdb;
    global $competences_in_themes_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $competences_in_themes_table_name;";
    $wpdb->query($dropQuery);
}

function get_all_competences_in_theme( WP_REST_Request $request ) : WP_REST_Response
{
    $themeId = $request['themeId'];
    global $wpdb;
    global $competences_in_themes_table_name;
    global $competences_table_name;
    $query = "SELECT `Competences`.* FROM $competences_in_themes_table_name AS `ThemeCompetences`
              INNER JOIN $competences_table_name AS `Competences`
              ON `Competences`.`id` = `ThemeCompetences`.`competence_id`
              WHERE `ThemeCompetences`.`theme_id` = $themeId;";
    $competences = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'competences' => $competences
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function delete_competence_in_theme( WP_REST_Request $request ): WP_REST_Response
{
    $themeId = $request['themeId'];
    $competenceId = $request['competenceId'];
    global $wpdb;
    global $competences_in_themes_table_name;
    $query = "DELETE FROM $competences_in_themes_table_name WHERE `theme_id` = $themeId AND `competence_id` = $competenceId;";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedCompetenceId' => $competenceId
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function add_competences_in_theme ( WP_REST_Request $request ) : WP_REST_Response
{
    $themeId = $request['themeId'];
    $body = json_decode($request->get_body());
    $added_competences = $body->competences;
    global $wpdb;
    global $competences_in_themes_table_name;
    $values = implode("," , array_map(function($competence) use ($themeId) { return "($themeId, $competence->id)"; }, $added_competences));
    $query = "INSERT INTO $competences_in_themes_table_name (`theme_id`, `competence_id`) VALUES $values";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'addedCompetences' => $added_competences
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}