<template>
  <v-card class="pt-3 pb-3" style="min-width: 100%">
    <v-toolbar flat>
      <v-chip small color="primary" class="text-button">Курсы</v-chip>
      <v-spacer></v-spacer>
      <v-dialog :retain-focus="false" v-model="createCourseDialogOpened" max-width="700">
        <template v-slot:activator="{ on, attrs }">
          <v-btn icon v-bind="attrs" v-on="on" class="mb-3"
                 @click="openCourseCreateDialog()">
            <v-icon color="green">mdi-plus</v-icon>
          </v-btn>
        </template>
        <template v-slot:default>
          <v-card class="d-flex flex-column justify-content-center">
            <v-list>
              <v-list-item>
                <v-list-item-content>
                  <v-text-field
                      v-model="createCourseState.courseName"
                      label="название"
                      autofocus
                      @keydown.enter="SendCourseCreateRequest(createCourseState.courseName)">
                  </v-text-field>
                </v-list-item-content>
              </v-list-item>
            </v-list>
            <v-btn :loading="courseCreatingRequest" :disabled="courseCreatingRequest"
                   justify-self="center"
                   @click="SendCourseCreateRequest(createCourseState.courseName)">Save
            </v-btn>
          </v-card>
        </template>
      </v-dialog>
    </v-toolbar>
    <v-divider></v-divider>
    <v-container fluid v-if="coursesLoadingRequest" class="pt-6 pb-6 h-50">
      <v-row justify="center" class="text-button text-uppercase"><span
          style="font-size: 17px;">Загрузка курсов</span>
      </v-row>
      <v-row justify="center">
        <v-col cols="5">
          <v-progress-linear
              color="orange"
              indeterminate
              rounded
              height="6"
          ></v-progress-linear>
        </v-col>
      </v-row>
    </v-container>
    <v-container v-if="!coursesLoadingRequest && courses.length == 0" class="pt-16 pb-16">
      <v-row justify="center" class="text-button text-uppercase">
        <span style="font-size: 18px;">Курсы не найдены</span>
      </v-row>
    </v-container>
    <v-list v-if="!coursesLoadingRequest && courses.length != 0">
      <v-list-group no-action
                    v-for="course in courses"
                    :key="course.name"
                    v-model="course.active"
                    @click="courseModulesOpen(course)"
                    @keydown.delete="(event) => SendCourseDeleteRequest(course, event)">
        <template v-slot:activator>
          <v-list-item class="course-list-group">
            <v-list-item-content>
              <v-list-item-title>
                <v-badge x-small color="grey darken-1" content="название">
                  {{ course.name }}
                </v-badge>
              </v-list-item-title>
            </v-list-item-content>
            <v-list-item-action>
              <v-dialog :retain-focus="false" v-model="course.updateImageDialogOpened"
                        max-width="700">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn v-bind="attrs" v-on="on" icon
                         class="mr-4" @click.stop="updateCourseImageState.url = course.image == null ? '' : course.image">
                    <v-icon>mdi-camera</v-icon>
                  </v-btn>
                </template>
                <template v-slot:default>
                  <v-card class="courseimagedialog pt-4">
                    <v-row class="ma-0" justify="center" align="center">
                      <v-icon v-if="course.image == null" x-large>mdi-school</v-icon>
                      <v-img v-else :src="course.image" contain max-height="200px"></v-img>
                    </v-row>
                    <v-row class="ma-0 ml-4 mr-4">
                      <v-text-field v-model="updateCourseImageState.url" label="image url"></v-text-field>
                    </v-row>
                    <v-row class="ma-0">
                      <v-btn :loading="course.courseImageUpdatingRequest"
                             :disabled="course.courseImageUpdatingRequest"
                             justify-self="center"
                             block
                             @click="SendCourseImageUpdateRequest(course, updateCourseImageState)">
                        Save
                      </v-btn>
                    </v-row>
                  </v-card>
                </template>
              </v-dialog>
            </v-list-item-action>
            <v-list-item-action>
              <v-dialog
                  @click:outside="destroyEditor(course)"
                  :retain-focus="false"
                  v-model="course.updateCourseDescriptionDialog"
                  fullscreen
                  hide-overlay
                  transition="dialog-bottom-transition"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-btn class="mr-4" v-bind="attrs" v-on="on" icon
                         @click="(event) => openCourseUpdateDescriptionDialog(course, event)">
                    <v-icon>mdi-file-document-edit-outline</v-icon>
                  </v-btn>
                </template>
                <template v-slot:default>
                  <v-card
                      flat
                  >
                    <v-toolbar
                        flat
                        color="primary"
                    >
                      <v-toolbar-title>
                        <span
                            class="white--text text-h6 pl-3"
                        >
                          Описание курса
                        </span>
                      </v-toolbar-title>
                      <v-spacer></v-spacer>
                      <v-toolbar-items>
                        <v-btn
                            :loading="course.courseDescriptionUpdating"
                            :disabled="course.courseDescriptionUpdating"
                            @click="SendUpdateCourseDescriptionRequest(course)"
                            color="primary"
                            text
                        >
                          <span class="white--text">Save</span>
                        </v-btn>
                        <v-btn
                            icon
                            @click="closeUpdateCourseDescriptionDialog(course)"
                        >
                          <v-icon color="white">mdi-close</v-icon>
                        </v-btn>
                      </v-toolbar-items>
                    </v-toolbar>
                    <v-row class="ma-0 pa-0" style="height: calc(100vh - 64px) !important; background: #eef5fa;">
                      <v-col class="ma-0 pa-0">
                        <v-card class="ml-5 mr-2 mt-5"
                                style="
                                  min-height: calc(100vh - 105px);
                                  max-height: calc(100vh - 105px);
                                  overflow-y: scroll;
                                  line-height: 27px;
                                  font-size: 17px;
                                "
                        >
                          <div  :id="course.name"></div>
                        </v-card>
                      </v-col>
                      <v-col class="ma-0 pa-0">
                        <v-card class="ml-2 mr-5 mt-5"
                                style="
                                  min-height: calc(100vh - 105px);
                                  max-height: calc(100vh - 105px);
                                  overflow-y: scroll;
                                "
                        >
                          <div
                              v-for="block in course.editorData.blocks"
                              :key="block.id"
                              style="
                                max-width: 80%;
                                margin: 0 auto;
                              "
                          >
                            <pre style="font-size: 14px;" v-if="block.type == 'code'" class="line-numbers language-csharp"><code :id="block.id"></code></pre>
                            <ul style="font-size: 17px; line-height: 27px;" v-else-if="block.type == 'list'">
                              <li v-for="item in block.data.items" v-html="item"></li>
                            </ul>
                            <span v-else-if="block.type == 'header'" v-html="`<h${block.data.level}>${block.data.text}</h${block.data.level}>`"></span>
                            <p style="font-size: 17px; line-height: 27px;" v-html="block.data.text" v-else></p>
                          </div>
                        </v-card>
                      </v-col>
                    </v-row>
                  </v-card>
                </template>
              </v-dialog>
            </v-list-item-action>
            <v-list-item-action>
              <v-dialog :retain-focus="false" v-model="updateCourseDialog"
                        max-width="700">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn v-bind="attrs" v-on="on" icon
                         @click="(event) => openCourseUpdateDialog(course, event)">
                    <v-icon>mdi-pencil</v-icon>
                  </v-btn>
                </template>
                <template v-slot:default>
                  <v-card class="d-flex flex-column justify-content-center">
                    <v-list>
                      <v-list-item>
                        <v-list-item-content>
                          <v-text-field autofocus
                                        @keydown.enter="SendCourseUpdateRequest(updatedCourse, updateCourseState.newCourseName)"
                                        v-model="updateCourseState.newCourseName"
                                        label="name">
                          </v-text-field>
                        </v-list-item-content>
                      </v-list-item>
                    </v-list>
                    <v-btn :loading="courseUpdatingRequest"
                           :disabled="courseUpdatingRequest"
                           justify-self="center"
                           @click="SendCourseUpdateRequest(updatedCourse, updateCourseState.newCourseName)">
                      Save
                    </v-btn>
                  </v-card>
                </template>
              </v-dialog>
            </v-list-item-action>
            <v-list-item-action>
              <v-btn icon :loading="course.deleting"
                     @click="(event) => SendCourseDeleteRequest(course, event)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </v-list-item-action>
          </v-list-item>
        </template>
        <v-divider></v-divider>
        <template v-slot:default>
          <v-window v-model="currentWindow">
            <div class="flex flex-row align-center justify-center" style="width: 100%; height: 50px; display: flex !important;">
              <div style="display: flex; flex-direction: row; align-items: center;">
                <span :class="currentWindow === 0 ? 'primary--text' : 'secondary--text'" class="text-button">Модули</span>
                <v-btn icon @click="currentWindow = 0">
                  <v-icon style="font-size: 32px;">mdi-chevron-left</v-icon>
                </v-btn>
              </div>
              <div style="display: flex; flex-direction: row; align-items: center">
                <v-btn icon @click="currentWindow = 1">
                  <v-icon style="font-size: 32px;">mdi-chevron-right</v-icon>
                </v-btn>
                <span :class="currentWindow === 1 ? 'primary--text' : 'secondary--text'" class="text-button">Начальные тесты</span>
              </div>
            </div>
            <v-window-item>
              <v-sheet rounded class="mr-4 ml-4"
                       style="border-left: 1px solid #E0E0E0; border-right: 1px solid #E0E0E0; border-bottom: 1px solid #E0E0E0;">
                <v-toolbar flat>
                  <v-chip small color="orange" text-color="white" class="text-button">
                    Вложенные модули
                  </v-chip>
                  <v-spacer></v-spacer>
                  <v-dialog :retain-focus="false" v-model="courseModulesDialogOpened"
                            max-width="900">
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn icon class="ml-3" v-bind="attrs" v-on="on"
                             @click="openCourseModulesDialog">
                        <v-icon color="green">mdi-plus</v-icon>
                      </v-btn>
                    </template>
                    <template v-slot:default>
                      <v-card>
                        <v-container fluid v-if="modulesLoading"
                                     class="pt-6 pb-6 h-50">
                          <v-row justify="center"
                                 class="text-button text-uppercase">
                            <span style="font-size: 17px;">Загрузка модулей</span>
                          </v-row>
                          <v-row justify="center">
                            <v-col cols="5">
                              <v-progress-linear
                                  color="orange"
                                  indeterminate
                                  rounded
                                  height="6"
                              ></v-progress-linear>
                            </v-col>
                          </v-row>
                        </v-container>
                        <v-container
                            v-if="!modulesLoading && (modules.length == 0 || getNotContainedCourseModules(lastOpenedCourse).length == 0)"
                            class="pt-16 pb-16">
                          <v-row justify="center"
                                 class="text-button text-uppercase">
                            <span style="font-size: 18px;">Модули для добавления не найдены</span>
                          </v-row>
                        </v-container>
                        <v-container
                            v-else-if="!modulesLoading && modules.length != 0">
                          <v-list>
                            <v-subheader>Модули</v-subheader>
                            <v-list-item
                                v-for="module in getNotContainedCourseModules(lastOpenedCourse)"
                                :key="module.name"
                            >
                              <v-list-item-action>
                                <v-checkbox
                                    v-model="module.selectedToAdd">
                                </v-checkbox>
                              </v-list-item-action>
                              <v-list-item-content>
                                <v-list-item-title>{{ module.name}}
                                </v-list-item-title>
                              </v-list-item-content>
                            </v-list-item>
                          </v-list>
                          <v-row class="mt-4">
                            <v-btn block :loading="courseModulesAdding"
                                   :disabled="courseModulesAdding"
                                   @click="courseModulesAddRequest(lastOpenedCourse)">
                              Save
                            </v-btn>
                          </v-row>
                        </v-container>
                      </v-card>
                    </template>
                  </v-dialog>
                </v-toolbar>
                <v-divider></v-divider>
                <v-container fluid v-if="course.modulesLoading" class="pt-6 pb-6 h-50">
                  <v-row justify="center" class="text-button text-uppercase"><span
                      style="font-size: 15px;">Загрузка модулей</span>
                  </v-row>
                  <v-row justify="center">
                    <v-col cols="5">
                      <v-progress-linear
                          color="orange"
                          indeterminate
                          rounded
                          height="3"
                      ></v-progress-linear>
                    </v-col>
                  </v-row>
                </v-container>
                <v-container v-if="!course.modulesLoading && course.modules.length == 0"
                             class="pt-16 pb-16">
                  <v-row justify="center" class="text-button text-uppercase">
                    <span style="font-size: 15px;">Модули не найдены</span>
                  </v-row>
                </v-container>
                <v-list v-if="!course.modulesLoading && course.modules.length != 0">
                  <v-list-item
                      v-for="module in course.modules"
                      :key="module.name"
                  >
                    <v-list-item-content>
                      <v-list-item-title>{{ module.name }}</v-list-item-title>
                    </v-list-item-content>
                    <v-list-item-action>
                      <v-btn icon :loading="module.deleting"
                             @click="courseModuleDelete(course, module)">
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>
                    </v-list-item-action>
                  </v-list-item>
                </v-list>
              </v-sheet>
            </v-window-item>
            <v-window-item>
              <v-sheet rounded class="mr-4 ml-4 mb-4"
                       style="border-left: 1px solid #E0E0E0; border-right: 1px solid #E0E0E0; border-bottom: 1px solid #E0E0E0;">
                <v-toolbar flat>
                  <v-chip small color="orange" text-color="white" class="text-button">
                    Начальные тесты
                  </v-chip>
                  <v-spacer></v-spacer>
                  <v-dialog :retain-focus="false" v-model="course.courseTestsDialogOpened"
                            max-width="900">
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn icon class="ml-3" v-bind="attrs" v-on="on"
                             @click="openCourseTestsDialog">
                        <v-icon color="green">mdi-plus</v-icon>
                      </v-btn>
                    </template>
                    <template v-slot:default>
                      <v-card>
                        <v-container fluid v-if="course.testsLoading"
                                     class="pt-6 pb-6 h-50">
                          <v-row justify="center"
                                 class="text-button text-uppercase">
                            <span style="font-size: 17px;">Загрузка тестов</span>
                          </v-row>
                          <v-row justify="center">
                            <v-col cols="5">
                              <v-progress-linear
                                  color="orange"
                                  indeterminate
                                  rounded
                                  height="6"
                              ></v-progress-linear>
                            </v-col>
                          </v-row>
                        </v-container>
                        <v-container
                            v-if="!course.testsLoading && (tests.length == 0 || getFilteredCourseTests(lastOpenedCourse).length == 0)"
                            class="pt-16 pb-16">
                          <v-row justify="center"
                                 class="text-button text-uppercase">
                            <span style="font-size: 18px;">Тесты для добавления не найдены</span>
                          </v-row>
                        </v-container>
                        <v-container v-else-if="!course.testsLoading && tests.length != 0">
                          <v-list>
                            <v-subheader>Тесты</v-subheader>
                            <v-list-item
                                v-for="test in getFilteredCourseTests(lastOpenedCourse)"
                                :key="test.name"
                            >
                              <v-list-item-action>
                                <v-checkbox
                                    v-model="test.selectedToAdd"></v-checkbox>
                              </v-list-item-action>
                              <v-list-item-content>
                                <div class="mt-2 mb-2">
                                  <span style="font-family: Roboto, sans-serif; font-size: 16px;">
                                    <v-badge x-small color="grey darken-1" content="название">
                                      {{ test.name }}
                                    </v-badge>
                                  </span>
                                </div>
                              </v-list-item-content>
                            </v-list-item>
                          </v-list>
                          <v-row class="mt-4">
                            <v-btn block :loading="courseTestsAdding"
                                   :disabled="courseTestsAdding"
                                   @click="SendCourseTestsAddRequest(lastOpenedCourse)">
                              Save
                            </v-btn>
                          </v-row>
                        </v-container>
                      </v-card>
                    </template>
                  </v-dialog>
                </v-toolbar>
                <v-divider></v-divider>
                <v-container fluid v-if="course.testsLoading" class="pt-6 pb-6 h-50">
                  <v-row justify="center" class="text-button text-uppercase"><span
                      style="font-size: 15px;">Загрузка тестов</span>
                  </v-row>
                  <v-row justify="center">
                    <v-col cols="5">
                      <v-progress-linear
                          color="orange"
                          indeterminate
                          rounded
                          height="3"
                      ></v-progress-linear>
                    </v-col>
                  </v-row>
                </v-container>
                <v-container v-if="!course.testsLoading && course.tests.length == 0"
                             class="pt-16 pb-16">
                  <v-row justify="center" class="text-button text-uppercase">
                    <span style="font-size: 15px;">Тесты не найдены</span>
                  </v-row>
                </v-container>
                <v-list v-if="!course.testsLoading && course.tests.length != 0">
                  <v-list-item
                      v-for="test in course.tests"
                      :key="test.name"
                  >
                    <v-list-item-content>
                      <div class="mt-2 mb-2">
                        <span
                            style="font-family: Roboto, sans-serif; font-size: 16px;">
                          <v-badge x-small color="grey darken-1"
                                   content="название">
                            {{ test.name }}
                          </v-badge>
                        </span>
                      </div>
                    </v-list-item-content>
                    <v-list-item-action>
                      <v-btn icon :loading="test.deleting"
                             @click="SendCourseTestDeleteRequest(course, test)">
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>
                    </v-list-item-action>
                  </v-list-item>
                </v-list>
              </v-sheet>
            </v-window-item>
          </v-window>
        </template>
      </v-list-group>
    </v-list>
  </v-card>
