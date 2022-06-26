<?php
global $theme_path;
$theme_path = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/OnlineEducation';
add_filter( 'template_include', 'override_templates' );
function override_templates( $template ) {
    global $theme_path;
    if( is_page('profile') ){
        $template = $theme_path . '/page-profile.php';
    }
    if( is_page('course') ){
        $template = $theme_path . '/page-course.php';
    }
    return $template;
};

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/post.php';
require_once $theme_path . '/' . 'models/entities_managment.php';
require_once $theme_path . '/' . 'admin_menus/lms_settings_admin_menu.php';
add_filter('show_admin_bar', '__return_false');

function add_style_css(): void
{
    wp_enqueue_style('style-css', get_template_directory_uri() . '/style.css');
}
function add_vue(): void
{
    wp_enqueue_script('vue', get_template_directory_uri() . '/node_modules/vue/dist/vue.js');
}
function add_axios(): void
{
    wp_enqueue_script('axios', get_template_directory_uri() . '/node_modules/axios/dist/axios.min.js');
}
function add_vuetify(): void
{
    wp_enqueue_script('vuetify', get_template_directory_uri() . '/node_modules/vuetify/dist/vuetify.min.js');
    wp_enqueue_style('vuetify_style', get_template_directory_uri() . '/node_modules/vuetify/dist/vuetify.min.css');
}
function add_material_design_icons(): void
{
    wp_enqueue_style('mdi', get_template_directory_uri() . '/node_modules/@mdi/font/css/materialdesignicons.min.css');
}
function add_editorjs(): void
{
    wp_enqueue_script('editorGeneral', get_template_directory_uri() . '/node_modules/@editorjs/editorjs/dist/editor.js');
    wp_enqueue_script('editorCode', get_template_directory_uri() . '/node_modules/@editorjs/code/dist/bundle.js');
    wp_enqueue_script('editorHeader', get_template_directory_uri() . '/node_modules/@editorjs/header/dist/bundle.js');
    wp_enqueue_script('editorList', get_template_directory_uri() . '/node_modules/@editorjs/list/dist/bundle.js');
}
function add_vueloader(): void
{
    wp_enqueue_script('loader', get_template_directory_uri() . '/node_modules/http-vue-loader/src/httpVueLoader.js');
}
function add_prismjs(): void
{
    wp_enqueue_script('prismjs', get_template_directory_uri() . '/prism.js');
    wp_enqueue_style('prismjs', get_template_directory_uri() . '/prism.css');
}
function default_setup(): void
{
    add_style_css();
    add_vue();
    add_vuetify();
    add_axios();
    add_vueloader();
    add_material_design_icons();
    add_editorjs();
    add_prismjs();
}

add_action('wp_enqueue_scripts', 'default_setup');
add_action('admin_enqueue_scripts', 'default_setup');

function upload_images(): array
{
    $images_src = [];
    $images_src['csharp'] = media_sideload_image(get_template_directory_uri() . '/assets/csharp.jpg', 0);
    $images_src['aspnetcore'] = media_sideload_image(get_template_directory_uri() . '/assets/aspnetcore.png', 0);
    $images_src['unity'] = media_sideload_image(get_template_directory_uri() . '/assets/unity.jpg', 0);
    return $images_src;
}
function activate_theme_handler(): void
{
    create_entities();
    $images_src = upload_images();
    if (!post_exists(title: 'profile', type: 'page', status: 'publish')) {
        wp_insert_post(array(
            'post_type'	=> 'page',
            'post_title' => 'profile',
            'post_status' => 'publish'
        ));
    }
    if (!post_exists(title: 'course', type: 'page', status: 'publish')) {
        wp_insert_post(array(
            'post_type'	=> 'page',
            'post_title' => 'course',
            'post_status' => 'publish'
        ));
    }
}
function deactivate_theme_handler(): void
{
    remove_entities();
}

add_action('after_switch_theme', 'activate_theme_handler');
add_action('switch_theme', 'deactivate_theme_handler');

