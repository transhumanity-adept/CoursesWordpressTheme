<?php

function create_concepts_table(): void
{
    global $wpdb;
    global $concepts_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $concepts_table_name (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL UNIQUE,
        `weight` FLOAT NOT NULL DEFAULT 0.00,
        PRIMARY KEY (`id`)
    );";
    $wpdb->query($createQuery);
}
function drop_concepts_table(): void
{
    global $wpdb;
    global $concepts_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $concepts_table_name";
    $wpdb->query($dropQuery);
}

function get_all_concepts() : WP_REST_Response
{
    global $concepts_table_name;
    global $wpdb;
    $query = "SELECT * FROM $concepts_table_name";
    $concepts = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'concepts' => $concepts
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function create_new_concept( WP_REST_Request $request ) : WP_REST_Response
{
    $body = json_decode($request->get_body());
    $newConcept = $body->newConcept;
    global $wpdb;
    global $concepts_table_name;
    $result = $wpdb->get_results("INSERT INTO $concepts_table_name (`name`, `weight`) VALUES ('$newConcept->name', '$newConcept->weight');");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $createdConcept = $wpdb->get_results("SELECT * FROM $concepts_table_name WHERE `name` = '$newConcept->name';", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'createdConcept' => $createdConcept
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function update_concept( WP_REST_Request $request ): WP_REST_Response
{
    $updateConceptId = $request['conceptId'];
    $body = json_decode($request->get_body());
    $newConcept = $body->newConcept;
    global $wpdb;
    global $concepts_table_name;
    $result = $wpdb->get_results("UPDATE $concepts_table_name SET `name` = '$newConcept->name', `weight` = $newConcept->weight WHERE `id` = $updateConceptId;");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $updatedConcept = $wpdb->get_results("SELECT * FROM $concepts_table_name WHERE `id` = $updateConceptId;", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'updatedConcept' => $updatedConcept
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function delete_concept( WP_REST_Request $request ) : WP_REST_Response
{
    $deletedConceptId = $request['conceptId'];
    global $wpdb;
    global $concepts_table_name;
    global $concepts_in_competences_table_name;
    $result = $wpdb->get_results("DELETE FROM $concepts_in_competences_table_name WHERE `required_concept_id` = $deletedConceptId;");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $deletedConcept = $wpdb->get_results("SELECT * FROM $concepts_table_name WHERE `id` = $deletedConceptId;", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $result = $wpdb->get_results("DELETE FROM $concepts_table_name WHERE `id` = $deletedConceptId;");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedConcept' => $deletedConcept
    ];
    return new WP_REST_Response(json_encode($response), 200);
}

function create_concepts_in_competences_table(): void
{
    global $wpdb;
    global $concepts_in_competences_table_name;
    global $concepts_table_name;
    global $competences_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $concepts_in_competences_table_name (
        `id` INT NOT NULL AUTO_INCREMENT,
        `competence_id` INT NOT NULL,
        FOREIGN KEY (`competence_id`) REFERENCES $competences_table_name(`id`),
        `required_concept_id` INT NOT NULL,
        FOREIGN KEY (`required_concept_id`) REFERENCES $concepts_table_name(`id`),
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_key` (`competence_id`, `required_concept_id`)
    );";
    $wpdb->query($createQuery);
}
function drop_concepts_in_competences_table(): void
{
    global $wpdb;
    global $concepts_in_competences_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $concepts_in_competences_table_name;";
    $wpdb->query($dropQuery);
}

function get_all_concepts_in_competence( WP_REST_Request $request ) : WP_REST_Response
{
    $competenceId = $request['competenceId'];
    global $wpdb;
    global $concepts_in_competences_table_name;
    global $concepts_table_name;
    $query = "SELECT `Concepts`.*  FROM $concepts_in_competences_table_name AS `CompetenceConcepts`
              INNER JOIN $concepts_table_name AS `Concepts`
              ON `Concepts`.`id` = `CompetenceConcepts`.`required_concept_id`
              WHERE `CompetenceConcepts`.`competence_id` = $competenceId;";
    $concepts = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'concepts' => $concepts
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function delete_concept_in_competence( WP_REST_Request $request ): WP_REST_Response
{
    $competenceId = $request['competenceId'];
    $conceptId = $request['conceptId'];
    global $wpdb;
    global $concepts_in_competences_table_name;
    $query = "DELETE FROM $concepts_in_competences_table_name WHERE `competence_id` = $competenceId AND `required_concept_id` = $conceptId;";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedConceptId' => $conceptId
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function add_concepts_in_competence ( WP_REST_Request $request ) : WP_REST_Response
{
    $competenceId = $request['competenceId'];
    $body = json_decode($request->get_body());
    $added_concepts = $body->concepts;
    global $wpdb;
    global $concepts_in_competences_table_name;
    $values = implode("," , array_map(function($concept) use ($competenceId) { return "($competenceId, $concept->id)"; }, $added_concepts));
    $query = "INSERT INTO $concepts_in_competences_table_name (`competence_id`, `required_concept_id`) VALUES $values";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'addedConcepts' => $added_concepts
    ];
    return new WP_REST_Response(json_encode($response), 200);
}

function create_concepts_in_theory_table(): void
{
    global $wpdb;
    global $concepts_in_theories_table_name;
    global $concepts_table_name;
    global $theories_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $concepts_in_theories_table_name(
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `theory_id` INT NOT NULL,
        FOREIGN KEY (`theory_id`) REFERENCES $theories_table_name (`id`),
        `concept_id` INT NOT NULL,
        FOREIGN KEY (`concept_id`) REFERENCES $concepts_table_name (`id`),
        UNIQUE KEY `unique_key` (`theory_id`, `concept_id`)
    );";
    $wpdb->query($createQuery);
}
function drop_concepts_in_theory_table(): void
{
    global $wpdb;
    global $concepts_in_theories_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $concepts_in_theories_table_name;";
    $wpdb->query($dropQuery);
}

function get_all_concepts_in_theory( WP_REST_Request $request ) : WP_REST_Response
{
    $theoryId = $request['theoryId'];
    global $wpdb;
    global $concepts_in_theories_table_name;
    global $concepts_table_name;
    $query = "SELECT `Concepts`.*  FROM $concepts_in_theories_table_name AS `TheoryConcepts`
              INNER JOIN $concepts_table_name AS `Concepts`
              ON `Concepts`.`id` = `TheoryConcepts`.`concept_id`
              WHERE `TheoryConcepts`.`theory_id` = $theoryId;";
    $concepts = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'concepts' => $concepts
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function delete_concept_in_theory( WP_REST_Request $request ): WP_REST_Response
{
    $theoryId = $request['theoryId'];
    $conceptId = $request['conceptId'];
    global $wpdb;
    global $concepts_in_theories_table_name;
    $query = "DELETE FROM $concepts_in_theories_table_name WHERE `theory_id` = $theoryId AND `concept_id` = $conceptId;";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedConceptId' => $conceptId
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function add_concepts_in_theory ( WP_REST_Request $request ) : WP_REST_Response
{
    $theoryId = $request['theoryId'];
    $body = json_decode($request->get_body());
    $added_concepts = $body->concepts;
    global $wpdb;
    global $concepts_in_theories_table_name;
    $values = implode("," , array_map(function($concept) use ($theoryId) { return "($theoryId, $concept->id)"; }, $added_concepts));
    $query = "INSERT INTO $concepts_in_theories_table_name (`theory_id`, `concept_id`) VALUES $values";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'addedConcepts' => $added_concepts
    ];
    return new WP_REST_Response(json_encode($response), 200);
}

function create_concepts_in_theme_table(): void {
    global $wpdb;
    global $concepts_in_themes_table_name;
    global $concepts_table_name;
    global $themes_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $concepts_in_themes_table_name(
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `theme_id` INT NOT NULL,
        FOREIGN KEY (`theme_id`) REFERENCES $themes_table_name (`id`),
        `concept_id` INT NOT NULL,
        FOREIGN KEY (`concept_id`) REFERENCES $concepts_table_name (`id`),
        UNIQUE KEY `unique_key` (`theme_id`, `concept_id`)
    );";
    $wpdb->query($createQuery);
}
function drop_concepts_in_theme_table(): void {
    global $wpdb;
    global $concepts_in_themes_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $concepts_in_themes_table_name;";
    $wpdb->query($dropQuery);
}

function get_all_concepts_in_theme( WP_REST_Request $request ) : WP_REST_Response
{
    $themeId = $request['themeId'];
    global $wpdb;
    global $concepts_in_themes_table_name;
    global $concepts_table_name;
    $query = "SELECT `Concepts`.*  FROM $concepts_in_themes_table_name AS `ThemeConcepts`
              INNER JOIN $concepts_table_name AS `Concepts`
              ON `Concepts`.`id` = `ThemeConcepts`.`concept_id`
              WHERE `ThemeConcepts`.`theme_id` = $themeId;";
    $concepts = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'concepts' => $concepts
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function delete_concept_in_theme( WP_REST_Request $request ): WP_REST_Response
{
    $themeId = $request['themeId'];
    $conceptId = $request['conceptId'];
    global $wpdb;
    global $concepts_in_themes_table_name;
    $query = "DELETE FROM $concepts_in_themes_table_name WHERE `theme_id` = $themeId AND `concept_id` = $conceptId;";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedConceptId' => $conceptId
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function add_concepts_in_theme ( WP_REST_Request $request ) : WP_REST_Response
{
    $themeId = $request['themeId'];
    $body = json_decode($request->get_body());
    $added_concepts = $body->concepts;
    global $wpdb;
    global $concepts_in_themes_table_name;
    $values = implode("," , array_map(function($concept) use ($themeId) { return "($themeId, $concept->id)"; }, $added_concepts));
    $query = "INSERT INTO $concepts_in_themes_table_name (`theme_id`, `concept_id`) VALUES $values";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'addedConcepts' => $added_concepts
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
