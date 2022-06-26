<?php
add_action('admin_menu', function () {
    add_menu_page('Настройка LMS', 'Настройка LMS', 'edit_themes', 'lms_settings', 'course_settings_template', 'dashicons-welcome-learn-more');
});

function course_settings_template(): void
{
    global $theme_path;
    $files = scandir($theme_path . '/cache');
    $json_files = array_filter($files, function ($filename) {
        return str_contains($filename, ".json");
    });
    ?>
    <div id="app" style="min-height: calc(100vh - 32px) !important;">
        <v-app>
            <v-toolbar color="primary" flat>
                <v-toolbar-title class="white--text ml-5">Управление {{ activeMenuName }}
                </v-toolbar-title>
                <v-spacer></v-spacer>
            </v-toolbar>
            <v-container
                    class="d-flex pa-0 ma-0 flex-row-reverse"
                    style="
                        min-width: 100%;
                    "
            >
                <v-list class="pa-0"
                        style="
                            min-width: 270px;
                        "
                >
                    <v-list-item-group v-model="activeMenuIndex" color="primary">
                        <v-list-item>
                            <v-list-item-icon>
                                <v-icon>mdi-folder-multiple</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>Курсы</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-icon>
                                <v-icon>mdi-folder</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>Модули</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-icon>
                                <v-icon>mdi-folder-open</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>Темы</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-icon>
                                <v-icon>mdi-book-open-variant</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>Теория</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-icon>
                                <v-icon>mdi-atom</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>Понятия</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-icon>
                                <v-icon>mdi-file-document-edit-outline</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>Тесты</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-icon>
                                <v-icon>mdi-lightbulb-question</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>Вопросы</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-icon>
                                <v-icon>mdi-lightbulb-on-10</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>Ответы</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-icon>
                                <v-icon>mdi-atom-variant</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>Компетенции</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-icon>
                                <v-icon color="error">mdi-database-cog</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title class="error--text">Кэш</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                    </v-list-item-group>
                </v-list>
                <v-main style="min-height: calc(100vh - 96px)">
                    <v-container class=" pl-8 pr-8 d-flex justify-center" v-if="activeMenuIndex == 0">
                        <v-courses :site-url="siteUrl"></v-courses>
                    </v-container>
                    <v-container class="pl-8 pr-8 d-flex justify-center" v-else-if="activeMenuIndex === 1">
                        <v-modules :site-url="siteUrl"></v-modules>
                    </v-container>
                    <v-container class="pl-8 pr-8 d-flex justify-center" v-else-if="activeMenuIndex === 2">
                        <v-themes :site-url="siteUrl"></v-themes>
                    </v-container>
                    <v-container class="pl-8 pr-8 d-flex justify-center" v-else-if="activeMenuIndex === 3">
                        <v-theories :site-url="siteUrl"></v-theories>
                    </v-container>
                    <v-container class="pl-8 pr-8 d-flex justify-center" v-else-if="activeMenuIndex === 4">
                        <v-concepts :site-url="siteUrl"></v-concepts>
                    </v-container>
                    <v-container class="pl-8 pr-8 d-flex justify-center" v-else-if="activeMenuIndex === 5">
                        <v-tests :site-url="siteUrl"></v-tests>
                    </v-container>
                    <v-container class="pl-8 pr-8 d-flex justify-center" v-else-if="activeMenuIndex === 6">
                        <v-questions :site-url="siteUrl"></v-questions>
                    </v-container>
                    <v-container class="pl-8 pr-8 d-flex justify-center" v-else-if="activeMenuIndex === 7">
                        <v-options :site-url="siteUrl"></v-options>
                    </v-container>
                    <v-container class="pl-8 pr-8 d-flex justify-center" v-else-if="activeMenuIndex === 8">
                        <v-competences :site-url="siteUrl"></v-competences>
                    </v-container>
                    <v-container class="pl-8 pr-8 d-flex justify-center" v-else-if="activeMenuIndex === 9">
                        <v-card class="pt-3 pb-3" style="min-width: 100%">
                            <v-toolbar flat>
                                <v-chip small color="primary" class="text-button">Закэшированные версии</v-chip>
                                <v-spacer></v-spacer>
                                <v-dialog :retain-focus="false" v-model="newCacheDialog" max-width="700">
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-btn :disabled="cacheAction" v-bind="attrs" v-on="on"
                                               @click="newCacheDialog = true" color="primary">
                                            Закэшировать
                                        </v-btn>
                                    </template>
                                    <template v-slot:default>
                                        <v-card class="d-flex flex-column justify-content-center">
                                            <v-list>
                                                <v-list-item>
                                                    <v-list-item-content>
                                                        <v-text-field
                                                                v-model="newCacheFileName"
                                                                label="Название нового кэш-файла"
                                                                autofocus
                                                                @keydown.enter="SendCacheCreateRequest(newCacheFileNameResult)">
                                                        </v-text-field>
                                                    </v-list-item-content>
                                                </v-list-item>
                                                <v-list-item>
                                                    <v-list-item-content>
                                                        <span class="error--text" style="font-size: 19px !important;">{{ newCacheFileNameResult }}</span>
                                                    </v-list-item-content>
                                                </v-list-item>
                                            </v-list>
                                            <v-btn
                                                    justify-self="center"
                                                    color="error"
                                                    @click="SendCacheCreateRequest(newCacheFileNameResult)"
                                            >
                                                Создать кэш LMS
                                            </v-btn>
                                        </v-card>
                                    </template>
                                </v-dialog>
                            </v-toolbar>
                            <v-divider></v-divider>
                            <v-container v-if="cacheAction"
                                    style="height: 40vh; display: flex; justify-content: center; align-items: center"
                            >
                                <v-progress-circular
                                        :size="220"
                                        color="red"
                                        indeterminate
                                >
                                    <span class="text-button pa-3"
                                          style="text-align: center; font-size: 15px !important;">{{ cacheActionText }}</span>
                                </v-progress-circular>
                            </v-container>
                            <v-container v-if="!cacheAction && cacheFileNames.length === 0"
                                         style="height: 40vh; display: flex; justify-content: center; align-items: center"
                            >
                                <span class="text-button error--text" style="font-size: 20px !important;">Закэшированных версий не найдено</span>
                            </v-container>
                            <v-list v-if="!cacheAction && cacheFileNames.length !== 0">
                                <v-list-item
                                        v-for="filename in cacheFileNames"
                                        class="pl-12"
                                >
                                    <v-list-item-content>
                                        <v-list-item-title class="error--text" style="font-size: 19px !important;">{{ filename }}</v-list-item-title>
                                    </v-list-item-content>
                                    <v-list-item-action class="mr-4">
                                        <v-btn color="error" @click="SendCacheLoadRequest(filename)">Восстановить версию</v-btn>
                                    </v-list-item-action>
                                    <v-list-item-action class="mr-4">
                                        <v-btn color="error" icon @click="SendCacheDeleteRequest(filename)">
                                            <v-icon>mdi-delete</v-icon>
                                        </v-btn>
                                    </v-list-item-action>
                                </v-list-item>
                            </v-list>
                        </v-card>
                    </v-container>
                </v-main>
            </v-container>
        </v-app>
    </div>
    <script>
        const app = new Vue({
            el: '#app',
            vuetify: new Vuetify(),
            components: {
                'v-courses': httpVueLoader('<?php echo get_template_directory_uri() . '/admin_menus/components/courses/v-courses.vue'; ?>'),
                'v-modules': httpVueLoader('<?php echo get_template_directory_uri() . '/admin_menus/components/modules/v-modules.vue'; ?>'),
                'v-concepts': httpVueLoader('<?php echo get_template_directory_uri() . '/admin_menus/components/concepts/v-concepts.vue'; ?>'),
                'v-competences': httpVueLoader('<?php echo get_template_directory_uri() . '/admin_menus/components/competences/v-competences.vue'; ?>'),
                'v-themes': httpVueLoader('<?php echo get_template_directory_uri() . '/admin_menus/components/themes/v-themes.vue'; ?>'),
                'v-tests': httpVueLoader('<?php echo get_template_directory_uri() . '/admin_menus/components/tests/v-tests.vue'; ?>'),
                'v-theories': httpVueLoader('<?php echo get_template_directory_uri() . '/admin_menus/components/theory/v-theories.vue'; ?>'),
                'v-questions': httpVueLoader('<?php echo get_template_directory_uri() . '/admin_menus/components/questions/v-questions.vue'; ?>'),
                'v-options': httpVueLoader('<?php echo get_template_directory_uri() . '/admin_menus/components/options/v-options.vue'; ?>'),
            },
            data: {
                siteUrl: '<?php echo get_site_url(); ?>',
                lmsApiUrl: '<?php echo get_site_url(); ?>/wp-json/lms/v1',
                activeMenuIndex: 0,
                cacheFileNames: Object.values(JSON.parse('<?php echo json_encode($json_files); ?>')),
                newCacheFileName: '',
                newCacheDialog: false,
                cacheAction: false,
                cacheActionText: '',
                currentDate: new Date(Date.now()).toISOString().substring(0,10).replaceAll('-','_'),
            },
            methods: {
                SendCacheCreateRequest: function (filename) {
                    this.cacheActionText = 'Кэширование базы данных';
                    this.cacheAction = true;
                    this.newCacheDialog = false;
                    let body = {
                        cacheFileName: filename
                    }
                    console.log(`${this.lmsApiUrl}/createcache`);
                    axios.post(`${this.lmsApiUrl}/createcache`, body)
                        .then(response => {
                            let createCacheFileName = JSON.parse(response.data).filename;
                            this.cacheFileNames.push(createCacheFileName);
                            this.cacheAction = false;
                        })
                        .catch(error => {
                            console.log(error);
                        });
                },
                SendCacheDeleteRequest: function (filename) {
                    this.cacheActionText = 'Удаление кэша';
                    this.cacheAction = true;
                    let body = {
                        cacheFileName: filename
                    }
                    axios.post(`${this.lmsApiUrl}/deletecache`, body)
                        .then(response => {
                            let deletedFileName = JSON.parse(response.data).deletedFileName;
                            this.cacheFileNames = this.cacheFileNames.filter(currentFilename => currentFilename != deletedFileName);
                            this.cacheAction = false;
                        })
                        .catch(error => {
                            console.log(error);
                        })
                },
                SendCacheLoadRequest: function (filename) {
                    this.cacheActionText = 'Восстановление базы данных';
                    this.cacheAction = true;
                    let body = {
                        cacheFileName: filename
                    }
                    axios.post(`${this.lmsApiUrl}/loadcache`, body)
                        .then(response => {
                            this.cacheAction = false;
                        })
                        .catch(error => {
                            console.log(error);
                        })
                }
            },
            computed: {
                activeMenuName: function () {
                    switch (this.activeMenuIndex) {
                        case 0:
                            return 'курсами';
                        case 1:
                            return 'модулями';
                        case 2:
                            return 'темами';
                        case 3:
                            return 'теорией';
                        case 4:
                            return 'понятиями';
                        case 5:
                            return 'тестами';
                        case 6:
                            return 'вопросами';
                        case 7:
                            return 'ответами';
                        case 8:
                            return 'компетенциями';
                        case 9:
                            return 'кэшем'
                    }
                },
                newCacheFileNameResult: function () {
                    return this.newCacheFileName.trim().split('.')[0].replaceAll(' ', '_') + '_' + this.currentDate + '.json';
                }
            },
            mounted() {
                this.currentMenu = 0;
                console.log(this.cacheFileNames);
            },
        });
    </script>
    <style>
        * {
            font-family: RalewayGeneral;
        }

        #wpcontent {
            padding: 0px !important;
        }

        #wpfooter {
            display: none !important;
        }

        #wpbody-content {
            padding: 0px !important;
        }

        .v-application--wrap {
            min-height: 100%;
        }

        .v-text-field * {
            border: 0 !important;
        }

        .v-input--is-focused * {
            box-shadow: none !important;
        }

        .v-tabs--vertical {
            flex-direction: row-reverse;
            justify-content: space-between;
        }

        #wpbody-content {
            min-height: calc(100vh - 32px) !important;
        }

        #app {
            min-height: calc(100vh - 32px) !important;
        }

        .cdx-input {
            min-height: 50px !important;
        }

        .codex-editor__redactor {
            padding-bottom: 0px !important;
        }

        .ce-block__content {
            max-width: 70% !important;
        }

        .ce-toolbar__content {
            max-width: 70% !important;
        }

        .ce-toolbar__actions {
            position: relative !important;
            right: 0px !important;
            left: -60px !important;
        }

        pre {
            border-radius: 0 !important;
        }

        code {
            padding: 0 !important;
        }

        #adminmenuwrap, #wpadminbar {
            z-index: 199 !important;
        }
    </style>
    <?php
}