require 'PersonalCertificate.php';
$name_template = "certificate_template.jpg";
$name_font = "font_template.ttf";
$dataFieldPaintName = [
    'start_y' => 260,
    'start_x' => 310,
    'width' => 458,
    'height' => 17,
    'font-size' => 20,
    'color' => [
        'red' => 0,
        'green' => 0,
        'blue' => 0
    ]
];
$dataFieldPaintLanguage = [
    'start_y' => 353,
    'start_x' => 310,
    'width' => 466,
    'height' => 20,
    'font-size' => 12,
    'color' => [
        'red' => 112,
        'green' => 113,
        'blue' => 115
    ]
];
$dataFieldPaintDate = [
    'day' =>[
        'x' => 392,
        'y' => 478
    ],
    'mounth' =>[
        'start_y' => 478,
        'start_x' => 417,
        'width' => 3,
        'height' => 0,
    ],
    'year' =>[
        'x' => 437,
        'y' => 478
    ],
    'font-size' => 9,
    'color' => [
        'red' => 112,
        'green' => 113,
        'blue' => 115
    ]
];
new PersonalCertificate($name_template,$name_font,$dataFieldPaintName,$dataFieldPaintLanguage,$dataFieldPaintDate);
function create_certificate( WP_REST_Request $request ): WP_REST_Response {
    $body = json_decode($request->get_body());
    $fio = $body->fio;
    $course_name = $body->course;
    $url = PersonalCertificate::Create($fio, $course_name, date('d-m-Y',time()));
    $response = [
        'url' => $url
    ];
    return new WP_REST_Response(json_encode($response, JSON_UNESCAPED_UNICODE), 200);
}

