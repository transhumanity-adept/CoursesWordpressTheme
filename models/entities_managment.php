<?php
global $lms_prefix;
global $theme_path;
$lms_prefix = 'wp_lms_bondarenko_';
require_once $theme_path . '/models/' . 'table_names.php';
require_once $theme_path . '/models/' . 'database/entities/competences.php';
require_once $theme_path . '/models/' . 'database/entities/concepts.php';
require_once $theme_path . '/models/' . 'database/entities/courses.php';
require_once $theme_path . '/models/' . 'database/entities/modules.php';
require_once $theme_path . '/models/' . 'database/entities/options.php';
require_once $theme_path . '/models/' . 'database/entities/questions.php';
require_once $theme_path . '/models/' . 'database/entities/tests.php';
require_once $theme_path . '/models/' . 'database/entities/themes.php';
require_once $theme_path . '/models/' . 'database/entities/theories.php';
require_once $theme_path . '/models/' . 'database/entities/user_results.php';

function create_cache( WP_REST_Request $request ): WP_REST_Response {
    global $wpdb;
    global $courses_table_name;
    global $courses_in_user_table_name;
    global $user_results_table_name;
    global $modules_table_name;
    global $modules_in_courses_table_name;
    global $themes_table_name;
    global $themes_in_modules_table_name;
    global $tests_table_name;
    global $tests_in_themes_table_name;
    global $tests_in_courses_table_name;
    global $questions_table_name;
    global $questions_in_test_table_name;
    global $questions_in_user_results_table_name;
    global $options_table_name;
    global $options_in_question_table_name;
    global $competences_table_name;
    global $competences_in_questions_table_name;
    global $competences_in_themes_table_name;
    global $theories_table_name;
    global $theories_in_themes_table_name;
    global $theories_in_user_results;
    global $concepts_table_name;
    global $concepts_in_theories_table_name;
    global $concepts_in_competences_table_name;
    global $concepts_in_themes_table_name;
    $filename = json_decode($request->get_body())->cacheFileName;
    $tables_array = [
        [
            'name' => $courses_table_name,
            'params' => [
                'id', 'name', 'description', 'image'
            ]
        ],
        [
            'name' => $courses_in_user_table_name,
            'params' => [
                'id', 'user_id', 'course_id'
            ]
        ],
        [
            'name' => $user_results_table_name,
            'params' => [
                'id', 'user_id', 'course_id'
            ]
        ],
        [
            'name' => $modules_table_name,
            'params' => [
                'id', 'name', 'description'
            ]
        ],
        [
            'name' => $modules_in_courses_table_name,
            'params' => [
                'id', 'module_id', 'course_id'
            ]
        ],
        [
            'name' => $themes_table_name,
            'params' => [
                'id', 'name'
            ]
        ],
        [
            'name' => $themes_in_modules_table_name,
            'params' => [
                'id', 'theme_id', 'module_id'
            ]
        ],
        [
            'name' => $tests_table_name,
            'params' => [
                'id', 'name', 'header'
            ]
        ],
        [
            'name' => $tests_in_themes_table_name,
            'params' => [
                'id', 'theme_id', 'test_id'
            ]
        ],
        [
            'name' => $tests_in_courses_table_name,
            'params' => [
                'id', 'course_id', 'test_id'
            ]
        ],
        [
            'name' => $questions_table_name,
            'params' => [
                'id', 'name', 'text'
            ]
        ],
        [
            'name' => $questions_in_test_table_name,
            'params' => [
                'id', 'question_id', 'test_id'
            ]
        ],
        [
            'name' => $questions_in_user_results_table_name,
            'params' => [
                'id', 'user_result_id', 'question_id'
            ]
        ],
        [
            'name' => $options_table_name,
            'params' => [
                'id', 'value'
            ]
        ],
        [
            'name' => $options_in_question_table_name,
            'params' => [
                'id', 'question_id', 'option_id', 'is_right_answer'
            ]
        ],
        [
            'name' => $competences_table_name,
            'params' => [
                'id', 'name'
            ]
        ],
        [
            'name' => $competences_in_questions_table_name,
            'params' => [
                'id', 'question_id', 'competence_id'
            ]
        ],
        [
            'name' => $competences_in_themes_table_name,
            'params' => [
                'id', 'theme_id', 'competence_id'
            ]
        ],
        [
            'name' => $theories_table_name,
            'params' => [
                'id', 'name', 'content'
            ]
        ],
        [
            'name' => $theories_in_themes_table_name,
            'params' => [
                'id', 'theme_id', 'theory_id'
            ]
        ],
        [
            'name' => $theories_in_user_results,
            'params' => [
                'id', 'user_results_id', 'theory_id'
            ]
        ],
        [
            'name' => $concepts_table_name,
            'params' => [
                'id', 'name', 'weight'
            ]
        ],
        [
            'name' => $concepts_in_theories_table_name,
            'params' => [
                'id', 'theory_id', 'concept_id'
            ]
        ],
        [
            'name' => $concepts_in_competences_table_name,
            'params' => [
                'id', 'competence_id', 'required_concept_id'
            ]
        ],
        [
            'name' => $concepts_in_themes_table_name,
            'params' => [
                'id', 'theme_id', 'concept_id'
            ]
        ]
    ];
    $tables_content_array = array_map(function ($table) use ($wpdb) {
        $table_name = $table['name'];
        $select_params = implode(', ', array_map(function ($param) use ($table, $table_name) { return "'$param', $table_name.$param"; }, $table['params']));
        $query = "SELECT JSON_OBJECT($select_params) FROM $table_name";
        $table_string = "\t\"$table_name\": [\n\t\t";
        $table_content = $wpdb->get_results($query, ARRAY_N);
        $table_content = implode(",\n\t\t", array_map(function ($array) { return $array[0]; }, $table_content));
        return $table_string . $table_content . "\n\t]";
    }, $tables_array);
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    }
    $cacheJSON = "{\n" . implode(",\n", $tables_content_array) . "\n}";
    $file = fopen($_SERVER['DOCUMENT_ROOT']."/wp-content/themes/OnlineEducation/cache/$filename",'w');
    if (!$file) {
        return new WP_REST_Response(array(
            'message' => 'failed file open or create'
        ), 400);
    }
    $write_success = fwrite($file, $cacheJSON);
    if (!$write_success) {
        return new WP_REST_Response(array(
            'message' => 'failed write in file'
        ), 400);
    }
    fclose($file);
    $response = [
        'filename' => $filename
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function delete_cache( WP_REST_Request $request ): WP_REST_Response {
    $filename = json_decode($request->get_body())->cacheFileName;
    $unlicked =  unlink($_SERVER['DOCUMENT_ROOT']."/wp-content/themes/OnlineEducation/cache/$filename");
    if (!$unlicked) {
        return new WP_REST_Response(array(
            'message' => 'failed detete file'
        ), 400);
    };
    $response = [
        'deletedFileName' => $filename
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}
function load_cache( WP_REST_Request $request ): WP_REST_Response {
    global $wpdb;
    $filename = json_decode($request->get_body())->cacheFileName;
    $json = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."/wp-content/themes/OnlineEducation/cache/$filename"));
    remove_entities();
    create_entities();
    foreach ($json as $table_name => $table_content) {
        foreach ($table_content as $content) {
            $content_array = (array) $content;
            $wpdb->insert($table_name, $content_array);
        }
    }
    if ($wpdb->last_error) {
        return new WP_REST_Response(array(
            'message' => $wpdb->last_error
        ), 400);
    };
    return new WP_REST_Response(status: 200);
}

