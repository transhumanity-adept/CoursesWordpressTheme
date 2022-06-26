<?php

function create_themes_table(): void
{
    global $wpdb;
    global $themes_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $themes_table_name (
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `name` VARCHAR(255) NOT NULL UNIQUE
    );";
    $wpdb->query($createQuery);
}
function drop_themes_table(): void
{
    global $wpdb;
    global $themes_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $themes_table_name";
    $wpdb->query($dropQuery);
}

function get_all_mythemes ( WP_REST_Request $request ) : WP_REST_Response
{
    global $themes_table_name;
    global $wpdb;
    $query = "SELECT * FROM $themes_table_name";
    $themes = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'themes' => $themes
    ];
    return  new WP_REST_Response(json_encode($response), 200);
}
function create_new_mytheme ( WP_REST_Request $request ) : WP_REST_Response
{
    $body = json_decode($request->get_body());
    $newTheme = $body->newTheme;
    global $wpdb;
    global $themes_table_name;
    $result = $wpdb->get_results("INSERT INTO $themes_table_name (`name`) VALUES ('$newTheme->name');");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $createdTheme = $wpdb->get_results("SELECT * FROM $themes_table_name WHERE `name` = '$newTheme->name';", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'createdTheme' => $createdTheme
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function update_mytheme( WP_REST_Request $request ): WP_REST_Response
{
    $updateThemeId = $request['themeId'];
    $body = json_decode($request->get_body());
    $newTheme = $body->newTheme;
    global $wpdb;
    global $themes_table_name;
    $result = $wpdb->get_results("UPDATE $themes_table_name SET `name` = '$newTheme->name' WHERE `id` = $updateThemeId;");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $updatedTheme = $wpdb->get_results("SELECT * FROM $themes_table_name WHERE `id` = $updateThemeId;", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'updatedTheme' => $updatedTheme
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function delete_mytheme( WP_REST_Request $request ) : WP_REST_Response
{
    $deletedThemeId = $request['themeId'];
    global $wpdb;
    global $themes_table_name;
    global $themes_in_modules_table_name;
    $result = $wpdb->get_results("DELETE FROM $themes_in_modules_table_name WHERE `theme_id` = $deletedThemeId");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    global $theories_in_themes_table_name;
    $result = $wpdb->get_results("DELETE FROM $theories_in_themes_table_name WHERE `theme_id` = $deletedThemeId");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    global $tests_in_themes_table_name;
    $result = $wpdb->get_results("DELETE FROM $tests_in_themes_table_name WHERE `theme_id` = $deletedThemeId");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    global $competences_in_themes_table_name;
    $result = $wpdb->get_results("DELETE FROM $competences_in_themes_table_name WHERE `theme_id` = $deletedThemeId");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    global $concepts_in_themes_table_name;
    $result = $wpdb->get_results("DELETE FROM $concepts_in_themes_table_name WHERE `theme_id` = $deletedThemeId");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $deletedTheme = $wpdb->get_results("SELECT * FROM $themes_table_name WHERE `id` = $deletedThemeId", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $result = $wpdb->get_results("DELETE FROM $themes_table_name WHERE `id` = $deletedThemeId");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedTheme' => $deletedTheme
    ];
    return new WP_REST_Response(json_encode($response), 200);
}

function create_themes_in_modules_table(): void
{
    global $wpdb;
    global $themes_in_modules_table_name;
    global $themes_table_name;
    global $modules_table_name;
    $createQuery = "CREATE TABLE IF NOT EXISTS $themes_in_modules_table_name(
        `id` INT NOT NULL AUTO_INCREMENT,
        `theme_id` INT NOT NULL,
        FOREIGN KEY (`theme_id`) REFERENCES $themes_table_name (`id`),
        `module_id` INT NOT NULL,
        FOREIGN KEY (`module_id`) REFERENCES $modules_table_name (`id`),
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_key` (`theme_id`, `module_id`)
    );";
    $wpdb->query($createQuery);
}
function drop_themes_in_modules_table(): void
{
    global $wpdb;
    global $themes_in_modules_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $themes_in_modules_table_name";
    $wpdb->query($dropQuery);
}

function get_all_themes_in_module( WP_REST_Request $request ) : WP_REST_Response
{
    $moduleId = $request['moduleId'];
    global $wpdb;
    global $themes_in_modules_table_name;
    global $themes_table_name;
    $query = "SELECT `Themes`.*  FROM $themes_in_modules_table_name AS `ModuleThemes`
              INNER JOIN $themes_table_name AS `Themes`
              ON `Themes`.`id` = `ModuleThemes`.`theme_id`
              WHERE `ModuleThemes`.`module_id` = $moduleId;";
    $themes = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'themes' => $themes
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function delete_theme_in_module( WP_REST_Request $request ): WP_REST_Response
{
    $moduleId = $request['moduleId'];
    $themeId = $request['themeId'];
    global $wpdb;
    global $themes_in_modules_table_name;
    $query = "DELETE FROM $themes_in_modules_table_name WHERE `module_id` = $moduleId AND `theme_id` = $themeId;";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return  new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedThemeId' => $themeId
    ];
    return new WP_REST_Response(json_encode($response), 200);
}
function add_themes_in_module ( WP_REST_Request $request ) : WP_REST_Response
{
    $moduleId = $request['moduleId'];
    $body = json_decode($request->get_body());
    $added_themes = $body->themes;
    global $wpdb;
    global $themes_in_modules_table_name;
    $values = implode("," , array_map(function($theme) use ($moduleId) { return "($theme->id, $moduleId)"; }, $added_themes));
    $query = "INSERT INTO $themes_in_modules_table_name (`theme_id`, `module_id`) VALUES $values";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'addedThemes' => $added_themes
    ];
    return new WP_REST_Response(json_encode($response), 200);
}