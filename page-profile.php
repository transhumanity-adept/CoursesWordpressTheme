<?php
/*
Template Name: profile
*/
if (isset($_POST['logout'])) {
    wp_logout();
    header('Location: ' . get_site_url());
    die();
}
global $current_user;
$user_meta = get_user_meta($current_user->ID);
$first_name_array = $user_meta['first_name'];
$last_name_array = $user_meta['last_name'];
$fio = $first_name_array[count($first_name_array) - 1] . ' ' . $last_name_array[count($last_name_array) - 1];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <?php wp_head(); ?>
</head>
<body>
    <div id="app">
        <v-app>
            <v-toolbar max-height="70px">
                <v-btn class="ml-3" text :href="siteUrl">CourseOcean</v-btn>
                <span v-if="currentUser.ID != 0" class="text-button ml-3 mr-4">{{ currentUser.data.user_nicename }}</span>
                <v-spacer></v-spacer>
                <v-btn
                        v-for="menu in menuItems"
                        :key="menu.title"
                        :href="menu.link"
                        class="mr-4 primary"
                >
                    {{ menu.title }}
                </v-btn>
                <form v-if="currentUser.ID != 0" class="mr-4" method="POST">
                    <input hidden name="logout" value="logout">
                    <v-btn color="primary" type="sumbit">Выйти</v-btn>
                </form>
            </v-toolbar>
            <v-main>
                <v-row justify="center" align="center" style="height: 50px !important;" class="mt-6 mb-7">
                    <span class="text-button" style="font-size: 18px !important;">Вы поступили на следующие курсы</span>
                </v-row>
                <v-row justify="center" v-if="coursesLoading">
                    <v-card flat width="70%" class="d-flex flex-row flex-wrap justify-center">
                        <v-skeleton-loader type=" list-item-avatar-three-line, divider, card" width="400px" height="270px"
                                           class="align-center pa-2 ml-6 mt-6"
                                           v-for="n in 6"
                        >
                        </v-skeleton-loader>
                    </v-card>
                </v-row>
                <v-row justify="center" v-else>
                    <v-card v-if="userCourses.length == 0" flat width="70%" height="30vh" class="d-flex flex-row flex-wrap justify-center align-center">
                        <span class="text-button" style="font-size: 20px !important;">Курсы не найдены</span>
                    </v-card>
                    <v-card v-else flat width="70%" class="d-flex flex-row flex-wrap justify-center">
                        <v-card
                                v-for="course in userCourses"
                                :key="course.id"
                                width="400px" min-height="270px" class="align-center courseview pa-4 ml-6 mt-6"
                                @click="redirectCourse(course)"
                        >
                            <v-row v-if="currentUser.ID !== 0 && userCourses.find(userCourse => userCourse.id === course.id)?.completed" justify="end" class="mb-2">
                                <span class="text-button success--text">Пройден</span>
                            </v-row>
                            <v-row v-if="currentUser.ID !== 0 && !userCourses.find(userCourse => userCourse.id === course.id)?.completed" justify="end" class="mb-2">
                                <span class="text-button error--text">Не завершен</span>
                            </v-row>
                            <v-container class="pa-0">
                                <v-row style="height: var(--courseview-header-height)">
                                    <v-col>
                                        <v-row style="height: 50%; font-weight: bold; font-size: 17px;">
                                            {{ course.name }}
                                        </v-row>
                                        <v-row style="height: 50%">
                                            <div class="d-flex flex-row align-end">
                                                <div>0</div>
                                                <v-icon>mdi-account</v-icon>
                                            </div>
                                        </v-row>
                                    </v-col>
                                    <v-col cols="4" class="d-flex justify-end">
                                        <div v-if="course.image == null" class="d-flex justify-center align-center" style="height: 100px; width: 100px;">
                                            <v-icon x-large>
                                                mdi-school
                                            </v-icon>
                                        </div>
                                        <div v-else style="height: 100px; width: 100px;">
                                            <v-img :src="course.image" contain max-height="100%" max-width="100%" style="border: 1px solid transparent; border-radius: 8px;"></v-img>
                                        </div>
                                    </v-col>
                                </v-row>
                                <v-divider class="mt-2 mb-2"></v-divider>
                                <v-row style="overflow-y: auto; max-height: 120px;">
                                    <div
                                            v-for="block in JSON.parse(course.description).blocks"
                                            :key="block.id"
                                    >
                                        <ul style="font-size: 14px; line-height: 15px;" v-if="block.type == 'list'">
                                            <li v-for="item in block.data.items" v-html="item"></li>
                                        </ul>
                                        <span v-else-if="block.type == 'header'" v-html="`<h${block.data.level}>${block.data.text}</h${block.data.level}>`"></span>
                                        <p style="font-size: 15px; line-height: 20px;" v-html="block.data.text" v-else></p>
                                    </div>
                                </v-row>
                            </v-container>
                            <v-row v-if="currentUser.ID !== 0 && userCourses.find(userCourse => userCourse.id === course.id)?.completed" justify="end" class="mt-3">
                                <v-btn :disabled="course.certificateDownloading" :loading="course.certificateDownloading" @click.stop="downloadCertificate(course)" block color="primary">Скачать сертификат</v-btn>
                            </v-row>
                        </v-card>
                    </v-card>
                </v-row>
            </v-main>
        </v-app>
    </div>