function add_sub_routes(): void
{
    register_rest_route('lms/v1', '/createcache', array(
        'methods' => 'POST',
        'callback' => 'create_cache'
    ));
    register_rest_route('lms/v1', '/loadcache', array(
        'methods' => 'POST',
        'callback' => 'load_cache'
    ));
    register_rest_route('lms/v1', '/deletecache', array(
        'methods' => 'POST',
        'callback' => 'delete_cache'
    ));
    register_rest_route('lms/v1', '/createcertificate', array(
        'methods' => 'POST',
        'callback' => 'create_certificate'
    ));
}
function add_user_routes(): void
{
    register_rest_route('lms/v1', '/users/(?P<userId>[\d]+)/courses', array(
        'methods' => 'GET',
        'callback' => 'get_user_courses'
    ));
    register_rest_route('lms/v1', '/users/(?P<userId>[\d]+)/courses_with_completed', array(
        'methods' => 'GET',
        'callback' => 'get_user_courses_with_completed'
    ));
    register_rest_route('lms/v1', '/users/(?P<userId>[\d]+)/courses/(?P<courseId>[\d]+)', array(
        'methods' => 'POST',
        'callback' => 'join_user_in_course'
    ));
}
function add_course_routes(): void
{
    register_rest_route('lms/v1', '/courses/weight', array(
        'methods' => 'GET',
        'callback' => 'get_courses_weight'
    ));
    register_rest_route('lms/v1', '/courses', array(
        'methods' => 'GET',
        'callback' => 'get_all_courses'
    ));
    register_rest_route('lms/v1', '/course/(?P<courseId>[\d]+)/tests', array(
        'methods' => 'GET',
        'callback' => 'get_all_tests_in_course'
    ));
    register_rest_route('lms/v1', '/course/(?P<courseId>[\d]+)/modules', array(
        'methods' => 'GET',
        'callback' => 'get_all_course_modules'
    ));
    register_rest_route('lms/v1', '/course/(?P<courseId>[\d]+)', array(
        'methods' => 'GET',
        'callback' => 'get_course_by_id'
    ));
    register_rest_route('lms/v1', '/course/(?P<courseId>[\d]+)/image', array(
        'methods' => 'PUT',
        'callback' => 'update_course_image'
    ));
    register_rest_route('lms/v1', '/course/(?P<courseId>[\d]+)/description', array(
        'methods' => 'PUT',
        'callback' => 'update_course_description'
    ));
    register_rest_route('lms/v1', '/course/(?P<courseId>[\d]+)', array(
        'methods' => 'PUT',
        'callback' => 'update_course'
    ));
    register_rest_route('lms/v1', '/course/(?P<courseId>[\d]+)/tests', array(
        'methods' => 'POST',
        'callback' => 'add_tests_in_course'
    ));
    register_rest_route('lms/v1', '/course/(?P<courseId>[\d]+)/modules', array(
        'methods' => 'POST',
        'callback' => 'add_course_modules'
    ));
    register_rest_route('lms/v1', '/course', array(
        'methods' => 'POST',
        'callback' => 'create_course'
    ));
    register_rest_route('lms/v1', '/course/(?P<courseId>[\d]+)/test/(?P<testId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_test_in_course'
    ));
    register_rest_route('lms/v1', '/course/(?P<courseId>[\d]+)/module/(?P<moduleId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_module_in_course'
    ));
    register_rest_route('lms/v1', '/course/(?P<courseId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_course'
    ));
}
function add_module_routes(): void
{
    register_rest_route('lms/v1', '/modules/weight', array(
        'methods' => 'GET',
        'callback' => 'get_modules_weight'
    ));
    register_rest_route('lms/v1', '/modules', array(
        'methods' => 'GET',
        'callback' => 'get_all_modules'
    ));
    register_rest_route('lms/v1', '/module/(?P<moduleId>[\d]+)/themes', array(
        'methods' => 'GET',
        'callback' => 'get_all_themes_in_module'
    ));
    register_rest_route('lms/v1', '/module', array(
        'methods' => 'POST',
        'callback' => 'create_new_module'
    ));
    register_rest_route('lms/v1', '/module/(?P<moduleId>[\d]+)/themes', array(
        'methods' => 'POST',
        'callback' => 'add_themes_in_module'
    ));
    register_rest_route('lms/v1', '/module/(?P<moduleId>[\d]+)/description', array(
        'methods' => 'PUT',
        'callback' => 'update_module_description'
    ));
    register_rest_route('lms/v1', '/module/(?P<moduleId>[\d]+)', array(
        'methods' => 'PUT',
        'callback' => 'update_module'
    ));
    register_rest_route('lms/v1', '/module/(?P<moduleId>[\d]+)/theme/(?P<themeId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_theme_in_module'
    ));
    register_rest_route('lms/v1', 'module/(?P<moduleId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_module'
    ));
}
function add_concept_routes(): void
{
    register_rest_route('lms/v1', '/concepts', array(
        'methods' => 'GET',
        'callback' => 'get_all_concepts'
    ));
    register_rest_route('lms/v1', '/concept', array(
        'methods' => 'POST',
        'callback' => 'create_new_concept'
    ));
    register_rest_route('lms/v1', '/concept/(?P<conceptId>[\d]+)', array(
        'methods' => 'PUT',
        'callback' => 'update_concept'
    ));
    register_rest_route('lms/v1', '/concept/(?P<conceptId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_concept'
    ));
}
function add_competence_routes(): void
{
    register_rest_route('lms/v1', '/competences', array(
        'methods' => 'GET',
        'callback' => 'get_all_competences'
    ));
    register_rest_route('lms/v1', '/competence/(?P<competenceId>[\d]+)/concepts', array(
        'methods' => 'GET',
        'callback' => 'get_all_concepts_in_competence'
    ));
    register_rest_route('lms/v1', '/competence', array(
        'methods' => 'POST',
        'callback' => 'create_new_competence'
    ));
    register_rest_route('lms/v1', '/competence/(?P<competenceId>[\d]+)/concepts', array(
        'methods' => 'POST',
        'callback' => 'add_concepts_in_competence'
    ));
    register_rest_route('lms/v1', '/competence/(?P<competenceId>[\d]+)', array(
        'methods' => 'PUT',
        'callback' => 'update_competence'
    ));
    register_rest_route('lms/v1', '/competence/(?P<competenceId>[\d]+)/concept/(?P<conceptId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_concept_in_competence'
    ));
    register_rest_route('lms/v1', '/competence/(?P<competenceId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_competence'
    ));
}
function add_theme_routes(): void
{
    register_rest_route('lms/v1', '/theme/(?P<themeId>[\d]+)/concepts', array(
        'methods' => 'GET',
        'callback' => 'get_all_concepts_in_theme'
    ));
    register_rest_route('lms/v1', '/theme/(?P<themeId>[\d]+)/competences', array(
        'methods' => 'GET',
        'callback' => 'get_all_competences_in_theme'
    ));
    register_rest_route('lms/v1', '/theme/(?P<themeId>[\d]+)/tests', array(
        'methods' => 'GET',
        'callback' => 'get_all_tests_in_theme'
    ));
    register_rest_route('lms/v1', '/theme/(?P<themeId>[\d]+)/theories', array(
        'methods' => 'GET',
        'callback' => 'get_all_theories_in_theme'
    ));
    register_rest_route('lms/v1', '/themes', array(
        'methods' => 'GET',
        'callback' => 'get_all_mythemes'
    ));
    register_rest_route('lms/v1', '/theme/(?P<themeId>[\d]+)/concepts', array(
        'methods' => 'POST',
        'callback' => 'add_concepts_in_theme'
    ));
    register_rest_route('lms/v1', '/theme/(?P<themeId>[\d]+)/competences', array(
        'methods' => 'POST',
        'callback' => 'add_competences_in_theme'
    ));
    register_rest_route('lms/v1', '/theme/(?P<themeId>[\d]+)/tests', array(
        'methods' => 'POST',
        'callback' => 'add_tests_in_theme'
    ));
    register_rest_route('lms/v1', '/theme/(?P<themeId>[\d]+)/theories', array(
        'methods' => 'POST',
        'callback' => 'add_theories_in_theme'
    ));
    register_rest_route('lms/v1', '/theme', array(
        'methods' => 'POST',
        'callback' => 'create_new_mytheme'
    ));
    register_rest_route('lms/v1', '/theme/(?P<themeId>[\d]+)', array(
        'methods' => 'PUT',
        'callback' => 'update_mytheme'
    ));
    register_rest_route('lms/v1', '/theme/(?P<themeId>[\d]+)/test/(?P<testId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_test_in_theme'
    ));
    register_rest_route('lms/v1', '/theme/(?P<themeId>[\d]+)/theory/(?P<theoryId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_theory_in_theme'
    ));
    register_rest_route('lms/v1', '/theme/(?P<themeId>[\d]+)/concept/(?P<conceptId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_concept_in_theme'
    ));
    register_rest_route('lms/v1', '/theme/(?P<themeId>[\d]+)/competence/(?P<competenceId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_competence_in_theme'
    ));
    register_rest_route('lms/v1', 'theme/(?P<themeId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_mytheme'
    ));
}
function add_test_routes(): void
{
    register_rest_route('lms/v1', '/tests', array(
        'methods' => 'GET',
        'callback' => 'get_all_tests'
    ));
    register_rest_route('lms/v1', '/test/(?P<testId>[\d]+)/questions', array(
        'methods' => 'GET',
        'callback' => 'get_all_questions_in_test'
    ));
    register_rest_route('lms/v1', '/test', array(
        'methods' => 'POST',
        'callback' => 'create_new_test'
    ));
    register_rest_route('lms/v1', '/test/(?P<testId>[\d]+)/questions', array(
        'methods' => 'POST',
        'callback' => 'add_questions_in_test'
    ));
    register_rest_route('lms/v1', '/test/(?P<testId>[\d]+)/header', array(
        'methods' => 'PUT',
        'callback' => 'update_test_header'
    ));
    register_rest_route('lms/v1', '/test/(?P<testId>[\d]+)', array(
        'methods' => 'PUT',
        'callback' => 'update_test'
    ));
    register_rest_route('lms/v1', '/test/(?P<testId>[\d]+)/question/(?P<questionId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_question_in_test'
    ));
    register_rest_route('lms/v1', 'test/(?P<testId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_test'
    ));
}
function add_question_routes(): void
{
    register_rest_route('lms/v1', '/questions', array(
        'methods' => 'GET',
        'callback' => 'get_all_questions'
    ));
    register_rest_route('lms/v1', '/questions/user/(?P<userId>[\d]+)/course/(?P<courseId>[\d]+)', array(
        'methods' => 'GET',
        'callback' => 'get_all_questions_in_user_results'
    ));
    register_rest_route('lms/v1', '/question/(?P<questionId>[\d]+)/options', array(
        'methods' => 'GET',
        'callback' => 'get_all_options_in_question'
    ));
    register_rest_route('lms/v1', '/question/(?P<questionId>[\d]+)/competences', array(
        'methods' => 'GET',
        'callback' => 'get_all_competences_in_question'
    ));
    register_rest_route('lms/v1', '/question', array(
        'methods' => 'POST',
        'callback' => 'create_new_question'
    ));
    register_rest_route('lms/v1', '/question/user/(?P<userId>[\d]+)/course/(?P<courseId>[\d]+)', array(
        'methods' => 'POST',
        'callback' => 'add_question_in_user_results'
    ));
    register_rest_route('lms/v1', '/question/(?P<questionId>[\d]+)/options', array(
        'methods' => 'POST',
        'callback' => 'add_options_in_question'
    ));
    register_rest_route('lms/v1', '/question/(?P<questionId>[\d]+)/competences', array(
        'methods' => 'POST',
        'callback' => 'add_competences_in_question'
    ));
    register_rest_route('lms/v1', '/question/(?P<questionId>[\d]+)/option/(?P<optionId>[\d]+)/isright', array(
        'methods' => 'PUT',
        'callback' => 'update_question_option_isright'
    ));
    register_rest_route('lms/v1', '/question/(?P<questionId>[\d]+)/text', array(
        'methods' => 'PUT',
        'callback' => 'update_question_text'
    ));
    register_rest_route('lms/v1', '/question/(?P<questionId>[\d]+)', array(
        'methods' => 'PUT',
        'callback' => 'update_question'
    ));
    register_rest_route('lms/v1', '/question/(?P<questionId>[\d]+)/option/(?P<optionId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_option_in_question'
    ));
    register_rest_route('lms/v1', '/question/(?P<questionId>[\d]+)/competence/(?P<competenceId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_competence_in_question'
    ));
    register_rest_route('lms/v1', '/question/(?P<questionId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_question'
    ));
}
function add_option_routes(): void
{
    register_rest_route('lms/v1', '/options', array(
        'methods' => 'GET',
        'callback' => 'get_all_options'
    ));
    register_rest_route('lms/v1', '/option', array(
        'methods' => 'POST',
        'callback' => 'create_new_option'
    ));
    register_rest_route('lms/v1', '/option/(?P<optionId>[\d]+)/value', array(
        'methods' => 'PUT',
        'callback' => 'update_option_value'
    ));
    register_rest_route('lms/v1', '/option/(?P<optionId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_myoption'
    ));
}
function add_theory_routes(): void
{
    register_rest_route('lms/v1', '/theories', array(
        'methods' => 'GET',
        'callback' => 'get_all_theories'
    ));
    register_rest_route('lms/v1', '/theories/user/(?P<userId>[\d]+)/course/(?P<courseId>[\d]+)', array(
        'methods' => 'GET',
        'callback' => 'get_all_theories_in_user_results'
    ));
    register_rest_route('lms/v1', '/theory/(?P<theoryId>[\d]+)/concepts', array(
        'methods' => 'GET',
        'callback' => 'get_all_concepts_in_theory'
    ));
    register_rest_route('lms/v1', '/theory', array(
        'methods' => 'POST',
        'callback' => 'create_new_theory'
    ));
    register_rest_route('lms/v1', '/theory/user/(?P<userId>[\d]+)/course/(?P<courseId>[\d]+)', array(
        'methods' => 'POST',
        'callback' => 'add_theory_in_user_results'
    ));
    register_rest_route('lms/v1', '/theory/(?P<theoryId>[\d]+)/concepts', array(
        'methods' => 'POST',
        'callback' => 'add_concepts_in_theory'
    ));
    register_rest_route('lms/v1', '/theory/(?P<theoryId>[\d]+)/content', array(
        'methods' => 'PUT',
        'callback' => 'update_theory_content'
    ));
    register_rest_route('lms/v1', '/theory/(?P<theoryId>[\d]+)', array(
        'methods' => 'PUT',
        'callback' => 'update_theory'
    ));
    register_rest_route('lms/v1', '/theory/(?P<theoryId>[\d]+)/concept/(?P<conceptId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_concept_in_theory'
    ));
    register_rest_route('lms/v1', '/theory/(?P<theoryId>[\d]+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_theory'
    ));
}

add_action('rest_api_init', function () {
    add_sub_routes();
    add_user_routes();
    add_course_routes();
    add_module_routes();
    add_concept_routes();
    add_competence_routes();
    add_theme_routes();
    add_test_routes();
    add_question_routes();
    add_option_routes();
    add_theory_routes();
});