</template>

<script>
module.exports = {
  name: "v-courses",
  props: {
    siteUrl : String,
  },
  data: function () {
    return {
      coursesLoadingRequest: true,
      courses: [],
      updateCourseState: {
        newCourseName: '',
      },
      updatedCourse: {},
      updateCourseDialog: false,
      courseUpdatingRequest: false,

      createCourseState: {
        courseName: '',
      },
      createCourseDialogOpened: false,
      courseCreatingRequest: false,
      lastOpenedCourse: {},

      courseModulesDialogOpened: false,
      courseModulesAdding: false,

      updateCourseImageState: {
        url: '',
      },

      modules: [],
      modulesLoading: false,

      lastUpdateDescriptionCourse: {},

      currentWindow: 0,

      tests: [],
      testsLoading: false,
      courseTestsAdding: false,
    };
  },
  methods: {
    sanitizeCourse: function (course) {
      return {id: course.id, name: course.name};
    },
    sanitizeModule: function (module) {
      return {id: module.id, name: module.name};
    },

    SendCourseImageUpdateRequest: function(course, state) {
      let body = {
        newUrl: state.url,
      };
      let route = `${this.siteUrl}/wp-json/lms/v1/course/${course.id}/image`;
      course.courseImageUpdatingRequest = true;
      axios.put(route, body)
          .then(response => {
            let updatedCourse = JSON.parse(response.data).updatedCourse;
            course.id = updatedCourse.id;
            course.name = updatedCourse.name;
            course.image = updatedCourse.image;
            course.courseImageUpdatingRequest = false;
            console.log('update course image success');
            console.log(response);
          })
          .catch(error => {
            course.courseImageUpdatingRequest = false;
            console.log('update course image failed');
            console.log(error.response.data.message);
          });
    },

    openCourseUpdateDialog: function (course, event) {
      event.stopPropagation();
      this.updateCourseState.newCourseName = course.name;
      this.updatedCourse = course;
    },
    SendCourseUpdateRequest: function (course, newCourseName) {
      let body = {
        newCourse: {id: course.id, name: newCourseName}
      };
      this.courseUpdatingRequest = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/course/${course.id}`;
      axios.put(route, body)
          .then((response) => {
            let updatedCourse = JSON.parse(response.data).updatedCourse;
            course.id = updatedCourse.id;
            course.name = updatedCourse.name;
            this.courseUpdatingRequest = false;
            this.updateCourseDialog = false;
            console.log('course update success');
            console.log(response);
          })
          .catch((error) => {
            this.courseUpdatingRequest = false;
            console.log('course update failed');
            console.log(error.response.data.message);
          });
    },

    openCourseCreateDialog: function () {
      this.createCourseDialogOpened = true;
      this.createCourseState.courseName = '';
    },
    SendCourseCreateRequest: function (courseName) {
      let body = {
        newCourse: {name: courseName}
      };
      this.courseCreatingRequest = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/course`;
      axios.post(route, body)
          .then((response) => {
            let createdCourse = JSON.parse(response.data).createdCourse;
            this.courses.push({
              id: createdCourse.id,
              name: createdCourse.name,
              description: createdCourse.description,
              image: createdCourse.image,
              updateImageDialogOpened: false,
              courseImageUpdatingRequest: false,
              active: false,
              modules: [],
              deleting: false,
              modulesLoading: false,
              modulesLoaded: false,
              updateCourseDescriptionDialog: false,
              courseDescriptionUpdating: false,
              editor: {},
              editorData: {},
            });
            this.courseCreatingRequest = false;
            this.createCourseDialogOpened = false;
            console.log('create course success');
            console.log(response);
          })
          .catch((error) => {
            this.courseCreatingRequest = false;
            console.log('create course failed');
            console.log(error.response.data.message);
          });
    },

    SendCourseDeleteRequest: function (course, event) {
      event.stopPropagation();
      course.deleting = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/course/${course.id}`;
      axios.delete(route)
          .then((response) => {
            course.deleting = false;
            let deletedCourse = JSON.parse(response.data).deletedCourse;
            this.courses = this.courses.filter(course => course.id !== deletedCourse.id);
            console.log('delete course success');
            console.log(response);
          })
          .catch((error) => {
            course.deleting = false;
            console.log('delete course failed');
            console.log(error.response.data.message);
          });
    },

    courseModulesOpen: function (course) {
      this.lastOpenedCourse = course;
      if (!course.modulesLoaded) {
        course.modulesLoading = true;
        let route = `${this.siteUrl}/wp-json/lms/v1/course/${course.id}/modules`;
        axios.get(route)
            .then((response) => {
              course.modulesLoading = false;
              course.modulesLoaded = true;
              course.modules = JSON.parse(response.data).modules.map(module => {
                module.deleting = false;
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
      }
      if(!course.testsLoaded) {
        course.testsLoading = true;
        let route = `${this.siteUrl}/wp-json/lms/v1/course/${course.id}/tests`;
        axios.get(route)
            .then((response) => {
              course.testsLoading = false;
              course.testsLoaded = true;
              course.tests = JSON.parse(response.data).tests.map(test => {
                test.deleting = false;
                return test;
              });
              console.log('tests loading success');
              console.log(response);
            })
            .catch((error) => {
              course.testsLoading = false;
              console.log('tests loading failed');
              console.log(error.response.data.message);
            });
      }
    },
    courseModuleDelete: function (course, module) {
      module.deleting = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/course/${course.id}/module/${module.id}`;
      axios.delete(route)
          .then((response) => {
            module.deleting = false;
            let deletedModuleId = JSON.parse(response.data).deletedModuleId;
            course.modules = course.modules.filter(currentModule => currentModule.id !== deletedModuleId);
            console.log('course module delete success');
            console.log(response);
          })
          .catch((error) => {
            console.log('course module delete failed');
            module.deleting = false;
            console.log(error.response.data.message);
          });
    },

    openCourseModulesDialog: function () {
      this.courseModulesDialogOpened = true;
      if (this.modules.length == 0) {
        this.modulesLoading = true;
        let route = `${this.siteUrl}/wp-json/lms/v1/modules`;
        axios.get(route)
            .then((response) => {
              this.modulesLoading = false;
              this.modules = JSON.parse(response.data).modules;
              console.log('modules loading success');
              console.log(response);
            })
            .catch((error) => {
              this.modulesLoading = false;
              console.log('modules loading failed');
              console.log(error.response.data.message);
            })
      }
    },
    getNotContainedCourseModules: function (course) {
      return this.modules
          .filter(module => !course.modules.map(currentModule => currentModule.name).includes(module.name))
          .map(module => {
            module.selectedToAdd = false;
            return module;
          });
    },
    courseModulesAddRequest: function (course) {
      this.courseModulesAdding = true;
      let sanitizedModules = this.modules.filter(module => module.selectedToAdd).map(module => this.sanitizeModule(module));
      this.modules.forEach(module => module.selectedToAdd = false);
      let route = `${this.siteUrl}/wp-json/lms/v1/course/${course.id}/modules`;
      axios.post(route, sanitizedModules)
          .then((response) => {
            let addedModules = JSON.parse(response.data).addedModules.map(module => {
              module.deleting = false;
              return module;
            });
            course.modules.push(...addedModules);
            this.courseModulesAdding = false;
            this.courseModulesDialogOpened = false;
            console.log('course modules adding success');
            console.log(response);
          })
          .catch((error) => {
            this.courseModulesAdding = false;
            console.log('course modules adding failed');
            console.log(error.response.data.message);
          })
    },

    async openCourseUpdateDescriptionDialog(course, event) {
      event.stopPropagation();
      this.lastUpdateDescriptionCourse = course;
      course.updateCourseDescriptionDialog = true;
      course.editorData = JSON.parse(course.description);
      await this.$nextTick();
      const editor = new EditorJS({
        holder: course.name,
        data: course.editorData,
        tools: {
          code: {
            class: CodeTool,
            shortcut: 'CTRL+SHIFT+C',
          },
          header: {
            class: Header,
            shortcut: 'CTRL+SHIFT+H',
          },
          list: {
            class: List,
            inlineToolbar: true,
            config: {
              defaultStyle: 'unordered'
            },
            shortcut: 'CTRL+SHIFT+V',
          }
        },
        onChange: this.editorChanged
      });
      await editor.isReady;
      course.editor = editor;
      let filteredBlocks = course.editorData.blocks.filter(block => block.type == 'code');
      for (const filteredBlock of filteredBlocks) {
        document.getElementById(filteredBlock.id).innerHTML = filteredBlock.data.code;
      }
      Prism.highlightAll();
    },
    editorChanged: function() {
      this.lastUpdateDescriptionCourse.editor.save()
          .then((outputData) => {
            this.lastUpdateDescriptionCourse.editorData = outputData;
            this.$nextTick()
                .then(() => {
                  let filteredBlocks = this.lastUpdateDescriptionCourse.editorData.blocks.filter(block => block.type == 'code');
                  for (const filteredBlock of filteredBlocks) {
                    document.getElementById(filteredBlock.id).innerHTML = filteredBlock.data.code;
                  }
                  Prism.highlightAll();
                });
          });
    },
    destroyEditor: function (course){
      course.editor.destroy();
    },
    closeUpdateCourseDescriptionDialog: function(course) {
      this.destroyEditor(course);
      course.updateCourseDescriptionDialog = false;
    },
    SendUpdateCourseDescriptionRequest: function (course) {
      course.courseDescriptionUpdating = true;
      course.editor.save()
          .then(outputData => {
            let body = {
              newDescription: JSON.stringify(outputData)
                  .replace(/\\n/g, '\\\\n')
                  .replace(/\\"/g, '\\\\"')
                  .replace(/\\t/g, '\\\\t')
                  .replace(/'/g, "\\'")
            };
            let config = {
              headers: {
                'Content-Type': 'application/json'
              }
            };
            let route = `${this.siteUrl}/wp-json/lms/v1/course/${course.id}/description`;
            axios.put(route, body, config)
                .then((response) => {
                  let updatedCourse = JSON.parse(response.data).updatedCourse;
                  course.id = updatedCourse.id;
                  course.name = updatedCourse.name;
                  course.description = updatedCourse.description;
                  course.courseDescriptionUpdating = false;
                  course.updateCourseDescriptionDialog = false;
                  course.editor.blocks.clear();
                  course.editor.destroy();
                  console.log('course update success');
                  console.log(response);
                })
                .catch((error) => {
                  course.courseDescriptionUpdating = false;
                  console.log('course update failed');
                  console.log(error.response.data.message);
                });
          })

    },

    sanitizeTest: function(test) {
      return {id: test.id, name: test.name};
    },
    openCourseTestsDialog: function (course) {
      course.courseTestsDialogOpened = true;
      if (this.tests.length == 0) {
        this.testsLoading = true;
        let route = `${this.siteUrl}/wp-json/lms/v1/tests`;
        axios.get(route)
            .then((response) => {
              this.testsLoading = false;
              this.tests = JSON.parse(response.data).tests;
              console.log('tests loading success');
              console.log(response);
            })
            .catch((error) => {
              this.testsLoading = false;
              console.log('tests loading failed');
              console.log(error.response.data.message);
            })
      }
    },
    getFilteredCourseTests: function (course) {
      return this.tests
          .filter(test => !course.tests.map(currentTest => currentTest.name).includes(test.name))
          .map(test => {
            test.selectedToAdd = false;
            return test;
          });
    },
    SendCourseTestsAddRequest: function (course) {
      this.courseTestsAdding = true;
      let sanitizedTests = this.tests.filter(test => test.selectedToAdd).map(test => this.sanitizeTest(test));
      this.tests.forEach(test => test.selectedToAdd = false);
      let route = `${this.siteUrl}/wp-json/lms/v1/course/${course.id}/tests`;
      let body = {
        tests: sanitizedTests
      };
      axios.post(route, body)
          .then((response) => {
            let addedTests = JSON.parse(response.data).addedTests.map(test => {
              test.deleting = false;
              return test;
            });
            course.tests.push(...addedTests);
            this.courseTestsAdding = false;
            course.courseTestsDialogOpened = false;
            console.log('course tests adding success');
            console.log(response);
          })
          .catch((error) => {
            console.log(error);
            this.courseTestsAdding = false;
            console.log('course tests adding failed');
            console.log(error.response.data.message);
          })
    },
    SendCourseTestDeleteRequest: function (course, test) {
      test.deleting = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/course/${course.id}/test/${test.id}`;
      axios.delete(route)
          .then((response) => {
            test.deleting = false;
            let deletedTestId = JSON.parse(response.data).deletedTestId;
            course.tests = course.tests.filter(currentTest => currentTest.id !== deletedTestId);
            console.log('course test delete success');
            console.log(response);
          })
          .catch((error) => {
            test.deleting = false;
            console.log('course test delete failed');
            console.log(error.response.data.message);
          });
    },
  },
  mounted() {
    axios.get(`${this.siteUrl}/wp-json/lms/v1/courses`)
        .then((response) => {
          this.courses = response.data.map(course => {
            course.active = false;
            course.updateImageDialogOpened = false;
            course.courseImageUpdatingRequest = false;
            course.modules = [];
            course.deleting = false;
            course.modulesLoaded = false;
            course.modulesLoading = false;
            course.updateCourseDescriptionDialog = false;
            course.editor = {};
            course.editorData = {};
            course.courseDescriptionUpdating = false;
            course.tests = [];
            course.testsLoading = false;
            course.testsLoaded = false;
            course.courseTestsDialogOpened = false;
            return course;
          });
          this.coursesLoadingRequest = false;
        });
  }
}
</script>

<style>

</style>