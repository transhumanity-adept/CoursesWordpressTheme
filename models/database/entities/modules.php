<?php

function create_modules_table(): void
{
    global $wpdb;
    global $modules_table_name;
    $currentTime = time();
    $defaultJSON = array(
        'time' => $currentTime,
        'blocks' => array(),
        'version' => '2.24.3'
    );
    $defaultJSONString = json_encode($defaultJSON);
    $createQuery = "CREATE TABLE IF NOT EXISTS $modules_table_name (
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `name` VARCHAR(255) NOT NULL UNIQUE,
        `description` VARCHAR(8000) NOT NULL DEFAULT '$defaultJSONString'
    );";
    $wpdb->query($createQuery);
}
function drop_modules_table(): void
{
    global $wpdb;
    global $modules_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $modules_table_name;";
    $wpdb->query($dropQuery);
}

function create_modules_in_courses_table(): void
{
    global $wpdb;
    global $modules_in_courses_table_name;
    global $modules_table_name;
    global $courses_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $modules_in_courses_table_name (
        `id` INT NOT NULL AUTO_INCREMENT,
        `module_id` INT NOT NULL,
        FOREIGN KEY (`module_id`) REFERENCES $modules_table_name(`id`),
        `course_id` INT NOT NULL,
        FOREIGN KEY (`course_id`) REFERENCES $courses_table_name(`id`),
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_key_four` (`module_id`, `course_id`)
    );";
    $wpdb->query($createQuery);
}
function drop_modules_in_courses_table(): void
{
    global $wpdb;
    global $modules_in_courses_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $modules_in_courses_table_name";
    $wpdb->query($dropQuery);
}

function get_all_modules ( WP_REST_Request $request ) : WP_REST_Response
{
    global $modules_table_name;
    global $wpdb;
    $query = "SELECT * FROM $modules_table_name";
    $modules = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
      'modules' => $modules
    ];
    return  new WP_REST_Response(json_encode($response), 200);
}
function get_modules_weight( WP_REST_Request $request): WP_REST_Response {
    global $wpdb;
    global $concepts_table_name;
    global $concepts_in_theories_table_name;
    global $theories_table_name;
    global $theories_in_themes_table_name;
    global $themes_table_name;
    global $themes_in_modules_table_name;
    global $modules_table_name;
    $query = "SELECT SUM(`ThemesInfo`.`SumThemesWeight`) as `ModuleWeight`, `Modules`.`id` as `ModuleId` 
    FROM $modules_table_name as `Modules`
    INNER JOIN $themes_in_modules_table_name as `ThemesModules`
    ON `ThemesModules`.`module_id` = `Modules`.`id`
    INNER JOIN(
        SELECT SUM(`TheoriesInfo`.`SumTheoriesWeight`) as `SumThemesWeight`, `Themes`.`id` as `ThemeId`  
        FROM $themes_table_name as `Themes`
        INNER JOIN $theories_in_themes_table_name as `TheoriesThemes`
        ON `TheoriesThemes`.`theme_id` = `Themes`.`id`
        INNER JOIN(
            SELECT SUM(`ConceptsInfo`.`SumConceptsWeight`) as `SumTheoriesWeight`, `Theories`.`id` as `TheoryId` 
            FROM $theories_table_name as `Theories`
            INNER JOIN $concepts_in_theories_table_name as `ConceptsTheories`
            ON `ConceptsTheories`.`theory_id` = `Theories`.`id`
                INNER JOIN (SELECT SUM(`Concepts`.`weight`) as `SumConceptsWeight`, `Concepts`.`id` as `ConceptId` 
                FROM $concepts_table_name as `Concepts`
                GROUP BY `Concepts`.`id`) as `ConceptsInfo`
            ON `ConceptsInfo`.`ConceptId` = `ConceptsTheories`.`concept_id`
            GROUP BY `Theories`.`id`) as `TheoriesInfo`
            ON `TheoriesInfo`.`TheoryId` = `TheoriesThemes`.`theory_id`
        GROUP BY `Themes`.`id`) as `ThemesInfo`
        ON `ThemesInfo`.`ThemeId` = `ThemesModules`.`theme_id`
    GROUP BY `Modules`.`id`;";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'ModulesWeights' => $result
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function create_new_module ( WP_REST_Request $request ) : WP_REST_Response
{
    $body = json_decode($request->get_body());
    $newModuleName = $body->newModule->name;
    global $wpdb;
    global $modules_table_name;
    $result = $wpdb->get_results("INSERT INTO $modules_table_name (`name`) VALUES ('$newModuleName');");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $createdModule = $wpdb->get_results("SELECT * FROM $modules_table_name WHERE `name` = '$newModuleName';", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'createdModule' => $createdModule
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function update_module( WP_REST_Request $request ): WP_REST_Response
{
    $updateModuleId = $request['moduleId'];
    $body = json_decode($request->get_body());
    $newModule = $body->newModule;
    global $wpdb;
    global $modules_table_name;
    $result = $wpdb->get_results("UPDATE $modules_table_name SET `name` = '$newModule->name' WHERE `id` = $updateModuleId;");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $updatedModule = $wpdb->get_results("SELECT * FROM $modules_table_name WHERE `id` = $updateModuleId;", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
      'updatedModule' => $updatedModule
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function delete_module( WP_REST_Request $request ) : WP_REST_Response
{
    $deletedModuleId = $request['moduleId'];
    global $wpdb;
    global $modules_table_name;
    global $modules_in_courses_table_name;
    $result = $wpdb->get_results("DELETE FROM $modules_in_courses_table_name WHERE `module_id` = $deletedModuleId");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    global $themes_in_modules_table_name;
    $result = $wpdb->get_results("DELETE FROM $themes_in_modules_table_name WHERE `module_id` = $deletedModuleId");
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $deletedModule = $wpdb->get_results("SELECT * FROM $modules_table_name WHERE `id` = $deletedModuleId", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $result = $wpdb->get_results("DELETE FROM $modules_table_name WHERE `id` = $deletedModuleId");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
      'deletedModule' => $deletedModule
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function update_module_description(WP_REST_Request $request): WP_REST_Response
{
    $body = json_decode($request->get_body());
    $updatedModuleId = $request['moduleId'];
    $newDescription = $body->newDescription;
    global $wpdb;
    global $modules_table_name;
    $result = $wpdb->get_results("UPDATE $modules_table_name SET `description` = '$newDescription' WHERE `id` = '$updatedModuleId';");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    };
    $updatedModule = $wpdb->get_results("SELECT * FROM $modules_table_name WHERE `id` = $updatedModuleId", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    };
    $response = [
        'updatedModule' => $updatedModule,
    ];
    return new WP_REST_Response(json_encode($response), 200);
}