</body>
</html>
<script>
    var app = new Vue({
        el: '#app',
        vuetify: new Vuetify(),
        data: {
            lmsApiUrl: '<?php echo get_site_url(); ?>/wp-json/lms/v1',
            siteUrl: '<?php echo get_site_url(); ?>',
            currentUser: JSON.parse('<?php echo json_encode($current_user, JSON_UNESCAPED_UNICODE); ?>'),
            userFIO: '<?php echo $fio ?>',
            coursesLoading: false,
            userCourses: [],
        },
        methods: {
            courseUrl: function(course) {
                return this.siteUrl + `/course/?courseId=${course.id}`;
            },
            redirectCourse: function (course) {
                window.location.replace(this.courseUrl(course));
            },
            downloadCertificate: function (course) {
                course.certificateDownloading = true;
                axios.post(`${this.lmsApiUrl}/createcertificate`, { fio: this.userFIO, course: course.name })
                    .then(response => {
                        const url = JSON.parse(response.data).url;
                        const link = document.createElement("a");
                        link.setAttribute('download', '');
                        link.href = url;
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                        course.certificateDownloading = false;
                    })
                    .catch(error => console.log(error));
            }
        },
        computed: {
            menuItems: function() {
                let menus = [];
                if (this.currentUser.ID === 0) {
                    menus.push({ title: 'Войти', link: `${this.siteUrl}/lmslogin`});
                } else {
                    if (this.currentUser.roles.includes('administrator'))
                        menus.push({ title: 'Админка', link: `${this.siteUrl}/wp-admin/admin.php?page=lms_settings`});
                    menus.push({ title: 'Профиль', link: `${this.siteUrl}/profile`});
                }
                return menus;
            },
            registrationUrl: function () {
                return this.siteUrl + '/lmslogin';
            }
        },
        mounted() {
            this.coursesLoading = true;
            axios.get(`${this.lmsApiUrl}/users/${this.currentUser.ID}/courses_with_completed`)
                .then(response => {
                    this.userCourses = JSON.parse(response.data).courses.map(course => {
                        course.certificateDownloading = false;
                        return course;
                    });
                    this.coursesLoading = false;
                })
                .catch(error => {
                    console.log(error);
                })
        }
    });
</script>

<style>
    body, html {
        overflow-y: auto;
    }
    .courseview {
        --courseview-header-height: 100px;
    }
    .courseview:hover {
        cursor: pointer;
        box-shadow: 0px 0px 20px #90CAF9 !important;
        transition: 0.3s;
    }
    .row {
        margin: 0px !important;
    }
    .col {
        padding: 0px !important;
    }
</style>