function create_entities(): void
{
    create_courses_table();
    create_courses_in_user_table();
    create_user_results_table();
    create_modules_table();
    create_modules_in_courses_table();
    create_themes_table();
    create_themes_in_modules_table();
    create_tests_table();
    create_tests_in_themes_table();
    create_tests_in_courses_table();
    create_questions_table();
    create_questions_in_tests_table();
    create_questions_in_user_results_table();
    create_options_table();
    create_options_in_questions_table();
    create_competences_table();
    create_competences_in_questions_table();
    create_competences_in_themes_table();
    create_theories_table();
    create_theories_in_themes_table();
    create_theories_in_user_results();
    create_concepts_table();
    create_concepts_in_theory_table();
    create_concepts_in_competences_table();
    create_concepts_in_theme_table();
}
function remove_entities(): void
{
    drop_concepts_in_theme_table();
    drop_concepts_in_competences_table();
    drop_concepts_in_theory_table();
    drop_concepts_table();
    drop_theories_in_user_results_table();
    drop_theories_in_themes_table();
    drop_theories_table();
    drop_competences_in_themes_table();
    drop_competences_in_questions_table();
    drop_competences_table();
    drop_options_in_questions_table();
    drop_options_table();
    drop_questions_in_user_results_table();
    drop_questions_in_tests_table();
    drop_questions_table();
    drop_tests_in_courses_table();
    drop_tests_in_themes_table();
    drop_tests_table();
    drop_themes_in_modules_table();
    drop_themes_table();
    drop_modules_in_courses_table();
    drop_modules_table();
    drop_user_results_table();
    drop_courses_in_user_table();
    drop_courses_table();
}