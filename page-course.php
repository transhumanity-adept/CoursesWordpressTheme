<?php
/*
Template Name: course
*/
if (isset($_POST['logout'])) {
    wp_logout();
    header('Location: ' . get_site_url());
    die();
}
global $current_user;
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
            <span v-if="!contentLoading" class="text-button ml-3 mr-4 primary--text">{{ currentCourse.name }}</span>
            <v-chip v-if="currentCourse.isCompleted" color="success" label class="text-button">Пройден</v-chip>
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
                <v-btn color="primary" type="submit">Выйти</v-btn>
            </form>
        </v-toolbar>
        <v-main>
            <v-row v-if="contentLoading" justify="center">
                <v-card width="60%" height="60vh" flat class="d-flex flex-column justify-center align-center mt-2">
                    <span class="text-button" style="font-size: 19px !important;">Загрузка информации</span>
                    <v-progress-linear
                            color="orange"
                            indeterminate
                            rounded
                            height="6"
                    ></v-progress-linear>
                </v-card>
            </v-row>
            <v-row v-else class="fill-height">
                <v-col cols="3">
                    <v-navigation-drawer width="300px;">
                        <v-list-item>
                            <v-list-item-content>
                                <v-list-item-title class="text-h6">
                                    Начальные тесты
                                </v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                        <v-divider></v-divider>
                        <v-list>
                            <v-list-item
                                v-for="test in currentCourse.tests"
                                :key="test.id"
                                class="pa-0"
                            >
                                <v-list-item-content class="pr-3 pl-6">
                                    <v-badge
                                            dark
                                            bordered
                                            x-large
                                            :color="test.isCompleted ? 'success' : 'primary'"
                                            :icon="test.isCompleted ? 'mdi-check' : 'mdi-alert-circle-outline'"
                                            overlap
                                            style="height: 30px !important;"
                                    >
                                        <v-btn text
                                               @click="initialTestSelect(test)"
                                               :class=" test.isCompleted ? 'success' :'primary'"
                                               block
                                               class="text-capitalize"
                                        >
                                            {{ test.name }}
                                        </v-btn>
                                    </v-badge>
                                </v-list-item-content>
                            </v-list-item>
                        </v-list>
                        <v-divider></v-divider>
                        <v-list-item>
                            <v-list-item-content>
                                <v-list-item-title class="text-h6">
                                    Модули
                                </v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                        <v-divider></v-divider>
                        <v-list>
                            <v-list-group
                                    v-for="module in currentCourse.modules"
                                    :key="module.id"
                                    prepend-icon="mdi-school"
                                    :value="true"
                            >
                                <template v-slot:activator>
                                    <v-list-item-content>
                                        <v-list-item-title v-text="module.name"></v-list-item-title>
                                    </v-list-item-content>
                                </template>
                                <v-list-item
                                        v-for="theme in module.themes"
                                        :key="theme.id"
                                        class="pa-0"
                                >
                                    <v-list-item-content class="pr-3 pl-6">
                                        <v-badge
                                                dark
                                                bordered
                                                x-large
                                                :color="theme.isCompleted ? 'success' : theme.isAvailable ? 'primary' : 'error'"
                                                :icon="theme.isCompleted ? 'mdi-check' : theme.isAvailable ? 'mdi-alert-circle-outline': 'mdi-lock'"
                                                overlap
                                                style="height: 30px !important;"
                                        >
                                            <v-btn text
                                                   @click="themeSelect(theme)"
                                                   :class="theme.isCompleted ? 'success' : theme.isAvailable ? 'primary' : 'error'"
                                                   block
                                                   class="text-capitalize"
                                            >
                                                {{ theme.name }}
                                            </v-btn>
                                        </v-badge>
                                    </v-list-item-content>
                                </v-list-item>
                            </v-list-group>
                        </v-list>
                    </v-navigation-drawer>
                </v-col>
                <v-col>
                    <v-card flat rounded="0" v-if="Object.keys(selectedInitialTest).length !== 0">
                        <v-card-title>
                            {{ selectedInitialTest.name }}
                        </v-card-title>
                        <v-card-text>
                            <div
                                    v-for="block in JSON.parse(selectedInitialTest.header).blocks"
                                    :key="block.id"
                            >
                                            <pre style="font-size: 14px;" v-if="block.type == 'code'"
                                                 class="line-numbers language-csharp"><code :id="block.id"
                                                                                            v-html="block.data.code"></code></pre>
                                <ul style="font-size: 17px; line-height: 27px;"
                                    v-else-if="block.type == 'list'">
                                    <li v-for="item in block.data.items" v-html="item"></li>
                                </ul>
                                <span v-else-if="block.type == 'header'"
                                      v-html="`<h${block.data.level}>${block.data.text}</h${block.data.level}>`"></span>
                                <p style="font-size: 17px; line-height: 27px;" v-html="block.data.text"
                                   v-else></p>
                            </div>
                            <v-card
                                    v-for="question in selectedInitialTest.questions"
                                    :key="question.id"
                                    class="mt-7 pa-6"
                            >
                                <div
                                        v-for="block in JSON.parse(question.text).blocks"
                                        :key="block.id"
                                >
                                                <pre style="font-size: 14px;" v-if="block.type == 'code'"
                                                     class="line-numbers language-csharp"><code :id="block.id"
                                                                                                v-html="block.data.code"></code></pre>
                                    <ul style="font-size: 17px; line-height: 27px;"
                                        v-else-if="block.type == 'list'">
                                        <li v-for="item in block.data.items" v-html="item"></li>
                                    </ul>
                                    <span v-else-if="block.type == 'header'"
                                          v-html="`<h${block.data.level}>${block.data.text}</h${block.data.level}>`"></span>
                                    <p style="font-size: 17px; line-height: 27px;" v-html="block.data.text"
                                       v-else></p>
                                </div>
                                <template
                                        v-if="question.options.filter(option => option.isRightAnswer).length > 1"
                                >
                                    <template
                                            v-for="option in question.options"
                                    >
                                        <div class="d-flex flex-row">
                                            <div>
                                                <v-checkbox v-model="option.selectedToAnswer"></v-checkbox>
                                            </div>
                                            <div
                                                    v-for="block in JSON.parse(option.value).blocks"
                                                    :key="block.id"
                                            >
                                                            <pre style="font-size: 14px;" v-if="block.type == 'code'"
                                                                 class="line-numbers language-csharp"><code
                                                                        :id="block.id" v-html="block.data.code"></code></pre>
                                                <ul style="font-size: 17px; line-height: 27px;"
                                                    v-else-if="block.type == 'list'">
                                                    <li v-for="item in block.data.items" v-html="item"></li>
                                                </ul>
                                                <span v-else-if="block.type == 'header'"
                                                      v-html="`<h${block.data.level}>${block.data.text}</h${block.data.level}>`"></span>
                                                <p style="font-size: 17px; line-height: 27px;"
                                                   v-html="block.data.text" v-else></p>
                                            </div>
                                        </div>
                                    </template>
                                    <v-alert
                                            v-if="question.isIncorrect"
                                            dense
                                            outlined
                                            type="error"
                                    >
                                        Прочтите теорию еще раз
                                    </v-alert>
                                    <div class="d-flex flex-row">
                                        <v-badge
                                                dark
                                                bordered
                                                x-large
                                                :color="question.isCompleted ? 'success' : question.isIncorrect ? 'error' : 'primary'"
                                                :icon="question.isCompleted ? 'mdi-check' : question.isIncorrect ? 'mdi-lock' : 'mdi-alert-circle-outline'"
                                                overlap
                                                style="height: 30px !important;"
                                        >
                                            <v-btn @click="SendAddQuestionInResultCheckbox(question)"
                                                   :class="question.isCompleted ? 'success' : question.isIncorrect ? 'error' : 'primary'"
                                                   class="text-capitalize"
                                                   style="width: 250px !important;"
                                                   :loading="question.adding"
                                            >
                                                {{ question.isCompleted ? 'Ответ учтен' : question.isIncorrect ? 'Заблокировано' : 'Ответить' }}
                                            </v-btn>
                                        </v-badge>
                                        <v-chip class="ml-4" v-if="question.isIncorrect" color="error" label>Доступно через: {{ question.incorrectRemainingTime }} секунд</v-chip>
                                    </div>
                                </template>
                                <template
                                        v-if="question.options.filter(option => option.isRightAnswer).length === 1 && question.options.length > 1"
                                >
                                    <v-radio-group v-model="question.selectedOptionIndex">
                                        <template
                                                v-for="(option, index) in question.options"
                                        >
                                            <div class="d-flex flex-row">
                                                <div class="d-flex justify-center align-center">
                                                    <v-radio :key="index" :value="index"></v-radio>
                                                </div>
                                                <div
                                                        v-for="block in JSON.parse(option.value).blocks"
                                                        :key="block.id"
                                                >
                                                                <pre style="font-size: 14px;"
                                                                     v-if="block.type == 'code'"
                                                                     class="line-numbers language-csharp"><code
                                                                            :id="block.id"
                                                                            v-html="block.data.code"></code></pre>
                                                    <ul style="font-size: 17px; line-height: 27px;"
                                                        v-else-if="block.type == 'list'">
                                                        <li v-for="item in block.data.items"
                                                            v-html="item"></li>
                                                    </ul>
                                                    <span v-else-if="block.type == 'header'"
                                                          v-html="`<h${block.data.level}>${block.data.text}</h${block.data.level}>`"></span>
                                                    <p style="font-size: 17px; line-height: 27px;"
                                                       v-html="block.data.text" v-else></p>
                                                </div>
                                            </div>
                                        </template>
                                    </v-radio-group>
                                    <v-alert
                                            v-if="question.isIncorrect"
                                            dense
                                            outlined
                                            type="error"
                                    >
                                        Прочтите теорию еще раз
                                    </v-alert>
                                    <div class="d-flex flex-row">
                                        <v-badge
                                                dark
                                                bordered
                                                x-large
                                                :color="question.isCompleted ? 'success' : question.isIncorrect ? 'error' : 'primary'"
                                                :icon="question.isCompleted ? 'mdi-check' : question.isIncorrect ? 'mdi-lock' : 'mdi-alert-circle-outline'"
                                                overlap
                                                style="height: 30px !important;"
                                        >
                                            <v-btn @click="SendAddQuestionInResultRadio(question)"
                                                   :class="question.isCompleted ? 'success' : question.isIncorrect ? 'error' : 'primary'"
                                                   class="text-capitalize"
                                                   style="width: 250px !important;"
                                                   :loading="question.adding"
                                            >
                                                {{ question.isCompleted ? 'Ответ учтен' : question.isIncorrect ? 'Заблокировано' : 'Ответить' }}
                                            </v-btn>
                                        </v-badge>
                                        <v-chip class="ml-4" v-if="question.isIncorrect" color="error" label>Доступно через: {{ question.incorrectRemainingTime }} секунд</v-chip>
                                    </div>
                                </template>
                                <template
                                        v-if="question.options.filter(option => option.isRightAnswer).length === 1 && question.options.length === 1"
                                >
                                    <v-text-field v-model="question.answerValue" label="Ответ на вопрос"></v-text-field>
                                    <v-alert
                                            v-if="question.isIncorrect"
                                            dense
                                            outlined
                                            type="error"
                                    >
                                        Прочтите теорию еще раз
                                    </v-alert>
                                    <div class="d-flex flex-row">
                                        <v-badge
                                                dark
                                                bordered
                                                x-large
                                                :color="question.isCompleted ? 'success' : question.isIncorrect ? 'error' : 'primary'"
                                                :icon="question.isCompleted ? 'mdi-check' : question.isIncorrect ? 'mdi-lock' : 'mdi-alert-circle-outline'"
                                                overlap
                                                style="height: 30px !important;"
                                        >
                                            <v-btn @click="SendAddQuestionResultValue(question)"
                                                   :class="question.isCompleted ? 'success' : question.isIncorrect ? 'error' : 'primary'"
                                                   class="text-capitalize"
                                                   style="width: 250px !important;"
                                                   :loading="question.adding"
                                            >
                                                {{ question.isCompleted ? 'Ответ учтен' : question.isIncorrect ? 'Заблокировано' : 'Ответить' }}
                                            </v-btn>
                                        </v-badge>
                                        <v-chip class="ml-4" v-if="question.isIncorrect" color="error" label>Доступно через: {{ question.incorrectRemainingTime }} секунд</v-chip>
                                    </div>
                                </template>
                            </v-card>
                        </v-card-text>
                    </v-card>
                    <v-stepper
                            v-else-if="Object.keys(currentTheme).length !== 0"
                            flat rounded="0" style="border-bottom: 1px solid #E0E0E0"
                            :value="currentStep"
                    >
                        <v-stepper-header style="height: 100px">
                            <template
                                    v-for="(theory, index) in currentTheme.theories"
                            >
                                <v-stepper-step
                                        :complete="theory.isCompleted"
                                        edit-icon="mdi-check"
                                        :color="theory.isCompleted ? 'success' : 'primary'"
                                        :step="index"
                                        @click="theorySelect(index)"
                                >
                                    <v-icon class="mr-2" :color="theory.isCompleted ? 'success' : 'primary'">mdi-book-open-variant</v-icon>
                                    <span class="text-button" :class="theory.isCompleted ? 'success--text' : 'primary--text'">{{ theory.name }}</span>
                                </v-stepper-step>
                                <v-divider></v-divider>
                            </template>
                            <template
                                    v-for="(test, index) in currentTheme.tests"
                            >
                                <v-stepper-step
                                        edit-icon="mdi-check"
                                        :color="test.isCompleted ? 'success' : test.isAvailable ? 'primary' : 'error'"
                                        :complete="test.isCompleted"
                                        :step="currentTheme.theories.length + index"
                                        @click="testSelect(currentTheme.theories.length + index, test)"
                                >
                                    <v-icon class="mr-2" :color="test.isCompleted ? 'success' : test.isAvailable ? 'primary' : 'error'">mdi-lightbulb-question-outline</v-icon>
                                    <span class="text-button" :class="test.isCompleted ? 'success--text' : test.isAvailable ? 'primary--text' : 'error--text'">{{ test.name }}</span>
                                </v-stepper-step>
                                <v-divider></v-divider>
                            </template>
                        </v-stepper-header>
                        <v-stepper-items>
                            <v-stepper-content
                                    v-for="(theory, index) in currentTheme.theories"
                                    :key="theory.id"
                                    :step="index"
                            >
                                <v-card>
                                    <v-card-title>
                                        {{ theory.name }}
                                    </v-card-title>
                                    <v-card-text>
                                        <div
                                                v-for="block in JSON.parse(theory.content).blocks"
                                                :key="block.id"
                                        >
                                            <pre style="font-size: 14px;" v-if="block.type == 'code'"
                                                 class="line-numbers language-csharp"><code :id="block.id"
                                                                                            v-html="block.data.code"></code></pre>
                                            <ul style="font-size: 17px; line-height: 27px;"
                                                v-else-if="block.type == 'list'">
                                                <li v-for="item in block.data.items" v-html="item"></li>
                                            </ul>
                                            <span v-else-if="block.type == 'header'"
                                                  v-html="`<h${block.data.level}>${block.data.text}</h${block.data.level}>`"></span>
                                            <p style="font-size: 17px; line-height: 27px;" v-html="block.data.text"
                                               v-else></p>
                                        </div>
                                        <v-btn :disabled="theory.isCompleted" :loading="theory.adding" block color="primary mt-5" @click="SendAddTheoryInResult(theory)">Материал прочитан</v-btn>
                                    </v-card-text>
                                </v-card>
                            </v-stepper-content>
                            <v-stepper-content
                                    v-for="(test, index) in currentTheme.tests"
                                    :key="test.id"
                                    :step="currentTheme.theories.length + index"
                            >
                                <v-card>
                                    <v-card-title>
                                        {{ test.name }}
                                    </v-card-title>
                                    <v-card-text>
                                        <div
                                                v-for="block in JSON.parse(test.header).blocks"
                                                :key="block.id"
                                        >
                                            <pre style="font-size: 14px;" v-if="block.type == 'code'"
                                                 class="line-numbers language-csharp"><code :id="block.id"
                                                                                            v-html="block.data.code"></code></pre>
                                            <ul style="font-size: 17px; line-height: 27px;"
                                                v-else-if="block.type == 'list'">
                                                <li v-for="item in block.data.items" v-html="item"></li>
                                            </ul>
                                            <span v-else-if="block.type == 'header'"
                                                  v-html="`<h${block.data.level}>${block.data.text}</h${block.data.level}>`"></span>
                                            <p style="font-size: 17px; line-height: 27px;" v-html="block.data.text"
                                               v-else></p>
                                        </div>
                                        <v-card
                                                v-for="question in test.questions"
                                                :key="question.id"
                                                class="mt-7 pa-6"
                                        >
                                            <div
                                                    v-for="block in JSON.parse(question.text).blocks"
                                                    :key="block.id"
                                            >
                                                <pre style="font-size: 14px;" v-if="block.type == 'code'"
                                                     class="line-numbers language-csharp"><code :id="block.id"
                                                                                                v-html="block.data.code"></code></pre>
                                                <ul style="font-size: 17px; line-height: 27px;"
                                                    v-else-if="block.type == 'list'">
                                                    <li v-for="item in block.data.items" v-html="item"></li>
                                                </ul>
                                                <span v-else-if="block.type == 'header'"
                                                      v-html="`<h${block.data.level}>${block.data.text}</h${block.data.level}>`"></span>
                                                <p style="font-size: 17px; line-height: 27px;" v-html="block.data.text"
                                                   v-else></p>
                                            </div>
                                            <template
                                                    v-if="question.options.filter(option => option.isRightAnswer).length > 1"
                                            >
                                                <template
                                                        v-for="option in question.options"
                                                >
                                                    <div class="d-flex flex-row">
                                                        <div>
                                                            <v-checkbox v-model="option.selectedToAnswer"></v-checkbox>
                                                        </div>
                                                        <div
                                                                v-for="block in JSON.parse(option.value).blocks"
                                                                :key="block.id"
                                                        >
                                                            <pre style="font-size: 14px;" v-if="block.type == 'code'"
                                                                 class="line-numbers language-csharp"><code
                                                                        :id="block.id" v-html="block.data.code"></code></pre>
                                                            <ul style="font-size: 17px; line-height: 27px;"
                                                                v-else-if="block.type == 'list'">
                                                                <li v-for="item in block.data.items" v-html="item"></li>
                                                            </ul>
                                                            <span v-else-if="block.type == 'header'"
                                                                  v-html="`<h${block.data.level}>${block.data.text}</h${block.data.level}>`"></span>
                                                            <p style="font-size: 17px; line-height: 27px;"
                                                               v-html="block.data.text" v-else></p>
                                                        </div>
                                                    </div>
                                                </template>
                                                <v-alert
                                                        v-if="question.isIncorrect"
                                                        dense
                                                        outlined
                                                        type="error"
                                                >
                                                    Прочтите теорию еще раз
                                                </v-alert>
                                                <div class="d-flex flex-row">
                                                    <v-badge
                                                            dark
                                                            bordered
                                                            x-large
                                                            :color="question.isCompleted ? 'success' : question.isIncorrect ? 'error' : 'primary'"
                                                            :icon="question.isCompleted ? 'mdi-check' : question.isIncorrect ? 'mdi-lock' : 'mdi-alert-circle-outline'"
                                                            overlap
                                                            style="height: 30px !important;"
                                                    >
                                                        <v-btn @click="SendAddQuestionInResultCheckbox(question)"
                                                               :class="question.isCompleted ? 'success' : question.isIncorrect ? 'error' : 'primary'"
                                                               class="text-capitalize"
                                                               style="width: 250px !important;"
                                                               :loading="question.adding"
                                                        >
                                                            {{ question.isCompleted ? 'Ответ учтен' : question.isIncorrect ? 'Заблокировано' : 'Ответить' }}
                                                        </v-btn>
                                                    </v-badge>
                                                    <v-chip class="ml-4" v-if="question.isIncorrect" color="error" label>Доступно через: {{ question.incorrectRemainingTime }} секунд</v-chip>
                                                </div>
                                            </template>
                                            <template
                                                    v-if="question.options.filter(option => option.isRightAnswer).length === 1 && question.options.length > 1"
                                            >
                                                <v-radio-group v-model="question.selectedOptionIndex">
                                                    <template
                                                            v-for="(option, index) in question.options"
                                                    >
                                                        <div class="d-flex flex-row">
                                                            <div class="d-flex justify-center align-center">
                                                                <v-radio :key="index" :value="index"></v-radio>
                                                            </div>
                                                            <div
                                                                    v-for="block in JSON.parse(option.value).blocks"
                                                                    :key="block.id"
                                                            >
                                                                <pre style="font-size: 14px;"
                                                                     v-if="block.type == 'code'"
                                                                     class="line-numbers language-csharp"><code
                                                                            :id="block.id"
                                                                            v-html="block.data.code"></code></pre>
                                                                <ul style="font-size: 17px; line-height: 27px;"
                                                                    v-else-if="block.type == 'list'">
                                                                    <li v-for="item in block.data.items"
                                                                        v-html="item"></li>
                                                                </ul>
                                                                <span v-else-if="block.type == 'header'"
                                                                      v-html="`<h${block.data.level}>${block.data.text}</h${block.data.level}>`"></span>
                                                                <p style="font-size: 17px; line-height: 27px;"
                                                                   v-html="block.data.text" v-else></p>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </v-radio-group>
                                                <v-alert
                                                        v-if="question.isIncorrect"
                                                        dense
                                                        outlined
                                                        type="error"
                                                >
                                                    Прочтите теорию еще раз
                                                </v-alert>
                                                <div class="d-flex flex-row">
                                                    <v-badge
                                                            dark
                                                            bordered
                                                            x-large
                                                            :color="question.isCompleted ? 'success' : question.isIncorrect ? 'error' : 'primary'"
                                                            :icon="question.isCompleted ? 'mdi-check' : question.isIncorrect ? 'mdi-lock' : 'mdi-alert-circle-outline'"
                                                            overlap
                                                            style="height: 30px !important;"
                                                    >
                                                        <v-btn @click="SendAddQuestionInResultRadio(question)"
                                                               :class="question.isCompleted ? 'success' : question.isIncorrect ? 'error' : 'primary'"
                                                               class="text-capitalize"
                                                               style="width: 250px !important;"
                                                               :loading="question.adding"
                                                        >
                                                            {{ question.isCompleted ? 'Ответ учтен' : question.isIncorrect ? 'Заблокировано' : 'Ответить' }}
                                                        </v-btn>
                                                    </v-badge>
                                                    <v-chip class="ml-4" v-if="question.isIncorrect" color="error" label>Доступно через: {{ question.incorrectRemainingTime }} секунд</v-chip>
                                                </div>
                                            </template>
                                            <template
                                                    v-if="question.options.filter(option => option.isRightAnswer).length === 1 && question.options.length === 1"
                                            >
                                                <v-text-field v-model="question.answerValue" label="Ответ на вопрос"></v-text-field>
                                                <v-alert
                                                        v-if="question.isIncorrect"
                                                        dense
                                                        outlined
                                                        type="error"
                                                >
                                                    Прочтите теорию еще раз
                                                </v-alert>
                                                <div class="d-flex flex-row">
                                                    <v-badge
                                                            dark
                                                            bordered
                                                            x-large
                                                            :color="question.isCompleted ? 'success' : question.isIncorrect ? 'error' : 'primary'"
                                                            :icon="question.isCompleted ? 'mdi-check' : question.isIncorrect ? 'mdi-lock' : 'mdi-alert-circle-outline'"
                                                            overlap
                                                            style="height: 30px !important;"
                                                    >
                                                        <v-btn @click="SendAddQuestionResultValue(question)"
                                                               :class="question.isCompleted ? 'success' : question.isIncorrect ? 'error' : 'primary'"
                                                               class="text-capitalize"
                                                               style="width: 250px !important;"
                                                               :loading="question.adding"
                                                        >
                                                            {{ question.isCompleted ? 'Ответ учтен' : question.isIncorrect ? 'Заблокировано' : 'Ответить' }}
                                                        </v-btn>
                                                    </v-badge>
                                                    <v-chip class="ml-4" v-if="question.isIncorrect" color="error" label>Доступно через: {{ question.incorrectRemainingTime }} секунд</v-chip>
                                                </div>
                                            </template>
                                        </v-card>
                                    </v-card-text>
                                </v-card>
                            </v-stepper-content>
                        </v-stepper-items>
                    </v-stepper>
                </v-col>
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
            currentCourse: {},
            currentTheme: {},
            modules: [],
            themes: [],
            theories: [],
            tests: [],
            questions: [],
            currentStep: 0,
            completedQuestions: [],
            completedTheories: [],
            contentLoading: false,
            selectedInitialTest: {},
        },
        computed: {
            menuItems: function () {
                let menus = [];
                if (this.currentUser.ID === 0) {
                    menus.push({title: 'Войти', link: `${this.siteUrl}/lmslogin`});
                } else {
                    if (this.currentUser.roles.includes('administrator'))
                        menus.push({title: 'Админка', link: `${this.siteUrl}/wp-admin/admin.php?page=lms_settings`});
                    menus.push({title: 'Профиль', link: `${this.siteUrl}/profile`});
                }
                return menus;
            },
            registrationUrl: function () {
                return this.siteUrl + '/lmslogin';
            },
            completedConcepts: function() {
                return this.completedTheories.map(theory => theory.concepts).flat();
            },
            completedCompetences: function() {
                return this.completedQuestions.map(question => question.competences).flat();
            }
        },
        methods: {
            theorySelect: function (index) {
                this.currentStep = index;
                this.$nextTick()
                    .then(() => {
                        Prism.highlightAll();
                    })
            },
            testSelect: function (index, test) {
                if (!test.isAvailable) return;
                this.currentStep = index;
                this.$nextTick()
                    .then(() => {
                        Prism.highlightAll();
                    });
            },
            themeSelect: function (theme) {
                if (!theme.isAvailable) return;
                this.selectedInitialTest = {}
                this.currentTheme = theme;
                this.currentStep = 1;
                this.$nextTick()
                    .then(() => {
                        this.currentStep = 0;
                        this.$nextTick()
                            .then(() => {
                                Prism.highlightAll();
                            });
                    })
            },
            initialTestSelect: function(test) {
                this.selectedInitialTest = test;
                this.$nextTick()
                    .then(() => {
                        Prism.highlightAll();
                    })
            },
            timeout: function(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            },
            showIncorrectQuestion: function (question) {
                question.adding = false;
                question.isIncorrect = true;
                new Promise(async (resolve) => {
                    for (let i = 15; i >= 0; i --) {
                        question.incorrectRemainingTime = i;
                        await this.timeout(1000);
                    }
                    resolve();
                }).then(() => {
                    question.isIncorrect = false;
                });
            },
            SendAddTheoryInResult: function (theory) {
                theory.adding = true;
              axios.post(`${this.lmsApiUrl}/theory/user/${this.currentUser.ID}/course/${this.currentCourse.id}`, { theory: theory })
                  .then(response => {
                      theory.adding = false;
                      this.completedTheories.push(theory);
                      this.currentStep++;
                  })
            },
            SendAddQuestionInResultCheckbox: function (question) {
                if (question.isCompleted) return;
                if (question.isIncorrect) return;
                question.adding = true;
                let selectedOptions = question.options.filter(option => option.selectedToAnswer);
                let rightOptions = question.options.filter(option => option.isRightAnswer);
                let isRight = selectedOptions.length === rightOptions.length && selectedOptions.every(option => rightOptions.includes(option));
                if (isRight) {
                    axios.post(`${this.lmsApiUrl}/question/user/${this.currentUser.ID}/course/${this.currentCourse.id}`, { question: question })
                        .then(response => {
                            question.adding = false;
                            this.completedQuestions.push(question);
                        })
                } else {
                    this.showIncorrectQuestion(question);
                }
            },
            SendAddQuestionInResultRadio: function (question) {
                if (question.isCompleted) return;
                if (question.isIncorrect) return;
                question.adding = true;
                let rightIndex = question.options.findIndex(option => option.isRightAnswer);
                let isRight = question.selectedOptionIndex === rightIndex;
                if (isRight) {
                    axios.post(`${this.lmsApiUrl}/question/user/${this.currentUser.ID}/course/${this.currentCourse.id}`, { question: question })
                        .then(response => {
                            question.adding = false;
                            this.completedQuestions.push(question);
                        })
                } else {
                    this.showIncorrectQuestion(question);
                }
            },
            SendAddQuestionResultValue: function (question) {
                if (question.isCompleted) return;
                if (question.isIncorrect) return;
                question.adding = true;
                let rightValue = JSON.parse(question.options.find(option => option.isRightAnswer).value).blocks[0].data.text;
                let isRight = question.answerValue === rightValue;
                if (isRight) {
                    axios.post(`${this.lmsApiUrl}/question/user/${this.currentUser.ID}/course/${this.currentCourse.id}`, { question: question })
                        .then(response => {
                            question.adding = false;
                            this.completedQuestions.push(question);
                        })
                } else {
                    this.showIncorrectQuestion(question);
                }
            },
        },
        watch: {
            completedQuestions(newValue) {
                for (const question of this.questions) {
                    question.isCompleted = !!newValue.find(completedQuestion => completedQuestion.id === question.id);
                }
                for (const test of this.tests) {
                    test.isCompleted = test.questions.every(question => question.isCompleted);
                }
                for (const theme of this.themes) {
                    const allRequiredConcepts = theme.concepts.every(concept => this.completedConcepts.find(completedConcept => completedConcept.id === concept.id));
                    const allRequiredCompetences = theme.competences.every(competence => this.completedCompetences.find(completedCompetence => completedCompetence.id === competence.id));
                    theme.isAvailable = allRequiredConcepts && allRequiredCompetences;
                    const allTheoriesCompleted = theme.theories.every(theory => theory.isCompleted);
                    const allTestsCompleted = theme.tests.every(test => test.isCompleted);
                    theme.isCompleted = allTheoriesCompleted && allTestsCompleted;
                }
                this.currentCourse.isCompleted = this.themes.every(theme => theme.isCompleted);
            },
            completedTheories(newValue) {
                for (const theory of this.theories) {
                    theory.isCompleted = !!newValue.find(completedTheory => completedTheory.id === theory.id);
                }
                for (const test of this.tests) {
                    test.isAvailable = test.questions
                        .map(question => question.competences).flat()
                        .map(competence => competence.concepts).flat()
                        .every(concept => this.completedConcepts.find(completedConcept => completedConcept.id === concept.id));
                }
                for (const theme of this.themes) {
                    const allRequiredConcepts = theme.concepts.every(concept => this.completedConcepts.find(completedConcept => completedConcept.id === concept.id));
                    const allRequiredCompetences = theme.competences.every(competence => this.completedCompetences.find(completedCompetence => completedCompetence.id == competence.id));
                    theme.isAvailable = allRequiredConcepts && allRequiredCompetences;
                    const allTheoriesCompleted = theme.theories.every(theory => theory.isCompleted);
                    const allTestsCompleted = theme.tests.every(test => test.isCompleted);
                    theme.isCompleted = allTheoriesCompleted && allTestsCompleted;
                }
                this.currentCourse.isCompleted = this.themes.every(theme => theme.isCompleted);
            }
        },
        async mounted() {
            let getCourseRoute = `${this.lmsApiUrl}/course/<?php echo $_GET['courseId']; ?>`;
            this.contentLoading = true;
            try {
                let response = await axios.get(getCourseRoute);
                this.currentCourse = JSON.parse(response.data).course;
                this.currentCourse.isCompleted = false;
                console.log('course loaded');
                response = await axios.get(`${this.lmsApiUrl}/course/${this.currentCourse.id}/modules`);
                this.currentCourse.modules = JSON.parse(response.data).modules;
                console.log('modules loaded');
                await Promise.all(this.currentCourse.modules.map(async module => {
                    response = await axios.get(`${this.lmsApiUrl}/module/${module.id}/themes`);
                    module.themes = JSON.parse(response.data).themes.map(theme => {
                        theme.isAvailable = true;
                        theme.isCompleted = false;
                        return theme;
                    })
                    await Promise.all(module.themes.map(async theme => {
                        response = await axios.get(`${this.lmsApiUrl}/theme/${theme.id}/theories`);
                        theme.theories = JSON.parse(response.data).theories.map(theory => {
                            theory.isCompleted = false;
                            theory.adding = false;
                            return theory;
                        });
                        await Promise.all(theme.theories.map(async theory => {
                            response = await axios.get(`${this.lmsApiUrl}/theory/${theory.id}/concepts`);
                            theory.concepts = JSON.parse(response.data).concepts;
                        }));
                        response = await axios.get(`${this.lmsApiUrl}/theme/${theme.id}/tests`);
                        theme.tests = JSON.parse(response.data).tests.map(test => {
                            test.isCompleted = false;
                            test.isAvailable = true;
                            return test;
                        });
                        await Promise.all(theme.tests.map(async test => {
                            response = await axios.get(`${this.lmsApiUrl}/test/${test.id}/questions`);
                            test.questions = JSON.parse(response.data).questions.map(question => {
                                question.adding = false;
                                question.selectedOptionIndex = null;
                                question.answerValue = '';
                                question.isIncorrect = false;
                                question.incorrectRemainingTime = 0;
                                return question;
                            });
                            await Promise.all(test.questions.map(async question => {
                                response = await axios.get(`${this.lmsApiUrl}/question/${question.id}/options`);
                                question.options = JSON.parse(response.data).options.map(option => {
                                    option.selectedToAnswer = false;
                                    return option;
                                });
                                response = await axios.get(`${this.lmsApiUrl}/question/${question.id}/competences`);
                                question.competences = JSON.parse(response.data).competences;
                                await Promise.all(question.competences.map(async competence => {
                                    response = await axios.get(`${this.lmsApiUrl}/competence/${competence.id}/concepts`);
                                    competence.concepts = JSON.parse(response.data).concepts;
                                }));
                            }));
                        }));
                        response = await axios.get(`${this.lmsApiUrl}/theme/${theme.id}/concepts`);
                        theme.concepts = JSON.parse(response.data).concepts;
                        response = await axios.get(`${this.lmsApiUrl}/theme/${theme.id}/competences`);
                        theme.competences = JSON.parse(response.data).competences;
                    }));
                }));
                response = await axios.get(`${this.lmsApiUrl}/course/${this.currentCourse.id}/tests`);
                this.currentCourse.tests = JSON.parse(response.data).tests.map(test => {
                    test.isCompleted = false;
                    test.isAvailable = true;
                    return test;
                });
                console.log('initial tests loaded');
                await Promise.all(this.currentCourse.tests.map(async test => {
                    response = await axios.get(`${this.lmsApiUrl}/test/${test.id}/questions`);
                    test.questions = JSON.parse(response.data).questions.map(question => {
                        question.adding = false;
                        question.selectedOptionIndex = null;
                        question.answerValue = '';
                        question.isIncorrect = false;
                        question.incorrectRemainingTime = 0;
                        return question;
                    });
                    await Promise.all(test.questions.map(async question => {
                        response = await axios.get(`${this.lmsApiUrl}/question/${question.id}/options`);
                        question.options = JSON.parse(response.data).options.map(option => {
                            option.selectedToAnswer = false;
                            return option;
                        });
                        response = await axios.get(`${this.lmsApiUrl}/question/${question.id}/competences`);
                        question.competences = JSON.parse(response.data).competences;
                        await Promise.all(question.competences.map(async competence => {
                            response = await axios.get(`${this.lmsApiUrl}/competence/${competence.id}/concepts`);
                            competence.concepts = JSON.parse(response.data).concepts;
                        }));
                    }));
                }));
                this.modules = this.currentCourse.modules;
                this.themes = this.modules.map(module => module.themes).flat();
                this.theories = this.themes.map(theme => theme.theories).flat();
                this.tests = [...this.themes.map(theme => theme.tests).flat(), ...this.currentCourse.tests];
                this.questions = this.tests.map(test => test.questions).flat();
                response = await axios.get(`${this.lmsApiUrl}/questions/user/${this.currentUser.ID}/course/${this.currentCourse.id}`);
                this.completedQuestions = JSON.parse(response.data).questionsId
                        .map(id => id[0])?.map(id => this.questions.find(question => question.id == id))
                    || [];
                response = await axios.get(`${this.lmsApiUrl}/theories/user/${this.currentUser.ID}/course/${this.currentCourse.id}`);
                this.completedTheories = JSON.parse(response.data).theoriesId
                        .map(id => id[0])?.map(id => this.theories.find(theory => theory.id == id))
                    || [];
                this.contentLoading = false;
            } catch (error) {
                console.log(error);
            }
        },
    });
</script>

<style>
    body, html {
        overflow-y: auto;
    }

    .v-badge__badge {
        color: white !important;
    }

    .v-stepper__step:hover {
        cursor: pointer;
        background-color: #ECEFF1 !important;
        transition: .1s;
    }

    .theme--dark.v-badge .v-badge__badge:after {
        border-color: white !important;
    }

    .row {
        margin: 0px !important;
    }

    .col {
        padding: 0px !important;
    }

    .v-navigation-drawer__content {
        width: 300px !important;
    }
</style>