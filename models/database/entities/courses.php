<?php

function create_courses_table(): void
{
    global $wpdb;
    global $courses_table_name;
    $currentTime = time();
    $defaultJSON = array(
        'time' => $currentTime,
        'blocks' => array(),
        'version' => '2.24.3'
    );
    $defaultJSONString = json_encode($defaultJSON);
    $createQuery = "CREATE TABLE IF NOT EXISTS $courses_table_name (
        `id` INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`),
        `name` VARCHAR(255) NOT NULL UNIQUE,
        `description` VARCHAR(8000) NOT NULL DEFAULT '$defaultJSONString',
        `image` VARCHAR(500)
    );";
    $wpdb->query($createQuery);
}

function drop_courses_table(): void
{
    global $wpdb;
    global $courses_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $courses_table_name";
    $wpdb->query($dropQuery);
}

function create_courses_in_user_table(): void
{
    global $wpdb;
    global $courses_in_user_table_name;
    global $courses_table_name;
    $users_table_name = 'wp_users';
    $createQuery = "CREATE TABLE IF NOT EXISTS $courses_in_user_table_name (
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

function drop_courses_in_user_table(): void
{
    global $wpdb;
    global $courses_in_user_table_name;
    $dropQuery = "DROP TABLE IF EXISTS $courses_in_user_table_name";
    $wpdb->query($dropQuery);
}

function get_user_courses( WP_REST_Request $request ): WP_REST_Response {
    $userId = $request['userId'];
    global $wpdb;
    global $courses_in_user_table_name;
    global $courses_table_name;
    $query = "SELECT `Courses`.* FROM $courses_in_user_table_name as `UsersCourses`
        INNER JOIN $courses_table_name as `Courses`
        ON `Courses`.`id` = `UsersCourses`.`course_id`
        WHERE `UsersCourses`.`user_id` = $userId;";
    $courses = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'courses' => $courses
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function get_user_courses_with_completed( WP_REST_Request $request ): WP_REST_Response {
    $userId = $request['userId'];
    global $wpdb;
    global $courses_in_user_table_name;
    global $courses_table_name;
    global $modules_in_courses_table_name;
    global $modules_table_name;
    global $themes_in_modules_table_name;
    global $themes_table_name;
    global $theories_in_themes_table_name;
    global $theories_table_name;
    global $user_results_table_name;
    global $theories_in_user_results;
    global $tests_in_themes_table_name;
    global $tests_table_name;
    global $questions_in_test_table_name;
    global $questions_table_name;
    global $questions_in_user_results_table_name;
    $query = "SELECT `Courses`.*, (SELECT COUNT(`Theories`.id) = 0 as `TheoriesCompleted`
    FROM $modules_in_courses_table_name as `CoursesModules`
    INNER JOIN $modules_table_name as `Modules`
    ON `Modules`.id = `CoursesModules`.`module_id`
    INNER JOIN $themes_in_modules_table_name as `ModulesThemes`
    ON `ModulesThemes`.`module_id` = `Modules`.`id`
    INNER JOIN $themes_table_name as `Themes`
    ON `Themes`.id = `ModulesThemes`.`theme_id`
    INNER JOIN $theories_in_themes_table_name as `ThemesTheories`
    ON `ThemesTheories`.`theme_id` = `Themes`.`id`
    INNER JOIN $theories_table_name as `Theories`
    ON `Theories`.id = `ThemesTheories`.`theory_id` AND `Theories`.id 
    NOT IN (SELECT `ResultTheories`.`theory_id` FROM $user_results_table_name as `Results`
        INNER JOIN $theories_in_user_results as `ResultTheories`
        ON `ResultTheories`.`user_results_id` = `Results`.`id`
        WHERE `Results`.`user_id` = `UserCourses`.`user_id` AND `Results`.`course_id` = `Courses`.id)
    WHERE `CoursesModules`.`course_id` = `Courses`.id) = 1 AND (SELECT COUNT(`Questions`.id) = 0 as `QuestionsCompleted`
    FROM $modules_in_courses_table_name as `CoursesModules`
    INNER JOIN $modules_table_name as `Modules`
    ON `Modules`.id = `CoursesModules`.`module_id`
    INNER JOIN $themes_in_modules_table_name as `ModulesThemes`
    ON `ModulesThemes`.`module_id` = `Modules`.`id`
    INNER JOIN $themes_table_name as `Themes`
    ON `Themes`.id = `ModulesThemes`.`theme_id`
    INNER JOIN $tests_in_themes_table_name as `ThemesTests`
    ON `ThemesTests`.`theme_id` = `Themes`.`id`
    INNER JOIN $tests_table_name as `Tests`
    ON `Tests`.id = `ThemesTests`.`test_id`
    INNER JOIN $questions_in_test_table_name as `TestsQuestions`
    ON `TestsQuestions`.`test_id` = `Tests`.id
    INNER JOIN $questions_table_name as `Questions`
    ON `Questions`.id = `TestsQuestions`.`question_id` AND `Questions`.id 
    NOT IN (SELECT `ResultQuestions`.`question_id` FROM $user_results_table_name as `Results`
        INNER JOIN $questions_in_user_results_table_name as `ResultQuestions`
        ON `ResultQuestions`.`user_result_id` = `Results`.`id`
        WHERE `Results`.`user_id` = `UserCourses`.`user_id` AND `Results`.`course_id` = `Courses`.id)
    WHERE `CoursesModules`.`course_id` = `Courses`.id) = 1 as `course_completed`
FROM $courses_in_user_table_name as `UserCourses`
INNER JOIN $courses_table_name as `Courses`
ON `Courses`.`id` = `UserCourses`.`course_id`
WHERE `UserCourses`.`user_id` = $userId;";
    $courses = $wpdb->get_results($query, ARRAY_A);
    $newCourses = array_map(function($course)
    {
        $course['completed'] = $course['course_completed'] == "1";
        array_splice($course, 4, 1);
        return $course;
    }, $courses);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'courses' => $newCourses
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function join_user_in_course ( WP_REST_Request $request ): WP_REST_Response {
    $userId = $request['userId'];
    $courseId = $request['courseId'];
    global $wpdb;
    global $courses_in_user_table_name;
    $query = "INSERT INTO $courses_in_user_table_name (`course_id`, `user_id`) VALUES ($courseId, $userId);";
    $result = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    global $user_results_table_name;
    $query = "INSERT INTO $user_results_table_name (`course_id`, `user_id`) VALUES ($courseId, $userId);";
    $result = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    return new WP_REST_Response(status: 200);
}
function get_all_courses(): array|object|null
{
    global $wpdb;
    global $courses_table_name;
    $tests = $wpdb->get_results("SELECT * FROM $courses_table_name", ARRAY_A);
    return $tests;
}
function get_courses_weight( WP_REST_Request $request ): WP_REST_Response {
    global $wpdb;
    global $concepts_table_name;
    global $concepts_in_theories_table_name;
    global $theories_table_name;
    global $theories_in_themes_table_name;
    global $themes_table_name;
    global $themes_in_modules_table_name;
    global $modules_table_name;
    global $modules_in_courses_table_name;
    global $courses_table_name;
    $query = "SELECT SUM(`ModulesInfo`.`SumModulesWeight`) as `CourseWeight`, `Courses`.`id` as `CourseId`  FROM $courses_table_name as `Courses`
INNER JOIN $modules_in_courses_table_name as `ModulesCourses`
ON `ModulesCourses`.`course_id` = `Courses`.`id`
INNER JOIN(
	SELECT SUM(`ThemesInfo`.`SumThemesWeight`) as `SumModulesWeight`, `Modules`.`id` as `ModuleId` 
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
    GROUP BY `Modules`.`id`) as `ModulesInfo`
    ON `ModulesInfo`.`ModuleId` = `ModulesCourses`.`module_id`
GROUP BY `Courses`.`id`;";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'coursesWeights' => $result
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function get_course_by_id( WP_REST_Request $request ): WP_REST_Response {
    global $wpdb;
    global $courses_table_name;
    $courseId = $request['courseId'];
    $query = "SELECT * FROM $courses_table_name WHERE `id` = $courseId";
    $course = $wpdb->get_results($query)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'course' => $course
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function create_course(WP_REST_Request $request): WP_REST_Response
{
    $body = json_decode($request->get_body());
    $newCourse = $body->newCourse;
    global $wpdb;
    global $courses_table_name;
    $wpdb->get_results("INSERT INTO $courses_table_name (`name`) VALUES ('$newCourse->name');");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $createdCourses = $wpdb->get_results("SELECT * FROM $courses_table_name WHERE `name` = '$newCourse->name';");
    $response = [
        'createdCourse' => $createdCourses[0]
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function update_course(WP_REST_Request $request): WP_REST_Response
{
    $body = json_decode($request->get_body());
    $updatedCourseId = $request['courseId'];
    $newCourse = $body->newCourse;
    global $wpdb;
    global $courses_table_name;
    $result = $wpdb->get_results("UPDATE $courses_table_name SET `name` = '$newCourse->name' WHERE `id` = '$updatedCourseId';");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'updatedCourse' => $newCourse,
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function delete_course(WP_REST_Request $request): WP_REST_Response
{
    $deletedCourseId = $request['courseId'];
    global $wpdb;
    global $courses_table_name;
    global $modules_in_courses_table_name;
    $query = "DELETE FROM $modules_in_courses_table_name WHERE `course_id` = $deletedCourseId;";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $deletedCourse = $wpdb->get_results("SELECT * FROM $courses_table_name WHERE `id` = $deletedCourseId;", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $result = $wpdb->get_results("DELETE FROM $courses_table_name WHERE `id` = $deletedCourseId;");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = array(
        'deletedCourse' => $deletedCourse
    );
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function get_all_course_modules(WP_REST_Request $request): WP_REST_Response
{
    $courseId = $request['courseId'];
    global $wpdb;
    global $modules_in_courses_table_name;
    global $modules_table_name;
    $query = "SELECT `Modules`.*  FROM $modules_in_courses_table_name AS `ModulesCourses`
              INNER JOIN $modules_table_name AS `Modules`
              ON `Modules`.`id` = `ModulesCourses`.`module_id`
              WHERE `ModulesCourses`.`course_id` = '$courseId';";
    $modules = $wpdb->get_results($query, ARRAY_A);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'modules' => $modules
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function delete_module_in_course(WP_REST_Request $request): WP_REST_Response
{
    $courseId = $request['courseId'];
    $moduleId = $request['moduleId'];
    global $wpdb;
    global $modules_in_courses_table_name;
    $query = "DELETE FROM $modules_in_courses_table_name WHERE `module_id` = '$moduleId' AND `course_id` = '$courseId';";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'deletedModuleId' => $moduleId
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function add_course_modules(WP_REST_Request $request): WP_REST_Response
{
    $courseId = $request['courseId'];
    $added_modules = json_decode($request->get_body());
    global $wpdb;
    global $modules_in_courses_table_name;
    $values = implode(",", array_map(function ($module) use ($courseId) {
        return "('$module->id', '$courseId')";
    }, $added_modules));
    $query = "INSERT INTO $modules_in_courses_table_name (`module_id`, `course_id`) VALUES $values";
    $result = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $response = [
        'addedModules' => $added_modules
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function update_course_description(WP_REST_Request $request): WP_REST_Response
{
    $body = json_decode($request->get_body());
    $updatedCourseId = $request['courseId'];
    $newDescription = $body->newDescription;
    global $wpdb;
    global $courses_table_name;
    $result = $wpdb->get_results("UPDATE $courses_table_name SET `description` = '$newDescription' WHERE `id` = '$updatedCourseId';");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    };
    $updatedCourse = $wpdb->get_results("SELECT * FROM $courses_table_name WHERE `id` = $updatedCourseId", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    };
    $response = [
        'updatedCourse' => $updatedCourse,
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function update_course_image(WP_REST_Request $request): WP_REST_Response
{
    $body = json_decode($request->get_body());
    $updatedCourseId = $request['courseId'];
    $newUrl = $body->newUrl;
    global $wpdb;
    global $courses_table_name;
    $result = $wpdb->get_results("UPDATE $courses_table_name SET `image` = '$newUrl' WHERE `id` = '$updatedCourseId';");
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    };
    $updatedCourse = $wpdb->get_results("SELECT * FROM $courses_table_name WHERE `id` = $updatedCourseId", ARRAY_A)[0];
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    };
    $response = [
        'updatedCourse' => $updatedCourse,
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}