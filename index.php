<?php
if (isset($_POST['logout'])) {
    wp_logout();
}
global $current_user;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <v-main class="mb-10">
            <v-row justify="center" style="height: 40px" class="mt-7 mb-10">
                <v-col cols="5">
                    <v-text-field height="40px" v-model="courseFilter" placeholder="Начните водить название курса" class="raleway" style="font-size: 20px"></v-text-field>
                </v-col>
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
                <v-card v-if="filteredCourses().length == 0" flat width="70%" height="30vh" class="d-flex flex-row flex-wrap justify-center align-center">
                    <span class="text-button" style="font-size: 20px !important;">Курсы не найдены</span>
                </v-card>
                <v-card v-else flat width="70%" class="d-flex flex-row flex-wrap justify-center">
                    <v-card
                            v-for="course in filteredCourses()"
                            :key="course.id"
                            width="400px" min-height="270px" class="align-center courseview pa-4 ml-6 mt-6">
                        <v-dialog
                                fullscreen
                                transition="dialog-bottom-transition"
                                v-model="course.coursePreview"
                        >
                            <template v-slot:activator="{ on }">
                                <v-container v-on="on" class="pa-0" @click="courseModulesOpen(course)">
                                    <v-row v-if="currentUser.ID !== 0 && userCourses.find(userCourse => userCourse.id === course.id)?.completed" justify="end" class="mb-2">
                                        <span class="text-button success--text">Курс пройден</span>
                                    </v-row>
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
                            </template>
                            <template v-slot:default>
                                <v-card>
                                    <v-toolbar
                                            rounded="0"
                                    >
                                        <v-btn
                                                icon class="ml-3"
                                                @click="course.coursePreview = false"
                                        >
                                            <v-icon>mdi-close</v-icon>
                                        </v-btn>
                                        <v-toolbar-title>Траектория курса</v-toolbar-title>
                                        <v-spacer></v-spacer>
                                        <v-btn v-if="currentUser.ID == 0" :href="registrationUrl" color="primary">Зарегистрироваться чтобы поступить</v-btn>
                                        <v-btn v-else-if="userCourses.find(c => c.id == course.id)" @click.stop="redirectCourse(course)" color="green" text>Перейти к курсу</v-btn>
                                        <v-btn v-else color="primary" :disabled="course.userJoined" :loading="course.userJoined"
                                               @click.stop="SendJoinUserInCourseRequest(course)">Поступить на курс</v-btn>
                                    </v-toolbar>
                                    <v-row v-if="course.modulesLoading" justify="center">
                                        <v-card width="60%" height="60vh" flat class="d-flex flex-column justify-center align-center mt-2">
                                            <span class="text-button" style="font-size: 19px !important;">Загрузка модулей</span>
                                            <v-progress-linear
                                                    color="orange"
                                                    indeterminate
                                                    rounded
                                                    height="6"
                                            ></v-progress-linear>
                                        </v-card>
                                    </v-row>
                                    <v-row v-else class="fill-height" justify="center">
                                        <v-card flat rounded="0" class="mt-2" width="50%">
                                            <v-timeline>
                                                <v-timeline-item fill-dot
                                                    v-for="module in course.modules"
                                                    :key="module.id"
                                                >
                                                    <template v-slot:icon>
                                                        <v-icon color="white">mdi-school-outline</v-icon>
                                                    </template>
                                                    <v-card>
                                                        <v-card-title>
                                                            {{ module.name }}
                                                        </v-card-title>
                                                        <v-card-text>
                                                            <div
                                                                    v-for="block in JSON.parse(module.description).blocks"
                                                                    :key="block.id"
                                                            >
                                                                <ul style="font-size: 14px; line-height: 15px;" v-if="block.type == 'list'">
                                                                    <li v-for="item in block.data.items" v-html="item"></li>
                                                                </ul>
                                                                <span v-else-if="block.type == 'header'" v-html="`<h${block.data.level}>${block.data.text}</h${block.data.level}>`"></span>
                                                                <p style="font-size: 15px; line-height: 20px;" v-html="block.data.text" v-else></p>
                                                            </div>
                                                        </v-card-text>
                                                    </v-card>
                                                </v-timeline-item>
                                            </v-timeline>
                                        </v-card>
                                    </v-row>
                                </v-card>
                            </template>
                        </v-dialog>
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
            currentUser: JSON.parse('<?php echo json_encode($current_user); ?>'),
            courses: [],
            coursesLoading: false,
            userCourses: [],
            courseFilter: '',
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
        methods: {
            courseUrl: function(course) {
              return this.siteUrl + `/course/?courseId=${course.id}`;
            },
            redirectCourse: function (course) {
                window.location.replace(this.courseUrl(course));
            },
            filteredCourses: function() {
              return this.courseFilter ? this.courses.filter(course => course.name.includes(this.courseFilter)) : this.courses;
            },
            SendJoinUserInCourseRequest: function (course) {
                course.userJoined = true;
                let route = `${this.lmsApiUrl}/users/${this.currentUser.ID}/courses/${course.id}`;
                axios.post(route)
                    .then(response => {
                        course.userJoined = false;
                        window.location.replace(this.courseUrl(course));
                    })
                    .catch(error => {
                        course.userJoined = false;
                        console.log(error);
                    })
            },
            courseModulesOpen: function (course) {
                if (course.modulesLoaded) return;
                course.modulesLoading = true;
                let route = `${this.siteUrl}/wp-json/lms/v1/course/${course.id}/modules`;
                axios.get(route)
                    .then((response) => {
                        course.modulesLoading = false;
                        course.modulesLoaded = true;
                        course.modules = JSON.parse(response.data).modules.map(module => {
                            return module;
                        });
                        console.log('modules loading success');
                        console.log(response);
                    })
                    .catch((error) => {
                        course.modulesLoading = false;
                        console.log('modules loading failed');
                        console.log(error.response.data.message);
                    });
            },

        },
        async mounted() {
            this.coursesLoading = true;
            console.log(this.currentUser);
            axios.get(`${this.lmsApiUrl}/courses`)
                .then(response => {
                    this.courses = response.data.map(course => {
                        course.coursePreview = false;
                        course.modules = [];
                        course.modulesLoading = false;
                        course.modulesLoaded = false;
                        course.userJoined = false;
                        return course;
                    });
                    axios.get(`${this.lmsApiUrl}/users/${this.currentUser.ID}/courses_with_completed`)
                        .then(response => {
                            this.userCourses = JSON.parse(response.data).courses;
                            this.coursesLoading = false;
                        })
                        .catch(error => {
                            console.log(error);
                        })
                })
                .catch(error => {
                    this.coursesLoading = false;
                });
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
        box-shadow: 0px 0px 19px #90CAF9 !important;
        transition: 0.3s;
    }
    .row {
        margin: 0px !important;
    }
    .col {
        padding: 0px !important;
    }
    .v-text-field--filled>.v-input__control>.v-input__slot, .v-text-field--full-width>.v-input__control>.v-input__slot, .v-text-field--outlined>.v-input__control>.v-input__slot {
        min-height: 0px !important;
    }
</style>